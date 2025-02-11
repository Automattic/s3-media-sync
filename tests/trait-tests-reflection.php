<?php
/**
 * Reflection functions used by integration tests
 *
 * @package S3_Media_Sync
 */

namespace S3_Media_Sync\Tests;

trait Tests_Reflection {

	/**
	 * Overrides the value of a private property on a given object. This is
	 * useful when mocking the internals of a class.
	 *
	 * Note that the property will no longer be private after setAccessible is called.
	 *
	 * @param class-string $class_name      The fully qualified class name, including namespace.
	 * @param object       $object_instance The object instance on which to set the value.
	 * @param string       $property_name   The name of the private property to override.
	 * @param mixed        $value           The value to set.
	 *
	 * @throws \ReflectionException If the class or property does not exist.
	 */
	public static function set_private_property( string $class_name, object $object_instance, string $property_name, mixed $value ): void {
		$property = ( new \ReflectionClass( $class_name ) )->getProperty( $property_name );
		$property->setValue( $object_instance, $value );
	}

	/**
	 * Retrieves the value of a private property on a given object. This is
	 * useful when testing the internals of a class.
	 *
	 * @param class-string $class_name      The fully qualified class name, including namespace.
	 * @param object       $object_instance The object instance on which to retrieve the value.
	 * @param string       $property_name   The name of the private property to return.
	 *
	 * @throws \ReflectionException If the class or property does not exist.
	 */
	public static function get_private_property( string $class_name, object $object_instance, string $property_name ): mixed {
		$property = ( new \ReflectionClass( $class_name ) )->getProperty( $property_name );
		return $property->getValue( $object_instance );
	}
}
