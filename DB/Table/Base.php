<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once 'PEAR.php';

/**
 * DB_Table_Base Base class for DB_Table and DB_Table_Database
 *
 * This utility class contains properties and methods that are common
 * to DB_Table and DB_Table database. These are all related to one of:
 *   - DB/MDB2 connection object [ $db and $backend properties ]
 *   - Error handling [ throwError() method, $error and $_primary_subclass ]
 *   - SELECT queries [ select*() methods, $sql & $fetchmode* properties]
 *   - buildSQL() and quote() SQL utilities
 *   - _swapModes() method 
 *
 *
 * PHP version 4 and 5
 *
 * @category Database
 * @package  DB_Table
 * @author   David C. Morse <morse@php.net>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL
 * @version  $Id$
 */

// {{{ DB_Table_Base

/**
 * Base class for DB_Table and DB_Table_Database
 *
 * @category Database
 * @package  DB_Table
 * @author   David C. Morse <morse@php.net>
 *
 */
class DB_Table_Base
{

    // {{{ properties

    /**
     * The PEAR DB/MDB2 object that connects to the database.
     *
     * @var    object
     * @access private
     */
    var $db = null;

    /**
     * The backend type, which may be 'db' or 'mdb2'
     *
     * @var    string
     * @access private
     */
    var $backend = null;

    /**
    * If there is an error on instantiation, this captures that error.
    *
    * This property is used only for errors encountered in the constructor
    * at instantiation time.  To check if there was an instantiation error...
    *
    * <code>
    * $obj =& new DB_Table_*();
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
     * Baseline SELECT maps for buildSQL() and select*() methods.
     *
     * @var    array
     * @access public
     */
    var $sql = array();

    /**
     * When calling select() and selectResult(), use this fetch mode (usually
     * a DB/MDB2_FETCHMODE_* constant).  If null, uses whatever is set in the 
     * $db DB/MDB2 object.
     *
     * @var    int
     * @access public
     */
    var $fetchmode = null;

    /**
     * When fetchmode is DB/MDB2_FETCHMODE_OBJECT, use this class for each
     * returned row. If null, uses whatever is set in the $db DB/MDB2 object.
     *
     * @var    string
     * @access public
     */
    var $fetchmode_object_class = null;

    /**
     * Upper case name of class 'DB_TABLE' or 'DB_TABLE_DATABASE'.
     *
     * This should be set in the constructor of the child class, and is 
     * used in the DB_Table_Base::throwError() method to determine the
     * location of the relevant error codes and messages. Error codes and
     * error code messages are defined in class $this->_primary_subclass.
     * Messages are stored in $GLOBALS['_' . $this->_primary_subclass]['error']
     *
     * @var    string
     * @access private
     */
     var $_primary_subclass = null;

    // }}}
    // {{{ Methods

    /**
     * Specialized version of throwError() modeled on PEAR_Error.
     * 
     * Throws a PEAR_Error with an error message based on an error code 
     * and corresponding error message defined in $this->_primary_subclass
     * 
     * @param string $code  An error code constant 
     * @param string $extra Extra text for the error (in addition to the 
     *                      regular error message).
     * @return object PEAR_Error
     * @access public
     * @static
     */
    function &throwError($code, $extra = null)
    {
        // get the error message text based on the error code
        $index = '_' . $this->_primary_subclass;
        $text = $this->_primary_subclass . " Error - \n" 
              . $GLOBALS[$index]['error'][$code];
        
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
        $index = '_' . $this->_primary_subclass;
        if (is_array($code)) {
            foreach ($code as $single_code => $single_message) {
                $GLOBALS[$index]['error'][$single_code] = $single_message;
            }
        } else {
            $GLOBALS[$index]['error'][$code] = $message;
        }
    }


