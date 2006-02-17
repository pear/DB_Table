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


class DB_Table_Manager_sqlite {

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
        $query = "SELECT sql FROM sqlite_master WHERE type='index' AND ";
        $query.= "LOWER(tbl_name)='".strtolower($table)."'";
        $query.= " AND sql NOT NULL ORDER BY name";
        $indexes = $this->_db->getCol($query);
        if (PEAR::isError($indexes)) {
            return $indexes;
        }

        $result = array();
        foreach ($indexes as $sql) {
            if (preg_match("/^create index ([^ ]*) on /i", $sql, $tmp)) {
                $result[$tmp[1]] = true;
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
        $query = "SELECT sql FROM sqlite_master WHERE type='index' AND ";
        $query.= "LOWER(tbl_name)='".strtolower($table)."'";
        $query.= " AND sql NOT NULL ORDER BY name";
        $indexes = $this->_db->getCol($query);
        if (PEAR::isError($indexes)) {
            return $indexes;
        }

        $result = array();
        foreach ($indexes as $sql) {
            if (preg_match("/^create unique index ([^ ]*) on /i", $sql, $tmp)) {
                $result[$tmp[1]] = true;
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
        $query = "SELECT sql FROM sqlite_master WHERE type='index' AND ";
        $query.= "LOWER(name)='".strtolower($index_name)."' AND LOWER(tbl_name)='".strtolower($table)."'";
        $query.= " AND sql NOT NULL ORDER BY name";
        $sql = $this->_db->getOne($query);
        if (PEAR::isError($sql)) {
            return $sql;
        }

        $sql = strtolower($sql);
        $start_pos = strpos($sql, '(');
        $end_pos = strrpos($sql, ')');
        $column_names = substr($sql, $start_pos+1, $end_pos-$start_pos-1);
        $column_names = split(',', $column_names);

        $definition = array();
        $count = count($column_names);
        for ($i=0; $i<$count; ++$i) {
            $column_name = strtok($column_names[$i]," ");
            $collation = strtok(" ");
            $definition['fields'][$column_name] = array();
            if (!empty($collation)) {
                $definition['fields'][$column_name]['sorting'] =
                    ($collation=='ASC' ? 'ascending' : 'descending');
            }
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
        $query = "SELECT sql FROM sqlite_master WHERE type='index' AND ";
        $query.= "LOWER(name)='".strtolower($index_name)."' AND LOWER(tbl_name)='".strtolower($table)."'";
        $query.= " AND sql NOT NULL ORDER BY name";
        $sql = $this->_db->getOne($query);
        if (PEAR::isError($sql)) {
            return $sql;
        }

        $sql = strtolower($sql);
        $start_pos = strpos($sql, '(');
        $end_pos = strrpos($sql, ')');
        $column_names = substr($sql, $start_pos+1, $end_pos-$start_pos-1);
        $column_names = split(',', $column_names);

        $definition = array();
        $definition['unique'] = true;
        $count = count($column_names);
        for ($i=0; $i<$count; ++$i) {
            $column_name = strtok($column_names[$i]," ");
            $collation = strtok(" ");
            $definition['fields'][$column_name] = array();
            if (!empty($collation)) {
                $definition['fields'][$column_name]['sorting'] =
                    ($collation=='ASC' ? 'ascending' : 'descending');
            }
        }

        return $definition;
    }

}

?>