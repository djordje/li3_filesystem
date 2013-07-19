<?php

/**
 * @copyright Copyright 2013, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_filesystem\storage;
use lithium\util\Validator;

/**
 * Easy static access to all adapter's methods fetched by defined location name.
 *
 * Example:
 * {{{
 * // List content of location
 * $ls = Filesystem::ls('location_name');
 *
 * // Make directory
 * $mkdir = Filesystem::mkdir('location_name', 'dir_name');
 *
 * // Upload file
 * $upload = Filesystem::upload('location_name', $_FILES['uploaded'], '/');
 *
 * // Copy file
 * $copy = Filesystem::copy('location_name', 'img.png', 'dir_name/img.png');
 *
 * // Move or rename file
 * $move = Filesystem::move('location_name', 'img.png', 'old_img.png');
 *
 * // Remove file
 * $remove = Filesystem::remove('location_name', 'dir_name');
 * }}}
 *
 * Class FS represent base model for interaction with named location.
 * You should extend this model and override `$location` property to define what named location
 * will be used.
 *
 * @package li3_filesystem\storage
 */
class Filesystem extends \lithium\core\StaticObject {

	/**
	 * Stores object instances for internal use.
	 *
	 * @var array
	 */
	protected static $_instances = array();

	/**
	 * Class dependencies.
	 *
	 * @var array
	 */
	protected static $_classes = array(
		'locations' => 'li3_filesystem\storage\Locations'
	);

	/**
	 * Placeholder for storing upload validation errors
	 *
	 * @var array
	 */
	public static $uploadErrors = array();

	protected static function _getAdapter($location) {
		if (!isset(static::$_instances['adapter'][$location])) {
			$locations = static::$_classes['locations'];
			static::$_instances['adapter'][$location] = $locations::get($location);
		}
		$adapter = static::$_instances['adapter'][$location];

		if ($adapter instanceof \li3_filesystem\storage\filesystem\Source) {
			return $adapter;
		}

		return false;
	}

	/**
	 * Call same method on adapter object.
	 *
	 * @filter This method can be filtered.
	 *
	 * @param string $location
	 * @param string $path
	 * @return boolean|\lithium\util\Collection
	 */
	public static function ls($location, $path = null) {
		if (!$adapter = static::_getAdapter($location)) {
			return false;
		}
		$params = compact('path');
		$callback = function($self, $params) use($adapter) {
			return $adapter->ls($params['path']);
		};
		return static::_filter(__FUNCTION__, $params, $callback);
	}

	/**
	 * Call same method on adapter object
	 *
	 * @filter This method can be filtered.
	 *
	 * @param string $location
	 * @param string $name
	 * @param array $options
	 * @return boolean
	 */
	public static function mkdir($location, $name, array $options = array()) {
		if (!$adapter = static::_getAdapter($location)) {
			return false;
		}
		$params = compact('name', 'options');
		$callback = function($self, $params) use($adapter) {
			return $adapter->mkdir($params['name'], $params['options']);
		};
		return static::_filter(__FUNCTION__, $params, $callback);
	}

	/**
	 * Call same method on adapter object
	 *
	 * @filter This method can be filtered.
	 *
	 * @param string $location
	 * @param array $file
	 * @param string $destination
	 * @param array $options
	 * @return boolean
	 */
	public static function upload($location, array $file, $destination = null, array $options = array()) {
		$options += array('validates' => array());

		if (!$adapter = static::_getAdapter($location)) {
			return false;
		}

		static::$uploadErrors = array();

		if (!empty($options['validates']) && is_array($options['validates'])) {
			$errors = Validator::check($file, $options['validates']);
			if (!empty($errors)) {
				static::$uploadErrors = $errors;
				return false;
			}
		}

		$params = compact('file', 'destination', 'options');
		$callback = function($self, $params) use($adapter) {
			return $adapter->upload($params['file'], $params['destination'], $params['options']);
		};
		return static::_filter(__FUNCTION__, $params, $callback);
	}

	/**
	 * Call same method on adapter object
	 *
	 * @filter This method can be filtered.
	 *
	 * @param string $location
	 * @param string $source
	 * @param string $destination
	 * @param array $options
	 * @return boolean
	 */
	public static function copy($location, $source, $destination, array $options = array()) {
		if (!$adapter = static::_getAdapter($location)) {
			return false;
		}
		$params = compact('source', 'destination', 'options');
		$callback = function($self, $params) use($adapter) {
			return $adapter->copy($params['source'], $params['destination'], $params['options']);
		};
		return static::_filter(__FUNCTION__, $params, $callback);
	}

	/**
	 * Call same method on adapter object
	 *
	 * @filter This method can be filtered.
	 *
	 * @param string $location
	 * @param string $source
	 * @param string $destination
	 * @param array $options
	 * @return boolean
	 */
	public static function move($location, $source, $destination) {
		if (!$adapter = static::_getAdapter($location)) {
			return false;
		}
		$params = compact('source', 'destination');
		$callback = function($self, $params) use($adapter) {
			return $adapter->move($params['source'], $params['destination']);
		};
		return static::_filter(__FUNCTION__, $params, $callback);
	}

	/**
	 * Call same method on adapter object
	 *
	 * @filter This method can be filtered.
	 *
	 * @param string $location
	 * @param string $path
	 * @param array $options
	 * @return boolean
	 */
	public static function remove($location, $path, array $options = array()) {
		if (!$adapter = static::_getAdapter($location)) {
			return false;
		}
		$params = compact('path', 'options');
		$callback = function($self, $params) use($adapter) {
			return $adapter->remove($params['path'], $params['options']);
		};
		return static::_filter(__FUNCTION__, $params, $callback);
	}

}

?>