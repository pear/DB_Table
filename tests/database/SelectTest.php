<?php
#require_once 'PHPUnit/TestCase.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'DB/Table/Database.php';

#class SelectTest extends PHPUnit_TestCase {
class SelectTest extends PHPUnit2_Framework_TestCase {

    var $name = null;
    var $conn = null;
    var $db   = null;
    var $fetchmod_assoc = null;
    var $fetchmod_order = null;

    function setUp() 
    {
        // Create DB_Table_Database object $db1 and insert data
        require 'db1/insert.php';

        $this->name = $db_name;
        $this->conn =& $conn;
        $this->db   =& $db1;
        $this->verbose = $verbose;
        $this->db_conn = $db_conn;

        if ($this->db->_backend == 'mdb2') {
            $this->fetchmode_assoc = MDB2_FETCHMODE_ASSOC;
        } else {
            $this->fetchmode_assoc = DB_FETCHMODE_ASSOC;
        }
        if ($this->db->_backend == 'mdb2') {
            $this->fetchmode_order = MDB2_FETCHMODE_ORDERED;
        } else {
            $this->fetchmode_order = DB_FETCHMODE_ORDERED;
        }

        // Copy properties of db 
        foreach ($properties as $property_name) {
            $this->$property_name = $$property_name;
        }

        // Copy arrays containing initial table data
        foreach ($table_arrays as $table_name => $array) {
            $this->$table_name = $array;
        }
 
    }

    function tearDown() {
        if (!$this->db_conn) {
           // print "Dropping Database\n";
           $this->conn->query("DROP DATABASE {$this->name}");
        } else {
            $tables = $this->db->getTable();
            foreach ($tables as $table) {
                $name = $table->table;
                $this->conn->query("DROP Table $name");
            }
            $this->conn->query("DROP Table DataFile");
            $this->conn->query("DROP Table Person_seq");
            $this->conn->query("DROP Table Address_seq");
            $this->conn->query("DROP Table Phone_seq");
        }
        // print "Disconnecting\n";
        $this->conn->disconnect();
    }
  
    function testSelect1()
    {
        print "\n" . ">testSelect1";

        // Loop over tables
        $success = true;
        $tables = $this->db->getTable();
        foreach ($tables as $table_name => $table_obj) {
            $sql = array('select'    => '*',
                         'from'      => $table_name,
                         'fetchmode' => $this->fetchmode_assoc);
            $result = $this->db->select($sql);
            if (PEAR::isError($result)){
                print $result->getMessage();
                $this->assertTrue(false);
                return;
            }
            $n_result = count($result);
            $n_table  = count($this->$table_name);
            if ($this->verbose > 0) {
                if ($this->verbose == 1) {
                    print "\n" . "$table_name $n_result $n_table";
                } else {
                    print "\nTable $table_name:";
                    foreach ($result as $row){
                        $s = array();
                        foreach ($row as $key => $value){
                            $s[] = "$key => $value";
                        }
                        print "\n" . implode(', ', $s);
                    }
                    print "\n" . "Count $n_result, $n_table";
                } 
            }
            if ($n_result != $n_table) {
                $success = false;
            }
        } // end loop over tables
        $this->assertTrue($success);
        
    }

    function testSelect2()
    {
        print "\n" . ">testSelect2";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $report['order'] = 'LastName';
        $result = $this->db->select($report, "Street.City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            foreach ($result as $row){
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = (string) $value;
                }
                print "\n" . implode(',', $s);
            }
        }
        $this->assertEquals(count($result), 10);
    }

    function testSelect3()
    {
        print "\n" . ">testSelect3";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $this->db->sql['report'] = $report;
        $result = $this->db->select('report',
                                    "Street.City = 'MINNETONKA'",
                                    'FirstName');
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            foreach ($result as $row){
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = (string) $value;
                }
                print "\n" . implode(',', $s);
            }
        }
        $this->assertEquals(count($result), 10);
    }

    function testSelectResult1()
    {
        print "\n" . ">testSelectResult1";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $report['order'] = 'LastName';
        $result = $this->db->selectResult($report, "Street.City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        $i = 0;
        while ($result->fetchInto($row)) {
            if ($this->verbose > 0) {
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = (string) $value;
                }
                print "\n" . implode(',', $s);
            }
            $i = $i + 1;
        }
        $this->assertEquals($i,10);
    }

    function testSelectResult2()
    {
        print "\n" . ">testSelectResult2";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $report['order'] = 'LastName';
        $this->db->sql['report'] = $report;
        $result = $this->db->selectResult('report',
                                          "Street.City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        $i = 0;
        while ($result->fetchInto($row)) {
            if ($this->verbose > 0) {
                $s = array();
                foreach ($row as $key => $value){
                    $s[] = (string) $value;
                }
                print "\n" . implode(',', $s);
            }
            $i = $i + 1;
        }
        $this->assertEquals($i,10);
    }

    function testSelectCount1()
    {
        print "\n" . ">testSelectCount1";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $report['order'] = 'LastName';
        $result = $this->db->selectCount($report, "Street.City = 'MINNETONKA'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            print "\n". "Count = $result";
        }
        $this->assertEquals($result, '10');
    }

    function testSelectCount2()
    {
        print "\n" . ">testSelectCount2";

        $cols = array();
        $cols[] = 'LastName';
        $cols[] = 'FirstName';
        $cols[] = 'PhoneNumber';
        $cols[] = 'Building';
        $cols[] = 'Street';
        $cols[] = 'City';
        $cols[] = 'ZipCode';
        $report = $this->db->autoJoin($cols);
        if (PEAR::isError($report)) {
            print "\n" . $report->getMessage();
            $this->assertTrue(false);
            return;
        }
        $result = $this->db->selectCount($report,
                             "Street.City = 'EDEN PRAIRIE'");
        if (PEAR::isError($result)) {
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
            return;
        }
        if ($this->verbose > 0) {
            print "\n" . "Count = $result";
        }
        $this->assertEquals($result, '8');
    }

}

?>
