<?php

/**
 * @copyright Copyright 2013, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_filesystem\storage\filesystem;

use lithium\util\Collection;

/**
 * Class Entity represent file or directory on filesystem.
 *
 * @package li3_filesystem\storage\filesystem
 */
class Entity extends \lithium\core\Object {

	/**
	 * **File or directory name**
	 *
	 * For example `'directory'` or `'file.ext'`.
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * If this entity is directory set `true` otherwise `false`
	 *
	 * @var boolean
	 */
	protected $_dir;

	/**
	 * If location have web access url store absolute url to this file
	 *
	 * @var string
	 */
	protected $_url;

	/**
	 * Path to current file or directory.
	 * If in root of location `'/'` otherwise this is path in format `'/director/'`.
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * If this entity is file this is size in bytes.
	 *
	 * @var int
	 */
	protected $_size;

	/**
	 * Unix style file permissions
	 *
	 * @var string
	 */
	protected $_mode;

	/**
	 * Adapter instance
	 *
	 * @var \li3_filesystem\extensions\data\Source
	 */
	protected $_adapter;

	/**
	 * The list of object properties to be automatically assigned from configuration passed to
	 * `__construct()`.
	 *
	 * @var array
	 */
	protected $_autoConfig = array('name', 'dir', 'url', 'path', 'size', 'mode', 'adapter');

	/**
	 * Concatenate path and name to create full path from location's root
	 *
	 * @return string
	 */
	public function getFullPath() {
		return $this->_path . $this->_name;
	}

	/**
	 * If entity is directory enable call  to `ls()` method on adapter.
	 * This will pass `$this->getFullPath()` as param.
	 *
	 * @return bool
	 */
	public function ls() {
		if ($this->_dir) {
			return $this->_adapter->ls($this->getFullPath());
		}
		return false;
	}

	/**
	 * If entity is directory enable call to `mkdir()` method on adapter.
	 * This will prepend `$name` param with `$this->getFullPath()`
	 *
	 * @param $name
	 * @param array $options
	 * @return bool
	 */
	public function mkdir($name, array $options = array()) {
		if ($this->_dir) {
			$name = $this->getFullPath() . '/' . $name;
			return $this->_adapter->mkdir($name, $options);
		}
		return false;
	}

	/**
	 * Enable entity to call `copy()` method on adapter.
	 * This will pass `$this->getFullPath()` as `$source` param.
	 *
	 * @param $destination
	 * @param array $options
	 * @return mixed
	 */
	public function copy($destination, array $options = array()) {
		$source = $this->getFullPath();
		return $this->_adapter->copy($source, $destination, $options);
	}

	/**
	 * Enable entity to call `move()` method on adapter.
	 * This will pass `$this->getFullPath()` as `$source` param.
	 *
	 * @param $destination
	 * @return mixed
	 */
	public function move($destination) {
		$source = $this->getFullPath();
		return $this->_adapter->move($source, $destination);
	}

	/**
	 * Enable entity to call `remove()` method on adapter.
	 * This will pass `$this->getFullPath()` as `$path` param.
	 *
	 * @param array $options
	 * @return mixed
	 */
	public function remove(array $options = array()) {
		return $this->_adapter->remove($this->getFullPath(), $options);
	}

	/**
	 * Convert size from default bytes to some other format that suit better for some purposes.
	 * Supported formats:
	 *      - `'kb'` KiloBytes (byte / 1024)
	 *      - `'mb'` MegaBytes (byte / 1024^2)
	 *      - `'gb'` GigaBytes (byte / 1024^3)
	 *      - `'tb'` TeraBytes (byte / 1024^4)
	 *
	 * @param string $format Format to convert unit from bytes (kb, mb, gb, tb)
	 * @param int $precision Number of decimals to round floats
	 * @return array Array with size and unit format eg. `array(0 => 128, 'unit' => 'kb')`
	 */
	public function size($format = null, $precision = 3) {
		$size = array(0, 'unit' => 'b');
		switch (strtolower($format)) {
			case 'kb':
				$size[0] = $this->_size / 1024;
				$size['unit'] = $format;
				break;
			case 'mb':
				$size[0] = $this->_size / pow(1024, 2);
				$size['unit'] = $format;
				break;
			case 'gb':
				$size[0] = $this->_size / pow(1024, 3);
				$size['unit'] = $format;
				break;
			case 'tb':
				$size[0] = $this->_size / pow(1024, 4);
				$size['unit'] = $format;
				break;
			default:
				$size[0] = $this->_size;
				break;
		}
		$size[0] = (is_int($size[0])) ? $size[0] : round($size[0], $precision);
		return $size;
	}

	/**
	 * Convert entity to some other format
	 *
	 * @param string $format Conversion format eg. `'array'`
	 * @param array $options Options that will be passed to `\lithium\util\Collection::toArray()`
	 *
	 * @return mixed `$this` or `lithium\util\Collection::toArray()`
	 * @see lithium\util\Collection::toArray()
	 */
	public function to($format, array $options = array()) {
		switch ($format) {
			case 'array':
				return Collection::toArray(array(
					'name' => $this->_name,
					'dir'  => $this->_dir,
					'url'  => $this->_url,
					'path' => $this->_path,
					'size' => $this->_size,
					'mode' => $this->_mode,
				), $options);
			default:
				return $this;
		}
	}

	/**
	 * Enable access to protected properties.
	 * `$entity->name` will return value of protected `$entity->_name`.
	 *
	 * @param string $name
	 * @return mixed string|null
	 */
	public function __get($name) {
		return (!empty($this->{'_' . $name})) ? $this->{'_' . $name} : null;
	}

}

?>