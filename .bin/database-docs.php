<?php

/**
 * Automatically generate LifterLMS Table documentation from registered schema
 * files.
 *
 * Usage: php ./.bin/database-docs.php
 */

// Define various constants, some WP Core constants are faked to ensure the script can run.
define( 'ROOT_DIR', dirname( __FILE__, 2 ) );
define( 'ABSPATH', 'FAKED' );
define( 'DOCFILE', ROOT_DIR . '/docs/database.md' );
define( 'LLMS_PLUGIN_DIR', ROOT_DIR . '/' );

// Require necessary classes and files.
require ROOT_DIR . '/includes/traits/llms-trait-singleton.php';
require ROOT_DIR . '/includes/class-llms-database.php';
require ROOT_DIR . '/includes/class-llms-database-table.php';
require ROOT_DIR . '/includes/class-llms-database-column.php';

// Fake the wpdb global.
global $wpdb;
$wpdb = new class {
	public $prefix = 'wp_';
	public function has_cap() {
		return false;
	}
};

/**
 * Creates a markdown document based on the parsed documentation
 *
 * @link https://gist.github.com/dapepe/9956717
 * @author Peter-Christoph Haider <peter.haider@zeyon.net>
 * @package Apidoc
 * @version 1.00 (2014-04-04)
 * @license GNU Lesser Public License
 */
class TextTable {
	/** @var int The source path */
	public $maxlen = 50;
	/** @var array The source path */
	private $data = array();
	/** @var array The source path */
	private $header = array();
	/** @var array The source path */
	private $len = array();
	/** @var array The source path */
	private $align = array(
		'name' => 'L',
		'type' => 'C'
	);

	/**
	 * @param array $header  The header array [key => label, ...]
	 * @param array $content Content
	 * @param array $align   Alignment optios [key => L|R|C, ...]
	 */
	public function __construct($header=null, $content=array(), $align=false) {
		if ($header) {
			$this->header = $header;
		} elseif ($content) {
			foreach ($content[0] as $key => $value)
				$this->header[$key] = $key;
		}

		foreach ($this->header as $key => $label) {
			$this->len[$key] = strlen($label);
		}

		if (is_array($align))
			$this->setAlgin($align);

		$this->addData($content);
	}

	/**
	 * Overwrite the alignment array
	 *
	 * @param array $align   Alignment optios [key => L|R|C, ...]
	 */
	public function setAlgin($align) {
		$this->align = $align;
	}

	/**
	 * Add data to the table
	 *
	 * @param array $content Content
	 */
	public function addData($content) {
		foreach ($content as &$row) {
			foreach ($this->header as $key => $value) {
				if (!isset($row[$key])) {
					$row[$key] = '-';
				} elseif (strlen($row[$key]) > $this->maxlen) {
					$this->len[$key] = $this->maxlen;
					$row[$key] = substr($row[$key], 0, $this->maxlen-3).'...';
				} elseif (strlen($row[$key]) > $this->len[$key]) {
					$this->len[$key] = strlen($row[$key]);
				}
			}
		}

		$this->data = $this->data + $content;
		return $this;
	}

	/**
	 * Add a delimiter
	 *
	 * @return string
	 */
	private function renderDelimiter() {
		$res = '|';
		foreach ($this->len as $key => $l)
			$res .= (isset($this->align[$key]) && ($this->align[$key] == 'C' || $this->align[$key] == 'L') ? ':' : ' ')
			        .str_repeat('-', $l)
			        .(isset($this->align[$key]) && ($this->align[$key] == 'C' || $this->align[$key] == 'R') ? ':' : ' ')
			        .'|';
		return $res."\r\n";
	}

	/**
	 * Render a single row
	 *
	 * @param  array $row
	 * @return string
	 */
	private function renderRow($row) {
		$res = '|';
		foreach ($this->len as $key => $l) {
			$res .= ' '.$row[$key].($l > strlen($row[$key]) ? str_repeat(' ', $l - strlen($row[$key])) : '').' |';
		}

		return $res."\r\n";
	}

	/**
	 * Render the table
	 *
	 * @param  array  $content Additional table content
	 * @return string
	 */
	public function render($content=array()) {
		$this->addData($content);

		$res = $this->renderRow($this->header)
		       .$this->renderDelimiter();
		foreach ($this->data as $row)
			$res .= $this->renderRow($row);

		return $res;
	}
}

