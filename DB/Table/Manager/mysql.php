<?php

/**
* 
* Index, constraint and alter methods for DB_Table usage with
* PEAR::DB as backend. (Code adopted from PEAR::MDB2)
* 
* @category DB
* 
* @package DB_Table
*
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
* Index, constraint and alter methods for DB_Table usage with
* PEAR::DB as backend. (Code adopted from PEAR::MDB2)
* 
* @category DB
* 
* @package DB_Table
*
* @author Mark Wiesemann <wiesemann@php.net>
*
*/


class DB_Table_Manager_mysql {

    /**
    * 
    * The PEAR DB object that connects to the database.
    * 
    * @access private
    * 
    * @var object
    * 
    */
    
    var $_db = null;


    /**
     * list all indexes in a table
     *
     * @param string    $table      name of table that should be used in method
     * @return mixed data array on success, a PEAR error on failure
     * @access public
     */
    function listTableIndexes($table)
    {
        $key_name = 'Key_name';
        $non_unique = 'Non_unique';

        $query = "SHOW INDEX FROM $table";
        $indexes = $this->_db->getAll($query, null, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($indexes)) {
            return $indexes;
        }

        $result = array();
        foreach ($indexes as $index_data) {
            if ($index_data[$non_unique]) {
                $result[$index_data[$key_name]] = true;
            }
        }
        $result = array_change_key_case($result, CASE_LOWER);

        return array_keys($result);
    }


    /**
     * list all constraints in a table
     *
     * @param string    $table      name of table that should be used in method
     * @return mixed data array on success, a PEAR error on failure
     * @access public
     */
    function listTableConstraints($table)
    {
        $key_name = 'Key_name';
        $non_unique = 'Non_unique';

        $query = "SHOW INDEX FROM $table";
        $indexes = $this->_db->getAll($query, null, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($indexes)) {
            return $indexes;
        }

        $result = array();
        foreach ($indexes as $index_data) {
            if (!$index_data[$non_unique]) {
                if ($index_data[$key_name] !== 'PRIMARY') {
                    $index = $index_data[$key_name];
                } else {
                    $index = 'PRIMARY';
                }
                $result[$index] = true;
            }
        }
        $result = array_change_key_case($result, CASE_LOWER);

        return array_keys($result);
    }


    /**
     * get the structure of an index into an array
     *
     * @param string    $table      name of table that should be used in method
     * @param string    $index_name name of index that should be used in method
     * @return mixed data array on success, a PEAR error on failure
     * @access public
     */
    function getTableIndexDefinition($table, $index_name)
    {
        $result = $this->_db->query("SHOW INDEX FROM $table");
        if (PEAR::isError($result)) {
            return $result;
        }

        $definition = array();
        while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC))) {
            $row = array_change_key_case($row, CASE_LOWER);
            $key_name = $row['key_name'];
            $key_name = strtolower($key_name);

            if ($index_name == $key_name) {
                $column_name = $row['column_name'];
                $column_name = strtolower($column_name);
                $definition['fields'][$column_name] = array();
                if (array_key_exists('collation', $row)) {
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] == 'A'
                        ? 'ascending' : 'descending');
                }
            }
        }

        $result->free();

        return $definition;
    }


    /**
     * get the structure of a constraint into an array
     *
     * @param string    $table      name of table that should be used in method
     * @param string    $index_name name of index that should be used in method
     * @return mixed data array on success, a PEAR error on failure
     * @access public
     */
    function getTableConstraintDefinition($table, $index_name)
    {
        $result = $this->_db->query("SHOW INDEX FROM $table");
        if (PEAR::isError($result)) {
            return $result;
        }

        $definition = array();
        while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC))) {
            $row = array_change_key_case($row, CASE_LOWER);
            $key_name = $row['key_name'];
            $key_name = strtolower($key_name);

            if ($index_name == $key_name) {
                if ($row['key_name'] == 'PRIMARY') {
                    $definition['primary'] = true;
                } else {
                    $definition['unique'] = true;
                }
                $column_name = $row['column_name'];
                $column_name = strtolower($column_name);
                $definition['fields'][$column_name] = array();
                if (array_key_exists('collation', $row)) {
                    $definition['fields'][$column_name]['sorting'] = ($row['collation'] == 'A'
                        ? 'ascending' : 'descending');
                }
            }
        }

        $result->free();

        return $definition;
    }

}

?>