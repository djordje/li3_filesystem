<?php

/**
 * @copyright Copyright 2013, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_filesystem\extensions\adapter\storage\filesystem;

use lithium\util\Collection;
use li3_filesystem\storage\filesystem\Entity;
use li3_filesystem\storage\filesystem\Source;
use DirectoryIterator;

/**
 * Extends the `Source` class to implement the necessary logic to handle files on local file system.
 *
 * @package li3_filesystem\extensions\adapter\storage\filesystem
 */
class Filesystem extends Source {

	/**
	 * List directory content
	 *
	 * @param string $path
	 * @return boolean
	 */
	public function ls($path = null) {
		$path = "{$this->_location}/{$path}";

		if (is_dir($path)) {
			$directory = new DirectoryIterator($path);
		} else {
			return false;
		}

		$collection = new Collection();

		foreach ($directory as $d) {
			if ($d->isDot()) {
				continue;
			}

			$url = rtrim($this->_config['url'], '/');
			$path = rtrim(substr($d->getPath(), strlen($this->_location)), '/') . '/';

			if ($url) {
				$url .= "{$path}{$d->getFilename()}";
			}

			$collection->append(new Entity(array(
				'name'    => $d->getFilename(),
				'dir'     => $d->isDir(),
				'url'     => $url,
				'path'    => $path,
				'size'    => ($d->isDir())? null : $d->getSize(),
				'mode'    => substr(sprintf('%o', $d->getPerms()), -4),
				'adapter' => $this
			)));
		}

		return $collection;
	}

	/**
	 * Create directory
	 *
	 * @param string $name
	 * @param array $options
	 * @return boolean
	 */
	public function mkdir($name, array $options = array()) {
		$options += array('mode' => 0777, 'recursive' => true);

		$name = "{$this->_location}/{$name}";

		if (!is_dir($name)) {
			return mkdir($name, $options['mode'], $options['recursive']);
		}
		return false;
	}

	/**
	 * Upload file to your location
	 *
	 * @param array $file Single uploaded file
	 * @param string $destination
	 * @param array $options
	 * @return bool
	 */
	public function upload(array $file, $destination = null, array $options = array()) {
		if (isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
			$destination = ($destination) ? $file['name'] : $destination . '/' . $file['name'];
			$destination = "{$this->_location}/{$destination}";

			if (file_exists($file['tmp_name']) && !file_exists($destination)) {
				return @rename($file['tmp_name'], $destination);
			}
		}

		return false;
	}

	/**
	 * Copy file or directory
	 *
	 * @param array $source
	 * @param string $destination
	 * @param array $options
	 * @return boolean
	 */
	public function copy($source, $destination, array $options = array()) {
		$fullSource      = "{$this->_location}/{$source}";
		$fullDestination = "{$this->_location}/{$destination}";

		if (file_exists($fullSource) && !file_exists($fullDestination)) {
			if (is_dir($fullSource) && $this->mkdir($destination)) {
				foreach ($this->ls($source) as $s) {
					if (!$s->copy($destination . '/' . $s->name)) {
						return false;
					}
				}
				return true;
			}

			return copy($fullSource, $fullDestination);
		}

		return false;
	}

	/**
	 * Move file or directory to another place
	 *
	 * @param $source
	 * @param $destination
	 * @return bool
	 */
	public function move($source, $destination) {
		$source      = "{$this->_location}/{$source}";
		$destination = "{$this->_location}/{$destination}";

		if (file_exists($source) && !file_exists($destination)) {
			return @rename($source, $destination);
		}

		return false;
	}

	/**
	 * Remove file or directory
	 *
	 * @param string $path
	 * @param array $options
	 * @return boolean
	 */
	public function remove($path, array $options = array()) {
		$options += array('recursive' => true, 'prependLocation' => true);

		$fullPath = $this->_location . '/' . $path;

		if (!file_exists($fullPath)) {
			return false;
		}

		if (is_dir($fullPath)) {
			$ls = $this->ls($path);
			if ($options['recursive'] && $ls->count()) {
				foreach ($ls as $d) {
					if (!$d->remove()) {
						return false;
					}
				}
			}

			return rmdir($fullPath);
		}

		return unlink($fullPath);
	}

}

?>