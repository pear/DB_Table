<?php

require dirname(__FILE__) . '/define.php';
#require dirname(__FILE__) . '/DataFile.php';

#if (!$db_conn) {  
#    // Create database (MySQL specific code)
#    $result = $conn->query("CREATE DATABASE $db_name");
#    if (PEAR::isError($result)) {
#        print $result->getMessage()."\n";
#    } 
#}
#
#// Set default database (MySQL specific code)
#$result = $conn->query("USE $db_name");
#if (PEAR::isError($result)){
#    print $result->getMessage()."\n";
#}
 
// create all tables in $db
$result = $db->createTables('drop');
if (PEAR::isError($result)){
    if ($verbose > -1) {
        print "Error during creation of tables of {$db->name}.\n";
        print $result->getMessage()."\n";
    }
#} else {
#    print "Database {$db->name} successfully created\n";
}

// Create table DataFile
$result = $DataFile->create('drop');
if (PEAR::isError($result)) {
    if ($verbose > -1) {
        print "Error during creation of table {$DataFile->table}.\n";
        print $result->getMessage()."\n";
    }
#} else {
#    print "Table {$DataFile->table} successfully created\n";
}

?>
