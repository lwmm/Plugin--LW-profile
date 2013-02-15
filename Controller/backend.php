<?php
/**
 * The backend controller saves done changes and generate the backend
 * html output.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_profile
 * @subpackage lw_i18n
 */
namespace lwProfile\Controller;

class backend extends \lw_object
{
    function __construct($config,$request,$repository,$pluginname,$oid, $db) 
    {
       $this->config = $config;
       $this->request = $request;
       $this->repository = $repository;
       $this->pluginname = $pluginname;
       $this->oid = $oid;
       $this->db = $db;
       $this->response = new \lwProfile\Services\Response();
    }
    
    /**
     * The informations which are set in the backend formular will be saved.
     */
    function backend_save()
    {
        $cH = new \LwI18n\Model\commandHandler($this->db);
        $langParams = $this->request->getRaw("lw_i18n");
        
        foreach($langParams as $lang => $plugindata) {
            foreach($plugindata as $pluginname => $entries) {
                foreach($entries as $key => $text) {
                    $cH->save($pluginname, $lang, $key, $text);
                }
            }
        }
        
        
        $parameter = array();
        $parameter["selectedLang"]          = $this->request->getAlnum("selectedLang");
        $parameter["logout_id"]             = $this->request->getInt("logout_id");

        $content = false;
        $this->repository->plugins()->savePluginData($this->pluginname, $this->oid, $parameter, $content);
        
        $this->pageReload($this->buildURL(false, array("pcmd")));
    }
    
    /**
     * The lw_i18n plugin will check the consitens of the saved key informations and add missing entries.
     * The saved language informations will be get by the lw_i18n plugin.
     * The backend formular placeholders will be filled and a constructed hmtl formular returned.
     * @string type
     */
    function backend_view()
    {        
        $Lw18nController = new \LwI18n\Controller\I18nController($this->db, $this->response);
        $Lw18nController->execute( array($this->pluginname, "lw_profile"), "de", $this->collectDataforLwI18nPlugin("de"));
        $Lw18nController->execute( array($this->pluginname, "lw_profile"), "en", $this->collectDataforLwI18nPlugin("en"));
        
        $data = $this->repository->plugins()->loadPluginData($this->pluginname, $this->oid);
        
        $view = new \lw_view(dirname(__FILE__).'/../views/templates/backendform.tpl.phtml');
        
        $view->data         = $data;
        $view->actionUrl    = $this->buildUrl(array("pcmd"=>"save"));
        $view->jqUI         = $this->config["url"]["media"] . "jquery/ui/jquery-ui-1.8.7.custom.min.js";
        $view->jqUIcss      = $this->config["url"]["media"] . "jquery/ui/css/smoothness/jquery-ui-1.8.7.custom.css";
        $view->bootstrapCSS = $this->config["url"]["media"] . "bootstrap/css/bootstrap.min.css";
        $view->bootstrapJS  = $this->config["url"]["media"] . "bootstrap/js/bootstrap.min.js";
        $view->Css          = $this->config["url"]["resource"] . "plugins/lw_profile/assets/css/LwProfileBackend.css";
        
        $view->de           = $this->response->getOutputByKey("i18n_de"); 
        $view->en           = $this->response->getOutputByKey("i18n_en"); 
        
        return $view->render();
    }
    
    /**
     * The language array will be constructed.
     * @param string $lang
     * @return array
     */
    public function collectDataforLwI18nPlugin($lang = false)
    {
        $profileDE  = \lwProfile\Views\PageOutPut::fillPlaceHolderWithSelectedLanguage("de");
        $de         = array_merge(array($this->pluginname => $profileDE));
        
        $profileEN  = \lwProfile\Views\PageOutPut::fillPlaceHolderWithSelectedLanguage("en");
        $en         = array_merge(array($this->pluginname => $profileEN)); 
        
        switch ($lang) {
            case "de":
                return array("de" => $de);
                break;
            case "en":
                return array("en" => $en);
                break;
            
            default:
                return array("de" => $de, "en" => $en);
                break;
        }
    }
}