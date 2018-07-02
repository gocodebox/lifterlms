<?php
/**
 * Allow testing functions that exit()
 * Use `$this->expectException( LLMS_Testing_Exception_Exit::class );` before calling the function
 * to test the function that calls `exit()`
 * @since    [version]
 * @version  [version]
 */
class LLMS_Testing_Exception_Exit extends Exception {}
