<?php

/**
* 
* Creates tables from DB_Table definitions.
* 
* DB_Table_Manager provides database automated table creation
* facilities.
* 
* @category DB
* 
* @package DB_Table
*
* @author Paul M. Jones <pmjones@php.net>
* @author Mark Wiesemann <wiesemann@php.net>
* 
* @license http://www.gnu.org/copyleft/lesser.html LGPL
* 
* @version $Id$
*
*/

require_once 'DB/Table.php';


/**
* 
* Creates tables from DB_Table definitions.
* 
* DB_Table_Manager provides database automated table creation
* facilities.
* 
* @category DB
* 
* @package DB_Table
*
* @author Paul M. Jones <pmjones@php.net>
* @author Mark Wiesemann <wiesemann@php.net>
*
*/

/**
* Valid types for the different data types in the different DBMS.
*/
$GLOBALS['_DB_TABLE']['valid_type'] = array(
    'fbsql' => array(  // currently not supported
        'boolean'   => '',
        'char'      => '',
        'varchar'   => '',
        'smallint'  => '',
        'integer'   => '',
        'bigint'    => '',
        'decimal'   => '',
        'single'    => '',
        'double'    => '',
        'clob'      => '',
        'date'      => '',
        'time'      => '',
        'timestamp' => ''
    ),
    'ibase' => array(
        'boolean'   => array('char', 'integer', 'real', 'smallint'),
        'char'      => array('char', 'varchar'),
        'varchar'   => 'varchar',
        'smallint'  => array('integer', 'smallint'),
        'integer'   => 'integer',
        'bigint'    => array('bigint', 'integer'),
        'decimal'   => 'numeric',
        'single'    => array('double precision', 'float'),
        'double'    => 'double precision',
        'clob'      => 'blob',
        'date'      => 'date',
        'time'      => 'time',
        'timestamp' => 'timestamp'
    ),
    'mssql' => array(  // currently not supported
        'boolean'   => '',
        'char'      => '',
        'varchar'   => '',
        'smallint'  => '',
        'integer'   => '',
        'bigint'    => '',
        'decimal'   => '',
        'single'    => '',
        'double'    => '',
        'clob'      => '',
        'date'      => '',
        'time'      => '',
        'timestamp' => ''
    ),
    'mysql' => array(
        'boolean'   => array('char', 'int', 'real'),
        'char'      => array('char', 'string'),
        'varchar'   => array('char', 'string'),
        'smallint'  => 'int',
        'integer'   => 'int',
        'bigint'    => 'int',
        'decimal'   => 'real',
        'single'    => 'real',
        'double'    => 'real',
        'clob'      => 'blob',
        'date'      => array('char', 'date', 'string'),
        'time'      => array('char', 'string', 'time'),
        'timestamp' => array('char', 'datetime', 'string')
    ),
    'mysqli' => array(
        'boolean'   => array('char', 'decimal', 'tinyint'),
        'char'      => 'varchar',
        'varchar'   => 'varchar',
        'smallint'  => 'int',
        'integer'   => 'int',
        'bigint'    => array('int', 'bigint'),
        'decimal'   => 'decimal',
        'single'    => array('double', 'float'),
        'double'    => 'double',
        'clob'      => 'blob',
        'date'      => array('date', 'varchar'),
        'time'      => array('time', 'varchar'),
        'timestamp' => array('datetime', 'varchar')
    ),
    'oci8' => array(  // currently not supported
        'boolean'   => '',
        'char'      => '',
        'varchar'   => '',
        'smallint'  => '',
        'integer'   => '',
        'bigint'    => '',
        'decimal'   => '',
        'single'    => '',
        'double'    => '',
        'clob'      => '',
        'date'      => '',
        'time'      => '',
        'timestamp' => ''
    ),
    'pgsql' => array(
        'boolean'   => array('bool', 'numeric'),
        'char'      => array('bpchar', 'varchar'),
        'varchar'   => 'varchar',
        'smallint'  => array('int2', 'int4'),
        'integer'   => 'int4',
        'bigint'    => array('int4', 'int8'),
        'decimal'   => 'numeric',
        'single'    => array('float4', 'float8'),
        'double'    => 'float8',
        'clob'      => array('oid', 'text'),
        'date'      => array('bpchar', 'date'),
        'time'      => array('bpchar', 'time'),
        'timestamp' => array('bpchar', 'timestamp')
    ),
    'sqlite' => array(
        'boolean'   => 'boolean',
        'char'      => 'char',
        'varchar'   => array('char', 'varchar'),
        'smallint'  => array('int', 'smallint'),
        'integer'   => array('int', 'integer'),
        'bigint'    => array('int', 'bigint'),
        'decimal'   => array('decimal', 'numeric'),
        'single'    => array('double', 'float'),
        'double'    => 'double',
        'clob'      => array('clob', 'longtext'),
        'date'      => 'date',
        'time'      => 'time',
        'timestamp' => array('datetime', 'timestamp')
    ),
);

