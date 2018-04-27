<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 18-11-17
 * Time: 14:14
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Class User
 * @package AppBundle\Entity
 * @UniqueEntity(fields="email", message="Email is al in gebruik")
 * @UniqueEntity(fields="username", message="Gebruikersnaam is al in gebruik")
 */
class User implements UserInterface, \Serializable
{

    private $id;

    /**
     * @Assert\NotBlank(groups = {"Default"})
     */
    private $username;


    private $password;

    /**
     * @Assert\NotBlank(groups = {"Default"})
     * @Assert\Length(max=4096)
     */
    private $plainPassword;


    /**
     * @Assert\NotBlank( groups = {"Default", "Update"})
     * @Assert\Email()
     */
    private $email;

    private $isActive;

    private $userProfile;

    private $student;



    public function __construct()
    {
        $this->isActive = true;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }




    public function getUsername()
    {
        return $this->username;
    }

    /**
 * @param mixed $username
 */
    public function setUsername($username)
    {
        if($this->username == null){
            $this->username = $username;
        }

    }



    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }



    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }



    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }


    public function getUserProfile(){
        return $this->userProfile;
    }

    public function setUserProfile(UserProfile $userProfile){
        $this->userProfile = $userProfile;
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


    public function getRoles()
    {
        return array('ROLE_USER');
    }



    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
        return null;
    }
}