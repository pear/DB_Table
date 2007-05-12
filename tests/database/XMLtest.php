<?php
#require_once 'PHPUnit/TestCase.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'DB/Table/Database.php';

#class XMLtest extends PHPUnit_TestCase {
class XMLTest extends PHPUnit2_Framework_TestCase {

    var $db = null;
    var $name = null;
    var $conn = null;
    var $table = null;
    var $table_subclass = null;
    var $primary_key = null;
    var $ref = null;
    var $ref_to = null;
    var $link = null;
    var $col = null;
    var $foreign_col = null;

    function setUp() 
    {
        require 'db1/define.php';
        $db1->addAllLinks();
        $this->name    =  $db_name;
        $this->conn   =& $conn;
        $this->db     =& $db1;
        $this->verbose = $verbose;
        foreach ($properties as $property_name) {
            $this->$property_name = $$property_name;
        }
    }

    function testToXML() {
        if ($this->verbose > -1) {
            print "\n" . ">Test toXML() method";
        }
        $xml_string = $this->db->toXML();
        if ($this->verbose > 1) {
            print "\n" . $xml_string;
        }
    }
    
    function testToAndFromXML() {
        if ($this->verbose > -1) {
            print "\n" . ">Test round-trip toXML() -> fromXML";
        }
        $first_string = $this->db->toXML();
        $db_obj =& DB_Table_Database::fromXML($first_string,$this->conn);
        $second_string = $db_obj->toXML();
        $this->assertEquals($second_string, $first_string);
    }

}

?>