    /**
     * Returns SQL SELECT string constructed from sql query array
     *
     * @param mixed  $query  SELECT query array, or key string of $this->sql
     * @param string $filter SQL snippet to AND with default WHERE clause
     * @param string $order  SQL snippet to override default ORDER BY clause
     * @param int    $start  The row number from which to start result set
     * @param int    $count  The number of rows to list in the result set.
     *
     * @return string SQL SELECT command string (or PEAR_Error on failure)
     *
     * @access public
     */
    function buildSQL($query, $filter = null, $order = null, 
                              $start = null, $count = null)
    {

        // Is $query a query array or a key of $this->sql ?
        if (!is_array($query)) {
            if (is_string($query)) {
                if (isset($this->sql[$query])) {
                    $query = $this->sql[$query];
                } else {
                    return $this->throwError(
                           constant($this->_primary_subclass . '_ERR_SQL_UNDEF'),
                           $query);
                }
            } else {
                return $this->throwError(
                       constant($this->_primary_subclass . '_ERR_SQL_NOT_STRING'));
            }
        }
       
        // Construct SQL command from parts
        $s = array();
        if (isset($query['select'])) {
            $s[] = 'SELECT ' . $query['select'];
        } else {
            $s[] = 'SELECT *';
        }
        if (isset($query['from'])) {
            $s[] = 'FROM ' . $query['from'];
        } elseif ($this->_primary_subclass == 'DB_TABLE') {
            $s[] = 'FROM ' . $this->table;
        }
        if (isset($query['join'])) {
            $s[] = $query['join'];
        }
        if (isset($query['where'])) {
            if ($filter) {
                $s[] = 'WHERE ( ' . $query['where'] . ' )';
                $s[] = '  AND ( '. $filter . ' )';
            } else {
                $s[] = 'WHERE ' . $query['where'];
            }
        } elseif ($filter) {
            $s[] = 'WHERE ' . $filter;
        }
        if (isset($query['group'])) {
            $s[] = 'GROUP BY ' . $query['group'];
        }
        if (isset($query['having'])) {
            $s[] = 'HAVING '. $query['having'];
        }
        // If $order parameter is set, override 'order' element
        if (!is_null($order)) {
            $s[] = 'ORDER BY '. $order;
        } elseif (isset($query['order'])) {
            $s[] = 'ORDER BY ' . $query['order'];
        }
        $cmd = implode("\n", $s);
        
        // add LIMIT if requested
        if (!is_null($start) && !is_null($count)) {
            $db =& $this->db;
            if ($this->backend == 'mdb2') {
                $db->setLimit($count, $start);
            } else {
                $cmd = $db->modifyLimitQuery(
                            $cmd, $start, $count);
            }
        }

        // Return command string
        return $cmd;
    }

  
    /**
     * Selects rows using one of the DB/MDB2 get*() methods.
     *
     * @param string $query SQL SELECT query array, or a key of the
     *                          $this->sql property array.
     * @param string $filter    SQL snippet to AND with default WHERE clause
     * @param string $order     SQL snippet to override default ORDER BY clause
     * @param int    $start     The row number from which to start result set
     * @param int    $count     The number of rows to list in the result set.
     * @param array  $params    Parameters for placeholder substitutions, if any
     * @return mixed  An array of records from the table if anything but 
     *                ('getOne'), a single value (if 'getOne'), or a PEAR_Error
     * @see DB::getAll()
     * @see MDB2::getAll()
     * @see DB::getAssoc()
     * @see MDB2::getAssoc()
     * @see DB::getCol()
     * @see MDB2::getCol()
     * @see DB::getOne()
     * @see MDB2::getOne()
     * @see DB::getRow()
     * @see MDB2::getRow()
     * @see DB_Table_Base::_swapModes()
     * @access public
     */
    function select($query, $filter = null, $order = null,
                            $start = null, $count = null, $params = array())
    {

        // Is $query a query array or a key of $this->sql ?
        // On output from this block, $query is an array
        if (!is_array($query)) {
            if (is_string($query)) {
                if (isset($this->sql[$query])) {
                    $query = $this->sql[$query];
                } else {
                    return $this->throwError(
                          constant($this->_primary_subclass . '_ERR_SQL_UNDEF'),
                          $query);
                }
            } else {
                return $this->throwError(
                    constant($this->_primary_subclass . '_ERR_SQL_NOT_STRING'));
            }
        }

        // build the base command
        $sql = $this->buildSQL($query, $filter, $order, $start, $count);
        if (PEAR::isError($sql)) {
            return $sql;
        }

        // set the get*() method name
        if (isset($query['get'])) {
            $method = ucwords(strtolower(trim($query['get'])));
            $method = "get$method";
        } else {
            $method = 'getAll';
        }

        // DB_Table assumes you are using a shared PEAR DB/MDB2 object.
        // Record fetchmode settings, to be restored before returning.
        $db =& $this->db;
        $restore_mode = $db->fetchmode;
        if ($this->backend == 'mdb2') {
            $restore_class = $db->getOption('fetch_class');
        } else {
            $restore_class = $db->fetchmode_object_class;
        }

        // swap modes
        $fetchmode = $this->fetchmode;
        $fetchmode_object_class = $this->fetchmode_object_class;
        if (isset($query['fetchmode'])) {
            $fetchmode = $query['fetchmode'];
        }
        if (isset($query['fetchmode_object_class'])) {
            $fetchmode_object_class = $query['fetchmode_object_class'];
        }
        $this->_swapModes($fetchmode, $fetchmode_object_class);

        // make sure params is an array
        if (!is_null($params)) {
            $params = (array) $params;
        }

        // get the result
        if ($this->backend == 'mdb2') {
            $result = $db->extended->$method($sql, null, $params);
        } else {
            switch ($method) {

                case 'getCol':
                    $result = $db->$method($sql, 0, $params);
                    break;

                case 'getAssoc':
                    $result = $db->$method($sql, false, $params);
                    break;

                default:
                    $result = $db->$method($sql, $params);
                    break;

            }
        }

        // restore old fetch_mode and fetch_object_class back
        $this->_swapModes($restore_mode, $restore_class);

        return $result;
    }


