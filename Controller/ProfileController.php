<?php
/**
 * The profile controller collects necessary informations and pass them
 * to the specific classes in case of which job has to be done.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_profile
 */
namespace lwProfile\Controller;

class ProfileController extends \lw_object
{
    private $response;
    private $config;
    private $request;
    private $userObject;
    protected $db;
    private $commandHandler;
    private $queryHandler;
    private $validate;
    private $uObjFactory;
    private $view;
    protected $plugindata;


    /**
     * In the contructor has 
     * 
     * @param object $response
     * @param object $userObject
     * @param object $db
     * @param array $plugindata
     */
    public function __construct($response, $userObject, $db, $plugindata) 
    {
        $reg = \lw_registry::getInstance();
        $this->config = $reg->getEntry("config");
        $this->request = $reg->getEntry("request");
        $this->response = $response;
        $this->userObject = $userObject;
        $this->db = $db;
        $this->plugindata = $plugindata;
        $this->commandHandler = new \lwProfile\Model\CommandHandler($db);
        $this->queryHandler = new \lwProfile\Model\QueryHandler($db);
        $this->validate = new \lwProfile\Services\FormValidate();
        $this->uObjFactory = new \lwProfile\Model\UserObjectFactory($userObject, $this->request);
        $this->view = new \lwProfile\Views\PageOutput($this->config,  $this->request, $this->db);   
    }
    
    /**
     * The specific task will be done. Updating or adding a user profile.
     * 
     * @param bool $TRUEupdateUser_FALSEaddUser
     * @param bool $admin
     * @param string $pluginUrlIntranetUser
     */
    public function execute($TRUEupdateUser_FALSEaddUser, $admin, $pluginUrlIntranetUser = false)
    {          
        $errors = array();
        $intranets = $this->queryHandler->getAllIntranets();
        
        if($this->request->getInt("sent") == 1) {
            
            #FOR ADDING USER: PW IS REQUIERED / FOR UPDATE USER: EMPTY PW FIELDS => OLD PW 
            $this->validate->setTRUEupdateUser_FALSEaddUser($TRUEupdateUser_FALSEaddUser); 
            
            $this->validate->setValues($this->setFormArray());
            $this->validate->validate();
            $validateErrors = $this->validate->getErrors();
            if(!empty($validateErrors)) {
                $this->response->setOutputByKey("intranet", $this->view->render($validateErrors, $this->setFormArray(), $admin, $intranets, $this->plugindata ,$pluginUrlIntranetUser));
            } else {
                if($TRUEupdateUser_FALSEaddUser === true) {
                    $this->update($pluginUrlIntranetUser);
                } else {
                    $this->add($pluginUrlIntranetUser);
                }
            }
        } else {   
            $this->response->setOutputByKey("intranet", $this->view->render($errors, $this->setFormArray(), $admin, $intranets,$this->plugindata ,$pluginUrlIntranetUser));
        }    
    }
    
    /**
     * An user will be updated.
     * @param string $pluginUrlIntranetUser
     * @throws \Exception
     */
    public function update($pluginUrlIntranetUser)
    {
        if($this->request->getRaw("loginname") != $this->userObject->getUserData("username")) {
            $result = $this->queryHandler->getUserByLoginname($this->request->getRaw("loginname"));
            if(!empty($result)) {
                $this->pageReload( \lw_page::getInstance()->getUrl(array("cmd" => "edit", "id" => $this->request->getInt("id") ,"error" => "name", "loginname" => $this->request->getAlnum("loginname"))));
            }
        }
        $updatedUserObject = $this->uObjFactory->buildPreparedUserObject(true);
        if($this->commandHandler->updateUser($updatedUserObject)) {
           $this->pageReload($pluginUrlIntranetUser . "&result=1");
        } else {
            throw new \Exception("Error: Update User");
        }
    }
    
    /**
     * An user will be added.
     * @param string $pluginUrlIntranetUser
     * @throws \Exception
     */
    public function add($pluginUrlIntranetUser)
    {
        $newUserObject = $this->uObjFactory->buildPreparedUserObject(false);
        $result = $this->queryHandler->getUserByLoginname($newUserObject->getUserData("username"));
        if(empty($result)) {
            if($this->commandHandler->addUser($newUserObject)) {
                $this->pageReload($pluginUrlIntranetUser ."&result=1");
            } else {
                throw new \Exception("Error: Add User");
            }
        } else {
            $this->pageReload( \lw_page::getInstance()->getUrl(array("cmd" => "add", "error" => "name")));
        }
    }
    
    /**
     * An array for the \LwProfile\Views\PageOutput will be prepared.
     * @return array
     */
    public function setFormArray()
    {
        if($this->request->getInt("sent") == 1) {
            return array(
                "intranet_id"       => $this->request->getInt("intranet"),
                "username"         => $this->request->getRaw("loginname"),
                "password"          => $this->request->getRaw("password"),
                "password_repeat"   => $this->request->getRaw("password_repeat"),
                "firstname"         => $this->request->getRaw("firstname"),
                "lastname"          => $this->request->getRaw("lastname"),
                "email"             => $this->request->getRaw("email"),
            );
        } else {
            return array(
                "intranet_id"       => $this->userObject->getUserData("intranet_id"),
                "username"          => $this->userObject->getUserData("username"),
                "password"          => $this->userObject->getUserData("password"),
                "firstname"         => $this->userObject->getUserData("firstname"),
                "lastname"          => $this->userObject->getUserData("lastname"),
                "email"             => $this->userObject->getUserData("email"),
            );
        }
    }
}