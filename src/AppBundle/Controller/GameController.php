<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 01-12-17
 * Time: 16:44
 */

namespace AppBundle\Controller;
use AppBundle\Entity\AttemptedWord;
use AppBundle\Entity\Game;
use AppBundle\Entity\Word;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\WordService;

class GameController extends Controller{

    private $game, $score_change, $level_change, $target_word, $wordservice;

    public function landingAction(Request $request){
        $this->game = $this->findActiveGame();
        return $this->render('game/landing.html.twig', ['load_btn' => $this->game !== NULL]);
    }

    public function newGameAction(Request $request){
        $this->newGame();
        return $this->redirectToRoute('game_play');
    }


    public function playAction(Request $request){
        $this->game = $this->findActiveGame();
        return $this->render('game/play.html.twig', ['level' => $this->game->getLevel(), 'score' => $this->game->getScore() ]);
    }

    public function endGameAction(Request $request){
        $this->endGame();
        return $this->render('game/landing.html.twig', ['load_btn' => FALSE]);
    }

    public function nextQuestionAction(Request $request, SessionInterface $session, WordService $ws){
        $this->wordservice = $ws;
        $question = $this->getNextQuestion();
        $session->set("question", ['word' => $question['word'], 'target_word' => $question['target_word'], 'answer_type' => $question['answer_type']]);
        $response = [   'message' => $question['message'],
                        'answer_type' => $question['answer_type'],
                        'word' => $question['word'],
                        'options' => $question['options']];
        return new JsonResponse($response);
    }

    public function checkAnswerAction(Request $request, SessionInterface $session){
        $answer = json_decode($request->getContent());
        $question = $session->get("question");
        $is_correct_answer = $this->checkAnswerType($question, $answer);
        $this->update($question, $answer, $is_correct_answer);
        return $this->appropriateResponse($answer->answer, $is_correct_answer, $question);
    }


    private function getNextQuestion(){
        $student = $this->getUser()->getStudent();
        $target_word = $this->wordservice->getWordForStudent($student);
        $answer_type = $this->randomAnswerType();
        $question = $this->generateQuestion($target_word, $answer_type);
        return $question;
    }

    private function generateQuestion(Word $target_word, $answer_type){
        $question_msg = ['type'=> "question", 'mood' => ''];
        $presented_word = "";
        $options = [];
        switch ($answer_type){
            case "CORRECT_INCORRECT":
                $question_msg['text'] = "Is het woord goed of fout gespeld?";
                $presented_word = $this->wordservice->swapCombinations($target_word);
                break;
            case "GUESS_FULL":
                $question_msg['text'] = "Welk woord staat hier?";
                $presented_word = $this->wordservice->changeLetters($target_word);
                break;
            default:
                break;
        }

        return ['message' => $question_msg,
                'answer_type' => $answer_type,
                'word' => $presented_word,
                'target_word' => $target_word->getId(),
                'options' => $options ];
    }

    private function appropriateResponse($answer,$is_correct_answer, $question){
        if ( $is_correct_answer){
            $message = ['type' => "exclamation" ,'mood' => "pos", 'text' => "Goedzo!"];
            $extra = ['type' => "exclamation" ,'mood' => "pos", 'text' => "Je bent geweldig!"];
        } else{
            $message = ['type' => "exclamation" ,'mood' => "neg", 'text' => "Oeps! Volgende keer beter." ];
            $extra = ['type' => "" ,'mood' => "neg", 'text' => "Het woord was: <b>" . $this->target_word->getText() . "</b>"];
        }
        $response = [
            'answer' => $question['answer_type'] == "CORRECT_INCORRECT" ? $question['word'] : $answer,
            'correct' => $is_correct_answer,
            'reward' => ['score' => $this->score_change, 'level' => $this->level_change],
            'message' => $message,
            'extra' => $extra,
            'target' => $question['target_word']
        ];

        return new JsonResponse($response);
    }

    private function update($question, $answer, $is_correct_answer){
        $this->updateGame($question, $answer, $is_correct_answer);
        $this->updateStudent($question, $answer, $is_correct_answer);

    }

