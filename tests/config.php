<?php

if (!empty($_ENV['MYSQL_TEST_USER']) && extension_loaded('mysqli')) {
    $dsn = array(
        'phptype' => 'mysqli',
        'username' => $_ENV['MYSQL_TEST_USER'],
        'password' => $_ENV['MYSQL_TEST_PASSWD'],
        'database' => $_ENV['MYSQL_TEST_DB'],

        'hostspec' => empty($_ENV['MYSQL_TEST_HOST'])
                ? null : $_ENV['MYSQL_TEST_HOST'],

        'port' => empty($_ENV['MYSQL_TEST_PORT'])
                ? null : $_ENV['MYSQL_TEST_PORT'],

        'socket' => empty($_ENV['MYSQL_TEST_SOCKET'])
                ? null : $_ENV['MYSQL_TEST_SOCKET'],
    );
} elseif (!empty($_ENV['PGSQL_TEST_USER']) && extension_loaded('pgsql')) {
    $dsn = array(
        'phptype' => 'pgsql',
        'username' => $_ENV['PGSQL_TEST_USER'],
        'password' => $_ENV['PGSQL_TEST_PASSWD'],
        'database' => $_ENV['PGSQL_TEST_DB'],

        'hostspec' => empty($_ENV['PGSQL_TEST_HOST'])
                ? null : $_ENV['PGSQL_TEST_HOST'],

        'port' => empty($_ENV['PGSQL_TEST_PORT'])
                ? null : $_ENV['PGSQL_TEST_PORT'],

        'socket' => empty($_ENV['PGSQL_TEST_SOCKET'])
                ? null : $_ENV['PGSQL_TEST_SOCKET'],

        'protocol' => empty($_ENV['PGSQL_TEST_PROTOCOL'])
                ? null : $_ENV['PGSQL_TEST_PROTOCOL'],

        'option' => empty($_ENV['PGSQL_TEST_OPTIONS'])
                ? null : $_ENV['PGSQL_TEST_OPTIONS'],

        'tty' => empty($_ENV['PGSQL_TEST_TTY'])
                ? null : $_ENV['PGSQL_TEST_TTY'],

        'connect_timeout' => empty($_ENV['PGSQL_TEST_CONNECT_TIMEOUT'])
                ? null : $_ENV['PGSQL_TEST_CONNECT_TIMEOUT'],

        'sslmode' => empty($_ENV['PGSQL_TEST_SSL_MODE'])
                ? null : $_ENV['PGSQL_TEST_SSL_MODE'],

        'service' => empty($_ENV['PGSQL_TEST_SERVICE'])
                ? null : $_ENV['PGSQL_TEST_SERVICE'],
    );
} else {
    die("skip DSN information not provided\n");
}

// Database name:
// Do NOT use an existing database for DB_TABLE_DATABASE unit tests //
// Do use an existing database for DB_TABLE_Generator test
$db_name = $dsn['database'];

// Choose MDB2 or DB
$backend  = 'DB'; // 'DB' or 'MDB2', capitalized

// Connection mode (change as needed)
// Set db_conn true to connect directly to an existing database
// Set false to connect to RDBMS and then create the database.
// Set false for MySQL
$db_conn = false;

// Verbosity of unit test output
// (-1 for silent, 0 for method names, 1 for some data, 2 for more)
$verbose = -1;

// ---------- Do not change below this line -----------------------

// Connect to RDBMS, $conn is DB/MDB2 object
if ($backend == 'DB') {
    require_once 'DB.php';
    $conn =& DB::connect($dsn);
    if (DB::isError($conn)) {
        print $conn->getMessage()."\n";
        die;
    }
} elseif ($backend == 'MDB2') {
    require_once 'MDB2.php';
    $conn =& MDB2::factory($dsn);
    if (PEAR::isError($conn)) {
        print "\n" . "Failure to connect by MDB2";
        print "\n" . $conn->getMessage();
        die;
    }
}

?>
