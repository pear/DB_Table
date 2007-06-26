<?php
require_once 'DatabaseTest.php';

class SelectTest extends DatabaseTest 
{

    var $data_dir  = 'SelectTest';
    var $data_mode = 'r';

    function testSelect1()
    {

        // Loop over tables
        $tables = $this->db->getTable();
        foreach ($tables as $table_name => $table_obj) {
            $sql = array('select'    => '*',
                         'from'      => $table_name,
                         'fetchmode' => $this->fetchmode_assoc );
            $result = $this->db->select($sql);
            $this->assertNotError($result);
            $n_result = count($result);
            $n_table  = count($this->$table_name);
            if ($this->verbose > 0) {
                print "\n\nQuery:\n" . $this->db->buildSQL($sql) . "\n";
            } 
            $this->assertEquals($n_table, $n_result);
            $this->addData($result, $table_name);
        } // end loop over tables
        
    }

    function testSelect2()
    {

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        $this->assertNotError($report);
        $report['order'] = 'LastName';
        $filter = "Street.City = 'MINNETONKA'";
        $result = $this->db->select($report, $filter);
        $this->assertNotError($result);
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL($report, $filter) . "\n";
        }
        $this->assertEquals(count($result), 10);
        $this->addData($result, 'Result');
    }

    function testSelect3()
    {

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        $this->assertNotError($report);
        $this->db->sql['report'] = $report;
        $result = $this->db->select('report', 
                                    "Street.City = 'MINNETONKA'",
                                    'FirstName');
        $this->assertNotError($result);
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL('report', 
                                   "Street.City = 'MINNETONKA'",
                                   'FirstName') . "\n";
        }
        $this->assertEquals(count($result), 10);
        $this->addData($result, 'Result');
    }

    function testSelect4() 
    {
        $db =& $this->db;
        $result = $db->select(1);
        $this->assertIsError($result);
    }

    function testSelect5() 
    {
        $db =& $this->db;
        $result = $db->select('not_a_key');
        $this->assertIsError($result);
    }


    function testSelectResult1()
    {
        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        $this->assertNotError($report);
        $report['order'] = 'LastName';
        $result = $this->db->selectResult($report, "Street.City = 'MINNETONKA'");
        $this->assertNotError($result);
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL($report, "Street.City = 'MINNETONKA'")
                  . "\n";
        }
        // Convert DB/MDB2_Result object to array
        $i = 0;
        $data = array();
        while ($row = $result->fetchRow()) {
            $data[] = $row;
            $i = $i + 1;
        }
        $this->assertEquals($i, 10);
        $this->addData($data, 'Result');
    }

    function testSelectResult2()
    {

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        $this->assertNotError($report);
        $report['order'] = 'LastName';
        $this->db->sql['report'] = $report;
        $result = $this->db->selectResult('report',
                                          "Street.City = 'MINNETONKA'");
        $this->assertNotError($result);
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL('report', "Street.City = 'MINNETONKA'")
                  . "\n";
        }

        // Convert DB/MDB2_Result object to array
        $i = 0;
        $data = array();
        while ($row = $result->fetchRow()) {
            $data[] = $row;
            $i = $i + 1;
        }

        // Test number of rows and contents of result set
        $this->assertEquals($i,10);
        $this->addData($data, 'Result');
    }

    function testSelectResult3() 
    {
        $db =& $this->db;
        $result = $db->selectResult(1);
        $this->assertIsError($result);
    }

    function testSelectResult4() 
    {
        $db =& $this->db;
        $result = $db->selectResult('not_a_key');
        $this->assertIsError($result);
    }


    function testSelectCount1()
    {

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        $this->assertNotError($report);
        $report['order'] = 'LastName';
        $result = $this->db->selectCount($report, "Street.City = 'MINNETONKA'");
        $this->assertNotError($result);
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL($report, "Street.City = 'MINNETONKA'")
                  . "\n";
            print "\nCount = $result\n";
        }
        $this->assertEquals($result, '10');
    }

    function testSelectCount2()
    {

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        $this->assertNotError($report);
        $result = $this->db->selectCount($report,
                             "Street.City = 'EDEN PRAIRIE'");
        $this->assertNotError($result);
        if ($this->verbose > 0) {
            print "\n\nQuery:\n" . 
                  $this->db->buildSQL($report,
                             "Street.City = 'EDEN PRAIRIE'") . "\n";
            print "\nCount = $result\n";
        }
        $this->assertEquals($result, '8');
    }

}

?>
