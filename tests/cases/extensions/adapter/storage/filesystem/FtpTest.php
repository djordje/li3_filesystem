<?php

namespace li3_filesystem\tests\cases\extensions\adapter\storage\filesystem;

use li3_filesystem\storage\Locations;

class FtpTest extends \lithium\test\Unit {

	protected $_timestamp;
	protected $_tmp_dir;

	/**
	 * @var \li3_filesystem\extensions\adapter\storage\filesystem\Ftp
	 */
	protected $_adapter;

	protected function _init() {
		parent::_init();

		$this->_timestamp = time();

		$this->_tmp_dir = 'li3_fs_' . $this->_timestamp . '_ftp_test';

		$this->_adapter = Locations::get('li3-fs-ftp-test');
	}

	public function skip() {
		$this->skipIf(
			!is_object($this->_adapter),
			'Adapter not initialized or you dont have location named <code>li3-fs-ftp-test</code>'
		);
		$this->skipIf(
			$this->_adapter->_config['adapter'] !== 'Ftp',
			'Adapter should be `Ftp`.'
		);
	}

	public function testPrependLocation() {
		$params = array('path', '/test');

		$this->assertEqual('test/path', $this->_adapter->invokeMethod('_prependLocation', $params));

		$params[1] = '/test///';
		$this->assertEqual('test/path', $this->_adapter->invokeMethod('_prependLocation', $params));

		$params[1] = 'test';
		$this->assertEqual('test/path', $this->_adapter->invokeMethod('_prependLocation', $params));
	}

	public function testMkdir() {
		$this->assertTrue($this->_adapter->mkdir($this->_tmp_dir, array('recursive' => false)));

		$this->assertTrue($this->_adapter->mkdir($this->_tmp_dir . '/Test_1/first'));

		$this->expectException();
		$this->assertFalse($this->_adapter->mkdir($this->_tmp_dir . '/Test_2/first', array('recursive' => false)));

		$this->assertTrue($this->_adapter->mkdir($this->_tmp_dir . '/Test_2'));

		$this->assertFalse($this->_adapter->mkdir($this->_tmp_dir . '/Test_1'));
	}

	public function testUplod() {
		$tmpFile = tempnam(sys_get_temp_dir(), 'li3');
		file_put_contents($tmpFile, 'Hello world!');

		$file = array(
			'name' => 'test.txt',
			'type' => 'text/plain',
			'size' => filesize($tmpFile),
			'tmp_name' => $tmpFile,
			'error' => UPLOAD_ERR_NO_FILE
		);
		$this->assertFalse($this->_adapter->upload($file, $this->_tmp_dir));

		$file['error'] = UPLOAD_ERR_OK;
		$this->assertTrue($this->_adapter->upload($file, $this->_tmp_dir));
	}

	public function testCopy() {
		$this->assertTrue($this->_adapter->copy(
			$this->_tmp_dir . '/test.txt', $this->_tmp_dir . '/Test_1/test.txt'
		));
		$this->assertTrue($this->_adapter->copy(
			$this->_tmp_dir . '/Test_1', $this->_tmp_dir . '/Test_3'
		));

		$this->assertFalse($this->_adapter->copy(
			$this->_tmp_dir . '/Tets_99', $this->_tmp_dir . '/Tets_98'
		));
	}

	public function testMove() {
		$this->assertTrue($this->_adapter->move(
			$this->_tmp_dir . '/Test_2', $this->_tmp_dir . '/Test_4'
		));
	}

	public function testLs() {
		$url = 'http://example.com/tmp/' . $this->_tmp_dir . '/';
		$expected = array(
			array(
				'name' => 'Test_1',
				'dir' => true,
				'url' => $url . 'Test_1',
				'path' => '/' . $this->_tmp_dir,
				'size' => null,
				'mode' => null
			),
			array(
				'name' => 'Test_3',
				'dir' => true,
				'url' => $url . 'Test_3',
				'path' => '/' . $this->_tmp_dir,
				'size' => null,
				'mode' => null
			),
			array(
				'name' => 'Test_4',
				'dir' => true,
				'url' => $url . 'Test_4',
				'path' => '/' . $this->_tmp_dir,
				'size' => null,
				'mode' => null
			),
			array(
				'name' => 'test.txt',
				'dir' => false,
				'url' => $url . 'test.txt',
				'path' => '/' . $this->_tmp_dir,
				'size' => 12,
				'mode' => null
			)
		);
		$result = $this->_adapter->ls($this->_tmp_dir)->to('array');
		$this->assertEqual($expected, $result);

		$this->assertEqual(array(), $this->_adapter->ls($this->_tmp_dir . '/Test_4')->to('array'));

		$this->assertFalse($this->_adapter->ls($this->_tmp_dir . '/Test_99'));
	}

	public function testFileExists() {
		$this->assertFalse($this->_adapter->invokeMEthod('_file_exists'));

		$this->assertFalse(
			$this->_adapter->invokeMEthod('_file_exists', array($this->_tmp_dir . 'not-exists'))
		);

		$this->assertTrue(
			$this->_adapter->invokeMEthod('_file_exists', array($this->_tmp_dir . '/Test_1'))
		);

		$this->assertTrue(
			$this->_adapter->invokeMEthod('_file_exists', array($this->_tmp_dir . '/test.txt'))
		);
	}

	public function testRemove() {
		$this->assertTrue($this->_adapter->remove($this->_tmp_dir . '/test.txt'));

		$this->expectException();
		$this->assertFalse($this->_adapter->remove(
			$this->_tmp_dir . '/Test_1', array('recursive' =>false)
		));

		$this->assertTrue($this->_adapter->remove($this->_tmp_dir));
	}

}

?>