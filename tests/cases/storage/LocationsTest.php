<?php

/**
 * @copyright Copyright 2013, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_filesystem\tests\cases\storage;

use li3_filesystem\storage\Locations;
use lithium\core\Libraries;

class LocationsTest extends \lithium\test\Unit {
	
	public function testAdding() {
		$location = Locations::add('test', array(
			'adapter' => 'Filesystem',
			'location' => Libraries::path(false, array('dirs' => true)) . '/resources/tmp'
		));
		$this->assertTrue(is_array($location));
	}

	public function testGetting() {
		$this->assertTrue(is_array(Locations::get()));
		$this->assertNull(Locations::get('test2'));
		$this->assertTrue(is_array(Locations::get('test', array('config' => true))));
		$this->assertNull(Locations::get('test', array('autoCreate' => false)));
		$this->assertNotEmpty(Locations::get('test'));
	}
	
}

?>