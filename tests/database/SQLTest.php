<?php
#require_once 'PHPUnit/TestCase.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'DB/Table/Database.php';

/**
 * Tests _quote(), buildFilter() and buildSQL string processing methods
 */
#class SQLTest extends PHPUnit_TestCase {
class SQLTest extends PHPUnit2_Framework_TestCase {

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

    function testQuoteString()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteString";
        }
        $result = $this->conn->quote("This is not a number");
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "'This is not a number'");
    } 

    function testQuoteInteger()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteInteger";
        }
        $result = $this->db->quote(256);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "256");
    }
 
    function testQuoteFloat()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteFloat";
        }
        $result = $this->db->quote(2.56);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "2.56");
    }
 
    function testQuoteBooleanFalse()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteBooleanFalse";
        }
        $result = $this->db->quote(false);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "0");
    }
 
    function testQuoteNull()
    {
        if ($this->verbose > -1) {
            print "\n" . ">testQuoteNull";
        }
        $result = $this->db->quote(null);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, "NULL");
    }

    function testBuildFilter1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter1";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $result = $this->db->buildFilter($data);
        $expect = "col1 = 1 AND col2 = 0 AND col3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter2() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter2";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data_key = 'col3';
        $result = $this->db->buildFilter($data, $data_key);
        $expect = "col3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter3() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter3";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data_key = array('col1', 'col3');
        $result = $this->db->buildFilter($data, $data_key);
        $expect = "col1 = 1 AND col3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter4() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter4";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = 'col3';
        $filt_key = 'COL3';
        $result = $this->db->buildFilter($data, $data_key, $filt_key);
        $expect = "COL3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter5() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter5";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = array('col1', 'col3');
        $filt_key = array('COL1', 'COL3');
        $result = $this->db->buildFilter($data, $data_key, $filt_key);
        $expect = "COL1 = 1 AND COL3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter6() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter6";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $result = $this->db->buildFilter($data);
        $expect = '';
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter7() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter7";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $result = $this->db->buildFilter($data, null, null, 'partial');
        $expect = "col1 = 1 AND col2 = 0 AND col3 = 'anyold string'";
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    /*
    function testBuildFilter8() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter8";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $result = $this->db->buildFilter($data, null, null, 'full');
        if (PEAR::isError($result)) {
            $this->assertTrue(true);
        } else {
            if ($this->verbose > 0) {
                print "\n" . $result;
                print "\n" . $result;
            }
        }
    }
    */

    function testBuildFilter9() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter9";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = 'col4';
        $result = $this->db->buildFilter($data, $data_key);
        $expect = '';
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter10() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter10";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = array('col2', 'col4');
        $result = $this->db->buildFilter($data, $data_key);
        $expect = '';
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildFilter11() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildFilter11";
        }
        $data['col1'] = 1;
        $data['col2'] = false;
        $data['col3'] = 'anyold string';
        $data['col4'] = null;
        $data_key = array('col1', 'col4');
        $filt_key = array('COL1', 'COL4');
        $result = $this->db->buildFilter($data, $data_key, $filt_key);
        $expect = '';
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertEquals($result, $expect);
    }

    function testBuildSQL1() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildSQL1";
        }
        $db =& $this->db;
        $query = array(
           'select' => 'FirstName, LastName, Building, Street, City',
           'from'   => 'Person, Address',
           'where'  => 'Person.PersonID = Address.PersonID2');
        $db->sql['test2'] = $query;
        $result = $db->buildSQL($query, "City = 'MINNETONKA'", 'City');
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertTrue(true);
    }
       
    function testBuildSQL2() 
    {
        if ($this->verbose > -1) {
            print "\n" . ">testBuildSQL2";
        }
        $db =& $this->db;
        $query = array(
           'select' => 'Street, count(Building)',
           'from'   => 'Address',
           'group'  => 'Street',
           'having' => "City = 'MINNETONKA'",
           'order'  => 'Street' );
        $result = $db->buildSQL($query);
        if ($this->verbose > 0) {
            print "\n" . $result;
        }
        $this->assertTrue(true);
    }

}

?>