/**
* Mapping between DB_Table and MDB2 data types.
*/
$GLOBALS['_DB_TABLE']['mdb2_type'] = array(
    'boolean'   => 'boolean',
    'char'      => 'text',
    'varchar'   => 'text',
    'smallint'  => 'integer',
    'integer'   => 'integer',
    'bigint'    => 'integer',
    'decimal'   => 'decimal',
    'single'    => 'float',
    'double'    => 'float',
    'clob'      => 'clob',
    'date'      => 'date',
    'time'      => 'time',
    'timestamp' => 'timestamp'
);


class DB_Table_Manager {


   /**
    * 
    * Create the table based on DB_Table column and index arrays.
    * 
    * @static
    * 
    * @access public
    * 
    * @param object &$db A PEAR DB/MDB2 object.
    * 
    * @param string $table The table name to connect to in the database.
    * 
    * @param mixed $column_set A DB_Table $this->col array.
    * 
    * @param mixed $index_set A DB_Table $this->idx array.
    * 
    * @return mixed Boolean false if there was no attempt to create the
    * table, boolean true if the attempt succeeded, and a PEAR_Error if
    * the attempt failed.
    * 
    */
    
    function create(&$db, $table, $column_set, $index_set)
    {
        if (is_subclass_of($db, 'db_common')) {
            $backend = 'db';
        } elseif (is_subclass_of($db, 'mdb2_driver_common')) {
            $backend = 'mdb2';
            $db->loadModule('Manager');
        }

        // columns to be created
        $column = array();

        // max. value for scope (only used with MDB2 as backend)
        $max_scope = 0;
        
        // indexes to be created
        $index = array();
        $unique_index = array();
        $normal_index = array();
        
        // is the table name too long?
        if (strlen($table) > 30) {
            return DB_Table::throwError(
                DB_TABLE_ERR_TABLE_STRLEN,
                " ('$table')"
            );
        }
        
        
        // -------------------------------------------------------------
        // 
        // validate each column mapping and build the individual
        // definitions, and note column indexes as we go.
        //
        
        if (is_null($column_set)) {
            $column_set = array();
        }
        
        foreach ($column_set as $colname => $val) {
            
            $colname = trim($colname);
            
            // column name cannot be a reserved keyword
            $reserved = in_array(
                strtoupper($colname),
                $GLOBALS['_DB_TABLE']['reserved']
            );
            
            if ($reserved) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_DECLARE_COLNAME,
                    " ('$colname')"
                );
            }
            
