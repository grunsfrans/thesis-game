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
use AppBundle\Entity\Student;
use AppBundle\Entity\Word;
use AppBundle\Controller\GameInitializer;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\WordService;


class GameController extends Controller{

    private $student, $tutor, $game, $score_change, $time_bonus, $level_change, $target_word, $wordservice;



    public function landingAction(Request $request, Session $session){
        $this->initGameParams();
        return $this->render('game/landing.html.twig', ['load_btn' => $this->game !== NULL]);
    }

    public function newGameAction(Request $request){
        $this->initGameParams();
        $this->newGame();
        return $this->redirectToRoute('game_play');
    }


    public function playAction(Request $request, Session $session){
        $this->initGameParams();
        if ($this->game){
            $tutor_output = $this->tutor->welcomeStudent();
            return $this->render('game/play.html.twig', [ 'tutor_output' => json_encode($tutor_output),
                'level' => $this->game->getLevel(),
                'time' => $this->getTimeLeftAsString(),
                'score' => $this->game->getScore() ]);
        }
        return $this->redirectToRoute('game_landing');
    }

    public function endGameAction(Request $request){
        $this->initGameParams();
        $playtime = json_decode($request->getContent())->playtime;
        $this->game->increaseTimePlayed($playtime);
        $this->endGame();
        return $this->render('game/landing.html.twig', ['load_btn' => FALSE]);
    }

    public function nextQuestionAction(Request $request, Session $session, WordService $ws){
        $this->initGameParams();
//        print_r($session->get('helpfulness'));
        $this->wordservice = $ws;
        $question = $this->getNextQuestion();
        $session->set("question", ['word' => $question['word'], 'target_word' => $question['target_word'], 'answer_type' => $question['answer_type']]);
        $response = [   'tutor_output' => $this->tutor->messageForQuestion($question),
                        'answer_type' => $question['answer_type'],
                        'word' => $question['word'],
                        'options' => $question['options']];
        return new JsonResponse($response);
    }

    public function checkAnswerAction(Request $request, Session $session){
        $this->initGameParams();
        $answer = json_decode($request->getContent());
        $question = $session->get("question");
        $is_correct_answer = $this->checkAnswerType($question, $answer);
        $this->update($question, $answer, $is_correct_answer);
        return $this->appropriateResponse($answer, $is_correct_answer, $question);
    }

    public function helpAction(Request $request, Session $session){
        $this->initGameParams();
        $this->tutor->increaseHelpfulness();
        $wordid = $session->get('question')['target_word'];
        $word = $this->getDoctrine()->getRepository(Word::class)->find($wordid)->getText();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://translate.yandex.net/api/v1.5/tr.json/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            "key=trnsl.1.1.20180513T094640Z.8cef691330965029.04d88ddf00f3b83c7c400e9c1161da8b5cbc2138&amp;text=".$word."&amp;lang=en-nl");
        $resp = curl_exec($ch);
        if ($this->student->getExperimental()){
            $resp = substr($resp, 0, -1) . ', "exp" : 1}';
        }
        return new JsonResponse( $resp );

    }

    private function initGameParams(){
        if(parent::getUser()){
            $this->student = parent::getUser()->getStudent();
            $this->tutor = $this->get('tutor');
            $this->tutor->prepareFor($this->student);
            $this->game = $this->findActiveGame();
        }
    }

    private function getNextQuestion(){
        $target_word = $this->wordservice->getWordForStudent($this->student, $this->tutor->getHelpfulness());
        $answer_type = $this->randomAnswerType();
        $question = $this->generateQuestion($target_word, $answer_type);
        return $question;
    }

    private function generateQuestion(Word $target_word, $answer_type){
        $options = [];
        $presented_word ='';
        switch ($answer_type){
            case "CORR_INCORR":
                $presented_word = $this->wordservice->swapLettersWithProbability($target_word);
                break;
            case "PICK_CORR":
                $options = $this->wordservice->twoWordsFrom($target_word);
                break;
            case "GUESS_SHUFF":
                $presented_word = $this->wordservice->shuffleLetters($target_word);
                break;
            case "GUESS_DOTS":
                $presented_word = $this->wordservice->redactLetters($target_word);
                break;
            default:
                break;
        }
        return ['answer_type' => $answer_type,
                'word' => $presented_word,
                'target_word' => $target_word->getId(),
                'options' => $options ];
    }

    private function appropriateResponse($answer,$is_correct_answer, $question){
        $target =  $this->getDoctrine()->getRepository(Word::class)->find($question['target_word'])->getText();
        $response = [
            'answer' => $question['answer_type'] == "CORR_INCORR" ? $question['word'] : $answer->answer,
            'correct' => $is_correct_answer,
            'reward' => ['score' => $this->score_change,'time_bonus' => $this->time_bonus, 'level' => $this->level_change],
            'tutor_output' => $this->tutor->appropriateResponse($answer, $is_correct_answer, $question),
            'target' => $target,
            'game_over' => $this->game->getTimePlayed() >= 300
        ];
        return new JsonResponse($response);
    }

