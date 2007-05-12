<?php
#require_once 'PHPUnit/TestCase.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'DB/Table/Database.php';

/**
 * Tests _quote(), buildFilter() and buildSQL string processing methods
 */
#class AutoJoinTest extends PHPUnit_TestCase {
class AutoJoinTest extends PHPUnit2_Framework_TestCase {

    var $db = null;
    var $name = null;
    var $conn = null;
    var $table = null;
    var $primary_key = null;
    var $ref = null;
    var $ref_to = null;
    var $link = null;
    var $col = null;
    var $foreign_col = null;

    function setUp() 
    {
        // Create DB_Table_Database object $db1 with no DB/MDB2 connection
        require 'db1/define.php';
        $db1->addAllLinks();
        $this->name =  $db_name;
        $this->conn =& $conn;
        $this->db   =& $db1;
        $this->verbose = $verbose;

        foreach ($properties as $property_name) {
            $this->$property_name = $$property_name;
        }

    }

    function testJoin1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testJoin1";
        }
        $db =& $this->db;
        $success = true;
       
        $cols = array(); 
        $cols[] = 'Street';
        $cols[] = 'FirstName';
        $cols[] = 'LastName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'City';
        $report = $db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
        }
        $result = $db->buildSQL($report, "City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
        } else {
            $expect = <<<EOT
SELECT Street.Street, Person.FirstName, Person.LastName, Phone.PhoneNumber, Address.Building, Street.City
FROM Street, Person, Phone, Address, PersonAddress, PersonPhone
WHERE Address.Street = Street.Street
  AND Address.City = Street.City
  AND Address.StateAbb = Street.StateAbb
  AND PersonAddress.PersonID2 = Person.PersonID
  AND PersonAddress.AddressID = Address.AddressID
  AND PersonPhone.PhoneID = Phone.PhoneID
  AND PersonPhone.PersonID = Person.PersonID
  AND City = 'MINNETONKA'
EOT;
            if ($this->verbose > 1) {
                print "\n" . $result;
            }
        }
        $this->assertEquals($result, $expect);
    }


    function testJoin2() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testJoin2";
        }
        $db =& $this->db;
        $success = true;
       
        $cols = array(); 
        $cols[] = 'PersonID';
        $cols[] = 'FirstName';
        $cols[] = 'LastName';
        $tables = array(); 
        $tables[] = 'PersonPhone'; 
        $report = $db->autoJoin($cols, $tables);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
        }
        $result = $db->buildSQL($report);
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
        } else {
            $expect = <<<EOT
SELECT PersonPhone.PersonID, Person.FirstName, Person.LastName
FROM PersonPhone, Person
WHERE PersonPhone.PersonID = Person.PersonID
EOT;
            if ($this->verbose > 1) {
                print "\n" . $result;
            }
        }
        $this->assertEquals($result, $expect);
    }

}

?>
