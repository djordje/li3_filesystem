<?php

/**
 * @copyright Copyright 2013, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_filesystem\storage\filesystem;

use lithium\core\Object;

/**
 * Class `Source` is base for all filesystem adapters.
 * This class define methods that all adapters must implement and contain shared logic.
 *
 * @package li3_filesystem\storage\filesystem
 */
abstract class Source extends Object {

	/**
	 * This property is shortcut to `$this->_config['location']`
	 *
	 * @var string
	 */
	protected $_location;

	/**
	 * The list of object properties to be automatically assigned from configuration passed to
	 * `__construct()`.
	 *
	 * @var array
	 */
	protected $_autoConfig = array('location');

	/**
	 * Abstract. Must be defined by child classes.
	 */
	abstract public function ls($path = null);

	/**
	 * Abstract. Must be defined by child classes.
	 */
	abstract public function mkdir($name, array $options = array());

	/**
	 * Abstract. Must be defined by child classes.
	 */
	abstract public function upload(array $file, $destination, array $options = array());

	/**
	 * Abstract. Must be defined by child classes.
	 */
	abstract public function copy($source, $destination, array $options = array());

	/**
	 * Abstract. Must be defined by child classes.
	 */
	abstract public function move($source, $destination);

	/**
	 * Abstract. Must be defined by child classes.
	 */
	abstract public function remove($path, array $options = array());

}

?>