            // column must be no longer than 30 chars
            if (strlen($colname) > 30) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_DECLARE_STRLEN,
                    "('$colname')"
                );
            }
            
            
            // prepare variables
            $type    = (isset($val['type']))    ? $val['type']    : null;
            $size    = (isset($val['size']))    ? $val['size']    : null;
            $scope   = (isset($val['scope']))   ? $val['scope']   : null;
            $require = (isset($val['require'])) ? $val['require'] : null;
            $default = (isset($val['default'])) ? $val['default'] : null;

            if ($backend == 'mdb2') {

                $new_column = array(
                    'type'    => $GLOBALS['_DB_TABLE']['mdb2_type'][$type],
                    'notnull' => $require
                );

                if ($size) {
                    $new_column['length'] = $size;
                }

                // determine integer length to be used in MDB2
                if (in_array($type, array('smallint', 'integer', 'bigint'))) {
                    switch ($type) {
                        case 'smallint':
                            $new_column['length'] = 2;
                            break;
                        case 'integer':
                            $new_column['length'] = 4;
                            break;
                        case 'bigint':
                            $new_column['length'] = 5;
                            break;
                    }
                }

                if ($scope) {
                    $max_scope = max($max_scope, $scope);
                }

                if ($default) {
                    $new_column['default'] = $default;
                }

                $column[$colname] = $new_column;

            } else {

                // get the declaration string
                $result = DB_Table_Manager::getDeclare($db->phptype, $type,
                    $size, $scope, $require, $default);

                // did it work?
                if (PEAR::isError($result)) {
                    $result->userinfo .= " ('$colname')";
                    return $result;
                }

                // add the declaration to the array of all columns
                $column[] = "$colname $result";

            }

        }
        
        
        // -------------------------------------------------------------
        // 
        // validate the indexes.
        //
        
        if (is_null($index_set)) {
            $index_set = array();
        }
        
        foreach ($index_set as $idxname => $val) {
            
            if (is_string($val)) {
                // shorthand for index names: colname => index_type
                $type = trim($val);
                $cols = trim($idxname);
            } elseif (is_array($val)) {
                // normal: index_name => array('type' => ..., 'cols' => ...)
                $type = (isset($val['type'])) ? $val['type'] : 'normal';
                $cols = (isset($val['cols'])) ? $val['cols'] : null;
            }
            
            // index name cannot be a reserved keyword
            $reserved = in_array(
                strtoupper($idxname),
                $GLOBALS['_DB_TABLE']['reserved']
            );
            
            if ($reserved) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_DECLARE_IDXNAME,
                    "('$idxname')"
                );
            }
            
            // are there any columns for the index?
            if (! $cols) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_IDX_NO_COLS,
                    "('$idxname')"
                );
            }
            
            // are there any CLOB columns, or any columns that are not
            // in the schema?
            settype($cols, 'array');
            $valid_cols = array_keys($column_set);
            foreach ($cols as $colname) {
            
                if (! in_array($colname, $valid_cols)) {
                    return DB_Table::throwError(
                        DB_TABLE_ERR_IDX_COL_UNDEF,
                        "'$idxname' ('$colname')"
                    );
                }
                
                if ($column_set[$colname]['type'] == 'clob') {
                    return DB_Table::throwError(
                        DB_TABLE_ERR_IDX_COL_CLOB,
                        "'$idxname' ('$colname')"
                    );
                }
                
            }
            
            // string of column names
            $colstring = implode(', ', $cols);
            
            // we prefix all index names with the table name,
            // and suffix all index names with '_idx'.  this
            // is to soothe PostgreSQL, which demands that index
            // names not collide, even when they indexes are on
            // different tables.
            $newIdxName = $table . '_' . $idxname . '_idx';
            
            // now check the length; must be under 30 chars to
            // soothe Oracle.
            if (strlen($newIdxName) > 30) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_IDX_STRLEN,
                    "'$idxname' ('$newIdxName')"
                );
            }

            // create index entry
            if ($backend == 'mdb2') {

                $idx_cols = array();
                foreach ($cols as $col) {
                    $idx_cols[$col] = array();
                }

                if ($type == 'unique') {
                    $unique_index[$newIdxName] = array('fields' => $idx_cols,
                                                       'unique' => true);
                } elseif ($type == 'normal') {
                    $normal_index[$newIdxName] = array('fields' => $idx_cols);
                } else {
                    return DB_Table::throwError(
                        DB_TABLE_ERR_IDX_TYPE,
                        "'$idxname' ('$type')"
                    );
                }
                
            } else {

                if ($type == 'unique') {
                    $index[] = "CREATE UNIQUE INDEX $newIdxName ON $table ($colstring)";
                } elseif ($type == 'normal') {
                    $index[] = "CREATE INDEX $newIdxName ON $table ($colstring)";
                } else {
                    return DB_Table::throwError(
                        DB_TABLE_ERR_IDX_TYPE,
                        "'$idxname' ('$type')"
                    );
                }

            }
            
        }
        
        
        // -------------------------------------------------------------
        // 
        // now for the real action: create the table and indexes!
        //
        if ($backend == 'mdb2') {

            // save user defined 'decimal_places' option
            $decimal_places = $db->getOption('decimal_places');
            $db->setOption('decimal_places', $max_scope);

            // attempt to create the table
            $result = $db->manager->createTable($table, $column);
            // restore user defined 'decimal_places' option
            $db->setOption('decimal_places', $decimal_places);
            if (PEAR::isError($result)) {
                return $result;
            }

            // save user defined 'idxname_format' option
            $idxname_format = $db->getOption('idxname_format');
            $db->setOption('idxname_format', '%s');

            // attempt to create the unique indexes / constraints
            foreach ($unique_index as $name => $definition) {
                $result = $db->manager->createConstraint($table, $name, $definition);
                if (PEAR::isError($result)) {
                    // restore user defined 'idxname_format' option
                    $db->setOption('idxname_format', $idxname_format);
                    return $result;
                }
            }

            // attempt to create the normal indexes
            foreach ($normal_index as $name => $definition) {
                $result = $db->manager->createIndex($table, $name, $definition);
                if (PEAR::isError($result)) {
                    // restore user defined 'idxname_format' option
                    $db->setOption('idxname_format', $idxname_format);
                    return $result;
                }
            }

            // restore user defined 'idxname_format' option
            $db->setOption('idxname_format', $idxname_format);

        } else {

            // build the CREATE TABLE command
            $cmd = "CREATE TABLE $table (\n\t";
            $cmd .= implode(",\n\t", $column);
            $cmd .= "\n)";

            // attempt to create the table
            $result = $db->query($cmd);
            if (PEAR::isError($result)) {
                return $result;
            }

            // attempt to create the indexes
            foreach ($index as $cmd) {
                $result = $db->query($cmd);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }

        }
        
        // we're done!
        return true;
    }
    
    /**
    * 
    * Verify whether the table and columns exist, whether the columns
    * have the right type and whether the indexes exist.
    * 
    * @static
    * 
    * @access public
    * 
    * @param object &$db A PEAR DB/MDB2 object.
    * 
    * @param string $table The table name to connect to in the database.
    * 
    * @param mixed $column_set A DB_Table $this->col array.
    * 
    * @param mixed $index_set A DB_Table $this->idx array.
    * 
    * @return mixed Boolean true if the verification was successful, and a
    * PEAR_Error if verification failed.
    * 
    */
    
    function verify(&$db, $table, $column_set, $index_set)
    {
        if (is_subclass_of($db, 'db_common')) {
            $backend = 'db';
            $reverse = $db;
            $table_info_mode = DB_TABLEINFO_FULL;
            $table_info_error = DB_ERROR_NEED_MORE_DATA;
        } elseif (is_subclass_of($db, 'mdb2_driver_common')) {
            $backend = 'mdb2';
            $reverse =& $this->db->loadModule('Reverse');
            $table_info_mode = MDB2_TABLEINFO_FULL;
            $table_info_error = MDB2_ERROR_NEED_MORE_DATA;
        }

        // check #1: does the table exist?
        $tableInfo = $reverse->tableInfo($table, $table_info_mode);
        if (PEAR::isError($tableInfo)) {
            if ($tableInfo->getCode() == $table_info_error) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_VER_TABLE_MISSING,
                    "(table='$table')"
                );
            }
            return $tableInfo;
        }

        if (is_null($column_set)) {
            $column_set = array();
        }
        
        foreach ($column_set as $colname => $val) {
            $colname = strtolower(trim($colname));

            // check #2: do all columns exist?
            $order = array_change_key_case($tableInfo['order'], CASE_LOWER);
            if (!array_key_exists($colname, $order)) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_VER_COLUMN_MISSING,
                    "(column='$colname')"
                );
            }

            // check #3: do all columns have the right type?

            // map of valid types for the current RDBMS
            list($phptype, $dbsyntax) = DB_Table::getPHPTypeAndDBSyntax($db);
            $map = $GLOBALS['_DB_TABLE']['valid_type'][$phptype];
            // is it a recognized column type?
            $types = array_keys($map);
            if (!in_array($val['type'], $types)) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_DECLARE_TYPE,
                    "('" . $val['type'] . "')"
                );
            }

            $colindex = $order[$colname];
            $type = strtolower($tableInfo[$colindex]['type']);
            // strip size information (e.g. NUMERIC(9,2) => NUMERIC) if given
            if (($pos = strpos($type, '(')) !== false) {
                $type = substr($type, 0, $pos);
            }
            if (!in_array($type, (array)$map[$val['type']])) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_VER_COLUMN_TYPE,
                    "(column='$colname', type='$type')"
                );
            }

        }

        // check #4: do all indexes exist?
        $table_indexes = array();
        if ($backend == 'mdb2') {

            // save user defined 'idxname_format' option
            $idxname_format = $db->getOption('idxname_format');
            $db->setOption('idxname_format', '%s');

            // get table constraints
            $table_indexes_tmp = $db->manager->listTableConstraints($table);
            if (PEAR::isError($table_indexes_tmp)) {
                // restore user defined 'idxname_format' option
                $db->setOption('idxname_format', $idxname_format);
                return $table_indexes_tmp;
            }

            // get fields of table constraints
            foreach ($table_indexes_tmp as $table_idx_tmp) {
                $index_fields =
                    $db->reverse->getTableConstraintDefinition($table,
                                                               $table_idx_tmp);
                if (PEAR::isError($index_fields)) {
                    // restore user defined 'idxname_format' option
                    $db->setOption('idxname_format', $idxname_format);
                    return $index_fields;
                }
                foreach ($index_fields['fields'] as $key => $value) {
                    $table_indexes[$table_idx_tmp][] = $key;
                }
            }

            // get table indexes
            $table_indexes_tmp = $db->manager->listTableIndexes($table);
            if (PEAR::isError($table_indexes_tmp)) {
                // restore user defined 'idxname_format' option
                $db->setOption('idxname_format', $idxname_format);
                return $table_indexes_tmp;
            }

            // get fields of table indexes
            foreach ($table_indexes_tmp as $table_idx_tmp) {
                $index_fields =
                    $db->reverse->getTableIndexDefinition($table,
                                                          $table_idx_tmp);
                if (PEAR::isError($index_fields)) {
                    // restore user defined 'idxname_format' option
                    $db->setOption('idxname_format', $idxname_format);
                    return $index_fields;
                }
                foreach ($index_fields['fields'] as $key => $value) {
                    $table_indexes[$table_idx_tmp][] = $key;
                }
            }
            // restore user defined 'idxname_format' option
            $db->setOption('idxname_format', $idxname_format);
        }
        else {
            // TODO: add index / constraint verification for PEAR::DB
        }

        if (is_null($index_set)) {
            $index_set = array();
        }
        
        foreach ($index_set as $idxname => $val) {
            
            if (is_string($val)) {
                // shorthand for index names: colname => index_type
                $type = trim($val);
                $cols = trim($idxname);
            } elseif (is_array($val)) {
                // normal: index_name => array('type' => ..., 'cols' => ...)
                $type = (isset($val['type'])) ? $val['type'] : 'normal';
                $cols = (isset($val['cols'])) ? $val['cols'] : null;
            }
            
            // index name cannot be a reserved keyword
            $reserved = in_array(
                strtoupper($idxname),
                $GLOBALS['_DB_TABLE']['reserved']
            );
            
            if ($reserved) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_DECLARE_IDXNAME,
                    "('$idxname')"
                );
            }
            
            // are there any columns for the index?
            if (! $cols) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_IDX_NO_COLS,
                    "('$idxname')"
                );
            }
            
            // are there any CLOB columns, or any columns that are not
            // in the schema?
            settype($cols, 'array');
            $valid_cols = array_keys($column_set);
            foreach ($cols as $colname) {
            
                if (! in_array($colname, $valid_cols)) {
                    return DB_Table::throwError(
                        DB_TABLE_ERR_IDX_COL_UNDEF,
                        "'$idxname' ('$colname')"
                    );
                }
                
                if ($column_set[$colname]['type'] == 'clob') {
                    return DB_Table::throwError(
                        DB_TABLE_ERR_IDX_COL_CLOB,
                        "'$idxname' ('$colname')"
                    );
                }
                
            }
            
            // we prefix all index names with the table name,
            // and suffix all index names with '_idx'.  this
            // is to soothe PostgreSQL, which demands that index
            // names not collide, even when they indexes are on
            // different tables.
            $newIdxName = $table . '_' . $idxname . '_idx';
            
            // now check the length; must be under 30 chars to
            // soothe Oracle.
            if (strlen($newIdxName) > 30) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_IDX_STRLEN,
                    "'$idxname' ('$newIdxName')"
                );
            }
            
            // check index type
            if ($type != 'unique' && $type != 'normal') {
                return DB_Table::throwError(
                    DB_TABLE_ERR_IDX_TYPE,
                    "'$idxname' ('$type')"
                );
            }

            $index_found = false;
            foreach ($table_indexes as $index_name => $index_fields) {
                if (strtolower($index_name) == strtolower($newIdxName)) {
                    $index_found = true;
                    array_walk($cols, create_function('&$value,$key',
                                      '$value = trim(strtolower($value));'));
                    array_walk($index_fields, create_function('&$value,$key',
                                      '$value = trim(strtolower($value));'));
                    foreach ($index_fields as $index_field) {
                        if (($key = array_search($index_field, $cols)) !== false) {
                            unset($cols[$key]);
                        }
                    }
                }
            }

            if (!$index_found) {
                return DB_Table::throwError(
                    DB_TABLE_ERR_VER_IDX_MISSING,
                    "'$idxname' ('$newIdxName')"
                );
            }

            if (count($cols) > 0) {
                // string of column names
                $colstring = implode(', ', $cols);
                return DB_Table::throwError(
                    DB_TABLE_ERR_VER_IDX_COL_MISSING,
                    "'$idxname' ($colstring)"
                );
            }

        }

        return true;
    }


   /**
    * 
    * Alter columns and indexes of a table based on DB_Table column and index
    * arrays.
    * 
    * @static
    * 
    * @access public
    * 
    * @param object &$db A PEAR DB/MDB2 object.
    * 
    * @param string $table The table name to connect to in the database.
    * 
    * @param mixed $column_set A DB_Table $this->col array.
    * 
    * @param mixed $index_set A DB_Table $this->idx array.
    * 
    * @return bool|object True if altering was successful or a PEAR_Error on
    * failure.
    * 
    */
    
    function alter(&$db, $table, $column_set, $index_set)
    {
        // TODO
    }


    /**
    * 
    * Check whether a table exists.
    * 
    * @static
    * 
    * @access public
    * 
    * @param object &$db A PEAR DB/MDB2 object.
    * 
    * @param string $table The table name that should be checked.
    * 
    * @return bool|object True if the table exists, false if not, or a
    * PEAR_Error on failure.
    * 
    */
    
    function tableExists(&$db, $table)
    {
        if (is_subclass_of($db, 'db_common')) {
            $list = $db->getListOf('tables');
        } elseif (is_subclass_of($db, 'mdb2_driver_common')) {
            $db->loadModule('Manager');
            $list = $db->manager->listTables();
        }
        if (PEAR::isError($list)) {
            return $list;
        }
        array_walk($list, create_function('&$value,$key',
                                          '$value = trim(strtolower($value));'));
        return in_array(strtolower($table), $list);
    }


    /**
    * 
    * Get the column declaration string for a DB_Table column.
    * 
    * @static
    * 
    * @access public
    * 
    * @param string $phptype The DB/MDB2 phptype key.
    * 
    * @param string $coltype The DB_Table column type.
    * 
    * @param int $size The size for the column (needed for string and
    * decimal).
    * 
    * @param int $scope The scope for the column (needed for decimal).
    * 
    * @param bool $require True if the column should be NOT NULL, false
    * allowed to be NULL.
    * 
    * @param string $default The SQL calculation for a default value.
    * 
    * @return string|object A declaration string on success, or a
    * PEAR_Error on failure.
    * 
    */
    
    function getDeclare($phptype, $coltype, $size = null, $scope = null,
        $require = null, $default = null)
    {
        // validate char and varchar: does it have a size?
        if (($coltype == 'char' || $coltype == 'varchar') &&
            ($size < 1 || $size > 255) ) {
            return DB_Table::throwError(
                DB_TABLE_ERR_DECLARE_STRING,
                "(size='$size')"
            );
        }
        
        // validate decimal: does it have a size and scope?
        if ($coltype == 'decimal' &&
            ($size < 1 || $size > 255 || $scope < 0 || $scope > $size)) {
            return DB_Table::throwError(
                DB_TABLE_ERR_DECLARE_DECIMAL,
                "(size='$size' scope='$scope')"
            );
        }
        
        // map of column types and declarations for this RDBMS
        $map = $GLOBALS['_DB_TABLE']['type'][$phptype];
        
        // is it a recognized column type?
        $types = array_keys($map);
        if (! in_array($coltype, $types)) {
            return DB_Table::throwError(
                DB_TABLE_ERR_DECLARE_TYPE,
                "('$coltype')"
            );
        }
        
        // basic declaration
        switch ($coltype) {
    
        case 'char':
        case 'varchar':
            $declare = $map[$coltype] . "($size)";
            break;
        
        case 'decimal':
            $declare = $map[$coltype] . "($size,$scope)";
            break;
        
        default:
            $declare = $map[$coltype];
            break;
        
        }
        
        // set the "NULL"/"NOT NULL" portion
        $null = ' NULL';
        if ($phptype == 'ibase') {  // Firebird does not like 'NULL'
            $null = '';             // in CREATE TABLE
        }
        $declare .= ($require) ? ' NOT NULL' : $null;
        
        // set the "DEFAULT" portion
        $declare .= ($default) ? " DEFAULT $default" : '';
        
        // done
        return $declare;
    }
}


