<?php
/**
 * This class is factory for user objects.
 * 
 */
namespace lwProfile\Model;

class UserObjectFactory
{
    private $oldUObj;
    private $newUObj;
    private $request;
   
    /**
     * The user object of the loggen in user needs to be passed to the constructor
     * and an instance of lw_request.
     * 
     * @param object $oldUObj
     * @param object $request
     */
    public function __construct($oldUObj, $request) 
    {
        $this->request = $request;
        $this->oldUObj = $oldUObj;
        $this->newUObj = new \lwProfile\Model\UserObject();
    }
    
    /**
     * If the progress is update then will be an object returned with new user
     * information which were submited by the profile formular but the id and
     * intranet_id will be passed by the old user object.
     * If the progress is add user then a new user object will be set with the
     * profile formular informations.
     * 
     * @param bool $TRUEupdateUser_FALSEaddUser
     * @return object \lwProfile\Model\UserObject
     */
    public function buildPreparedUserObject($TRUEupdateUser_FALSEaddUser)
    {
        if($TRUEupdateUser_FALSEaddUser == true) {
            if($this->request->getRaw("password") == "") {
                $password = $this->oldUObj->getUserData("password");
            } else {
                $password = sha1($this->request->getRaw("password"));
            }
            
            $this->newUObj->setUserId($this->oldUObj->getUserData("id"));
            $this->newUObj->setIntranetId($this->oldUObj->getUserData("intranet_id"));
            $this->newUObj->setPassword($password);
            $this->newUObj->setUsername($this->request->getRaw("loginname"));
            $this->newUObj->setFirstName($this->request->getRaw("firstname"));
            $this->newUObj->setLastName($this->request->getRaw("lastname"));
            $this->newUObj->setEmail($this->request->getRaw("email"));
            $this->newUObj->setInfo($this->request->getRaw("info"));

        } else {
            
            $this->newUObj->setIntranetId($this->request->getInt("intranet"));
            $this->newUObj->setPassword(sha1($this->request->getRaw("password")));
            $this->newUObj->setUsername($this->request->getRaw("loginname"));
            $this->newUObj->setFirstName($this->request->getRaw("firstname"));
            $this->newUObj->setLastName($this->request->getRaw("lastname"));
            $this->newUObj->setEmail($this->request->getRaw("email"));
            $this->newUObj->setInfo($this->request->getRaw("info"));
        }
        
        return $this->newUObj;
    }
}