    /**
     * Selects rows as a DB_Result/MDB2_Result_* object.
     *
     * @param string $query  The name of the SQL SELECT to use from the
     *                       $this->sql property array.
     * @param string $filter SQL snippet to AND to the default WHERE clause
     * @param string $order  SQL snippet to override default ORDER BY clause
     * @param int    $start  The record number from which to start result set
     * @param int    $count  The number of records to list in result set.
     * @param array $params  Parameters for placeholder substitutions, if any.
     * @return object DB_Result/MDB2_Result_* object on success
     *                (PEAR_Error on failure)
     * @see DB_Table::_swapModes()
     * @access public
     */
    function selectResult($query, $filter = null, $order = null,
                   $start = null, $count = null, $params = array())
    {
        // Is $query a query array or a key of $this->sql ?
        // On output from this block, $query is an array
        if (!is_array($query)) {
            if (is_string($query)) {
                if (isset($this->sql[$query])) {
                    $query = $this->sql[$query];
                } else {
                    return $this->throwError(
                           constant($this->_primary_subclass . '_ERR_SQL_UNDEF'),
                           $query);
                }
            } else {
                return $this->throwError(
                       constant($this->_primary_subclass . '_ERR_SQL_NOT_STRING'));
            }
        }
       
        // build the base command
        $sql = $this->buildSQL($query, $filter, $order, $start, $count);
        if (PEAR::isError($sql)) {
            return $sql;
        }

        // DB_Table assumes you are using a shared PEAR DB/MDB2 object.
        // Record fetchmode settings, to be restored afterwards.
        $db =& $this->db;
        $restore_mode = $db->fetchmode;
        if ($this->backend == 'mdb2') {
            $restore_class = $db->getOption('fetch_class');
        } else {
            $restore_class = $db->fetchmode_object_class;
        }

        // swap modes
        $fetchmode = $this->fetchmode;
        $fetchmode_object_class = $this->fetchmode_object_class;
        if (isset($query['fetchmode'])) {
            $fetchmode = $query['fetchmode'];
        }
        if (isset($query['fetchmode_object_class'])) {
            $fetchmode_object_class = $query['fetchmode_object_class'];
        }
        $this->_swapModes($fetchmode, $fetchmode_object_class);

        // make sure params is an array
        if (!is_null($params)) {
            $params = (array) $params;
        }

        // get the result
        if ($this->backend == 'mdb2') {
            $stmt =& $db->prepare($sql);
            if (PEAR::isError($stmt)) {
                return $stmt;
            }
            $result =& $stmt->execute($params);
        } else {
            $result =& $db->query($sql, $params);
        }

        // swap modes back
        $this->_swapModes($restore_mode, $restore_class);

        // return the result
        return $result;
    }