/**
* List of all reserved words for all supported databases. Yes, this is a
* monster of a list.
*/
if (! isset($GLOBALS['_DB_TABLE']['reserved'])) {
    $GLOBALS['_DB_TABLE']['reserved'] = array(
        '_ROWID_',
        'ABSOLUTE',
        'ACCESS',
        'ACTION',
        'ADD',
        'ADMIN',
        'AFTER',
        'AGGREGATE',
        'ALIAS',
        'ALL',
        'ALLOCATE',
        'ALTER',
        'ANALYSE',
        'ANALYZE',
        'AND',
        'ANY',
        'ARE',
        'ARRAY',
        'AS',
        'ASC',
        'ASENSITIVE',
        'ASSERTION',
        'AT',
        'AUDIT',
        'AUTHORIZATION',
        'AUTO_INCREMENT',
        'AVG',
        'BACKUP',
        'BDB',
        'BEFORE',
        'BEGIN',
        'BERKELEYDB',
        'BETWEEN',
        'BIGINT',
        'BINARY',
        'BIT',
        'BIT_LENGTH',
        'BLOB',
        'BOOLEAN',
        'BOTH',
        'BREADTH',
        'BREAK',
        'BROWSE',
        'BULK',
        'BY',
        'CALL',
        'CASCADE',
        'CASCADED',
        'CASE',
        'CAST',
        'CATALOG',
        'CHANGE',
        'CHAR',
        'CHAR_LENGTH',
        'CHARACTER',
        'CHARACTER_LENGTH',
        'CHECK',
        'CHECKPOINT',
        'CLASS',
        'CLOB',
        'CLOSE',
        'CLUSTER',
        'CLUSTERED',
        'COALESCE',
        'COLLATE',
        'COLLATION',
        'COLUMN',
        'COLUMNS',
        'COMMENT',
        'COMMIT',
        'COMPLETION',
        'COMPRESS',
        'COMPUTE',
        'CONDITION',
        'CONNECT',
        'CONNECTION',
        'CONSTRAINT',
        'CONSTRAINTS',
        'CONSTRUCTOR',
        'CONTAINS',
        'CONTAINSTABLE',
        'CONTINUE',
        'CONVERT',
        'CORRESPONDING',
        'COUNT',
        'CREATE',
        'CROSS',
        'CUBE',
        'CURRENT',
        'CURRENT_DATE',
        'CURRENT_PATH',
        'CURRENT_ROLE',
        'CURRENT_TIME',
        'CURRENT_TIMESTAMP',
        'CURRENT_USER',
        'CURSOR',
        'CYCLE',
        'DATA',
        'DATABASE',
        'DATABASES',
        'DATE',
        'DAY',
        'DAY_HOUR',
        'DAY_MICROSECOND',
        'DAY_MINUTE',
        'DAY_SECOND',
        'DBCC',
        'DEALLOCATE',
        'DEC',
        'DECIMAL',
        'DECLARE',
        'DEFAULT',
        'DEFERRABLE',
        'DEFERRED',
        'DELAYED',
        'DELETE',
        'DENY',
        'DEPTH',
        'DEREF',
        'DESC',
        'DESCRIBE',
        'DESCRIPTOR',
        'DESTROY',
        'DESTRUCTOR',
        'DETERMINISTIC',
        'DIAGNOSTICS',
        'DICTIONARY',
        'DISCONNECT',
        'DISK',
        'DISTINCT',
        'DISTINCTROW',
        'DISTRIBUTED',
        'DIV',
        'DO',
        'DOMAIN',
        'DOUBLE',
        'DROP',
        'DUMMY',
        'DUMP',
        'DYNAMIC',
        'EACH',
        'ELSE',
        'ELSEIF',
        'ENCLOSED',
        'END',
        'END-EXEC',
        'EQUALS',
        'ERRLVL',
        'ESCAPE',
        'ESCAPED',
        'EVERY',
        'EXCEPT',
        'EXCEPTION',
        'EXCLUSIVE',
        'EXEC',
        'EXECUTE',
        'EXISTS',
        'EXIT',
        'EXPLAIN',
        'EXTERNAL',
        'EXTRACT',
        'FALSE',
        'FETCH',
        'FIELDS',
        'FILE',
        'FILLFACTOR',
        'FIRST',
        'FLOAT',
        'FOR',
        'FORCE',
        'FOREIGN',
        'FOUND',
        'FRAC_SECOND',
        'FREE',
        'FREETEXT',
        'FREETEXTTABLE',
        'FREEZE',
        'FROM',
        'FULL',
        'FULLTEXT',
        'FUNCTION',
        'GENERAL',
        'GET',
        'GLOB',
        'GLOBAL',
        'GO',
        'GOTO',
        'GRANT',
        'GROUP',
        'GROUPING',
        'HAVING',
        'HIGH_PRIORITY',
        'HOLDLOCK',
        'HOST',
        'HOUR',
        'HOUR_MICROSECOND',
        'HOUR_MINUTE',
        'HOUR_SECOND',
        'IDENTIFIED',
        'IDENTITY',
        'IDENTITY_INSERT',
        'IDENTITYCOL',
        'IF',
        'IGNORE',
        'ILIKE',
        'IMMEDIATE',
        'IN',
        'INCREMENT',
        'INDEX',
        'INDICATOR',
        'INFILE',
        'INITIAL',
        'INITIALIZE',
        'INITIALLY',
        'INNER',
        'INNODB',
        'INOUT',
        'INPUT',
        'INSENSITIVE',
        'INSERT',
        'INT',
        'INTEGER',
        'INTERSECT',
        'INTERVAL',
        'INTO',
        'IO_THREAD',
        'IS',
        'ISNULL',
        'ISOLATION',
        'ITERATE',
        'JOIN',
        'KEY',
        'KEYS',
        'KILL',
        'LANGUAGE',
        'LARGE',
        'LAST',
        'LATERAL',
        'LEADING',
        'LEAVE',
        'LEFT',
        'LESS',
        'LEVEL',
        'LIKE',
        'LIMIT',
        'LINENO',
        'LINES',
        'LOAD',
        'LOCAL',
        'LOCALTIME',
        'LOCALTIMESTAMP',
        'LOCATOR',
        'LOCK',
        'LONG',
        'LONGBLOB',
        'LONGTEXT',
        'LOOP',
        'LOW_PRIORITY',
        'LOWER',
        'MAIN',
        'MAP',
        'MASTER_SERVER_ID',
        'MATCH',
        'MAX',
        'MAXEXTENTS',
        'MEDIUMBLOB',
        'MEDIUMINT',
        'MEDIUMTEXT',
        'MIDDLEINT',
        'MIN',
        'MINUS',
        'MINUTE',
        'MINUTE_MICROSECOND',
        'MINUTE_SECOND',
        'MLSLABEL',
        'MOD',
        'MODE',
        'MODIFIES',
        'MODIFY',
        'MODULE',
        'MONTH',
        'NAMES',
        'NATIONAL',
        'NATURAL',
        'NCHAR',
        'NCLOB',
        'NEW',
        'NEXT',
        'NO',
        'NO_WRITE_TO_BINLOG',
        'NOAUDIT',
        'NOCHECK',
        'NOCOMPRESS',
        'NONCLUSTERED',
        'NONE',
        'NOT',
        'NOTNULL',
        'NOWAIT',
        'NULL',
        'NULLIF',
        'NUMBER',
        'NUMERIC',
        'OBJECT',
        'OCTET_LENGTH',
        'OF',
        'OFF',
        'OFFLINE',
        'OFFSET',
        'OFFSETS',
        'OID',
        'OLD',
        'ON',
        'ONLINE',
        'ONLY',
        'OPEN',
        'OPENDATASOURCE',
        'OPENQUERY',
        'OPENROWSET',
        'OPENXML',
        'OPERATION',
        'OPTIMIZE',
        'OPTION',
        'OPTIONALLY',
        'OR',
        'ORDER',
        'ORDINALITY',
        'OUT',
        'OUTER',
        'OUTFILE',
        'OUTPUT',
        'OVER',
        'OVERLAPS',
        'PAD',
        'PARAMETER',
        'PARAMETERS',
        'PARTIAL',
        'PATH',
        'PCTFREE',
        'PERCENT',
        'PLACING',
        'PLAN',
        'POSITION',
        'POSTFIX',
        'PRECISION',
        'PREFIX',
        'PREORDER',
        'PREPARE',
        'PRESERVE',
        'PRIMARY',
        'PRINT',
        'PRIOR',
        'PRIVILEGES',
        'PROC',
        'PROCEDURE',
        'PUBLIC',
        'PURGE',
        'RAISERROR',
        'RAW',
        'READ',
        'READS',
        'READTEXT',
        'REAL',
        'RECONFIGURE',
        'RECURSIVE',
        'REF',
        'REFERENCES',
        'REFERENCING',
        'REGEXP',
        'RELATIVE',
        'RENAME',
        'REPEAT',
        'REPLACE',
        'REPLICATION',
        'REQUIRE',
        'RESOURCE',
        'RESTORE',
        'RESTRICT',
        'RESULT',
        'RETURN',
        'RETURNS',
        'REVOKE',
        'RIGHT',
        'RLIKE',
        'ROLE',
        'ROLLBACK',
        'ROLLUP',
        'ROUTINE',
        'ROW',
        'ROWCOUNT',
        'ROWGUIDCOL',
        'ROWID',
        'ROWNUM',
        'ROWS',
        'RULE',
        'SAVE',
        'SAVEPOINT',
        'SCHEMA',
        'SCOPE',
        'SCROLL',
        'SEARCH',
        'SECOND',
        'SECOND_MICROSECOND',
        'SECTION',
        'SELECT',
        'SENSITIVE',
        'SEPARATOR',
        'SEQUENCE',
        'SESSION',
        'SESSION_USER',
        'SET',
        'SETS',
        'SETUSER',
        'SHARE',
        'SHOW',
        'SHUTDOWN',
        'SIMILAR',
        'SIZE',
        'SMALLINT',
        'SOME',
        'SONAME',
        'SPACE',
        'SPATIAL',
        'SPECIFIC',
        'SPECIFICTYPE',
        'SQL',
        'SQL_BIG_RESULT',
        'SQL_CALC_FOUND_ROWS',
        'SQL_SMALL_RESULT',
        'SQL_TSI_DAY',
        'SQL_TSI_FRAC_SECOND',
        'SQL_TSI_HOUR',
        'SQL_TSI_MINUTE',
        'SQL_TSI_MONTH',
        'SQL_TSI_QUARTER',
        'SQL_TSI_SECOND',
        'SQL_TSI_WEEK',
        'SQL_TSI_YEAR',
        'SQLCODE',
        'SQLERROR',
        'SQLEXCEPTION',
        'SQLITE_MASTER',
        'SQLITE_TEMP_MASTER',
        'SQLSTATE',
        'SQLWARNING',
        'SSL',
        'START',
        'STARTING',
        'STATE',
        'STATEMENT',
        'STATIC',
        'STATISTICS',
        'STRAIGHT_JOIN',
        'STRIPED',
        'STRUCTURE',
        'SUBSTRING',
        'SUCCESSFUL',
        'SUM',
        'SYNONYM',
        'SYSDATE',
        'SYSTEM_USER',
        'TABLE',
        'TABLES',
        'TEMPORARY',
        'TERMINATE',
        'TERMINATED',
        'TEXTSIZE',
        'THAN',
        'THEN',
        'TIME',
        'TIMESTAMP',
        'TIMESTAMPADD',
        'TIMESTAMPDIFF',
        'TIMEZONE_HOUR',
        'TIMEZONE_MINUTE',
        'TINYBLOB',
        'TINYINT',
        'TINYTEXT',
        'TO',
        'TOP',
        'TRAILING',
        'TRAN',
        'TRANSACTION',
        'TRANSLATE',
        'TRANSLATION',
        'TREAT',
        'TRIGGER',
        'TRIM',
        'TRUE',
        'TRUNCATE',
        'TSEQUAL',
        'UID',
        'UNDER',
        'UNDO',
        'UNION',
        'UNIQUE',
        'UNKNOWN',
        'UNLOCK',
        'UNNEST',
        'UNSIGNED',
        'UPDATE',
        'UPDATETEXT',
        'UPPER',
        'USAGE',
        'USE',
        'USER',
        'USER_RESOURCES',
        'USING',
        'UTC_DATE',
        'UTC_TIME',
        'UTC_TIMESTAMP',
        'VALIDATE',
        'VALUE',
        'VALUES',
        'VARBINARY',
        'VARCHAR',
        'VARCHAR2',
        'VARCHARACTER',
        'VARIABLE',
        'VARYING',
        'VERBOSE',
        'VIEW',
        'WAITFOR',
        'WHEN',
        'WHENEVER',
        'WHERE',
        'WHILE',
        'WITH',
        'WITHOUT',
        'WORK',
        'WRITE',
        'WRITETEXT',
        'XOR',
        'YEAR',
        'YEAR_MONTH',
        'ZEROFILL',
        'ZONE',
    );
}
        
?>