<?php

/**
 * @copyright Copyright 2013, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_filesystem\extensions\adapter\storage\filesystem;

use li3_filesystem\storage\filesystem\Entity;
use li3_filesystem\storage\filesystem\Source;
use lithium\util\Collection;

/**
 * Extends the `Source` class to implement the necessary logic to handle files on ftp server.
 *
 * @package li3_filesystem\extensions\adapter\storage\filesystem
 */
class Ftp extends Source {

	/**
	 * Tell us does we successfully connected and loged in on FTP server
	 *
	 * @var boolean
	 */
	protected $_connected = false;

	/**
	 * Connection stream place holder
	 */
	protected $_connection;

	/**
	 * Connect to FTP server on initialization
	 */
	protected function _init() {
		parent::_init();
		if ($this->_config['url'] && substr($this->_config['url'], -1) === '/') {
			$this->_config['url'] = rtrim($this->_config['url'], '/');
		}
		$this->_connect();
	}

	/**
	 * This method setup connection (FTP, SFTP) and prepare
	 * connection stream, then try to login.
	 */
	protected function _connect() {
		if ($this->_config['ssl']) {
			$this->_connection = ftp_ssl_connect(
				$this->_config['host'], $this->_config['port'], $this->_config['timeout']
			);
		} else {
			$this->_connection = ftp_connect(
				$this->_config['host'], $this->_config['port'], $this->_config['timeout']
			);
		}
		if ($this->_connection) {
			$login = ftp_login(
				$this->_connection, $this->_config['username'], $this->_config['password']
			);
			if ($this->_connection) {
				$this->_connected = true;
			}
			if ($this->_config['passive']) {
				ftp_pasv($this->_connection, $this->_config['passive']);
			}
		}
	}

	/**
	 * This method close connection stream
	 */
	protected function _disconnect() {
		if ($this->_connected) {
			if (ftp_close($this->_connection)) {
				$this->_connected = false;
			}
		}
	}

	/**
	 * This method emulate PHP's is_dir() in FTP enviorment
	 *
	 * @param string $location
	 * @return boolean
	 */
	protected function _is_dir($location = null) {
		$glob = ftp_nlist($this->_connection, $location);
		if ($glob && in_array('.', $glob) && in_array('..', $glob)) {
			return true;
		}
		return false;
	}

	/**
	 * This method emulate PHP's file_exists() in FTP enviorment
	 *
	 * @param string $path
	 * @return boolean
	 */
	protected function _file_exists($path = null) {
		if (!$path) {
			return false;
		} elseif ($this->_is_dir($path)) {
			return true;
		} elseif (ftp_size($this->_connection, $path) > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Download and upload file to new location
	 * We use this method only inside `copy()` method
	 *
	 * @param dtring $source
	 * @param string $dest
	 * @return boolean
	 */
	protected function _copy_file($source, $dest) {
		$tmp = tempnam(sys_get_temp_dir(), 'FTP');
		$get = ftp_get($this->_connection, $tmp, $source, FTP_BINARY);
		$put = false;
		if ($get) {
			$put = ftp_put($this->_connection, $dest, $tmp, FTP_BINARY);
		}
		unlink($tmp);
		return $put;
	}

	/**
	 * Prepand adapters location to path.
	 * This method will trim slashes.
	 *
	 * @param string $path
	 * @param string $location
	 * @return string
	 */
	protected function _prependLocation($path, $location = null) {
		($location) || $location = $this->_config['location'];
		$location = trim($location, '/');
		return ($path === '/') ? $location . $path : $location . '/' . trim($path, '/');
	}

	/**
	 * Disconnect connection stream on destruction
	 */
	public function __destruct() {
		$this->_disconnect();
	}

	/**
	 * List directory content
	 *
	 * @param string $path
	 * @return boolean
	 */
	public function ls($path = null) {
		if (!$path = $this->_prependLocation($path)) {
			$path = '/';
		}

		if (!$this->_is_dir($path)) {
			return false;
		}

		$collection = new Collection();

		foreach (ftp_nlist($this->_connection, $path) as $d) {
			if ($d === '.' || $d === '..') continue;

			if ($path === '/') {
				$path_d = $path;
				$url = "/{$d}";
			} else {
				$path_d = "/{$path}";
				$url = "{$path}/{$d}";
			}

			if (($size = ftp_size($this->_connection, $url)) < 0) {
				$size = null;
			}

			$collection->append(new Entity(array(
				'name' => $d,
				'dir' => $this->_is_dir($url),
				'url' => "{$this->_config['url']}{$url}",
				'path' => $path,
				'size' => $size,
				'mode' => null
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
		$dirCreated = false;
		$currentPath = null;
		$name = $this->_prependLocation($name);

		$mkdir = function($connection, $name) use ($options) {
			if (@ftp_mkdir($connection, $name)) {
				if (ftp_chmod($connection, $options['mode'], $name)) {
					return true;
				}
			}
			return false;
		};

		if ($options['recursive']) {
			$parts = explode('/', $name);

			foreach ($parts as $part) {
				if (!$currentPath) {
					$currentPath = $part;
				} else {
					$currentPath .= "/{$part}";
				}

				if ($this->_is_dir($currentPath)) {
					continue;
				}

				$dirCreated = $mkdir($this->_connection, $currentPath);
			}

			return $dirCreated;
		}

		return $mkdir($this->_connection, $name);
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
		$options += array('overwrite' => false);
		$destination = $this->_prependLocation($destination);

		if (isset($file['error']) &&
			$file['error'] === UPLOAD_ERR_OK &&
			$this->_is_dir($destination))
		{
			$name = "{$destination}/{$file['name']}";
			if (!$options['overwrite'] && $this->_file_exists($name)) {
				return false;
			}
			$uploaded = ftp_put($this->_connection, $name, $file['tmp_name'], FTP_BINARY);
			@unlink($file['tmp_name']);
			return $uploaded;
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
		$source = $this->_prependLocation($source);
		$destination = $this->_prependLocation($destination);

		if ($this->_file_exists($source) && !$this->_file_exists($destination)) {
			if ($this->_is_dir($source)) {
				if ($this->mkdir($destination)) {
					foreach ($this->ls($source) as $f) {
						$src = "{$source}/{$f->name}";
						$dst = "{$destination}/{$f->name}";
						if (!$this->copy($src, $dst)) {
							return false;
						}
					}

					return true;
				}

				return false;
			}

			return $this->_copy_file($source, $destination);
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
		$source = $this->_prependLocation($source);
		$destination = $this->_prependLocation($destination);

		if ($this->_file_exists($source) && !$this->_file_exists($destination)) {
			return ftp_rename($this->_connection, $source, $destination);
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
		$options += array('recursive' => true);
		$path = $this->_prependLocation($path);

		if ($this->_is_dir($path)) {
			if ($options['recursive']) {
				foreach ($this->ls($path) as $f) {
					if (!$this->remove("{$path}/{$f->name}")) {
						return false;
					}
				}
			}

			return ftp_rmdir($this->_connection, $path);
		}

		return ftp_delete($this->_connection, $path);
	}

}

?>