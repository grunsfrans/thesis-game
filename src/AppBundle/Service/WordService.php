<?php

namespace AppBundle\Service;

use AppBundle\Entity\Student;
use AppBundle\Entity\Word;
use Doctrine\ORM\EntityManager;

class WordService
{
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getWordForStudent(Student $student){
        return $this->randomWord();
    }

    public function randomWord(){
        $random_id = rand(1,5000);
        $word = $this->em->getRepository(Word::class)->find($random_id);
        return $word;
    }


    public function misspellWord(Word $word){

    }

    public function changeLetters(Word $word){
        $functions = ["redactLetters", "swapRandomLetters"];
        $f = $functions[rand(0,count($functions)-1)];
        return $this->$f($word);
    }

    public function redactLetters(Word $word){
        $redacted = $word->getText();
        $number_of_redactions = rand(1, $word->getLength() -2);
        $redact_positions = $this->randomPositionsInWord($word, $number_of_redactions);
        foreach ($redact_positions as $position){
            $redacted = substr_replace($redacted, '.', $position, 1);
        }
        return $redacted;
    }

    public function swapCombinations(Word $word){
        $swapped = $word->getText();
        $combinations = ["ae","ea","th","gh","io","oi"];
        foreach ($combinations as $combination){
            if($pos = strpos($swapped, $combination)){
                if ($pos > 0){
                    $swapped = str_replace($combination, str_shuffle(substr($swapped, $pos, strlen($combination))), $swapped);
                    break;
                }
            }
        }
        return $swapped;
    }

    public function swapRandomLetters(Word $word){
        $text = $word->getText();
        if ( $position = rand(1 , $word->getLength()-3) ){
            $to_be_swapped = substr($text, $position, rand(2, $word->getLength() - $position - 1));
            $text = str_replace($to_be_swapped, str_shuffle($to_be_swapped), $text);
        }
        return $text;
    }


    private function randomPositionsInWord(Word $word, $number){
        $positions = [];
        while(count($positions)< $number){
          $position = rand(0, $word->getLength());
          if (!in_array($position,$positions)){
              array_push($positions, $position);
          }
        }
        return $positions;
    }
}