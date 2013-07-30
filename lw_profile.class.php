<?php
/**
 * This plguin allowes the logged in user to change his personal
 * account information. 
 *  
 * The following information can be changed:
 *      - loginname
 *      - password
 *      - intranet (only for admins)
 *      - firstname
 *      - lastname
 *      - email
 *      - additional personal information
 * 
 * This class is the interface to the contentory system and returns
 * the required html output for front- or backend.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_profile
 */

class lw_profile extends lw_plugin
{
    private $in_auth;
    protected $db;
    protected $repository;
    protected $request;



    /**
     * For the functionality of this plugin is it necessary to include
     * the "Autoloader" and the instances of "in_auth" and "auth"
     * objects.
     */
    public function __construct()
    {
        parent::__construct();
        include_once(dirname(__FILE__).'/Services/Autoloader.php');
        $autoloader = new \lwProfile\Services\Autoloader();
        $this->in_auth = \lw_in_auth::getInstance();
        $this->auth = \lw_registry::getInstance()->getEntry("auth");
       
    }
    
    /**
     * The HTML frontend output will be build for logged in user. Not logged in
     * user will be redirected to the login page. 
     * 
     * @return string
     */
    public function buildPageOutput()
    {        
        if($this->request->getAlnum("cmd") == "checkpw"){
            $pws = new \lw_passwordStrength('', $this->request->getRaw('password'));
            exit($pws->getPasswordStrength());
        }
        
        $plugindata = $this->repository->plugins()->loadPluginData($this->getPluginName(),$this->params['oid']);
        
        if($this->request->getAlnum("cmd") == "logout"){
            $this->in_auth->logout();
            $this->pageReload(\lw_page::getInstance($plugindata["parameter"]["logout_id"])->getUrl());
        }
        
        if($this->in_auth->isLoggedIn()) {            
            $admin = false;
            if($this->config["intranetUser"]["admin"] == $this->in_auth->getUserData("intranet_id") || $this->auth->isLoggedIn()) {
                $admin = true;
            } 
            $response = \lwProfile\Services\Response::getInstance();

            $controller = new \lwProfile\Controller\ProfileController($response, $this->setUserObject(), $this->db, $plugindata);
            $controller->execute(true, $admin,\lw_page::getInstance()->getUrl());

            return $response->getOutputByKey("intranet");
        }
        
        $this->pageReload(\lw_page::getInstance($plugindata["parameter"]["logout_id"])->getUrl());
        
    }
    
    /**
     * Information of the logged in intranet user will be collected in this object.
     * 
     * @return \lwProfile\Model\UserObject
     */
    public function setUserObject()
    {
        $inUserObject = new \lwProfile\Model\UserObject();
        $inUserObject->setUserId($this->in_auth->getUserData("id"));
        $inUserObject->setUsername($this->in_auth->getUserData("name"));
        $inUserObject->setPassword($this->in_auth->getUserData("password"));
        $inUserObject->setIntranetId($this->in_auth->getUserData("intranet_id"));
        $inUserObject->setEmail($this->in_auth->getUserData("email"));
        $inUserObject->setFirstName($this->in_auth->getUserData("opt1text"));
        $inUserObject->setLastName($this->in_auth->getUserData("opt2text"));
        
        return $inUserObject;
    }
    
    /**
     * The HTML backend output will be build.
     * 
     * @return string
     */
    public function getOutput()
    {
        $backend = new \lwProfile\Controller\backend($this->config,$this->request,$this->repository,$this->getPluginName(),$this->getOid(), $this->db);
        if ($this->request->getAlnum("pcmd") == "save"){
            $backend->backend_save();
        }
        return $backend->backend_view();
    }
    
    
    /**
     * Here will be set if the plugin-conetentbox is deleteable from a page.
     * 
     * @return boolean
     */
    function deleteEntry()
    {
        return true;
    }
}