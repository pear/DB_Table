<?php

/**
 * DB_Table_Generator - Generates DB_Table subclass skeleton code
 *
 * PHP version 4 and 5
 *
 * @category Database
 * @package  DB_Table
 * @author   David C. Morse <morse@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  $Id$
 */

// {{{ Error code constants

/**
 * Parameter is not a DB/MDB2 object
 */
define('DB_TABLE_GENERATOR_ERR_DB_OBJECT', -301);

/**
 * DB_Table table abstraction class
 */
require_once 'DB/Table.php';

/**
 * The PEAR class for errors
 */
require_once 'PEAR.php';

/** 
 * US-English default error messages. 
 */
$GLOBALS['_DB_TABLE_GENERATOR']['default_error'] = array(
        DB_TABLE_GENERATOR_ERR_DB_OBJECT =>
        'Invalid DB/MDB2 object parameter. Function'
    );

// merge default and user-defined error messages
if (!isset($GLOBALS['_DB_TABLE_GENERATOR']['error'])) {
    $GLOBALS['_DB_TABLE_GENERATOR']['error'] = array();
}
foreach ($GLOBALS['_DB_TABLE_GENERATOR']['default_error'] as $code => $message) {
    if (!array_key_exists($code, $GLOBALS['_DB_TABLE_GENERATOR']['error'])) {
        $GLOBALS['_DB_TABLE_GENERATOR']['error'][$code] = $message;
    }
}

// {{{ DB_Table_Generator

class DB_Table_Generator
{

    // {{{ properties

    /**
     * Name of the database
     *
     * @var    string
     * @access public
     */
    var $name   = null;

    /**
     * The PEAR DB/MDB2 object that connects to the database.
     *
     * @var    object
     * @access private
     */
    var $_db = null;

    /**
     * The backend type, which may be 'db' or 'mdb2'
     *
     * @var    string
     * @access private
     */
    var $_backend = null;

    /**
    * If there is an error on instantiation, this captures that error.
    *
    * This property is used only for errors encountered in the constructor
    * at instantiation time.  To check if there was an instantiation error...
    *
    * <code>
    * $obj =& new DB_Table_Generator();
    * if ($obj->error) {
    *     // ... error handling code here ...
    * }
    * </code>
    *
    * @var    object PEAR_Error
    * @access public
    */
    var $error = null;

    /**
     * Numerical array of table names
     *
     * @var    array
     * @access public
     */
    var $tables = array();

    /**
     * class being extended 
     *
     * @var    string
     * @access public
     */
    var $extends = 'DB_Table';

    /**
     * line to use for require('DB/DataObject.php');
     *
     * @var    string
     * @access public
     */
    var $extends_file = "DB/Table.php";

    /**
     * Suffix to add to end of table nams to obtain class names
     *
     * @var    string
     * @access public
     */
    var $class_suffix = "_Table";

    /**
     * Suffix to add to end of table nams to obtain class names
     *
     * @var    string
     * @access public
     */
    var $class_location = "/var/lib/DB_Table/project";

    /**
     * Array of column definitions
     *
     * Array $this->columns[table][column] = column definition
     * The column definition is as returned by tableInfo().
     *
     * @var    array
     * @access public
     */
    var $columns = array();

    /**
     * Array of column definitions, as returned by getConstraint
     *
     * Array $this->indexes[table][index name] = column definition
     *
     * @var    array
     * @access public
     */
    var $indexes = array();

    /**
     * MDB2 'idxname_format' option, format of index names, for use 
     * in fstring().
     */
    var $idxname_format = '%s';

    /**
     * Constructor
     *
     * If an error is encountered during instantiation, the error
     * message is stored in the $this->error property of the resulting
     * object. See $error property docblock for a discussion of error
     * handling. 
     * 
     * @param  object $db   DB/MDB2 database connection object
     * @param  string $name the database name
     * @return object DB_Table_Generator
     * @access public
     */
    function DB_Table_Generator(&$db, $name)
    {
        // Is $db an DB/MDB2 object or null?
        if (is_a($db, 'db_common')) {
            $this->_backend = 'db';
        } elseif (is_a($db, 'mdb2_driver_common')) {
            $this->_backend = 'mdb2';
        } else {
            $this->error =& DB_Table_Generator::throwError(
                            DB_TABLE_GENERATOR_ERR_DB_OBJECT,
                            "DB_Table_Generator");
            return;
        }
        $this->_db  =& $db;
        $this->name = $name;

    }

