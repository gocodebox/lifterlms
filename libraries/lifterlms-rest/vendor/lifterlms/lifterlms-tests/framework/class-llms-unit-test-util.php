<?php
/**
 * Testing Utilities
 */
class LLMS_Unit_Test_Util {

	/**
	 * @param object $obj
	 * @return ReflectionClass
	 * @throws ReflectionException
	 */
	private static function get_class( $obj ) {

		return new ReflectionClass( $obj );

	}

	/**
	 * Call a method (private or protected)
	 *
	 * @param object $obj  Instantiated class instance
	 * @param string $name Name of the method to call
	 * @param array  $args arguments to pass to the method
	 * @return mixed
	 * @throws ReflectionException
	 */
	public static function call_method( $obj, $name, array $args = array() ) {

		$method = self::get_private_method( $obj, $name );
		$obj = is_string( $obj ) ? null : $obj;
		return $method->invokeArgs( $obj, $args );

	}

	/**
	 * Alias of LLMS_Unit_Test_Utilities::get_private_method()
	 *
	 * @param object $obj  Instantiated class instance
	 * @param string $name Name of the method to call
	 * @return ReflectionMethod
	 * @throws ReflectionException
	 */
	public static function get_protected_method( $obj, $name ) {

		return self::get_private_method( $obj, $name );

	}

	/**
	 * Retrieve a testable private/protected class method
	 *
	 * @param object $obj  Instantiated class instance
	 * @param string $name Name of the method to call
	 * @return ReflectionMethod
	 * @throws ReflectionException
	 */
	public static function get_private_method( $obj, $name ) {

		$class = self::get_class( $obj );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );
		return $method;

	}

	/**
	 * @param object $obj
	 * @param string $name
	 * @return ReflectionProperty
	 * @throws ReflectionException
	 */
	public static function get_private_property( $obj, $name ) {

		$class = self::get_class( $obj );
		$prop = $class->getProperty( $name );
		return $prop;

	}

	/**
	 * Get the value of a private property.
	 *
	 * @since 1.11.0
	 * @since 2.0.2 Add ability to get values from static properties.
	 *
	 * @param object|string $obj  Object. String is allowed if the property is static.
	 * @param string        $name Property name.
	 * @return mixed
	 */
	public static function get_private_property_value( $obj, $name ) {

		$prop = self::get_private_property( $obj, $name );
		$prop->setAccessible( true );
		return $prop->isStatic() ? $prop->getValue() : $prop->getValue( $obj );

	}

	/**
	 * @param object $obj
	 * @param string $name
	 * @param mixed $val
	 * @throws ReflectionException
	 */
	public static function set_private_property( $obj, $name, $val ) {

		$prop = self::get_private_property( $obj, $name );
		$prop->setAccessible( true );
		$prop->setValue( $obj, $val );

	}

}
