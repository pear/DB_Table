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


class DB_Table_Manager_ibase {

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
        $table = strtoupper($table);
        $query = "SELECT RDB\$INDEX_NAME
                    FROM RDB\$INDICES
                   WHERE UPPER(RDB\$RELATION_NAME)='$table'
                     AND RDB\$UNIQUE_FLAG IS NULL
                     AND RDB\$FOREIGN_KEY IS NULL";
        $indexes = $this->_db->getCol($query);
        if (PEAR::isError($indexes)) {
            return $indexes;
        }

        $result = array();
        foreach ($indexes as $index) {
            $result[trim($index)] = true;
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
        $table = strtoupper($table);
        $query = "SELECT RDB\$INDEX_NAME
                    FROM RDB\$INDICES
                   WHERE UPPER(RDB\$RELATION_NAME)='$table'
                     AND (
                           RDB\$UNIQUE_FLAG IS NOT NULL
                        OR RDB\$FOREIGN_KEY IS NOT NULL
                     )";
        $constraints = $this->_db->getCol($query);
        if (PEAR::isError($constraints)) {
            return $constraints;
        }

        $result = array();
        foreach ($constraints as $constraint) {
            $result[trim($constraint)] = true;
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
        $table = strtoupper($table);
        $index_name = strtoupper($index_name);
        $query = "SELECT RDB\$INDEX_SEGMENTS.RDB\$FIELD_NAME AS field_name,
                         RDB\$INDICES.RDB\$UNIQUE_FLAG AS unique_flag,
                         RDB\$INDICES.RDB\$FOREIGN_KEY AS foreign_key,
                         RDB\$INDICES.RDB\$DESCRIPTION AS description
                    FROM RDB\$INDEX_SEGMENTS
               LEFT JOIN RDB\$INDICES ON RDB\$INDICES.RDB\$INDEX_NAME = RDB\$INDEX_SEGMENTS.RDB\$INDEX_NAME
               LEFT JOIN RDB\$RELATION_CONSTRAINTS ON RDB\$RELATION_CONSTRAINTS.RDB\$INDEX_NAME = RDB\$INDEX_SEGMENTS.RDB\$INDEX_NAME
                   WHERE UPPER(RDB\$INDICES.RDB\$RELATION_NAME)='$table'
                     AND UPPER(RDB\$INDICES.RDB\$INDEX_NAME)='$index_name'
                     AND RDB\$RELATION_CONSTRAINTS.RDB\$CONSTRAINT_TYPE IS NULL
                ORDER BY RDB\$INDEX_SEGMENTS.RDB\$FIELD_POSITION;";
        $result = $this->_db->query($query);
        if (PEAR::isError($result)) {
            return $result;
        }

        $index = $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        $fields = array();
        do {
            $row = array_change_key_case($row, CASE_LOWER);
            $fields[] = $row['field_name'];
        } while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)));
        $result->free();

        $fields = array_map('strtolower', $fields);

        $definition = array();
        $index = array_change_key_case($index, CASE_LOWER);
        foreach ($fields as $field) {
            $definition['fields'][$field] = array();
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
        $table = strtoupper($table);
        $index_name = strtoupper($index_name);
        $query = "SELECT RDB\$INDEX_SEGMENTS.RDB\$FIELD_NAME AS field_name,
                         RDB\$INDICES.RDB\$UNIQUE_FLAG AS unique_flag,
                         RDB\$INDICES.RDB\$FOREIGN_KEY AS foreign_key,
                         RDB\$INDICES.RDB\$DESCRIPTION AS description,
                         RDB\$RELATION_CONSTRAINTS.RDB\$CONSTRAINT_TYPE AS constraint_type
                    FROM RDB\$INDEX_SEGMENTS
               LEFT JOIN RDB\$INDICES ON RDB\$INDICES.RDB\$INDEX_NAME = RDB\$INDEX_SEGMENTS.RDB\$INDEX_NAME
               LEFT JOIN RDB\$RELATION_CONSTRAINTS ON RDB\$RELATION_CONSTRAINTS.RDB\$INDEX_NAME = RDB\$INDEX_SEGMENTS.RDB\$INDEX_NAME
                   WHERE UPPER(RDB\$INDICES.RDB\$RELATION_NAME)='$table'
                     AND UPPER(RDB\$INDICES.RDB\$INDEX_NAME)='$index_name'
                ORDER BY RDB\$INDEX_SEGMENTS.RDB\$FIELD_POSITION;";
        $result = $this->_db->query($query);
        if (PEAR::isError($result)) {
            return $result;
        }

        $index = $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        $fields = array();
        do {
            $row = array_change_key_case($row, CASE_LOWER);
            $fields[] = $row['field_name'];
        } while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)));
        $result->free();

        $fields = array_map('strtolower', $fields);

        $definition = array();
        $index = array_change_key_case($index, CASE_LOWER);
        if ($index['constraint_type'] == 'PRIMARY KEY') {
            $definition['primary'] = true;
        }
        if ($index['unique_flag']) {
            $definition['unique'] = true;
        }
        foreach ($fields as $field) {
            $definition['fields'][$field] = array();
        }
        return $definition;
    }

}

?>