<?php

namespace li3_filesystem\tests\cases\storage;

use lithium\test\Unit;
use li3_filesystem\storage\Locations;
use li3_filesystem\storage\Filesystem;

class FilesystemTest extends Unit {

	protected $_timestamp;
	protected $_tmp_dir;

	protected function _init() {
		parent::_init();

		$this->_timestamp = time();

		$this->_tmp_dir = sys_get_temp_dir() . '/li3_fs_' . $this->_timestamp . '_test-2';

		Locations::add('test-2', array(
			'adapter' => 'Filesystem',
			'url' => 'http://example.com/tmp/',
			'location' => $this->_tmp_dir
		));

		$this->_adapter = Locations::get('test');
	}

	public function skip() {
		$this->skipIf(!mkdir($this->_tmp_dir), 'Couldn\'t create directory for further testing!');
		$this->skipIf(
			!file_put_contents("{$this->_tmp_dir}/test.txt", "This is test data\n"),
			'Couldn\'t write test file!'
		);
	}

	public function testGetAdapter() {
		$this->assertFalse(Filesystem::invokeMethod('_getAdapter', array('test-2-false')));
		$this->assertTrue(
			is_object(Filesystem::invokeMethod('_getAdapter', array('test-2')))
		);
	}

	public function testFalseAdapter(){
		$this->assertFalse(Filesystem::ls('test-2-false', 'Test_1'));
		$this->assertFalse(Filesystem::mkdir('test-2-false', 'Test_1'));
		$this->assertFalse(Filesystem::upload('test-2-false', array(), ''));
		$this->assertFalse(Filesystem::copy('test-2-false', 'Test_1', 'Test_2'));
		$this->assertFalse(Filesystem::move('test-2-false', 'Test_1', 'Test_2'));
		$this->assertFalse(Filesystem::remove('test-2-false', 'Test_1'));
	}

	public function testMkdir() {
		$this->assertTrue(Filesystem::mkdir('test-2', 'Test_1/first'));

		$this->expectException();
		$this->assertFalse(Filesystem::mkdir('test-2', 'Test_2/first', array('recursive' => false)));

		$this->assertTrue(Filesystem::mkdir('test-2', 'Test_2', array('recursive' => false)));

		$this->assertFalse(Filesystem::mkdir('test-2', 'Test_1'));
	}

	public function testCopy() {
		$this->assertTrue(Filesystem::copy('test-2', 'test.txt', 'Test_1/test.txt'));
		$this->assertTrue(Filesystem::copy('test-2', 'Test_1', 'Test_3'));

		$this->assertFalse(Filesystem::copy('test-2', 'Tets_99', 'Tets_98'));
	}

	public function testMove() {
		$this->assertTrue(Filesystem::move('test-2', 'Test_2', 'Test_4'));
	}

	public function testRemove() {
		$this->assertTrue(Filesystem::remove('test-2', 'test.txt'));

		$this->expectException();
		$this->assertFalse(Filesystem::remove('test-2', 'Test_1', array('recursive' =>false)));

		$this->assertTrue(Filesystem::remove('test-2', 'Test_1'));
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
		$this->assertFalse(Filesystem::upload('test-2', $file));

		$file['error'] = UPLOAD_ERR_OK;
		$this->assertTrue(Filesystem::upload('test-2', $file));
	}

	public function testUploadValidation() {
		$file = array(
			'name' => '',
			'type' => 'text/plain',
			'size' => 510,
			'tmp_name' => '/tmp/FooBar.tmp',
			'error' => UPLOAD_ERR_OK
		);
		$validates = array(
			'name' => array('notEmpty', 'message' => 'File must have name!'),
			'size' => array('inRange', 'upper' => 192, 'message' => 'File size incorrect!')
		);

		$this->assertFalse(Filesystem::upload('test-2', $file, null, compact('validates')));
		$this->assertEqual('File must have name!', Filesystem::$uploadErrors['name'][0]);
		$this->assertEqual('File size incorrect!', Filesystem::$uploadErrors['size'][0]);
	}

	public function testLs() {
		$this->assertFalse(Filesystem::ls('test-2', 'Test_1'));

		$this->assertTrue(is_array(Filesystem::ls('test-2', 'Test_4')->to('array')));

		$this->assertFalse(Filesystem::ls('test-2', 'Test_3/test.txt'));

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
		foreach (Filesystem::ls('test-2', 'Test_3') as $file) {
			$result[$file->name] = $file->to('array');
			$expected[$file->name]['size'] = $file->size;
			$expected[$file->name]['mode'] = $file->mode;
		}
		$this->assertEqual($expected, $result);
	}

}