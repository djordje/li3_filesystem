<?php

namespace li3_filesystem\tests\cases\storage\filesystem;

use li3_filesystem\storage\filesystem\Entity;
use li3_filesystem\tests\mocks\extensions\adapter\storage\filesystem\MockFilesystem;

class EntityTest extends \lithium\test\Unit {

	protected $_dir;

	protected $_file;

	public function setUp() {
		$this->_dir = new Entity(array(
			'name' => 'dir',
			'dir' => true,
			'url' => null,
			'path' => '/',
			'size' => null,
			'mode' => '0777',
			'adapter' => new MockFilesystem()
		));
		$this->_file = new Entity(array(
			'name' => 'file.ext',
			'dir' => false,
			'url' => null,
			'path' => '/dir',
			'size' => 55,
			'mode' => '0666',
			'adapter' => new MockFilesystem()
		));
	}

	public function testGetFullPath() {
		$this->assertEqual($this->_dir->getFullPath(), '/dir');
	}

	public function testLs() {
		$this->assertEqual($this->_dir->ls()->count(), 0);
		$this->assertFalse($this->_file->ls());
	}

	public function testMkdir() {
		$this->assertTrue($this->_dir->mkdir('new'));
		$this->assertFalse($this->_file->mkdir('new'));
	}

	public function testCopy() {
		$this->assertTrue($this->_dir->copy('dir_copy'));
	}

	public function testMove() {
		$this->assertTrue($this->_dir->move('moved_dir'));
	}

	public function testRemove() {
		$this->assertTrue($this->_dir->remove());
	}

	public function testSize() {
		$entity = new Entity(array(
			'name' => 'file.ext',
			'dir' => false,
			'url' => null,
			'path' => '/',
			'size' => 1726233255608,
			'mode' => '0777',
			'adapter' => new MockFilesystem()
		));

		$expected = array(1726233255608, 'unit' => 'b');
		$this->assertEqual($entity->size(), $expected);

		$expected = array(1685774663.68, 'unit' => 'kb');
		$this->assertEqual($entity->size('kb'), $expected);

		$expected = array(1646264.32, 'unit' => 'mb');
		$this->assertEqual($entity->size('mb'), $expected);

		$expected = array(1607.68, 'unit' => 'gb');
		$this->assertEqual($entity->size('gb'), $expected);

		$expected = array(1.57, 'unit' => 'tb');
		$this->assertEqual($entity->size('tb'), $expected);
	}

	public function testTo() {
		$expected = array(
			'name' => 'dir',
			'dir' => true,
			'url' => null,
			'path' => '/',
			'size' => null,
			'mode' => '0777',
		);
		$this->assertEqual($expected, $this->_dir->to('array'));
	}

	public function testGet() {
		$this->assertEqual('dir', $this->_dir->name);
		$this->assertTrue($this->_dir->dir);
		$this->assertNull($this->_dir->url);
		$this->assertEqual('/', $this->_dir->path);
		$this->assertNull($this->_dir->size);
		$this->assertEqual('0777', $this->_dir->mode);
	}

}

?>