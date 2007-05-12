<?php

require 'db1/define.php';
#require 'db1/DataFile.php';

#print "\nFinalize database \n";
$db1->addAllLinks();

if (!$db_conn) {  
    // Create database (MySQL specific code)
    $result = $conn->query("CREATE DATABASE $db_name");
    if (PEAR::isError($result)) {
        print $result->getMessage()."\n";
    } 
}

// Set default database (MySQL specific code)
$result = $conn->query("USE $db_name");
if (PEAR::isError($result)){
    print $result->getMessage()."\n";
}
 
// create all tables in $db1
$result = $db1->createTables('drop');
if (PEAR::isError($result)){
    print "Error during creation of tables of {$db1->name}.\n";
    print $result->getMessage()."\n";
#} else {
#    print "Database {$db1->name} successfully created\n";
}

// Create table DataFile
$result = $DataFile->create('drop');
if (PEAR::isError($result)) {
    print "Error during creation of table {$DataFile->table}.\n";
    print $result->getMessage()."\n";
#} else {
#    print "Table {$DataFile->table} successfully created\n";
}

?>
