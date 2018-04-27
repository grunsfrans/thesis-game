<?php

namespace AppBundle\Service;

use AppBundle\Entity\Student;
use AppBundle\Entity\Word;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Tutor {
    private $question, $student, $mood, $helpfulness;

    public function __construct(SessionInterface $session){

    }

    public function textForQuestion(){

    }

    public function checkOnStudent(){

    }

    public function giveHint(){
        
    }

    private function determineMood(){

    }

    private function determineHelpfulness(){

    }

}