    /**
     * Specialized version of throwError() modeled on PEAR_Error.
     * 
     * Throws a PEAR_Error with a DB_Table_Generator error message based 
     * on a DB_Table_Generator constant error code.
     * 
     * @param string $code  A DB_Table_Generator error code constant.
     * @param string $extra Extra text for the error (in addition to the 
     *                      regular error message).
     * @return object PEAR_Error
     * @access public
     * @static
     */
    function &throwError($code, $extra = null)
    {
        // get the error message text based on the error code
        $text = 'DB_TABLE_GENERATOR ERROR - ' . "\n" .
                $GLOBALS['_DB_TABLE_GENERATOR']['error'][$code];
        
        // add any additional error text
        if ($extra) {
            $text .= ' ' . $extra;
        }
        
        // done!
        $error = PEAR::throwError($text, $code);
        return $error;
    }
   
    /**
     * Overwrites one or more error messages, e.g., to internationalize them.
     * 
     * @param mixed $code If string, the error message with code $code will be
     *                    overwritten by $message. If array, each key is a code
     *                    and each value is a new message. 
     * 
     * @param string $message Only used if $key is not an array.
     * @return void
     * @access public
     */
    function setErrorMessage($code, $message = null) {
        if (is_array($code)) {
            foreach ($code as $single_code => $single_message) {
                $GLOBALS['_DB_TABLE_GENERATOR']['error'][$single_code] 
                    = $single_message;
            }
        } else {
            $GLOBALS['_DB_TABLE_GENERATOR']['error'][$code] = $message;
        }
    }

    /**
     * Get a list of tables from the database
     * and store it in $this->tables and $this->columns[tablename];
     *
     * @access  public
     * @return  none
     */
    function getTableNames()
    {

        if ($this->_backend == 'db') {
            // try getting a list of schema tables first. (postgres)
            $this->_db->expectError(DB_ERROR_UNSUPPORTED);
            $this->tables = $this->_db->getListOf('schema.tables');
            $this->_db->popExpect();
            if (PEAR::isError($this->tables)) {
                // try getting a list of schema tables first. (postgres)
                $this->_db->expectError(DB_ERROR_UNSUPPORTED);
                $this->tables = $this->_db->getListOf('tables');
                $this->_db->popExpect();
            }
        } else {
            $this->_db->setOption('portability', MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_FIX_CASE);
            $this->_db->loadModule('Manager');
            $this->_db->loadModule('Reverse');
            $this->tables = $this->_db->manager->listTables();
            $sequences = $this->_db->manager->listSequences();
            foreach ($sequences as $k => $v) {
                $this->tables[] = $this->_db->getSequenceName($v);
            }
        }
    }

