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
 * DB_Table_Manager class - used to reverse engineer indices
 */
require_once 'DB/Table/Manager.php';

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
     * Numerical array of table name strings
     *
     * @var    array
     * @access public
     */
    var $tables = array();

    /**
     * Class being extended (DB_Table or generic subclass)
     *
     * @var    string
     * @access public
     */
    var $extends = 'DB_Table';

    /**
     * Path to definition of the class $this->extends 
     *
     * @var    string
     * @access public
     */
    var $extends_file = 'DB/Table.php';

    /**
     * Suffix to add to table names to obtain corresponding class names
     *
     * @var    string
     * @access public
     */
    var $class_suffix = "_DB_Table";

    /**
     * Path to directory in which subclass definitions should be written
     *
     * @var    string
     * @access public
     */
    var $class_location = ".";

    /**
     * Array of column definitions
     *
     * Array $this->columns[table_name][column_name] = column definition.
     * Column definition is the array returned by DB/MDB2::tableInfo().
     *
     * @var    array
     * @access public
     */
    var $columns = array();

    /**
     * Array of index/constraint definitions.
     *
     * Array $this->indexes[table][index name] = Index definition. Index
     * definition is an array returned by getTable<Constraint|Index>()
     *
     * @var    array
     * @access public
     */
    var $indexes = array();

    /**
     * MDB2 'idxname_format' option, format of index names 
     *
     * For use in fprint() formatting. Use '%s' to use index names returned
     * by getTableConstraints/Indexes, '%s_idx' to '_idx' suffix.
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
     * @param  string $name database name string
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
    function setErrorMessage($code, $message = null) 
    {
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
     * Gets a list of tables from the database
     * 
     * Names are stored in the $this->tables array
     *
     * @access  public
     * @return  void
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
            if (!PEAR::isError($sequences)) {
                foreach ($sequences as $k => $v) {
                    $sequence = $this->_db->getSequenceName($v);
                    if (!PEAR::isError($sequence)) {
                        $this->tables[] = $sequence;
                    }
                }
            }
        }
    }

    /**
     * Gets column and (if possible) index definitions by querying database
     * 
     * Column definitions are stored in this $this->columns and index
     * definitions (if any) in $this->indexes. Calls DB/MDB2::tableInfo()
     * to obtain column definitions. For MDB2 only, use Manager and 
     * Reverse module functions to obtain index definitions.
     *
     * @access  public
     * @return  none
     */
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

            // Temporarily reset 'idxname_format' MDB2 option to $this->idx_format
            $idxname_format = $db->getOption('idxname_format');
            $db->setOption('idxname_format', $this->idxname_format);

            // Constraints/Indexes
            $this->indexes[$table] = DB_Table_Manager::getIndexes($db, $table);

            // Restore original MDB2 idxname_format
            $db->setOption('idxname_format', $idxname_format);
        }
    }

    /**
     * Returns one skeleton DB_Table subclass definition, as php code
     *
     * @access public
     * @return skeleton subclass definition
     */
    function tableClass($table, $indent = '')
    {
        $s   = array();
        $idx = array();
        $s[] = $indent . 'class ' . $this->className($table) . 
               ' extends ' . $this->extends . " {\n";
        $indent = $indent . '    ';
        $s[] = $indent . 'var $col = array(' . "\n";
        $u   = array(); 
        $indent = $indent . '    ';
       
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
                    $col['type'] = $t['type'] . ' (Unknown type)';
                    break;
            }
        
            // Set length and scope if required 
            if (in_array($col['type'], array('char','varchar','decimal'))) { 
                if (isset($t['len'])) {
                    $col['size'] = (int) $t['len'];
                } elseif ($col['type'] == 'varchar') { 
                    $col['size'] = 255; // default length
                } elseif ($col['type'] == 'char') { 
                    $col['size'] = 128; // default length
                } elseif ($col['type'] == 'decimal') { 
                    $col['size'] =  15; // default length
                }
                if ($col['type'] == 'decimal') { 
                    $col['scope'] =  2;
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
        $n_idx = 0;
        $u = array(); 
        foreach ($this->indexes[$table] as $type => $indexes) {
            if (count($indexes) > 0) {
                foreach ($indexes as $name => $fields) {
                    if ($n_idx == 0) {
                        $s[] = $indent . 'var $idx = array(' . "\n";
                        $indent = $indent . '    ';
                    }
                    $n_idx = $n_idx + 1;
                    $v = $indent . "'" . $name . "' => array(\n";
                    $indent = $indent . '    ';
                    $v = $v . $indent . "'type' => '$type',\n";
                    if (count($fields) == 1) {
                        foreach ($fields as $key => $value) {
                            $v = $v . $indent . "'cols' => '$value'\n";
                        }
                    } else {
                        $v = $v . $indent . "'cols' => array(\n";
                        $indent = $indent . '    ';
                        $t = array();
                        foreach ($fields as $key => $value) {
                            $t[] = $indent . "'$value'";
                        }
                        $v = $v . implode($t,",\n") . "\n";
                        $indent = substr($indent, 0, -4);
                        $v = $v . $indent . ")\n";
                    }
                    $indent = substr($indent, 0, -4);
                    $v = $v . $indent . ")";
                    $u[] = $v;
                }
            }
        }
        if ($n_idx == 0) {
            $s[] = $indent . 'var $idx = array();' . "\n";
        } else {
            $s[] = implode($u,",\n\n") . "\n";
            $indent = substr($indent, 0, -4);
            $s[] = $indent . ");\n";
        }

        if (isset($auto_inc_col)) {
           $s[] = $indent . 'var $auto_inc_col = ' . "'$auto_inc_col';\n";
        }
        $indent = substr($indent, 0, -4);
        $s[] = $indent . '}';
        return implode($s,"\n") . "\n";
        
    }

    /**
     * Returns a string containing all table class definitions
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
     *     print $generator->allTablesClasses();
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
            $s[] = $this->tableClass($table) . "\n";
        }
        $s[] = '?>';
        return implode($s,"\n");
    }


    /**
     * Writes all table class definitions to separate files
     *
     * Usage:
     * <code>
     *     $generator = DB_Table_Generator($db, $database);
     *     $generator->getTableNames();
     *     $generator->generateTableClasses();
     * <code>
     *
     * @return void
     * @access public 
     */
    function generateTableClasses() 
    {
        foreach($this->tables as $table) {
            $classname = $this->className($table);
            $filename  = $this->classFileName($classname);
            if (!file_exists($filename)) {
                $s = array();
                $s[] = "<?php";
                $s[] = "require_once '{$this->extends_file}';\n";
                $this->getTableDefinition($table);
                $s[] = $this->tableClass($table) ;
                $s[] = '?>';
                $out = implode($s,"\n");
                $file = fopen($filename, "w");
                fputs($file, $out);
                fclose($file);
            }
        }
    }


    /**
     * Convert a table name into a class name 
     *
     * Converts all non-alphanumeric characters to '_', capitalizes 
     * first letter, and adds $this->class_suffix to end. Override 
     * this if you want something else.
     *
     * @param   string $class_name name of table
     * @return  string class name;
     * @access  public
     */
    function className($table)
    {
        $name = preg_replace('/[^A-Z0-9]/i','_',ucfirst(trim($table)));
        return  $name . $this->class_suffix;
    }
    
    
    /**
     * Returns the path to a file containing a class definition
     *
     * Prepends $this->class_location and appends '.php' to class name.
     * Creates directory $this->class_location if it does not exist.
     *
     * @param   string $class_name name of class
     * @return  string file name   
     * @access  public
     */
    function classFileName($class_name)
    {
        $base = $this->class_location;
        if (!file_exists($base)) {
            require_once 'System.php';
            System::mkdir(array('-p',$base));
        }
        $filename = "{$base}/" . $class_name . ".php" ;
        return $filename;
        
    }

}
