<?php
require_once dirname(__FILE__) . '/DatabaseTest.php';

/**
 * Tests methods needed to modify data in a database:
 * validForeignKeys(), insert(), delete(), and update() 
 *
 * Also uses setOnDelete() and setOnUpdate() to modify
 * referentially triggered actions
 *
 * --- Person ---
 *   PersonID: 15
 *  FirstName: JENNIFER
 * MiddleName: JEAN
 *   LastName: MACKENTHUN
 * NameSuffix: NULL
 * --- PersonPhone ---
 *   PersonID: 15  (default 75)
 *    PhoneID: 13
 * --- PersonAddress ---
 *  PersonID2: 15  (default 50)
 *  AddressID: 15
 */
class ModifyTest extends DatabaseTest {

    var $data_dir  = 'ModifyTest';
    var $data_mode = 'r';
    var $verbose   = 2;

    function testValidForeignKeys1()
    {
        if ($this->verbose > 0) {
            print "\n" . "Test FKs of PersonPhone row (18,2) with valid FKs";
        }
        $data = array();
        $data['PersonID'] = 18;
        $data['PhoneID']  = 2;
        $result = $this->db->ValidForeignKeys('PersonPhone', $data);
        $this->assertNotError($result);
        $this->assertTrue($result);
    }

    function testValidForeignKeys2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Test FKs of Address with valid multi-column FK";
        }

        $data = array();
        $data['Building'] = '1357';
        $data['Street']   = 'NORMAN DR';
        $data['City']     = 'MINNETONKA';
        $data['StateAbb'] = 'MN';
        $data['ZipCode']  = '55345';
        $result = $this->db->ValidForeignKeys('Address', $data);

        // Check that $result is boolean true
        $this->assertNotError($result);
        $this->assertTrue($result);

    }

    function testValidForeignKeys3()
    {
        if ($this->verbose > 0) {
            print "\n" . "Test FKs of PersonPhone row (18,38) with invalid FK";
        }
        $data = array();
        $data['PersonID'] = 18;
        $data['PhoneID']  = 38;
        $result = $this->db->ValidForeignKeys('PersonPhone', $data);

        // Check that $result is a PEAR_Error object
        $this->assertIsError($result, 'No Error for invalid foreign key');
    }

    function testValidForeignKeys4()
    {
        if ($this->verbose > 0) {
            print "\n" . "Test FKs of Address with invalid multi-column FK";
        }

        $data = array();
        $data['Building'] = '1357';
        $data['Street']   = 'NORMAL DR'; // Invalid: Should be 'NORMAN DR'
        $data['City']     = 'MINNETONKA';
        $data['StateAbb'] = 'MN';
        $data['ZipCode']  = '55345';
        $result = $this->db->ValidForeignKeys('Address', $data);

        // Check that $result is a PEAR_Error object
        $this->assertIsError($result, 'No Error for invalid foreign key');
    }

    function testInsert1()
    {
        if ($this->verbose > 0) {
            print "\n" . "Insert row with valid integer FKs in PersonPhone";
        }

        // Insert new row into PersonPhone
        $data = array();
        $data['PersonID'] = 18;
        $data['PhoneID']  =  2;
        $result = $this->db->insert('PersonPhone', $data);
        $this->assertNotError($result);
        $this->assertTrue($result);

        // Inspect PersonPhone
        $report = array('select' => implode(', ', array_keys($data)),
                        'from' => 'PersonPhone',
                        'where' => 'PersonID = 18 and PhoneID = 2',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals($data, $result[0]);
    }

    function testInsert2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Insert row with valid multi-column FK into Address";
        }

        $data = array();
        $data['Building'] = '1357';
        $data['Street']   = 'NORMAN DR';
        $data['City']     = 'MINNETONKA';
        $data['StateAbb'] = 'MN';
        $data['ZipCode']  = '55345';
        $result = $this->db->insert('Address', $data);
        $this->assertNotError($result);
        $this->assertTrue($result);

        // Inspect Address 
        $report = array('select' => implode(', ', array_keys($data)),
                        'from' => 'Address',
                        'where' => "Building = '1357'",
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals($data, $result[0]);
    }

    function testInsertForeignKeyCheck1()
    {
        if ($this->verbose > 0) {
            print "\n" . 
            "Attempt insert with invalid FK integer PhoneID in PersonPhone";
        }

        $assoc = array();
        $assoc['PersonID'] = 17;
        $assoc['PhoneID']  = 28; // Beyond range available in Phone
        $result = $this->db->insert('PersonPhone', $assoc);
        $this->assertIsError($result, 'Successful insert with invalid FKey');
    }
        
    function testInsertForeignKeyCheck2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Attempt insert with invalid multi-column FK in Address";
        }

        $data = array();
        $data['Building'] = '1357';
        $data['Street']   = 'EASY ST';    // No such street in Street table
        $data['City']     = 'MINNETONKA';
        $data['StateAbb'] = 'MN';
        $data['ZipCode']  = '12345';
        $result = $this->db->insert('Address', $data);
        $this->assertIsError($result, 'Successful insert with invalid FKey');
    }
        
    function testDeleteCascade1()
    {
        if ($this->verbose > 0) {
            print "\n" . 
            'Cascading delete with integer referenced key from Person';
        }

        $where  = 'PersonID = 15';
        $result = $this->db->delete('Person', $where);
        $this->assertNotError($result);

        // Inspect PersonPhone
        $report = array('select' => '*',
                        'from' => 'PersonPhone',
                        'where' => $where,
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(array(), $result, 'Should have had no result.');

        // Inspect PersonAddress
        $report = array('select' => '*',
                        'from' => 'PersonAddress',
                        'where' => 'PersonID2 = 15',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(array(), $result, 'Should have had no result.');
    }
       
    function testDeleteCascade2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Cascading delete with multi-column referenced key from Street";
        }

        $where  = "Street = 'NORMAN DR'";
        $result = $this->db->delete('Street', $where);
        $this->assertNotError($result);

        // Inspect Street
        $report = array('select' => '*',
                        'from'   => 'Street',
                        'where'  => $where,
                        'fetchmode' => $this->fetchmode_assoc );
        $count = $this->db->selectCount($report);
        $result = $this->db->select($report);
        $this->assertEquals(array(), $result, 'Should have had no result.');

        // Inspect Address 
        $report = array('select' => '*',
                        'from'   => 'Address',
                        'where'  => 'AddressID = 2',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals(array(), $result, 'Should have had no result.');

        // Inspect PersonAddress 
        $report = array('select' => '*',
                        'from'   => 'PersonAddress',
                        'where'  => 'AddressID = 2',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals(array(), $result, 'Should have had no result.');
    }

    function testDeleteNullify1()
    {
        if ($this->verbose > 0) {
            print "\n" . 
            'Nullifying delete with integer referenced key from Person';
        }

        $this->db->setOnDelete('PersonPhone', 'Person', 'set null');
        $this->db->SetOnUpdate('PersonPhone', 'Person', 'set null');

        $where  = 'PersonID = 15';
        $result = $this->db->delete('Person', $where);
        $this->assertNotError($result);

        // Inspect Person
        $report = array('select' => '*',
                        'from'   => 'Person',
                        'where'  => $where,
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals(array(), $result, 'Should have had no result.');

        // Inspect PersonPhone
        $report = array('select' => '*',
                        'from' => 'PersonPhone',
                        'where' => 'PhoneID = 13',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertNull($result[0]['PersonID']);

        // Inspect PersonAddress
        $report = array('select' => '*',
                        'from' => 'PersonAddress',
                        'where' => 'AddressID = 15',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(array(), $result, 'Should have had no result.');
    }
        
    function testDeleteNullify2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Nullifying delete with multi-column referenced key from Street";
        }

        $this->db->setOnDelete('Address', 'Street', 'set null');
        $this->db->SetOnUpdate('Address', 'Street', 'set null');

        $where  = "Street = 'NORMAN DR'";
        $result = $this->db->delete('Street', $where);
        $this->assertNotError($result);

        // Inspect Street
        $report = array('select' => '*',
                        'from'   => 'Street',
                        'where'  => $where,
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals(array(), $result, 'Should have had no result.');

        // Inspect Address 
        $report = array('select' => '*',
                        'from'   => 'Address',
                        'where'  => 'AddressID = 2',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNull($result[0]['Street']);
    }

    function testDeleteDefault1()
    {
        if ($this->verbose > 0) {
            print "\n" . 
            'Nullifying delete with integer referenced key from Person';
        }

        $this->db->setOnDelete('PersonPhone', 'Person', 'set default');
        $this->db->SetOnUpdate('PersonPhone', 'Person', 'set default');

        $where  = 'PersonID = 15';
        $result = $this->db->delete('Person', $where);
        $this->assertNotError($result);

        // Inspect Person
        $report = array('select' => '*',
                        'from'   => 'Person',
                        'where'  => $where,
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals(array(), $result, 'Should have had no result.');

        // Inspect PersonPhone
        $report = array('select' => '*',
                        'from' => 'PersonPhone',
                        'where' => 'PhoneID = 13',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(75, $result[0]['PersonID']);

        // Inspect PersonAddress
        $report = array('select' => '*',
                        'from' => 'PersonAddress',
                        'where' => 'AddressID = 15',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(array(), $result, 'Should have had no result.');
    }

    function testDeleteDefault2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Delete multi-col key from Street with on default";
        }

        $this->db->setOnDelete('Address', 'Street', 'set default');
        $this->db->SetOnUpdate('Address', 'Street', 'set default');

        $where  = "Street = 'NORMAN DR'";
        $result = $this->db->delete('Street', $where);
        $this->assertNotError($result);

        // Inspect Street
        $report = array('select' => '*',
                        'from'   => 'Street',
                        'where'  => $where,
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals(array(), $result, 'Should have had no result.');

        // Inspect Address 
        $report = array('select' => '*',
                        'from'   => 'Address',
                        'where'  => 'AddressID = 2',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals('AnyStreet', $result[0]['Street']);
    }

    function testDeleteRestrict1()
    {
        if ($this->verbose > 0) {
            print "\n" . 
            'Restricted delete with integer referenced key from Person';
        }

        $this->db->setOnDelete('PersonPhone', 'Person', 'restrict');
        $this->db->SetOnUpdate('PersonPhone', 'Person', 'restrict');

        $where  = 'PersonID = 15';
        $result = $this->db->delete('Person', $where);
        $this->assertIsError($result, 'Restricted delete should have failed');
    }

    function testDeleteRestrict2()
    {
        if ($this->verbose > 0) {
            print "\n" . "Restricted delete with multi-col key from Street";
        }

        $this->db->setOnDelete('Address', 'Street', 'restrict');
        $this->db->SetOnUpdate('Address', 'Street', 'restrict');

        $where  = "Street = 'NORMAN DR'";
        $result = $this->db->delete('Street', $where);
        $this->assertIsError($result, 'Restricted delete should have failed');
    }

    function testUpdate()
    {
        if ($this->verbose > 0) {
            print "\n" . "Allowed update of integer foreign key of PersonPhone";
        }

        $assoc = array();
        $assoc['PhoneID'] = 9;
        $where = 'PhoneID = 18';
        $result = $this->db->update('PersonPhone', $assoc, $where);
        $this->assertNotError($result);
    }

    function testUpdateForeignKeyCheck()
    {
        if ($this->verbose > 0) {
            print "\n" . "Attempt update with invalid integer foreign key";
        }

        $assoc = array();
        $assoc['PhoneID'] = 28;  // beyond range of valid values 
        $where = 'PhoneID = 18';
        $result = $this->db->update('PersonPhone', $assoc, $where);
        $this->assertIsError($result, 'Invalid FK update should have failed');
    }

    function testUpdateCascade1()
    {
        if ($this->verbose > 0) {
            print "\n" . "Cascading update of integer primary key of Person";
        }

        $where = 'PersonID = 15';
        $assoc = array('PersonID' => 38);
        $result = $this->db->update('Person', $assoc, $where);
        $this->assertNotError($result);

        // Inspect Person
        $report = array('select' => '*',
                        'from'   => 'Person',
                        'where'  => 'PersonID = 38',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals('MACKENTHUN', $result[0]['LastName']);

        // Inspect PersonPhone
        $report = array('select' => '*',
                        'from' => 'PersonPhone',
                        'where' => 'PhoneID = 13',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(38, $result[0]['PersonID']);

        // Inspect PersonAddress
        $report = array('select' => '*',
                        'from' => 'PersonAddress',
                        'where' => 'AddressID = 15',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(38, $result[0]['PersonID2']);
    }

    function testUpdateCascade2()
    {
        if ($this->verbose > 0) {
            print "\n" ."Cascading update of multi-column referenced key from Street";
        }

        $where = "Street = 'NORMAN DR'";
        $data  = array('Street' => 'NOX BOULEVARD', 'City' => 'ANYTOWN');
        $result = $this->db->update('Street', $data, $where);
        $this->assertNotError($result);

        // Inspect Address 
        $report = array('select' => '*',
                        'from'   => 'Address',
                        'where'  => 'AddressID = 2',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals('NOX BOULEVARD', $result[0]['Street']);
    }

    function testUpdateNullify1()
    {
        if ($this->verbose > 0) {
            print "\n" . "Nullifying update of integer primary key of Person";
        }

        $this->db->setOnDelete('PersonPhone', 'Person', 'set null');
        $this->db->SetOnUpdate('PersonPhone', 'Person', 'set null');

        $where = 'PersonID = 15';
        $assoc = array('PersonID' => 38);
        $result = $this->db->update('Person', $assoc, $where);
        $this->assertNotError($result);

        // Inspect Person
        $report = array('select' => '*',
                        'from'   => 'Person',
                        'where'  => 'PersonID = 38',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals('MACKENTHUN', $result[0]['LastName']);

        // Inspect PersonPhone
        $report = array('select' => '*',
                        'from' => 'PersonPhone',
                        'where' => 'PhoneID = 13',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertNull($result[0]['PersonID']);

        // Inspect PersonAddress
        $report = array('select' => '*',
                        'from' => 'PersonAddress',
                        'where' => 'AddressID = 15',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(38, $result[0]['PersonID2']);
    }

    function testUpdateNullify2()
    {
        if ($this->verbose > 0) {
            print "\n" ."Nullifying update of multi-column referenced key from Street";
        }

        $this->db->setOnDelete('Address', 'Street', 'set null');
        $this->db->SetOnUpdate('Address', 'Street', 'set null');

        $where = "Street = 'NORMAN DR'";
        $data  = array('Street' => 'NOX BOULEVARD', 'City' => 'ANYTOWN');
        $result = $this->db->update('Street', $data, $where);
        $this->assertNotError($result);

        // Inspect Address 
        $report = array('select' => '*',
                        'from'   => 'Address',
                        'where'  => 'AddressID = 2',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNull($result[0]['Street']);
    }

    function testUpdateDefault1()
    {
        if ($this->verbose > 0) {
            print "\n" . "Set default on Update of integer primary key of Person";
        }

        $this->db->setOnDelete('PersonPhone', 'Person', 'set default');
        $this->db->SetOnUpdate('PersonPhone', 'Person', 'set default');

        $where  = 'PersonID = 15';
        $assoc = array('PersonID' => 38);
        $result = $this->db->update('Person', $assoc, $where);
        $this->assertNotError($result);

        // Inspect Person
        $report = array('select' => '*',
                        'from'   => 'Person',
                        'where'  => 'PersonID = 38',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals('MACKENTHUN', $result[0]['LastName']);

        // Inspect PersonPhone
        $report = array('select' => '*',
                        'from' => 'PersonPhone',
                        'where' => 'PhoneID = 13',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(75, $result[0]['PersonID']);

        // Inspect PersonAddress
        $report = array('select' => '*',
                        'from' => 'PersonAddress',
                        'where' => 'AddressID = 15',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertNotError($result);
        $this->assertEquals(38, $result[0]['PersonID2']);
    }

    function testUpdateDefault2()
    {
        if ($this->verbose > 0) {
            print "\n" ."Nullifying update of multi-column referenced key from Street";
        }

        $this->db->setOnDelete('Address', 'Street', 'set default');
        $this->db->SetOnUpdate('Address', 'Street', 'set default');

        $where = "Street = 'NORMAN DR'";
        $data  = array('Street' => 'NOX BOULEVARD', 'City' => 'ANYTOWN');
        $result = $this->db->update('Street', $data, $where);
        $this->assertNotError($result);

        // Inspect Address 
        $report = array('select' => '*',
                        'from'   => 'Address',
                        'where'  => 'AddressID = 2',
                        'fetchmode' => $this->fetchmode_assoc );
        $result = $this->db->select($report);
        $this->assertEquals('AnyStreet', $result[0]['Street']);
    }

    function testUpdateRestrict1()
    {
        if ($this->verbose > 0) {
            print "\n" . "Restricted update of integer primary key of Person";
        }
        $this->db->setOnDelete('PersonPhone', 'Person', 'restrict');
        $this->db->SetOnUpdate('PersonPhone', 'Person', 'restrict');

        $where = 'PersonID = 13';
        $assoc = array('PersonID' => 38);
        $result = $this->db->update('Person', $assoc, $where);
        $this->assertIsError($result, 'Restricted update should have failed');
    }

    function testUpdateRestrict2()
    {
        if ($this->verbose > 0) {
            print "\n" ."Restricted update of multi-column referenced key from Street";
        }
        $this->db->setOnDelete('Address', 'Street', 'restrict');
        $this->db->SetOnUpdate('Address', 'Street', 'restrict');

        $where = "Street = 'NORMAN DR'";
        $data  = array('Street' => 'NOX BOULEVARD', 'City' => 'ANYTOWN');
        $result = $this->db->update('Street', $data, $where);
        $this->assertIsError($result, 'Restricted update should have failed');
    }

}

?>
