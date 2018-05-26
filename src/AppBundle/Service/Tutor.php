<?php

namespace AppBundle\Service;

use AppBundle\Entity\AttemptedWord;
use AppBundle\Entity\Game;
use AppBundle\Entity\Message;
use AppBundle\Entity\Student;
use AppBundle\Entity\User;
use AppBundle\Entity\Word;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Tutor extends Controller {
    private $question, $student, $mood, $helpfulness, $session, $em, $control;

    public function __construct(Session $session, EntityManager $em){
        $this->session = $session;
        $this->em = $em;
    }

    public function prepareFor(Student $student){
        $this->student = $student;
        $this->control = $this->student->getExperimental() ? "" : "CONTR_";
    }

    public function welcomeStudent(){
        if (count($this->student->getGames()) < 1){
            $message = $this->getMessage( $this->control."WELCOME_INIT");
            $hint = $this->getMessage("WELCOME_INIT_HINT");
        } else if ($time= $this->student->getActiveGame()->getTimePlayed() > 3){
            $message = $this->getMessage( $this->control."WELCOME_RESUME");
            $hint = $this->getMessage("WELCOME_RESUME_HINT");
        } else {
            $message = $this->getMessage( $this->control."WELCOME");
            $hint = $this->getMessage("WELCOME_HINT");
        }

        $this->insertValuesInMessages([$message, $hint]);
        return [$message->toArray(), $hint->toArray()];
    }

    public function messageForQuestion($question){
        $answer_type = $question["answer_type"];
        $message = $this->getMessage($answer_type);
        $hint = NULL;
        $helped = $this->em->getRepository(AttemptedWord::class)->findOneBy(['id'=>$question['target_word'], 'helped'=>1], ['id' => 'DESC']);
        if ($helped){
            $hint = $this->getMessage("HELPED_HINT");
        }
        return [$message->toArray(), $hint== NULL ? NULL : $hint->toArray()];

    }

    public function appropriateResponse($answer, $is_correct_answer, $question){
        $this->question = $question;
        if ($this->control){
            if ($answer->skipped == 1){
                $message = $this->getMessage("CONTR_SKIPPED");
                $this->insertValuesInMessage($message);
                return [$message->toArray()];
            }
            return ["controlgroup"];
        }
        if ($answer->skipped == 1){
            $message = $this->getMessage("SKIPPED");
            $hint = $this->getMessage("SKIPPED_HINT");
        } else{
            switch ($is_correct_answer){
                case 0:
                    $message = $this->getMessage("ANS_INCORR");
                    $hint = $this->getMessage("ANS_INCORR_HINT");
                    break;
                case 1:
                    $message = $this->getMessage("ANS_CORR");
                    $hint = $this->getMessage("ANS_CORR_HINT");
                    $this->decreaseHelpfulness();
                    break;
                case 2:
                    $message = $this->getMessage("ANS_ALTCORR");
                    $hint = $this->getMessage("ANS_ALTCORR_HINT");
                    break;
            }
        }

        $this->insertValuesInMessages([$message, $hint]);
        return [$message->toArray(), $hint->toArray()];
    }


    public function checkOnStudent(){

    }


    public function getHelpfulness(){
        return $this->session->get('helpfulness');
    }

    public function increaseHelpfulness(){
        if ($this->session->get('helpfulness') < 1){
            $helpfulness = $this->session->get('helpfulness') + 0.1;
            $this->session->set('helpfulness', $helpfulness);
        }
    }

    public function decreaseHelpfulness(){
        if ($this->session->get('helpfulness') > 0){
            $helpfulness = $this->session->get('helpfulness') - 0.02;
            $this->session->set('helpfulness', $helpfulness);
        }
    }

    public function getMessage($type){
        $messages = $this->em->getRepository(Message::class)->findBy(['type' => $type]);
        return $messages[rand(0, count($messages)-1)];
    }

    private function determineMood(){

    }

    private function determineHelpfulness(){

    }

    private function insertValuesInMessages($messages){
        foreach ($messages as $message){
            $this->insertValuesInMessage($message);
        }
    }

    private function insertValuesInMessage(Message $message){
        preg_match('/(%[A-z]+)/', $message->getText(), $matches);
        if (!empty( $matches)){
            $placeholders = $matches[0];
            if (is_array($placeholders)){
                foreach ($placeholders as $placeholder){
                    $f = "insert".ucwords(ltrim($placeholder, '%'));
                    if (method_exists($this, $f)){
                        $this->$f($message);
                    }
                }
            } else{
                $f = "insert".ucwords(ltrim($placeholders, '%'));
                if (method_exists($this, $f)){
                    $this->$f($message);
                }
            }
        }
    }

    private function insertFirstname(Message $message){
        $firstname = $this->student->getUser()->getUserProfile()->getFirstName();
        $message->setText( preg_replace('/(%firstname)/', $firstname, $message->getText()));
    }

    private function insertTargetWord(Message $message){
        $target = $this->em->getRepository(Word::class)->find($this->question['target_word']);
        $message->setText( preg_replace('/(%targetword)/', $target->getText(), $message->getText()));
    }
}