    private function findActiveGame(){
        if ( $this->getUser() ){
            $student = $this->getUser()->getStudent();
            $game = $this->getDoctrine()->getRepository(Game::class)->findOneBy(['student' => $student->getID(),
                                                                                                 'active' => 1]);
        } else{
            $game = NULL;
        }
        return $game;
    }

    private function newGame(){
        $this->endGame();
        $game = new Game();
        $student = $this->getUser()->getStudent();
        $game->setStudent($student);
        $game->setActive(TRUE);
        $this->store($game);
    }

    private function updateGame($question, $answer, $is_correct_answer){
        $this->game = $this->findActiveGame();
        $this->game->increaseTimePlayed($answer->reactiontime);
        $this->score_change = $this->adjustScore($is_correct_answer, $question);
        $this->level_change = $this->adjustLevel();
        $this->store($this->game);

    }

    private function endGame(){
        $game = $this->findActiveGame();
        if ($game){
            $game->setActive(FALSE);
            $this->store($game);
        }
    }

    private function updateStudent($question, $answer, $is_correct_answer){
        $student = $this->getUser()->getStudent();
        $attempted_word = new AttemptedWord($student, $this->target_word, $answer, $is_correct_answer);
        if (!$is_correct_answer){
            $student->increaseErrors();
        }
        $student->setCurrentLevel($this->game->getLevel());
        if ($student->getHighestLevel() < $this->game->getLevel()){
            $student->setHighestLevel($this->game->getLevel());
        }
        $student->increaseTimePlayed($answer->reactiontime);
        $this->store($student);
        $this->store($attempted_word);
    }


    private function determineDifficulty($question){

    }


    private function adjustScore($is_correct_answer, $question){
        $score = $this->determineScore($is_correct_answer);
        $this->game->setScore($this->game->getscore() + $score);
        return $score;
    }

    private function determineScore($is_correct_answer, $difficulty = 1){
        if ($is_correct_answer){
            $score = 1 * $difficulty;
        } else{
            $score = 0;
        }
        return $score;
    }


    private function adjustLevel(){
        $score = $this->game->getScore();
        $current_level = $this->game->getLevel();
        if ($score / ( 25 * pow(2, $current_level-1)) >= 1){
            $this->game->setLevel($current_level + 1);
        }
        return $this->game->getLevel() - $current_level;
    }



    private function store($entity){
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
    }

    private function randomAnswerType(){
        $types = ["CORRECT_INCORRECT", "GUESS_FULL"];
        return $types[rand(0, count($types) -1)];
    }

    /* Check answer types */

    private function checkAnswerType($question, $answer){
        $this->target_word  = $this->getDoctrine()->getRepository(Word::class)->find($question["target_word"]);
        switch ($question['answer_type']){
            case "CORRECT_INCORRECT":
                $response = $this->checkCorrectIncorrect($question, $answer);
                break;
            case "GUESS_FULL":
                $response = $this->checkGuessFull($question, $answer);
                break;
            default:
                $response = new JsonResponse(array('correct' => -1, 'text' => 'Fout bij nakijken, type onbekend.'  ));
                break;
        }
        return $response;
    }

    private function checkCorrectIncorrect($question, $answer){
        $is_correct_word = $this->target_word->getText() == $answer->word;
        if (($is_correct_word && $answer->answer == 'correct') || (!$is_correct_word && $answer->answer == 'incorrect')){
            $is_correct_answer = 1;
        } else{
            $is_correct_answer = 0;
        }
        return $is_correct_answer;
    }

    private function checkGuessFull($question, $answer){
        $same_as_target = $this->target_word->getText() == strtolower($answer->answer);
        if ($same_as_target){
            $response = 1;
        } else{
            $existing_word = $this->getDoctrine()->getRepository(Word::class)->findOneBy(["text" => $answer->answer]);
            if (!empty($existing_word)){
                $response = 2;
            } else{
                $response = 0;
            }
        }
        return $response;
    }

}
