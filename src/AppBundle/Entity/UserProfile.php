<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 26-11-17
 * Time: 12:39
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class UserProfile
{
    private $id;

    /**
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $dob;

    /**
     * @Assert\NotBlank()
     */
    private $gender;


    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getFullName(){
        return $this->firstName ." ". $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * @param mixed $dob
     */
    public function setDob($dob)
    {
        $this->dob = $dob;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }




  }