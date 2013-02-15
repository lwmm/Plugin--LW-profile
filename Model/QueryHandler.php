<?php
/**
 * QueryHandler collects information from the database.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package lw_profile
 */

namespace lwProfile\Model;

class QueryHandler 
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
     * Saved information for a specific username will be returned.
     * @param string $name
     * @return array
     */
    public function getUserByLoginname($name)
    {
        $this->db->setStatement("SELECT * FROM t:lw_in_user WHERE name = :loginname ");
        $this->db->bindParameter("loginname", "s", $name);
        
        return $this->db->pselect();
    }
    
    /**
     * All existing intranets will be returned.
     * @return array
     */
    public function getAllIntranets()
    {
        $this->db->setStatement("SELECT * FROM t:lw_intranets ");
        return $this->db->pselect();
    }
}