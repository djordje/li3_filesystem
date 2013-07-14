<?php

/**
 * @copyright Copyright 2013, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_filesystem\storage;

/**
 * Easy static access to all adapter's methods fetched by defined location name.
 *
 * Example:
 * {{{
 * class WebrootImg extends \li3_filesystem\storage\FS {
 *     public static $location = 'webroot_img';
 * }
 *
 * // List content of location
 * $ls = WebrootImg::ls();
 *
 * // Make directory
 * $mkdir = WebrootImg::mkdir('dir_name');
 *
 * // Upload file
 * $upload = WebrootImg::upload($_FILES['uploaded'], '/');
 *
 * // Copy file
 * $copy = WebrootImg::copy('img.png', 'dir_name/img.png');
 *
 * // Move or rename file
 * $move = WebrootImg::move('img.png', 'old_img.png');
 *
 * // Remove file
 * $remove = WebrootImg::remove('dir_name');
 * }}}
 *
 * Class FS represent base model for interaction with named location.
 * You should extend this model and overrive `$location` property to define what named location
 * will be used.
 *
 * @package li3_filesystem\storage
 */
class FS extends \lithium\core\StaticObject {

	/**
	 * Location to initialize
	 *
	 * @var string
	 * @see li3_filesystem\extensions\data\Locations::get()
	 */
	public static $location = 'default';

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
	 * State of model initialization
	 *
	 * @var boolean
	 */
	protected static $_initialized = false;

	/**
	 * Instantiate location adapter
	 */
	public static function __init() {
		if (!static::$_initialized) {
			$locations = static::$_classes['locations'];
			static::$_instances['adapter'] = $locations::get(static::$location);
			static::$_initialized = true;
		}
	}

	/**
	 * Call same method on adapter object.
	 *
	 * @filter This method can be filtered.
	 *
	 * @param string $location
	 * @return boolean|\lithium\util\Collection
	 */
	public static function ls($path = null) {
		$params = compact('path');
		$adapter = static::$_instances['adapter'];
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
	 * @param string $name
	 * @param array $options
	 * @return boolean
	 */
	public static function mkdir($name, array $options = array()) {
		$params = compact('name', 'options');
		$adapter = static::$_instances['adapter'];
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
	 * @param array $file
	 * @param string $destination
	 * @param array $options
	 * @return boolean
	 */
	public static function upload(array $file, $destination, array $options = array()) {
		$params = compact('file', 'destination', 'options');
		$adapter = static::$_instances['adapter'];
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
	 * @param string $source
	 * @param string $destination
	 * @param array $options
	 * @return boolean
	 */
	public static function copy($source, $destination, array $options = array()) {
		$params = compact('source', 'destination', 'options');
		$adapter = static::$_instances['adapter'];
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
	 * @param string $source
	 * @param string $destination
	 * @param array $options
	 * @return boolean
	 */
	public static function move($source, $destination) {
		$params = compact('source', 'destination');
		$adapter = static::$_instances['adapter'];
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
	 * @param string $path
	 * @param array $options
	 * @return boolean
	 */
	public static function remove($path, array $options = array()) {
		$params = compact('path', 'options');
		$adapter = static::$_instances['adapter'];
		$callback = function($self, $params) use($adapter) {
			return $adapter->remove($params['path'], $params['options']);
		};
		return static::_filter(__FUNCTION__, $params, $callback);
	}

}

?>