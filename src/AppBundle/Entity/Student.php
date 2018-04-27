<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 08-12-17
 * Time: 13:33
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Student
{
    private $id;

    private $experimental;

    private $currentLevel;

    private $highestLevel;

    private $timePlayed;

    private $games;

    private $errors;

    private $attemptedWords;

    public function __construct(){
        $this->currentLevel = 0;
        $this->highestLevel = 0;
        $this->timePlayed = 0;
        $this->wordsAttempted = 0;
        $this->errors = 0;
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
    public function getExperimental()
    {
        return $this->experimental;
    }

    /**
     * @param mixed $experimental
     */
    public function setExperimental($experimental)
    {
        $this->experimental = $experimental;
    }

    /**
     * @return mixed
     */
    public function getCurrentLevel()
    {
        return $this->currentLevel;
    }

    /**
     * @param mixed $currentLevel
     */
    public function setCurrentLevel($currentLevel)
    {
        $this->currentLevel = $currentLevel;
    }

    /**
     * @return mixed
     */
    public function getHighestLevel()
    {
        return $this->highestLevel;
    }

    /**
     * @param mixed $highestLevel
     */
    public function setHighestLevel($highestLevel)
    {
        $this->highestLevel = $highestLevel;
    }

    /**
     * @return mixed
     */
    public function getTimePlayed()
    {
        return $this->timePlayed;
    }

    /**
     * @param mixed $timePlayed
     */
    public function setTimePlayed($timePlayed)
    {
        $this->timePlayed = $timePlayed;
    }


    public function increaseTimePlayed($time_played){
        $this->timePlayed += $time_played;
    }


    /**
     * @return mixed
     */
    public function getGames()
    {
        return $this->games;
    }

    /**
     * @param mixed $games
     */
    public function setGames($games)
    {
        $this->games = $games;
    }


    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function increaseErrors(){
        $this->errors += 1;
    }

    /**
     * @return mixed
     */
    public function getAttemptedWords()
    {
        return $this->attemptedWords;
    }

    /**
     * @param mixed $attemptedWords
     */
    public function setAttemptedWords($attemptedWords)
    {
        $this->attemptedWords = $attemptedWords;
    }



}