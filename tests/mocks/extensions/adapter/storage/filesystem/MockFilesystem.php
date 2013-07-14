<?php

namespace li3_filesystem\tests\mocks\extensions\adapter\storage\filesystem;

use lithium\util\Collection;

class MockFilesystem extends \li3_filesystem\storage\filesystem\Source {

	public function ls($path = null) {
		switch ($path) {
			case '/dir':
				return new Collection();
			default:
				return false;
		}
	}

	public function mkdir($name, array $options = array()) {
		return true;
	}

	public function upload(array $file, $destination, array $options = array()) {
		return true;
	}

	public function copy($source, $destination, array $options = array()) {
		return true;
	}

	public function move($source, $destination) {
		return true;
	}

	public function remove($path, array $options = array()) {
		return true;
	}

}

?>