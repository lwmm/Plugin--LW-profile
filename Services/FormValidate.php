<?php
/**
 * The inputs of the html formular from the "ProfileForm.tpl.phtml"
 * file will be validated.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_profile
 */

namespace lwProfile\Services;

define("REQUIRED", "1");    # array( 1 => array( "error" => 1, "options" => "" ));
define("MAXLENGTH", "2");   # array( 2 => array( "error" => 1, "options" => array( "maxlength" => $maxlength, "actuallength" => $strlen ) ));
define("YEAR", "3");        # array( 3 => array( "error" => 1, "options" => array( "enteredyear" => $year ) ));
define("DATE", "4");        # array( 4 => array( "error" => 1, "options" => array( "entereddate" => $date ) ));  [$date = JJJJMMDD]
define("EMAIL", "5");       # array( 5 => array( "error" => 1, "options" => "" ));
define("DIGITFIELD", "6");  # array( 6 => array( "error" => 1, "options" => "" ));
define("ZIP", "7");         # array( 7 => array( "error" => 1, "options" => "" ));
define("PAYMENT", "8");     # array( 8 => array( "error" => 1, "options" => "" ));
define("BOOL", "9");        # array( 9 => array( "error" => 1, "options" => "" ));
define("PWREPEAT", "10");   # array( 10 => array( "error" => 1, "options" => "" ));
define("SPECIALCHARS","11");# array( 10 => array( "error" => 1, "options" => "" ));
define("PWSTRENGTH","12");# array( 10 => array( "error" => 1, "options" => "" ));


class FormValidate 
{
    private $updateOrAddUser;
    private $allowedKeys;
    private $array;
    private $errors;

    /**
     * It's important to set the $this->allowedKeys with
     * the elements of the formular, because this keys are
     * functionnames for the validation of the specific element.
     */
    public function __construct()
    {
        $this->updateOrAddUser = true;
        $this->pwMinStrength = 0;
        $this->errors = array();
        
        $this->allowedKeys = array(
                    "id"               ,
                    "username"         ,
                    "password"         ,
                    "password_repeat"  ,
                    "intranet_id"      ,
                    "email"            ,
                    "firstname"        ,
                    "lastname"         
            );
        
    }

    /**
     * Profile update or adding new profile will be set.
     * @param bool $bool
     */
    public function setTRUEupdateUser_FALSEaddUser($bool)
    {
        $this->updateOrAddUser = $bool;
    }
    
    public function setPwMinStrength($value)
    {
        $this->pwMinStrength = $value;
    }
    
    /**
     * The values of the submited formular will be set.
     * @param array $array
     */
    public function setValues($array) 
    {
        $this->array = $array;
    }
    
    /**
     * Each value will be passed to his validation function. 
     * @bool boolean
     */
    public function validate()
    {                   
        $valid = true;
        foreach($this->allowedKeys as $key){
            $function = $key."Validate";
            $result = $this->$function($this->array[$key]);
            if($result == false){
                $valid = false;
            }
        }
        return $valid;
    }
    
    /**
     * If an error occured then this error will be set to this error array.
     * @param string $key
     * @param int $number
     * @param array $array
     */
    private function addError($key, $number, $array=false)
    {
        $this->errors[$key][$number]['error'] = 1;
        $this->errors[$key][$number]['options'] = $array;
    }
    
    /**
     * All error will be returned.
     * @array type
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * One specific error will be returned.
     * @param string $key
     * @return array
     */
    public function getErrorsByKey($key)
    {
        return $this->errors[$key];
    }
    
    /**
     * Id will be checked if this value is a digit and its not required.
     * @param int $value
     * @return boolean
     */
    public function idValidate($value){
        if(empty($value)){
            return true;
        }else{
            if(ctype_digit($value)){
                return true;
            }else{
                $this->addError("id", DIGITFIELD);
                return false;
            }
        }
    }
    
    /**
     * Max_langth and existence of specialchars will be checked of username.
     * @param string $value
     * @return boolean
     */
    public function usernameValidate($value)
    {
        $bool =  $this->defaultValidation("username", $value, 255, true);
     
        if(!ctype_alnum($value)) {
            $bool = false;
            $this->addError("username", SPECIALCHARS);
        }
        if(!$bool) {
            return false;
        }
        return true;
    }
    
    /**
     * Intranet will be cheked if one intranet is selected.
     * @param int $value
     * @return boolean
     */
    public function intranet_idValidate($value)
    {
        if($value === 0) {
            $this->addError("intranet_id", REQUIRED);
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Password and password repetition will be checked of max_length and if both 
     * passwords are identical.
     * @param string $value
     * @return boolean
     */
    public function passwordValidate($value)
    {
        if($this->updateOrAddUser == true) {
            $required = false;
        } else {
            $required = true;
        }
        
        $bool = $this->defaultValidation("password", $value, 255, $required);
        if($value !== $this->array["password_repeat"]) {
            $this->addError("password", PWREPEAT);
            $bool = false;
        }
        
        if(strlen($value) > 0){
            $pws = new \lw_passwordStrength('', $value);
            $strength = $pws->getPasswordStrength();

            if($strength < $this->pwMinStrength){
                $this->addError("password", PWSTRENGTH);
                $bool = false;
            }
        }
        
        if(!$bool) {
            return false;
        }
        return true;
    }
    
    public function password_repeatValidate($value)
    {
        return true;
    }
    
    /**
     * Email syntax will be checked.
     * @param string $value
     * @return boolean
     */
    public function emailValidate($value)
    {
        if(empty($value)) {
            return true;
        }
        $bool = true;

        if(filter_var($value, FILTER_VALIDATE_EMAIL) == false) {
            $this->addError("email", EMAIL);
            $bool = false;
        }

        if($bool == false){
            return false;
        }
        return true;
    }
    
    /**
     * Firstname will be validated.
     * @param string $value
     * @return bool
     */
    public function firstnameValidate($value)
    {
        return $this->defaultValidation("firstname", $value, 255);
    }
    
    /**
     * Lastname will be validated.
     * @param string $value
     * @return bool
     */
    public function lastnameValidate($value)
    {
        return $this->defaultValidation("lastname", $value, 255);
    }
    
    /**
     * Default validation checks the max_length of the value and if required is true
     * if the value is not empty.
     * @param string $key
     * @param string $value
     * @param int $length
     * @param bool $required
     * @return boolean
     */
    public function defaultValidation($key,$value,$length,$required = false)
    {
        $bool = true;
        
        if($required === true){
            $bool = $this->requiredValidation($key, $value);
        }
        
        if(strlen($value) > $length){
            $this->addError($key, MAXLENGTH, array("maxlength" => $length, "actuallength" => strlen($value)));
            $bool = false;
        }
        
        if($bool == false){
            return false;
        }
        return true;
    }
    
    /**
     * Checks if a required field is empty.
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function requiredValidation($key, $value)
    {
        if($value == ""){
            $this->addError($key, REQUIRED);
            return false;
        }
        return true;
    }
}