<?php
require_once dirname(__FILE__) . '/DatabaseTest.php';

class GetTest extends DatabaseTest {

    var $insert = false;

    function testGetTable1()
    {
        // Test get of entire $table property"
        $db =& $this->db;
        $table = $db->getTable();
        $this->assertNotError($table);
        $this->assertInternalType('array', $table);
    }
 
    function testGetTable2()
    {
        // Test get of entire $table['Person'] property
        $db =& $this->db;
        $table = $db->getTable('Person');
        $this->assertNotError($table);
        $this->assertInstanceOf('DB_Table', $table);
    }
 
    function testGetTable3()
    {
        // Test get of invalid table name
        $db =& $this->db;
        $table = $db->getTable('Thwack');
        $this->assertIsError($table);
    }
 
    function testGetPrimaryKey1()
    {
        // Test get of entire $primary_key property
        $db =& $this->db;
        $primary_key = $db->getPrimaryKey();
        $this->assertNotError($primary_key);
        $this->assertEquals($this->primary_key, $primary_key);
    }
 
    function testGetPrimaryKey2()
    {
        // Test get of $primary_key['Person'] 
        $db =& $this->db;
        $primary_key = $db->getPrimaryKey('Person');
        $this->assertNotError($primary_key);
        $this->assertEquals($this->primary_key['Person'], $primary_key);
    }
 
    function testGetPrimaryKey3()
    {
        // Test get of $primary_key with invalid Table name
        $db =& $this->db;
        $primary_key = $db->getPrimaryKey('Thwack');
        $this->assertIsError($primary_key);
   }

    function testTableSubclass1()
    {
        // Test get of entire $table_subclass property
        $db =& $this->db;
        $table_subclass = $db->getTableSubclass();
        $this->assertNotError($table_subclass);
        $this->assertEquals($this->table_subclass, $table_subclass);
    }

    function testGetRef1() 
    {
        $db =& $this->db;
        $ref = $db->getRef();
        $this->assertNotError($ref);
        $this->assertEquals($this->ref, $ref);
    }

    function testGetRef2() 
    {
        // Test get of $ref['PersonAddress'], which should be an array
        $db =& $this->db;
        $ref = $db->getRef('PersonAddress');
        $this->assertNotError($ref);
        $this->assertEquals($this->ref['PersonAddress'], $ref);
    }

    function testGetRef3() 
    {
        // Test get of $ref['Person'], which should return null
        $db =& $this->db;
        $ref = $db->getRef('Person');
        $this->assertNotError($ref);
        $this->assertNull($ref);
    }

    function testGetRef4() 
    {
        // Test get of $ref['PersonAddress']['Person'], which should be an array
        $db =& $this->db;
        $ref = $db->getRef('PersonAddress', 'Person');
        $this->assertNotError($ref);
        $this->assertEquals($this->ref['PersonAddress']['Person'], $ref);
    }

    function testGetRefTo1() 
    {
        $db =& $this->db;
        $ref_to = $db->getRefTo();
        $this->assertNotError($ref_to);
        $this->assertEquals($this->ref_to, $ref_to);
    }

    function testGetRefTo2() 
    {
        // Test get of $ref_to['Person'], which should be an array
        $db =& $this->db;
        $ref_to = $db->getRefTo('Person');
        $this->assertNotError($ref_to);
        $this->assertEquals($this->ref_to['Person'], $ref_to);
    }

    function testGetRefTo3() 
    {
        // Test get of $ref_to['PersonAddress'], which should return null
        $db =& $this->db;
        $ref_to = $db->getRefTo('PersonAddress');
        $this->assertNotError($ref_to);
        $this->assertNull($ref_to);
    }

    function testGetLink1() 
    {
        $db =& $this->db;
        $link = $db->getLink();
        $this->assertNotError($link);
        $this->assertEquals($this->link, $link);
    }

