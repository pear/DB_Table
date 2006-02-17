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


class DB_Table_Manager_pgsql {

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
        $subquery = "SELECT indexrelid FROM pg_index, pg_class";
        $subquery.= " WHERE pg_class.relname='$table' AND pg_class.oid=pg_index.indrelid AND indisunique != 't' AND indisprimary != 't'";
        $query = "SELECT relname FROM pg_class WHERE oid IN ($subquery)";
        $indexes = $this->_db->getCol($query);
        if (PEAR::isError($indexes)) {
            return $indexes;
        }

        $result = array();
        foreach ($indexes as $index) {
            $result[$index] = true;
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
        $subquery = "SELECT indexrelid FROM pg_index, pg_class";
        $subquery.= " WHERE pg_class.relname='$table' AND pg_class.oid=pg_index.indrelid AND (indisunique = 't' OR indisprimary = 't')";
        $query = "SELECT relname FROM pg_class WHERE oid IN ($subquery)";
        $constraints = $this->_db->getCol($query);
        if (PEAR::isError($constraints)) {
            return $constraints;
        }

        $result = array();
        foreach ($constraints as $constraint) {
            $result[$constraint] = true;
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
        $query = "SELECT relname, indkey FROM pg_index, pg_class
            WHERE pg_class.relname = ".$this->_db->quoteSmart($index_name)." AND pg_class.oid = pg_index.indexrelid
               AND indisunique != 't' AND indisprimary != 't'";
        $row = $this->_db->getRow($query, null, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($row)) {
            return $row;
        }

        $columns = $this->_listTableFields($table);

        $definition = array();

        $index_column_numbers = explode(' ', $row['indkey']);

        foreach ($index_column_numbers as $number) {
            $definition['fields'][$columns[($number - 1)]] = array('sorting' => 'ascending');
        }
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
        $query = "SELECT relname, indisunique, indisprimary, indkey FROM pg_index, pg_class
            WHERE pg_class.relname = ".$this->_db->quoteSmart($index_name)." AND pg_class.oid = pg_index.indexrelid
              AND (indisunique = 't' OR indisprimary = 't')";
        $row = $this->_db->getRow($query, null, DB_FETCHMODE_ASSOC);
        if (PEAR::isError($row)) {
            return $row;
        }

        $columns = $this->_listTableFields($table);

        $definition = array();
        if ($row['indisprimary'] == 't') {
            $definition['primary'] = true;
        } elseif ($row['indisunique'] == 't') {
            $definition['unique'] = true;
        }

        $index_column_numbers = explode(' ', $row['indkey']);

        foreach ($index_column_numbers as $number) {
            $definition['fields'][$columns[($number - 1)]] = array('sorting' => 'ascending');
        }
        return $definition;
    }

    /**
     * list all fields in a tables in the current database
     *
     * @param string $table name of table that should be used in method
     * @return mixed data array on success, a PEAR error on failure
     * @access private
     */
    function _listTableFields($table)
    {
        $table = $this->_db->quoteIdentifier($table);
        $result2 = $this->_db->query("SELECT * FROM $table");
        if (PEAR::isError($result2)) {
            return $result2;
        }
        $columns = array();
        $numcols = $result2->numCols();
        for ($column = 0; $column < $numcols; $column++) {
            $column_name = @pg_field_name($result2->result, $column);
            $columns[$column_name] = $column;
        }
        $result2->free();
        return array_flip($columns);
    }

}

?>