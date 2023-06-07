<?php
/**
 * File management
 *
 * @since Unknown
 */
class LLMS_Unit_Test_Files {

	/**
	 * Copy a file in the tests assets directory to a new directory.
	 *
	 * @since Unknown
	 *
	 * @param string $file     Asset filename to be copied.
	 * @param string $dest     Path to the directory where the file should be stored.
	 * @param string $filename Optional new filename (with extension). If not supplied the basename() of $file (with the extension) is used.
	 * @return string Full path to the copy.
	 */
	public static function copy_asset( $file, $dest, $filename = '' ) {
		return self::copy( self::get_asset_path( $file ), $dest, $filename );
	}

	/**
	 * Copy a file to a location
	 *
	 * @since Unknown
	 *
	 * @param string $file     Full path to the original file to be copied.
	 * @param string $dest     Path to the directory where the file should be stored.
	 * @param string $filename Optional new filename (with extension). If not supplied the basename() of $file (with the extension) is used.
	 * @return string Full path to the copy of $file.
	 */
	public static function copy( $file, $dest, $filename = '' ) {

		// Setup the filename
		$filename = $filename ? $filename : basename( $file );

		// Full path to the copy.
		$copy = trailingslashit( $dest ) . $filename;

		// Remove the copy before trying to copy there to prevent `copy()` from failing.
		self::remove( $copy );

		// Make sure the destination dir exists.
		if ( ! file_exists( $dest ) ) {
			mkdir( $dest, 0777, true );
		}

		copy( $file, $copy );

		return $copy;

	}

	/**
	 * Retrieve the full path to the tests assets directory.
	 *
	 * @since Unknown
	 *
	 * @return string
	 */
	public static function get_asset_dir() {

		global $_llms_tests_bootstrap;
		return $_llms_tests_bootstrap->assets_dir;

	}

	/**
	 * Get the full path to an asset in the tests asset directory.
	 *
	 * @since Unknown
	 *
	 * @param string $filename The filename of a file in the tests asset directory.
	 * @return string
	 */
	public static function get_asset_path( $filename ) {
		return self::get_asset_dir() . '/' . $filename;
	}

	/**
	 * Delete a file.
	 *
	 * @since Unknown
	 *
	 * @param string $file Pull path to the file to delete.
	 * @return void
	 */
	public static function remove( $file ) {

		if ( file_exists( $file ) ) {
			unlink( $file );
		}

	}


}
