<?php
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Abstract class of for TestCase objects that can do regression tests.
 *
 * To implement regression tests within a unit test framework, this class
 * provides a method of writing values of variables (including arrays) to
 * file in 'write mode', and then comparing the stored values to those
 * obtained for in later regression tests, in 'read mode'.
 *
 * The public addData() method is used to submit a PHP variable for
 * regression testing. In write mode, the addData() method stores the value
 * of the variable during the test, and then exports all of the registered
 * variables in a single test method to a data file at the end of the test.
 * In read mode, the addData() calls the assertEquals() method to compare
 * the current value to a value of that variable that was stored in the
 * data file.
 *
 * One data file is created for each test method that calls the addData()
 * method. Each such data file is created in a directory specified by the
 * value of the $data_dir property. The name of each file is the name of
 * the corresponding test method, plus a '.php' extension. In write mode,
 * the file associated with a test method is created and written after
 * the test itself returns, but before the tearDown() method is called.
 * In read mode, the data file (if it exists) is imported after the setUp()
 * method is called, but before the test method is run.
 */
class DataTestCase extends PHPUnit_Framework_TestCase {

    /**
     * Path to data files for all tests in this TestCase class.
     *
     * This path must be set in the TestCase class definition. If no
     * regression testing is needed for any method in the class, leave
     * this null, and no data files will be created or imported.
     *
     * @var string $data_dir
     */
    var $data_dir  = null;

    /**
     * Data mode: Set to 'r' for read mode (default) or 'w' for write mode.
     *
     * Use write mode ('w') to create the data files that store variable
     * values initially, and read mode ('r') for later regression tests.
     *
     * This variable should be set in the TestCase class definition.
     *
     * @var string $data_dir
     * @access public
     */
    var $data_mode = 'r';

    /**
     * File handle for the data file for a single test
     *
     * @var handle $data_file
     */
    var $data_file = null;

    /**
     * Level of 'verbosity' of report printed to standard output
     *
     * Verbosity levels:
     *   - $verbose = -1 No output except '.' for sucessful tests
     *   - $verbose =  0 Print name of each test method
     *   - $verbose =  1 Print comments, and messages from expected errors
     *   - $verbose =  2 Print values of data stored for regression tests
     *
     * @var integer $verbose
     */
    var $verbose   = 0;

    /**
     * Returns the path to the data file for this test
     *
     * The directory is given by $this->data_dir, and the base filename is
     * the name of the test method, $this->getName(), with a '.php' extension.
     *
     * Returns null if $this->data_dir is null
     *
     * @return string Path to data file for a single test
     * @access public
     */
    function dataFilePath()
    {
        if ($this->data_dir) {
            return $this->data_dir . '/' . $this->getName() . '.php';
        } else {
            return null;
        }
    }

    /**
     * Registers a PHP variable $data for regression testa
     *
     * Write mode: If $this->data_mode == 'w', add $data to the $this->data
     * array, which contains variables to be exported to the data file
     *
     * Read mode: If $this->data_mode == 'r', compare $data to the expected
     * value of the same variable, which is obtained from the data file
     *
     * In either mode, $data is also printed if $this->verbose > 1
     *
     * @param mixed  $data Data value
     * @param string $name Data variable name
     * @return void
     * @access public
     */
    function addData($data, $name)
    {
        if ($this->data_mode == 'w') {
            $this->data[$name] = $data;
        }
        if ($this->data_mode == 'r') {
            if (isset($this->expect[$name])) {
                $this->assertEquals($this->expect[$name], $data);
            } else {
                print "\n Failure: No expected value for variable '$name'";
                $this->assertTrue(false);
            }
        }
        if ($this->verbose > 1) {
            if (is_array($data)) {
                $this->print_result($data, $name);
            }
        }
    }

    /**
     * Write data file for this test method. Used only in write mode.
     *
     * If $this->data_mode = 'w', this method is used to export all of
     * the the data values in the $data property array to the data file.
     * The method is called from the runBare() method after the actual
     * actual test is complete, before calling the tearDown() method.
     *
     * No data file is created or written if $this->data is empty.
     *
     * @return void
     * @access public
     */
    function writeDataFile()
    {

        // If no data has been stored, return without writing file
        if (!$this->data) {
            return;
        }

        // Open file
        $filename = $this->dataFilePath();
        if ($this->data_mode == 'w' && !is_dir($this->data_dir)) {
            if (!file_exists($this->data_dir)) {
                mkdir($this->data_dir, 0777, true);
            } else {
                return;
            }
        }
        $this->data_file = fopen($filename, $this->data_mode);

        // Write file
        fwrite($this->data_file, '<?php');
        $this->writeLine('$this->expect = array();');
        foreach ($this->data as $name => $datum) {
            $text = '$this->expect[' . "'$name'" . '] =';
            if (is_array($datum)) {
                $text = $text . "\n";
            }
            $text = $text . var_export($datum, true) . ';' ;
            $this->writeLine($text);
        }
        $this->writeLine('?>');

        // Close file
        fclose($this->data_file);

    }


    /**
     * Write one line of $text to the data file
     *
     * @param string $text Text line to be written
     * @return void
     * @access public
     */
    function writeLine($text = '')
    {
        if ($this->data_file) {
            fwrite($this->data_file, "\n" . $text);
        }
    }


    /**
     * Print result of database result set array.
     *
     * Input is sequential array of rows, each row may be a sequential or
     * associative array
     *
     * @param array  $result Result set as an array
     * @param string $name   Name of result set (used for title line)
     * @return void
     * @access public
     */
    function print_result($result, $name) {
        if ($name) {
            print "\n$name :";
        }
        foreach ($result as $row) {
            $s = array();
            foreach ($row as $key => $value){
                $s[] = "$value";
            }
            print "\n" . implode(', ',$s);
        }
    }

    /**
     * Asserts that argument is a NOT a PEAR_Error object
     *
     * Throws exception if $result is a PEAR_Error
     *
     * @param object $result PEAR_Error object
     * @return void
     * @access public
     */
    function assertNotError($result)
    {
        if (PEAR::isError($result)){
            print "\n" . $result->getMessage();
            $this->assertTrue(false);
        }
    }

    /**
     * Asserts that argument is a PEAR_Error object
     *
     * Throws exception if $result is a PEAR_Error
     *
     * @param object $result PEAR_Error object
     * @return void
     * @access public
     */
    function assertIsError($result, $msg = null)
    {
        if (PEAR::isError($result)){
            if ($this->verbose > 0) {
                print "\n" . $result->getMessage();
            }
            $this->assertTrue(true);
        } else {
            if ($msg) {
                print "\n Failure: " . $msg;
            }
            $this->assertTrue(false);
        }
    }

    /**
     * Runs the bare test sequence.
     *
     * @access public
     */
    public function runBare()
    {
        $catchedException = NULL;

        $this->setUp();

        // Setup data array and data file for test
        if ($this->data_dir) {
            if ($this->data_mode == 'w') {
                $this->data = array();
            }
            if ($this->data_mode == 'r') {
                $filename = $this->dataFilePath();
                if (file_exists($filename)) {
                    require_once $filename;
                }
            }
        }

        try {
            $this->runTest();
        }

        catch (Exception $e) {
            $catchedException = $e;
        }

        // Write data file if data_mode == 'w'
        if ($this->data_dir && $this->data_mode == 'w') {
            $this->writeDataFile();
        }

        $this->tearDown();

        // Workaround for missing "finally".
        if ($catchedException !== NULL) {
            throw $catchedException;
        }
    }

}