    private function update($question, $answer, $is_correct_answer){
        $this->updateGame($question, $answer, $is_correct_answer);
        $this->updateStudent($question, $answer, $is_correct_answer);
    }

    private function skipQuestion(){

    }

//$this->getDoctrine()->getRepository(Game::class)->findOneBy(['student' => $student->getID(),
//'active' => 1]);
    private function findActiveGame(){
        if ( $this->student != NULL  && $game = $this->student->getActiveGame()){
            return $game;
        }
        return NULL;
    }

    private function newGame(){
        $this->endGame();
        $this->game = new Game();
        $this->game->setStudent($this->student);
        $this->game->setActive(TRUE);
        $this->store($this->game);
    }

    private function updateGame($question, $answer, $is_correct_answer){
        if($answer->skipped == 0){
            $this->score_change = $this->determineScore($is_correct_answer);
            $this->time_bonus = $this->score_change > 0 && $answer->reactiontime < 3;
            $this->score_change+= $this->time_bonus;
            $this->game->setScore($this->game->getscore() + $this->score_change);
            $this->level_change = $this->adjustLevel();
        }
        $this->game->increaseTimePlayed($answer->reactiontime);
        $this->store($this->game);

    }

    private function endGame(){
        if ($this->game){
            $this->game->setActive(FALSE);
            $this->store($this->game);
        }
    }

    private function updateStudent($question, $answer, $is_correct_answer){
        $attempted_word = new AttemptedWord($this->student, $this->target_word, $answer, $is_correct_answer);
        if ($answer->skipped != 1 && !$is_correct_answer){
            $this->student->increaseErrors();
        }
        $this->student->setCurrentLevel($this->game->getLevel());
        if ($this->student->getHighestLevel() < $this->game->getLevel()){
            $this->student->setHighestLevel($this->game->getLevel());
        }
        $this->student->increaseTimePlayed($answer->reactiontime);
        $this->store($this->student);
        $this->store($attempted_word);
    }


    private function determineWordDifficulty(Word $word){

    }


    private function adjustScore($is_correct_answer, $question){
        $score = $this->determineScore($is_correct_answer);
        $this->game->setScore($this->game->getscore() + $score);
        return $score;
    }

    private function determineScore($is_correct_answer){
        if ($is_correct_answer){
            $score = 1 *  $this->game->getLevel();
        } else{
            $score = -1 * $this->game->getLevel();
        }
        return $score;
    }


    private function adjustLevel(){
        $score = $this->game->getScore();
        $current_level = $this->game->getLevel();
        if ($score / ( 10 * pow(2, $current_level-1)) > 1){
            $this->game->setLevel($current_level + 1);
        } elseif ($score / ( 10 * pow(2, $current_level-1)) <= 0.5  && $current_level != 1){
            $this->game->setLevel($current_level - 1);
        }
        return $this->game->getLevel() - $current_level;
    }


    private function getTimeLeftAsString(){
        $time = 300 - $this->game->getTimePlayed();
        $mins = floor($time / 60);
        $secs = $time - $mins * 60;
        $extrazero = $secs < 10 ? '0' : '';
        return $mins.":".$extrazero.$secs;
    }

    private function store($entity){
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
    }

    private function randomAnswerType(){
        $types = ["CORR_INCORR","PICK_CORR", "GUESS_SHUFF", "GUESS_DOTS", "GUESS_SHUFF", "GUESS_DOTS", "GUESS_DOTS", "GUESS_DOTS"];
        return $types[rand(0, $this->game->getLevel() -1)];
    }

    /* Check answer types */

    private function checkAnswerType($question, $answer){
        $this->target_word  = $this->getDoctrine()->getRepository(Word::class)->find($question["target_word"]);
        switch ($question['answer_type']){
            case "CORR_INCORR":
                $response = $this->checkCorrectIncorrect($question, $answer);
                break;
            case "PICK_CORR":
                $response = $this->checkPickCorrect($question,$answer);
                break;
            case "GUESS_SHUFF":
            case "GUESS_DOTS":
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

    private function checkPickCorrect($question, $answer){
        $same_as_target = $this->target_word->getText() == strtolower($answer->answer);
        if ($same_as_target){
            $response = 1;
        } else{
            $response = 0;
        }
        return $response;
    }

    private function checkGuessFull($question, $answer){
        $same_as_target = $this->target_word->getText() == strtolower($answer->answer);
        if ($same_as_target){
            $response = 1;
        } else{
            $existing_word = $this->getDoctrine()->getRepository(Word::class)->findOneBy(["text" => $answer->answer, "length" => $this->target_word->getLength()]);
            if (!empty($existing_word)){
                $response = 2;
            } else{
                $response = 0;
            }
        }
        return $response;
    }

}
