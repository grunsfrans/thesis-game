<?php
/**
 * Created by PhpStorm.
 * User: frans
 * Date: 18-11-17
 * Time: 14:14
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;


class User implements UserInterface, \Serializable
{

    private $id;

    private $username;

    private $password;

    private $email;

    private $isActive;

    private $userProfile;



    public function __construct()
    {
        $this->isActive = true;
    }


    public function getUsername()
    {
        return $this->username;
    }


    public function getPassword()
    {
        return $this->password;
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

    public function setUserProfile($userProfile){
        $this->userProfile = $userProfile;
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