/**
 * Limit lines to 80 characters max.
 *
 * @param string $str The string to wrap.
 */
function wrap_str( string $str ): string {
	return wordwrap( $str, 80 );
}

/**
 * Adds indentation to a table create statement string.
 *
 * @param string $str The create table statment.
 * @return string
 */
function format_create_stmt( string $str ): string {

	$lines = explode( PHP_EOL, $str );
	$len   = count( $lines );

	foreach ( $lines as $i => &$line ) {

		// Add indentation on all lines except first and last.
		if ( $i !== 0 && $i !== $len - 1 ) {
			$line = "  {$line}";
		}

		// Fix weird spacing due to table options being excluded from the docs.
		if ( ') ;' === $line ) {
			$line = ");";
		}
	}
	return implode( PHP_EOL, $lines );

}

/**
 * Retrieves the "Type" table column string for a given database column.
 *
 * @param array $cfg A column configuration array.
 */
function field_type( array $cfg ): string {
	$type = $cfg['type'];
	if ( $cfg['length'] ) {
		$type .= "({$cfg['length']})";
	}

	if ( $cfg['unsigned'] ) {
		$type .= ' unsigned';
	}

	return $type;

}

/**
 * Retrieves the "Key" table column string for a given database column.
 *
 * @param string $key  The column name.
 * @param array  $keys The table keys schema array.
 */
function field_key( string $key, array $keys ): string {

	$cfg = $keys[ $key ] ?? null;
	if ( ! $cfg ) {
		return '';
	}

	if ( LLMS_Database_Table::KEY_DEFAULT === $cfg['type'] ) {
		return 'MUL';
	}

	return strtoupper( substr( $cfg['type'], 0, 3 ) );

}

/**
 * Retrieves the "Extra" table column string for a given database column.
 *
 * @since [version]
 *
 * @param array $cfg The column configuration array.
 */
function field_extra( array $cfg ): string {
	return $cfg['auto_increment'] ? 'auto_increment' : '';
}

/**
 * Retrieves the markdown header and description paragraph for a given table.
 *
 * @since [version]
 *
 * @param array $schema The table schema array.
 */
function table_intro( array $schema ): string {

	$prefix = LLMS_Database::instance()->get_prefix();
	$str    = "## {$prefix}{$schema['name']}" . PHP_EOL . PHP_EOL;
	$desc   = $schema['description'] ?? '';
	if ( $desc ) {
		$str .= wrap_str( $desc );
	}

	return $str;
}

/**
 * Generates the docs.
 */
function generate() {

	$fh = fopen( DOCFILE, 'r+' );
	$contents = fread( $fh, filesize( DOCFILE ) );
	fclose( $fh );

	$time = ( new DateTime( 'now', new DateTimeZone( 'GMT' ) ) )->format( 'c' );

	$docs = [
		"<!-- Last Generated at: {$time} -->",
	];

	$head = [
		'Field',
		'Type',
		'Null',
		'Key',
		'Default',
		'Extra',
		'Description',
	];

	foreach ( LLMS_Database::instance()->get_core_tables( true ) as $table ) {

		$schema = $table->get_schema( false );

		$rows = [];

		foreach ( $schema['columns'] as $key => $cfg ) {
			$rows[] = [
				$key,
				field_type( $cfg ),
				$cfg['allow_null'] ? 'YES' : 'NO',
				field_key( $key, $schema['keys'] ),
				$cfg['default'] ?? '',
				field_extra( $cfg ),
				$cfg['description'] ?? '',
			];
		}

		$md_table = new TextTable( $head, $rows, true );
		$docs[] = table_intro( $schema );

		$docs[] = '### Columns';
		$docs[] = trim( $md_table->render() );

		$docs[] = '### Create Statement';
		$docs[] = "```mysql" . PHP_EOL . format_create_stmt( $table->get_create_statement() ) .  PHP_EOL . "```";

	}

	$contents = preg_replace(
		'#(<!-- START TOKEN\(Autogenerated API docs\) -->\s)[.\s\S]*?(<!-- END TOKEN\(Autogenerated API docs\) -->)#m',
		'$1' . implode( PHP_EOL . PHP_EOL, $docs ) . PHP_EOL . '$2',
		$contents
	);

	$fh = fopen( DOCFILE, 'w+' );
	fwrite( $fh, $contents );
	fclose( $fh );

}

generate();