    function getTableDefinition($table) 
    {
        #// postgres strip the schema bit from the
        #if (!empty($options['generator_strip_schema'])) {
        #    $bits = explode('.', $table,2);
        #    $table = $bits[0];
        #    if (count($bits) > 1) {
        #        $table = $bits[1];
        #    }
        #}
        $db =& $this->_db;
        if ($this->_backend == 'db') {

            $defs =  $db->tableInfo($table);
            $this->columns[$table] = $defs;

        } else {

            // Columns
            $defs =  $db->reverse->tableInfo($table);
            // rename the 'length' key, so it matches db's return.
            foreach ($defs as $k => $v) {
                if (isset($defs[$k]['length'])) {
                    $defs[$k]['len'] = $defs[$k]['length'];
                }
            }
            $this->columns[$table] = $defs;

            // Temporarily reset $idxname_format MDB2 option 
            $idxname_format = $db->getOption('idxname_format');
            $db->setOption('idxname_format', $this->idxname_format);

            // Constraints/Indexes
            $this->indexes[$table] = array();
            $constraints = $db->manager->listTableConstraints($table);
            if (!PEAR::isError($constraints)) {
                foreach ($constraints as $c_name) {
                    $cdef = $db->reverse->getTableConstraintDefinition($table, $c_name);
                    if (!PEAR::isError($cdef)) {
                        $this->indexes[$table][$c_name] = $cdef;
                    }
                }
            }
            $indexes = $db->manager->listTableIndexes($table);
            if (!PEAR::isError($indexes)) {
                foreach ($indexes as $i_name) {
                    $idef = $db->reverse->getTableIndexDefinition($table, $i_name);
                    if (!PEAR::isError($idef)) {
                        $this->indexes[$table][$i_name] = $idef;
                    }
                }
            }

            // Restore original MDB2 idxname_format
            $db->setOption('idxname_format', $idxname_format);
        }
    }

