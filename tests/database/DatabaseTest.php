<?php
require_once 'DB/Table/Database.php';
require_once dirname(__FILE__) . '/../DataTestCase.php';

class DatabaseTest extends DataTestCase {

    var $insert  = true;

    var $db_name = null;
    var $conn    = null;
    var $db      = null;
    var $db_conn = null;
    var $fetchmode_assoc = null;
    var $fetchmode_order = null;

    function setUp()
    {
        $this->verbose = $GLOBALS['verbose'];
        $this->name = $GLOBALS['db_name'];

        // Create DB_Table_Database object $db and insert data
        if ($this->insert) {
            require dirname(__FILE__) . '/db1/insert.php';
        } else {
            require dirname(__FILE__) . '/db1/define.php';
        }
        $db->setTableSubclassPath('db1');

        $this->db_name = $db_name;
        $this->conn    =& $conn;
        $this->db      =& $db;
        $this->db_conn = $db_conn;

        if ($this->db->backend == 'mdb2') {
            $this->fetchmode_assoc = MDB2_FETCHMODE_ASSOC;
            $this->fetchmode_order = MDB2_FETCHMODE_ORDERED;
        } else {
            $this->fetchmode_assoc = DB_FETCHMODE_ASSOC;
            $this->fetchmode_order = DB_FETCHMODE_ORDERED;
        }

        // Copy expected values of properties of $db
        foreach ($properties as $property_name) {
            $this->$property_name = $$property_name;
        }

        // Copy arrays containing contents of tables of $db
        if ($this->insert) {
            foreach ($table_arrays as $table_name => $array) {
                $this->$table_name = $array;
            }
        }

        // Print announcement of test method name
        if ($this->verbose > -1) {
            print "\n>" . $this->getName();
        }

    }

    function tearDown() {

        // Drop all tables from database
        if ($this->insert) {
            if (!$this->db_conn) {
               // print "\nDropping Database";
//               $this->conn->query("DROP DATABASE {$this->name}");
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
            // print "\nDisconnecting";
            $this->conn->disconnect();
        }
    }

}

?>
