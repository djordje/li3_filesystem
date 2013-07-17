<?php

namespace li3_filesystem\tests\cases\extensions\adapter\storage\filesystem;

use li3_filesystem\storage\Locations;
use lithium\core\Libraries;

class FilesystemTest extends \lithium\test\Unit {

	protected $_timestamp;
	protected $_tmp_dir;

	/**
	 * @var \li3_filesystem\extensions\adapter\storage\filesystem\Filesystem
	 */
	protected $_adapter;

	protected function _init() {
		parent::_init();

		$this->_timestamp = time();

		$this->_tmp_dir = sys_get_temp_dir() . '/' . $this->_timestamp . '_test';

		Locations::add('test', array(
			'adapter' => 'Filesystem',
			'url' => 'http://example.com/tmp/',
			'location' => $this->_tmp_dir
		));

		$this->_adapter = Locations::get('test');
	}

	public function skip() {
		$this->skipIf(!is_object($this->_adapter), 'Adapter not initialized');
		$this->skipIf(
			$this->_adapter->_config['adapter'] !== 'Filesystem',
			'Adapter should be `FileSystem`.'
		);
		$this->skipIf(
			$this->_adapter->_config['location'] !== $this->_tmp_dir,
			"Location should be `li3_filesystem/tests/location/{$this->_timestamp}_test`."
		);
		$this->skipIf(
			!is_writable(
				Libraries::path('li3_filesystem\\', array('dirs' => true)) . '/tests/location'),
				'Test location not writable!<br />' .
				'Check does `li3_filemanager/resources/tmp`' .
				'directorty exists if not create it.<br />' .
				'On *nix OS-es you should <code>$ chmod -R 0777' .
				'libraries/li3_filemanager/resources</code>'
			);
		$this->skipIf(!mkdir($this->_tmp_dir), 'Couldn\'t create directory for further testing!');
		$this->skipIf(
			!file_put_contents("{$this->_tmp_dir}/test.txt", "This is test data\n"),
			'Couldn\'t write test file!'
		);
	}

	public function testMkdir() {
		$this->assertTrue($this->_adapter->mkdir('Test_1/first'));

		$this->expectException();
		$this->assertFalse($this->_adapter->mkdir('Test_2/first', array('recursive' => false)));

		$this->assertTrue($this->_adapter->mkdir('Test_2', array('recursive' => false)));

		$this->assertFalse($this->_adapter->mkdir('Test_1'));
	}

	public function testCopy() {
		$this->assertTrue($this->_adapter->copy('test.txt', 'Test_1/test.txt'));
		$this->assertTrue($this->_adapter->copy('Test_1', 'Test_3'));

		$this->assertFalse($this->_adapter->copy('Tets_99', 'Tets_98'));
	}

	public function testMove() {
		$this->assertTrue($this->_adapter->move('Test_2', 'Test_4'));
	}

	public function testRemove() {
		$this->assertTrue($this->_adapter->remove('test.txt'));

		$this->expectException();
		$this->assertFalse($this->_adapter->remove('Test_1', array('recursive' =>false)));

		$this->assertTrue($this->_adapter->remove('Test_1'));
	}

	public function testUpload() {
		$tmpFile = tempnam(sys_get_temp_dir(), 'li3');
		file_put_contents($tmpFile, 'Hello world!');

		$file = array(
			'name' => 'test.txt',
			'type' => 'text/plain',
			'size' => filesize($tmpFile),
			'tmp_name' => $tmpFile,
			'error' => UPLOAD_ERR_NO_FILE
		);
		$this->assertFalse($this->_adapter->upload($file));

		$file['error'] = UPLOAD_ERR_OK;
		$this->assertTrue($this->_adapter->upload($file));
	}

	public function testLs() {
		$this->assertFalse($this->_adapter->ls('Test_1'));

		$this->assertTrue(is_array($this->_adapter->ls('Test_4')->to('array')));

		$this->assertFalse($this->_adapter->ls('Test_3/test.txt'));

		$expected = array(
			'first' => array(
				'name' => 'first',
				'dir' => true,
				'url' => 'http://example.com/tmp/Test_3/first',
				'path' => '/Test_3/'
			),
			'test.txt' => array(
				'name' => 'test.txt',
				'dir' => false,
				'url' => 'http://example.com/tmp/Test_3/test.txt',
				'path' => '/Test_3/'
			)
		);
		$result = array();
		foreach ($this->_adapter->ls('Test_3') as $file) {
			$result[$file->name] = $file->to('array');
			$expected[$file->name]['size'] = $file->size;
			$expected[$file->name]['mode'] = $file->mode;
		}
		$this->assertEqual($expected, $result);
	}

}

?>