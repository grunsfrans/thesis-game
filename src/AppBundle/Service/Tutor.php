<?php

namespace AppBundle\Service;

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
        $this->control = $this->student->getExperimental() ? "" : "CONT_";
    }

    public function welcomeStudent(){
        $message = $this->getMessage( $this->control."WELCOME");
        $hint = $this->getMessage("WELCOME_HINT");
        $this->insertValuesInMessage($message);
        return [$message->toArray(), $hint->toArray()];
    }

    public function messageForQuestion($question){
        $answer_type = $question["answer_type"];
        $message = $this->getMessage($answer_type);
        return [$message->toArray(), NULL];

    }

    public function appropriateResponse($answer, $is_correct_answer, $question){
        $this->question = $question;
        if ($this->control){
            if ($answer->skipped == 1){
                $message = $this->getMessage("CONT_SKIPPED");
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