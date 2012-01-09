<?php
require_once dirname(__FILE__) . '/DatabaseTest.php';

class SerialTest extends DatabaseTest {

     var $insert   = false;
 
     function setUp() 
     {
         parent::setUp();

         $serial_string = serialize($this->db);
         unset($this->db);
         $this->db = unserialize($serial_string);

     }

    function testGetTable1()
    {
        // Test get of entire $table property
        $db =& $this->db;
        $result = $db->getTable();
        $this->assertNotError($result);
        $this->assertInternalType('array', $result);
    }
 
    function testGetPrimaryKey1()
    {
        // Test get of entire $primary_key property
        $db =& $this->db;
        $result = $db->getPrimaryKey();
        $this->assertNotError($result);
        $this->assertEquals($this->primary_key, $result);
    }
 
    function testGetRef1() 
    {
        $db =& $this->db;
        $result = $db->getRef();
        $this->assertNotError($result);
        $this->assertEquals($this->ref, $result);
    }

    function testGetRefTo1() 
    {
        $db =& $this->db;
        $result = $db->getRefTo();
        $this->assertNotError($result);
        $this->assertEquals($this->ref_to, $result);
    }

    function testGetLink1() 
    {
        $db =& $this->db;
        $result = $db->getLink();
        $this->assertNotError($result);
        $this->assertEquals($this->link, $result);
    }

    function testGetCol1() 
    {
        // Test get of entire column property
        $db =& $this->db;
        $result = $db->getCol();
        $this->assertNotError($result);
        $this->assertEquals($this->col, $result);
    }

    function testGetForeignCol1() 
    {
        // Test get of entire column property
        $db =& $this->db;
        $result = $db->getForeignCol();
        $this->assertNotError($result);
        $this->assertEquals($this->foreign_col, $result);
    }

}

?>