    /**
     * Returns skeleton DB_Table subclass definition, as php code
     *
     * @access public
     * @return skeleton subclass definition
     */
    function tableClass($table, $indent = '')
    {
        $s = array();
        $s[] = $indent . 'class ' . $table . 
               ' extends ' . $this->extends . " {\n";
        $indent = $indent . '    ';
        $s[] = $indent . 'var $col = array(' . "\n";
        $u   = array(); 
        $indent = $indent . '    ';
       
        $idx = array();
        // Begin loop over columns
        foreach($this->columns[$table] as $t) {

            $name = $t['name'];
            $col  = array();
             
            switch (strtoupper($t['type'])) {
                case 'INT2':     // postgres
                case 'TINYINT':
                case 'TINY':     //mysql
                case 'SMALLINT':
                    $col['type'] = 'smallint';
                    break;
                case 'INT4':     // postgres
                case 'SERIAL4':  // postgres
                case 'INT':
                case 'SHORT':    // mysql
                case 'INTEGER':
                case 'MEDIUMINT':
                case 'YEAR':
                    $col['type'] = 'integer';
                    break;
                case 'BIGINT':
                case 'LONG':     // mysql
                case 'INT8':     // postgres
                case 'SERIAL8':  // postgres
                    $col['type'] = 'bigint';
                    break;
                case 'REAL':
                case 'NUMERIC':
                case 'NUMBER': // oci8 
                case 'FLOAT':  // mysql
                case 'FLOAT4': // real (postgres)
                    $col['type'] = 'single';
                    break;
                case 'DOUBLE':
                case 'DOUBLE PRECISION': // double precision (firebird)
                case 'FLOAT8': // double precision (postgres)
                    $col['type'] = 'double';
                    break;
                case 'DECIMAL':
                case 'MONEY':  // mssql and maybe others
                    $col['type'] = 'decimal';
                    break;
                case 'BIT':
                case 'BOOL':   
                case 'BOOLEAN':   
                    $col['type'] = 'boolean';
                    break;
                case 'STRING':
                case 'CHAR':
                    $col['type'] = 'char';
                    break;
                case 'VARCHAR':
                case 'VARCHAR2':
                case 'TINYTEXT':
                    $col['type'] = 'varchar';
                    break;
                case 'TEXT':
                case 'MEDIUMTEXT':
                case 'LONGTEXT':
                    $col['type'] = 'clob';
                    break;
                case 'ENUM':
                case 'SET':         // not really but oh well
                case 'TIMESTAMPTZ': // postgres
                case 'BPCHAR':      // postgres
                case 'INTERVAL':    // postgres (eg. '12 days')
                case 'CIDR':        // postgres IP net spec
                case 'INET':        // postgres IP
                case 'MACADDR':     // postgress network Mac address.
                case 'INTEGER[]':   // postgres type
                case 'BOOLEAN[]':   // postgres type
                    $col['type'] = 'varchar';
                    break;
                case 'DATE':    
                    $col['type'] = 'date';
                    break;
                case 'TIME':    
                    $col['type'] = 'time';
                    break;
                case 'DATETIME':   // mysql
                case 'TIMESTAMP':
                    $col['type'] = 'timestamp';
                    break;
                default:     
                    echo "**********************************************\n".
                         "**               WARNING UNKNOWN TYPE       **\n".
                         "** Column '{$t->name}' of type '{$t->type}' **\n".
                         "** Please submit a bug, describe what type  **\n".
                         "** you expect this column  to be            **\n".
                         "**********************************************\n";
                    break;
            }
         
            if (in_array($col['type'], array('char','varchar','decimal'))) { 
                if (isset($t['len'])) {
                    $col['size'] = (int) $t['len'];
                }
                if ($col['type'] == 'decimal') { 
                    $col['scope'] = 2;
                }
            }
            if (isset($t['notnull'])) {
                if ($t['notnull']) {
                   $col['required'] = true;
                }
            }
            if (isset($t['autoincrement'])) {
                $auto_inc_col = $name;
            }
            if (isset($t['flags'])){ 
                $flags = $t['flags'];
                if (preg_match('/not[ _]null/i',$flags)) {
                    $col['required'] = true;
                }
                if (preg_match("/(auto_increment|nextval\()/i", $flags)) {
                    $auto_inc_col = $name;
                } 
                if (preg_match("/(primary)/i", $flags)) {
                    $idx[$name] = array(
                        'type' => 'primary', 
                        'cols' => $name
                    );
                }
                elseif (preg_match("/(unique)/i", $flags)) {
                    $idx[$name] = array(
                        'type' => 'unique', 
                        'cols' => $name
                    );
                }
            }
            $required = isset($col['required']) ? $col['required'] : false;
            if ($required) {
                if (isset($t['default'])) {
                    $default = $t['default'];
                    $type    = $col['type'];
                    if (in_array($type, 
                                 array('smallint','integer','bigint'))) {
                        $default = (int) $default;
                    } elseif (in_array($type, array('single','double'))) {
                        $default = (float) $default;
                    } elseif ($type == 'boolean') {
                        $default = (int) $default ? 1 : 0;
                    }
                    $col['default'] = $default;
                }
            }

            // Generate DB_Table column definitions as php code
            $v = $indent . "'" . $name . "' => array(\n";
            $indent = $indent . '    ';
            $t = array();
            foreach ($col as $key => $value) {
                if (is_string($value)) {
                    $value = "'" . $value . "'";
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } else {
                    $value = (string) $value;
                }
                $t[] = $indent . "'" . $key . "'" . ' => ' . $value ;
            }
            $v = $v . implode($t,",\n") . "\n";
            $indent = substr($indent, 0, -4);
            $v = $v . $indent . ")";
            $u[] = $v;

        } //end loop over columns
        $s[] = implode($u,",\n\n") . "\n";
        $indent = substr($indent, 0, -4);
        $s[] = $indent . ");\n";

        // Generate index definitions, if any, as php code
        #if (count($idx) > 0) {
        if (count($this->indexes[$table]) > 0) {
            $s[] = $indent . 'var $idx = array(' . "\n";
            $indent = $indent . '    ';
            $u = array(); 
            #foreach ($idx as $name => $def) {
            #    $v = $indent . "'" . $name . "' => array(\n";
            #    $indent = $indent . '    ';
            #    $t = array();
            #    foreach ($def as $key => $value) {
            #        if (is_string($value)) {
            #            $value = "'" . $value . "'";
            #        }
            #        $t[] = $indent . "'" . $key . "'" . 
            #               ' => ' . $value ;
            #    }
            #    $v = $v . implode($t,",\n") . "\n";
            #    $indent = substr($indent, 0, -4);
            #    $v = $v . $indent . ")";
            #    $u[] = $v;
            #}
            foreach ($this->indexes[$table] as $name => $def) {
                 $v = $indent . "'" . $name . "' => array(\n";
                 $indent = $indent . '    ';
                 $primary = isset($def['primary']) ? $def['primary'] : false;
                 $unique  = isset($def['unique']) ? $def['unique'] : false;
                 if ($primary) {
                     $type = 'primary';
                 } elseif ($unique) {
                     $type = 'unique';
                 } else {
                     $type = 'normal';
                 }

                 $v = $v . $indent . "'type' => '$type',\n";
                 $fields = $def['fields'];
                 if (count($fields) == 1) {
                     foreach ($fields as $key => $value) {
                         $v = $v . $indent . "'cols' => $key\n";
                     }
                 } else {
                     $v = $v . $indent . "'cols' => array(\n";
                     $indent = $indent . '    ';
                     $t = array();
                     foreach ($fields as $key => $value) {
                         $t[] = $indent . $key;
                     }
                     $v = $v . implode($t,",\n") . "\n";
                     $indent = substr($indent, 0, -4);
                     $v = $v . $indent . ")\n";
                 }
                 $indent = substr($indent, 0, -4);
                 $v = $v . $indent . ")";
                 $u[] = $v;
            }
            $s[] = implode($u,",\n\n") . "\n";
            $indent = substr($indent, 0, -4);
            $s[] = $indent . ");\n";
        } else {
            $s[] = $indent . 'var $idx = array();' . "\n";
        }

        if (isset($auto_inc_col)) {
           $s[] = $indent . 'var $auto_inc_col = ' . "'$auto_inc_col';\n";
        }
        $indent = substr($indent, 0, -4);
        $s[] = $indent . '}';
        return implode($s,"\n") . "\n\n";
        
    }

