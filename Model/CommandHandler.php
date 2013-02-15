<?php
/**
 * CommandHanlder updates and add new user into the database.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_profile
 */
namespace lwProfile\Model;

class CommandHandler 
{
    private $db;
    
    /**
     * An instance of lw_db is required.
     * @param object $db
     */
    public function __construct($db) 
    {
        $this->db = $db;
    }
    
    /**
     * A new user will be added to the database with the information
     * from the user object.
     * 
     * @param \lwProfile\Model\UserObject $uObj
     * @return bool
     */
    public function addUser(\lwProfile\Model\UserObject $uObj)
    {
        $this->db->setStatement("INSERT INTO t:lw_in_user (intranet_id, name, password, email, lw_first_date, opt1text, opt2text, opt1clob) VALUES (:intranet_id, :name, :password, :email, :lw_first_date, :opt1text, :opt2text, :opt1clob) ");
        $this->db->bindParameter("intranet_id", "i", $uObj->getUserData("intranet_id"));
        $this->db->bindParameter("name", "s", $uObj->getUserData("username"));
        $this->db->bindParameter("password", "s", $uObj->getUserData("password"));
        $this->db->bindParameter("email", "s", $uObj->getUserData("email"));
        $this->db->bindParameter("lw_first_date", "i", date("YmdHis"));
        $this->db->bindParameter("opt1text", "s", htmlspecialchars($uObj->getUserData("firstname")));
        $this->db->bindParameter("opt2text", "s", htmlspecialchars($uObj->getUserData("lastname")));
        $this->db->bindParameter("opt1clob", "s", htmlspecialchars($uObj->getUserData("info")));
        
        return $this->db->pdbquery();
    }
    
    /**
     * A user will be updated in the database with the information
     * from the user object.
     * 
     * @param \lwProfile\Model\UserObject $uObj
     * @return bool
     */
    public function updateUser(\lwProfile\Model\UserObject $uObj)
    {
        $this->db->setStatement("UPDATE t:lw_in_user SET name = :name, password = :password, email = :email, opt1text = :firstname, opt2text = :lastname, opt1clob = :info WHERE id = :id ");
        $this->db->bindParameter("id", "i", $uObj->getUserData("id"));
        $this->db->bindParameter("name", "s", $uObj->getUserData("username"));
        $this->db->bindParameter("password", "s", $uObj->getUserData("password"));
        $this->db->bindParameter("email", "s", $uObj->getUserData("email"));
        $this->db->bindParameter("firstname", "s", htmlspecialchars($uObj->getUserData("firstname")));
        $this->db->bindParameter("lastname", "s", htmlspecialchars($uObj->getUserData("lastname")));
        $this->db->bindParameter("info", "s", htmlspecialchars($uObj->getUserData("info")));
        
        return $this->db->pdbquery();
    }
}