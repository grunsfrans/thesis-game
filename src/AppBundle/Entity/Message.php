<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 08-12-17
 * Time: 13:33
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Message {

    private $id;
    private $type;
    private $mood;
    private $text;

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getMood()
    {
        return $this->mood;
    }

    public function getMoodName(){
        switch ($this->mood){
            case -1:
                return "neg";
            case 1:
                return "pos";
            default:
                return "";
        }
    }
    /**
     * @param mixed $mood
     */
    public function setMood($mood)
    {
        $this->mood = $mood;
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

    public function toArray(){
        return ['type' => $this->getType(), 'mood' => $this->getMoodName(),'text' => $this->getText()];
    }



}