    /**
     * Returns string containing all table class definitions
     *
     * The returned string contains the contents of a single php
     * file with definitions of DB_Table subclasses associated with 
     * all of the tables in $this->tables. The string includes the 
     * opening and closing <?php and ?> symbols and the require_once 
     * line needed to include the DB_Table class that is being 
     * extended. To use, write the string to a new php file. 
     *
     * Usage:
     * <code>
     *     $generator = DB_Table_Generator($db, $database);
     *     $generator->getTableNames();
     *     $generator->getTableDefinitions();
     *     print $generator->allTablesClass();
     * <code>
     * 
     */
    function allTableClasses() 
    {
        $s = array();
        $s[] = "<?php";
        $s[] = "require_once '{$this->extends_file}';\n";
        foreach($this->tables as $table) {
            $this->getTableDefinition($table);
            $s[] = $this->tableClass($table) ;
        }
        $s[] = '?>';
        return implode($s,"\n");
    }

    /**
     * Convert a table name into a class name 
     *
     * Converts all non-alphanumeric characters to '_', capitalizes first 
     * letter, and adds $this->class_suffix to end. Override this if you 
     * want something else.
     *
     * @access  public
     * @return  string class name;
     */
    function ClassName($table)
    {
        $name = preg_replace('/[^A-Z0-9]/i','_',ucfirst(trim($table)));
        return  $name . $this->class_suffix;
    }
    
    
    /**
    * Returns the name of a file containing a class definition
    *
    * @access  public
    * @return  string file name;
    */
    
    
    function ClassFileName($class_name)
    {
        $base = $this->class_location;
        if (!file_exists($base)) {
            require_once 'System.php';
            System::mkdir(array('-p',$base));
        }
        $outfilename = "{$base}/" . $class_name . ".php" ;
        return $outfilename;
        
    }
    
    
    /*
     * building the class files
     * for each of the tables output a file!
     */
    function generateClasses()
    {
       
        foreach($this->tables as $this->table) {
            $this->table        = trim($this->table);
            $this->classname    = $this->getClassNameFromTableName($this->table);
            $i = '';
            $outfilename        = $this->getFileNameFromTableName($this->table);
            
            $oldcontents = '';
            if (file_exists($outfilename)) {
                // file_get_contents???
                $oldcontents = implode('',file($outfilename));
            }
            
            $out = $this->_generateClassTable($oldcontents);
            $this->debug( "writing $this->classname\n");
            $fh = fopen($outfilename, "w");
            fputs($fh,$out);
            fclose($fh);
        }
        //echo $out;
    }

}
