<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 08-12-17
 * Time: 13:33
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Word
{
    private $id;

    private $text;

    private $frequency;

    private $length;

    private $difficulty;

    public function __construct($text, $frequency){
        $this->text = $text;
        $this->frequency = $frequency;
        $this->length = strlen($this->text);
        $this->difficulty = 10000000-(((($this->frequency/$this->length) - 386)/10143814)*10000000);
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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param mixed $frequency
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return float|int
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * @param float|int $difficulty
     */
    public function setDifficulty($difficulty)
    {
        $this->difficulty = $difficulty;
    }



}