<?php
/**
 * This class prepares the html frontend output. The template
 * will be set with the required informations.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_profile
 */

namespace lwProfile\Views;

class PageOutput 
{
    private $view;
    private $config;
    private $request;
    
    /**
     * The constructor requires the config,request and db object and
     * it's necessary to get a new instance of lw_view object with the
     * ProfileForm.tpl.phtml.
     * 
     * @param object $config
     * @param object $request
     * @param object $db
     */
    public function __construct($config, $request, $db) 
    {
        $this->request = $request;
        $this->config = $config;
        $this->db = $db;
        $this->view = new \lw_view(dirname(__FILE__).'/Templates/ProfileForm.tpl.phtml');
    }
    
    /**
     * The html template will be filled with the required information
     * and the constructed html will be returned.
     * 
     * @param array $errors
     * @param array $data
     * @param bool $admin
     * @param array $intranets
     * @param array $plugindata
     * @param string $pluginUrlIntranetUser
     * @return html
     */
    public function render($errors, $data, $admin, $intranets, $plugindata, $pluginUrlIntranetUser = false)
    {
        $pintranets = $this->prepareIntranetsArray($intranets);
        
        switch ($this->request->getAlnum("cmd")) {
            case "edit":
                $this->view->formUrl    = \lw_page::getInstance()->getUrl(array("cmd" => "edit", "id" => $this->request->getInt("id")));
                break;

            case "add":
                $this->view->formUrl    = \lw_page::getInstance()->getUrl(array("cmd" => "add"));
                break;
            default:
                $this->view->formUrl    = \lw_page::getInstance()->getUrl();
                break;
        }
        
        if($this->request->getAlnum("cmd")  && $this->request->getAlnum("error") == "name") {
            $this->view->setNameError = true;
            $this->view->loginname  = $this->request->getAlnum("loginname");
        } else {
            $this->view->setNameError = false;
            $this->view->loginname  = $data["username"];
        }
        
        if(!$pluginUrlIntranetUser) {
            $this->view->url_cancel = \lw_page::getInstance()->getUrl();
        } else {
            $this->view->url_cancel = $pluginUrlIntranetUser;
        }
        
        if($this->request->getInt("result")) {
            $this->view->jqUI       = $this->config["url"]["media"] . "jquery/jquery.min.js";
            $this->view->response   = $this->request->getInt("result");
        } else {
             $this->view->response  = 0;
        }
        
        if(isset($plugindata["parameter"]["bootstrap"]) && $plugindata["parameter"]["bootstrap"] == 1) {
            $this->view->useBootstrap   = 1;
            $this->view->urlCSS         = $this->config["url"]["media"] . "/bootstrap/css/bootstrap.min.css";
            $this->view->urlJS          = $this->config["url"]["media"] . "/bootstrap/js/bootstrap.min.js";
        } else {
            $this->view->useBootstrap   = 0;
        }
        
        $this->view->intranets      = $pintranets;
        $this->view->admin          = $admin;
        $this->view->errors         = $errors;
        $this->view->urlPluginCSS   = $this->config["url"]["resource"] . "plugins/lw_profile/assets/css/lwProfile.css";
        $this->view->logoutUrl      = \lw_page::getInstance()->getUrl(array("cmd" => "logout"));
        $this->view->firstname      = $data["firstname"];
        $this->view->lastname       = $data["lastname"];
        $this->view->intranet_id    = $data["intranet_id"];
        $this->view->email          = $data["email"];
        $this->view->info           = $data["info"];
        
        if(isset($plugindata["parameter"]["selectedLang"])) {
            $qH = new \LwI18n\Model\queryHandler($this->db);
            $result = $qH->getAllEntriesForCategoryAndLang("lw_profile", $plugindata["parameter"]["selectedLang"]);
            $temp = array();
            foreach($result as $value) {
                $temp[$value["lw_key"]] = $value["text"];
            }
            $this->view->lang       = $temp;
        } else {
            $this->view->lang       = $this->fillPlaceHolderWithSelectedLanguage("de");
        }
        
        return $this->view->render();
    }
    
    /**
     * Array structure of existing intranets will be
     * adjusted for the use in the render function.
     * 
     * @param array $intranets
     * @return array
     */
    public function prepareIntranetsArray($intranets)
    {
        $pintranets = array();
        
        foreach($intranets as $intranet){
            $pintranets[$intranet["id"]] = $intranet["name"];
        }
        
        return $pintranets;
    }
    
    
    /**
     * All placholders will be set with the output text. 
     * The backend.php of this package will collect
     * this informations as base information.
     * 
     * @param string $lang
     * @return array
     */
    public function fillPlaceHolderWithSelectedLanguage($lang)
    {
        $languageDE = array(
            "base_data"                                         => "Grunddaten",
            "username"                                          => "Benutzername",
            "password"                                          => "Passwort",
            "password_repeat"                                   => "Passwort WDHL",
            "intranet"                                          => "Intranet",
            "optional_data"                                     => "optionale Daten",
            "firstname"                                         => "Vorname",
            "lastname"                                          => "Nachname",
            "email"                                             => "Email",
            "submit"                                            => "Senden",
            "cancel"                                            => "Abbrechen",
            "error_username_required"                           => "Pflichtfeld",
            "error_username_length"                             => "Max 255 Zeichen!",
            "error_username_specialchars"                       => "Keine Sonderzeichen erlaubt!",
            "error_username_existing"                           => "Benutzername existiert bereits!",
            "error_password_and_password_repeat_not_identical"  => "Passwort und Password Wiederholung m&uuml;ssen identisch sein!",
            "error_intranet_not_chosen"                         => "Der Benutzer muss einem Intranet zugewiesen werden!",
            "error_firstname_length"                            => "Max 255 Zeichen!",
            "error_lastname_length"                             => "Max 255 Zeichen!",
            "error_email"                                       => "Keine korrekte Email-Adresse!",
        );
        
        $languageEN = array(
            "base_data"                                         => "Base Data",
            "username"                                          => "Username",
            "password"                                          => "Password",
            "password_repeat"                                   => "Password repetition",
            "intranet"                                          => "Intranet",
            "optional_data"                                     => "optional Data",
            "firstname"                                         => "Firstname",
            "lastname"                                          => "Lastname",
            "email"                                             => "Email",
            "submit"                                            => "Submit",
            "cancel"                                            => "Cancel",
            "error_username_required"                           => "Required",
            "error_username_length"                             => "Max 255 characters!",
            "error_username_specialchars"                       => "No special chars allowed!",
            "error_username_existing"                           => "Username is already existing!",
            "error_password_and_password_repeat_not_identical"  => "Password and password repetition have to be identical!",
            "error_intranet_not_chosen"                         => "The user have to be allocated to an intranet!",
            "error_firstname_length"                            => "Max 255 characters!",
            "error_lastname_length"                             => "Max 255 characters!",
            "error_email"                                       => "No correct email address",
        );
        
        if($lang == "de") {
            return $languageDE;
        } else {
            return $languageEN;
        }
    }
}