    function testGetLink2() 
    {
        // Test get of $link['Person'], which should be an array
        $db =& $this->db;
        $link = $db->getLink('Person');
        $this->assertNotError($link);
        $this->assertEquals($this->link['Person'], $link);
    }

    function testGetLink3() 
    {
        // Test get of $link['PersonAddress'], which should return null
        $db =& $this->db;
        $link = $db->getLink('PersonAddress');
        $this->assertNotError($link);
        $this->assertNull($link);
    }

    function testGetLink4() 
    {
        // Test get of $link['Person']['Address'], which should be an array
        $db =& $this->db;
        $link = $db->getLink('Person', 'Address');
        $this->assertNotError($link);
        $this->assertEquals($this->link['Person']['Address'], $link);
    }

    function testGetCol1() 
    {
        // Test get of entire column property
        $db =& $this->db;
        $col = $db->getCol();
        $this->assertNotError($col);
        $this->assertEquals($this->col, $col);
    }

    function testGetCol2() 
    {
        // Test get of col['Building']
        $db =& $this->db;
        $col = $db->getCol('Building');
        $this->assertNotError($col);
        $this->assertEquals($this->col['Building'], $col);
    }

    function testGetForeignCol1() 
    {
        // Test get of entire column property
        $db =& $this->db;
        $foreign_col = $db->getForeignCol();
        $this->assertNotError($foreign_col);
        $this->assertEquals($this->foreign_col, $foreign_col);
    }

    function testGetForeignCol2() 
    {
        // Test get of entire column property
        $db =& $this->db;
        $foreign_col = $db->getForeignCol('PersonID');
        $this->assertNotError($foreign_col);
        $this->assertEquals($this->foreign_col['PersonID'], $foreign_col);
    }

    function testValidCol1()
    {
        // Test validCol('Building')
        $db =& $this->db;
        $name = implode('.', $db->validCol('Building'));
        $this->assertNotError($name);
        $this->assertEquals('Address.Building', $name);
    }

    function testValidCol1b()
    {
        // Test validCol('Building')
        $db =& $this->db;
        $from = array('Address');
        $col = $db->validCol('City', $from);
        $this->assertNotError($col);
        $name = implode('.', $col);
        $this->assertEquals('Address.City', $name);
    }

    function testValidCol2()
    {
        $db =& $this->db;
        $col = $db->validCol('PersonID');
        $this->assertNotError($col);
        $name = implode('.', $col);
        $this->assertEquals('Person.PersonID', $name);
    }

    function testValidCol2b()
    {
        $db =& $this->db;
        $from = array('PersonPhone');
        $col = $db->validCol('PersonID', $from);
        $this->assertNotError($col);
        $name = implode('.', $col);
        $this->assertEquals('PersonPhone.PersonID', $name);
    }

    function testValidCol3()
    {
        $db =& $this->db;
        $col = $db->validCol('PersonID2');
        $this->assertNotError($col);
        $name = implode('.', $col);
        $this->assertEquals('PersonAddress.PersonID2', $name);
    }

    function testValidCol4()
    {
        $db =& $this->db;
        $col = $db->validCol('Person.FirstName');
        $this->assertNotError($col);
        $name = implode('.', $col);
        $this->assertEquals('Person.FirstName', $name);
    }

    function testValidCol5()
    {
        // validCol('Thwack.Building')
        $db =& $this->db;
        $result = $db->validCol('Person.Thingy');
        $this->assertIsError($result, 'Was expecting error on bad column');
    }

    function testValidCol6()
    {
        // validCol('Thwack.Building')
        $db =& $this->db;
        $result = $db->validCol('Thwack.Building');
        // TODO: is this right?  From notes above, seems like want to pass.
        $this->assertIsError($result, 'Was expecting error on bad column');
    }

    function testValidCol7()
    {
        $db =& $this->db;
        $result = $db->validCol('Street');
        $this->assertNotError($result);
        $name = implode('.', $result);
        $this->assertEquals('Street.Street', $name);
    }

}

?>
