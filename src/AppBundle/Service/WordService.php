<?php

namespace AppBundle\Service;

use AppBundle\Entity\AttemptedWord;
use AppBundle\Entity\Student;
use AppBundle\Entity\Word;
use Doctrine\ORM\EntityManager;

class WordService
{
    protected $em;

    private $max_difficulties = [0,9988376, 9994959, 9997019, 9998172, 9998800, 9999200, 9999522, 10000000];

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getWordForStudent(Student $student, $help = 0){
        if((rand(1,10)/10) < $help){
            $helped = $this->em->getRepository(AttemptedWord::class)->findBy(['helped' => 1]);
            $wordid = $helped[rand(0, count($helped))]->getId();
            $word = $this->em->getRepository(Word::class)->find($wordid);
        } else{
            $lvl = $student->getCurrentLevel();
            $sql = "SELECT id FROM word w WHERE difficulty BETWEEN :min AND :max";
            $conn = $this->em->getConnection();
            $prep = $conn->prepare($sql);
            $prep->execute(['min' => $this->max_difficulties[$lvl-1] , 'max' => $this->max_difficulties[$lvl]]);
            $ids = $prep->fetchAll();
            $id = $ids[rand(0, count($ids)-1)];
            $word = $this->em->getRepository(Word::class)->find($id);
        }
        return $word;
    }

    public function getWordById($id){
        return $this->em->getRepository(Word::class)->find($id);
    }

    public function randomWord(){
        $random_id = rand(1,4999);
        $word = $this->em->getRepository(Word::class)->find($random_id);
        return $word;
    }


    public function misspellWord(Word $word){

    }

    public function swapLettersWithProbability(Word $word, $prob=0.5){
        if((rand(1,10)/10) > $prob){
            $text = $this->swapLetters($word);
        } else{
          $text = $word->getText();
        }
        return $text;
    }

    public function redactLetters(Word $word){
        $redacted = $word->getText();
        $number_of_redactions = rand(1, $word->getLength() /2);
        $redact_positions = $this->randomPositionsInWord($word, $number_of_redactions);
        foreach ($redact_positions as $position){
            $redacted = substr_replace($redacted, '.', $position, 1);
        }
        return $redacted;
    }

    public function shuffleLetters(Word $word, $numberOfLetters = 0){
        $numberOfLetters = $numberOfLetters < 2 ? $word->getLength() : $numberOfLetters;
        $shuffled = $word->getText();
        $start = $word->getLength() > 3 ? 1 : 0;
        $end = $word->getLength() > 3 ? $word->getLength()-3 : $word->getLength()-1;
        $counter = 0;
        do {
            $counter++;
            if ( $position = rand($start , $end) ){
                $to_be_swapped = substr($shuffled, $position, rand(0, $numberOfLetters - $position - 1));
                $shuffled = str_replace($to_be_swapped, str_shuffle($to_be_swapped), $shuffled);
            } else{
                break;
            }
        } while( $shuffled == $word->getText() && $counter < 10);
        return $shuffled;
    }

    public function swapLetters(Word $word){
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
        if ($swapped == $word->getText()){
            $swapped = $this->shuffleLetters($word);
        }
        return $swapped;
    }

    public function twoWordsFrom(Word $word){
        $misspelled_word = $this->swapLetters($word);
        $words = [$word->getText(), $misspelled_word];
        shuffle($words);
        return $words;
    }


    private function randomPositionsInWord(Word $word, $number){
        $positions = [];
        while(count($positions)< $number){
          $position = rand(1, $word->getLength()-2);
          if (!in_array($position,$positions)){
              array_push($positions, $position);
          }
        }
        return $positions;
    }
}