    /**
     * Counts the number of rows which will be returned by a query.
     *
     * This function works identically to {@link select()}, but it
     * returns the number of rows returned by a query instead of the
     * query results themselves.
     *
     * @author Ian Eure <ian@php.net>
     * @param string $query  The name of the SQL SELECT to use from the
     *                       $this->sql property array.
     * @param string $filter Ad-hoc SQL snippet to AND with the default
     *                       SELECT WHERE clause.
     * @param string $order  Ad-hoc SQL snippet to override the default
     *                       SELECT ORDER BY clause.
     * @param int    $start  Row number from which to start listing in result
     * @param int    $count  Number of rows to list in result set
     * @param array  $params Parameters to use in placeholder substitutions
     *                       (if any).
     * @return int   Number of records from the table (or PEAR_Error on failure)
     *
     * @see DB_Table::select()
     * @access public
     */
    function selectCount($query, $filter = null, $order = null,
                       $start = null, $count = null, $params = array())
    {

        // Is $query a query array or a key of $this->sql ?
        if (is_array($query)) {
            $sql_key = null;
            $count_query = $query;
        } else {
            if (is_string($query)) {
                if (isset($this->sql[$query])) {
                    $sql_key = $query;
                    $count_query = $this->sql[$query];
                } else {
                    return $this->throwError(
                           constant($this->_primary_subclass . '_ERR_SQL_UNDEF'), 
                           $query);
                }
            } else {
                return $this->throwError(
                       constant($this->_primary_subclass . '_ERR_SQL_NOT_STRING'));
            }
        }

        // Use Table name as default 'from' if child class is DB_TABLE
        if ($this->_primary_subclass == 'DB_TABLE') {
            if (!isset($query['from'])) {
                $count_query['from'] = $this->table;
            }
        }

        // If the query is a stored query in $this->sql, then create a corresponding
        // key for the count query, or check if the count-query already exists
        $ready = false;
        if ($sql_key) {
            // Create an sql key name for this count-query
            $count_key = '__count_' . $sql_key;
            // Check if a this count query alread exists in $this->sql
            if (isset($this->sql[$count_key])) {
                $ready = true;
            }
        }

        // If a count-query does not already exist, create $count_query array
        if ($ready) {

            $count_query = $this->sql[$count_key];

        } else {

            // Is a count-field set for the query?
            if (!isset($count_query['count']) || 
                trim($count_query['count']) == '') {
                $count_query['count'] = '*';
            }

            // Replace the SELECT fields with a COUNT() command
            $count_query['select'] = "COUNT({$count_query['count']})";

            // Replace the 'get' key so we only get one result item
            $count_query['get'] = 'one';

            // Create a new count-query in $this->sql
            if ($sql_key) {
                $this->sql[$count_key] = $count_query;
            }

        }

        // Retrieve the count results
        return $this->select($count_query, $filter, $order,
                             $start, $count, $params);

    }

    /**
     * Changes the $this->db PEAR DB/MDB2 object fetchmode and
     * fetchmode_object_class.
     *
     * @param string $new_mode A DB/MDB2_FETCHMODE_* constant.  If null,
     * defaults to whatever the DB/MDB2 object is currently using.
     *
     * @param string $new_class The object class to use for results when
     * the $db object is in DB/MDB2_FETCHMODE_OBJECT fetch mode.  If null,
     * defaults to whatever the the DB/MDB2 object is currently using.
     *
     * @return void
     * @access private
     */
    function _swapModes($new_mode, $new_class)
    {
        // get the old (current) mode and class
        $db =& $this->db;
        $old_mode = $db->fetchmode;
        if ($this->backend == 'mdb2') {
            $old_class = $db->getOption('fetch_class');
        } else {
            $old_class = $db->fetchmode_object_class;
        }

        // don't need to swap anything if the new modes are both
        // null or if the old and new modes already match.
        if ((is_null($new_mode) && is_null($new_class)) ||
            ($old_mode == $new_mode && $old_class == $new_class)) {
            return;
        }

        // set the default new mode
        if (is_null($new_mode)) {
            $new_mode = $old_mode;
        }

        // set the default new class
        if (is_null($new_class)) {
            $new_class = $old_class;
        }

        // swap modes
        $db->setFetchMode($new_mode, $new_class);
    }

    /**
    * Returns SQL literal string representation of a php value
    *
    * If $value is: 
    *    - a string, return the string enquoted and escaped
    *    - a number, return cast of number to string, without quotes
    *    - a boolean, return '1' for true and '0' for false
    *    - null, return the string 'NULL'
    * 
    * @param  mixed  $value 
    * @return string representation of value as an SQL literal
    * 
    * @see DB_Common::quoteSmart()
    * @see MDB2::quote()
    * @access public
    */
    function quote($value)
    {
        if (is_bool($value)) {
           return $value ? '1' : '0';
        } 
        if ($this->backend == 'mdb2') {
            $value = $this->db->quote($value);
        } else {
            $value = $this->db->quoteSmart($value);
        }
        return (string) $value;
    }

    // }}}
}

// }}}

/* Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
