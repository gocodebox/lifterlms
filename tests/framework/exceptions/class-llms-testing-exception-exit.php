<?php
/**
 * Allow testing functions that exit()
 * Use `$this->expectException( LLMS_Testing_Exception_Exit::class );` before calling the function
 * to test the function that calls `exit()`
 * @since    3.19.4
 * @version  3.19.4
 */
class LLMS_Testing_Exception_Exit extends Exception {}
