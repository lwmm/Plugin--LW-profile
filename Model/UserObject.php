<?php
/**
 * This object is a container for user informations which
 * can be used in this plugin by different classes.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_profile
 */

namespace lwProfile\Model;

class UserObject 
{
    private $userData = array(
                    "id"            => "",
                    "username"      => "",
                    "password"      => "",
                    "intranet_id"   => "",
                    "email"         => "",
                    "firstname"     => "",
                    "lastname"      => "",
                    "info"          => ""
                    );
    
    public function __construct() 
    {
    }
    
    public function setUserId($id)
    {
        $this->userData["id"] = $id;
    }
    
    public function setUsername($userName)
    {
        $this->userData["username"] = $userName;
    }
    
    public function setPassword($password)
    {
        $this->userData["password"] = $password;
    }
    
    public function setIntranetId($iid)
    {
        $this->userData["intranet_id"] = $iid;
    }
    
    public function setEmail($mail)
    {
        $this->userData["email"] = $mail;
    }
    
    public function setFirstName($firstname)
    {
        $this->userData["firstname"] = $firstname;
    }
    
    public function setLastName($lastName)
    {
        $this->userData["lastname"] = $lastName;
    }
    
    public function setInfo($info)
    {
        $this->userData["info"] = $info;
    }
    
    /**
     * Returns all user information with empty param else
     * the specific information for the key.
     * @param string $key
     * @return array
     */
    public function getUserData($key = false)
    {
        if($key){
            if(isset($this->userData[$key])) {
                return $this->userData[$key];
            } else {
                return "";
            }
        }
        return $this->userData;
    }
}