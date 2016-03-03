<?php

/**
* Session Class
*
* Abstract Session class used for Session management
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
abstract class LLMS_Session {

	/**
	* user id
	* @access private
	* @var int
	*/
	protected $_person_id;

	/**
	* data array
	* @access private
	* @var array
	*/
	protected $_data = array();

	/**
	* has something changed?
	* @access private
	* @var bool
	*/
	protected $_dirty = false;

	/**
	* __get function
	*
	*  @return void
	*/
	public function __get( $key ) {

		return $this->get( $key );

	}

	/**
	* __set function
	*
	*  @return void
	*/
	public function __set( $key, $value ) {

		$this->set( $key, $value );

	}

	/**
	* __isset function
	*
	*  @return void
	*/
	public function __isset( $key ) {

		return isset( $this->_data[ sanitize_title( $key ) ] );

	}

	/**
	* __unset function
	*
	*  @return void
	*/
	public function __unset( $key ) {

		if ( isset( $this->_data[ $key ] ) ) {

			unset( $this->_data[ $key ] );
	   		$this->_dirty = true;

		}

	}

	/**
	* Get session
	*
	* @param string $item
	*/
	public function get( $key, $default = null ) {

		$key = sanitize_key( $key );
		return isset( $this->_data[ $key ] ) ? maybe_unserialize( $this->_data[ $key ] ) : $default;

	}

	/**
	* Set session
	*
	* @return void
	*/
	public function set( $key, $value ) {

		$this->_data[ sanitize_key( $key ) ] = maybe_serialize( $value );
		$this->_dirty = true;

	}

	/**
	* Get person id
	*
	* @return int
	*/
	public function get_person_id() {

		return $this->_person_id;

	}

}

