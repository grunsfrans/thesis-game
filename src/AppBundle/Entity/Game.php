<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 08-12-17
 * Time: 13:33
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Game
{
    private $id;

    private $student;

    private $active;

    private $score;

    private $level;

    private $timePlayed;


    public function __construct(){
        $this->active = 0;
        $this->score = 0;
        $this->level = 1;
        $this->timePlayed = 0;
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
     * @param mixed $studentId
     */
    public function setStudent($student)
    {
        $this->student = $student;
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $is_active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }



    /**
     * @return mixed
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param mixed $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getTimePlayed()
    {
        return $this->timePlayed;
    }

    /**
     * @param mixed $time_played
     */
    public function setTimePlayed($time_played)
    {
        $this->timePlayed = $time_played;
    }

    public function increaseTimePlayed($time_played){
        $this->timePlayed += $time_played;
    }



}