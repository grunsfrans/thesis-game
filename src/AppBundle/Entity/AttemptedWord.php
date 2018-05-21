<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class AttemptedWord
{
    private $id;

    private $student;

    private $word;

    private $reactiontime;

    private $skipped;

    private $helped;

    private $incorrect;


    public function __construct(Student $student = NULL, Word $word = NULL, $answer = NULL, $is_correct_answer = NULL){
        $this->student = $student;
        $this->word = $word;
        $this->reactiontime = $answer->reactiontime;
        $this->skipped = $answer->skipped;
        $this->helped = $answer->helped;
        $this->incorrect = !$is_correct_answer;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * @param mixed $student
     */
    public function setStudent($student)
    {
        $this->student = $student;
    }

    /**
     * @return mixed
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param mixed $word
     */
    public function setWord($word)
    {
        $this->word = $word;
    }

    /**
     * @return int
     */
    public function getReactiontime()
    {
        return $this->reactiontime;
    }

    /**
     * @param int $reactiontime
     */
    public function setReactiontime($reactiontime)
    {
        $this->reactiontime = $reactiontime;
    }

    /**
     * @return int
     */
    public function getSkipped()
    {
        return $this->skipped;
    }

    /**
     * @param int $skipped
     */
    public function setSkipped($skipped)
    {
        $this->skipped = $skipped;
    }

    /**
     * @return int
     */
    public function getHelped()
    {
        return $this->helped;
    }

    /**
     * @param int $helped
     */
    public function setHelped($helped)
    {
        $this->helped = $helped;
    }

    /**
     * @return int
     */
    public function getIncorrect()
    {
        return $this->incorrect;
    }

    /**
     * @param int $incorrect
     */
    public function setIncorrect($incorrect)
    {
        $this->incorrect = $incorrect;
    }



}