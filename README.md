#### li3_filesystem - File management for the [Lithium PHP framework](https://github.com/UnionOfRAD/lithium)

[![Latest Stable Version](https://poser.pugx.org/djordje/li3_filesystem/v/stable.png)](https://packagist.org/packages/djordje/li3_filesystem)
[![Build Status](https://travis-ci.org/djordje/li3_filesystem.png?branch=master)](https://travis-ci.org/djordje/li3_filesystem)

This library enable easy file operations in the Lithium PHP framework.

This is based on extracted and better organized logic from [li3_filemanager](https://github.com/djordje/li3_filemanager)
withoud controllers and views. So you can use it in your app's or library to perform file operations.
You can also make file browser on top of this library, or you can use `li3_filemanager` that will
be basic file browser implementation on top of this library.
You can extend this library to support your storage system by creating new adapter that should
extend `li3_filesystem\storage\filesystem\Source` and implement all abstract methods defined in `Source`.

#### Installation

**1a.** You can install it trough composer:
```json
{
    "require": {
        "djordje/li3_filesystem": "dev-master"
    }
}
```

**1b.** Or you can clone git repo to any of your libraries dir:
```
cd libraries
git clone git://github.com/djordje/li3_filesystem.git
```

**2.** Add it to your app's `bootstrap/libraries.php` file:
```php
// backend routes with default prefix `backend`
Libraries::add('li3_filesystem');
```

**3.** Define named location
```php
// Example that use Filesystem adapter to access img dir in app's webroot
li3_filesystem\storage\Locations::add('webroot_img', array(
	'adapter' => 'Filesystem',
    'url' => 'http://example.com/img/',
    'location' => LITHIUM_APP_PATH . '/webroot/img'
));
```

#### Included adapters

You define adapter with `'adapter'` key passed to location's option array.

###### Filesystem

This is adapter that enable file operations on local file system. You will proabably use this adapter
most of the time.

**Oprion keys that will be used by this adapter**

* `'location'` - path  to root of location eg. `LITHIUM_APP_PATH . '/webroot/img'`
* `'url'` - _optional_, pass absolute link to root of location eg. `'http://example.com/img/'`
and all files will concatonate path to this url and enable you to get `$entity->url`.

#### Create your own adapters

You can writte your own adapters to support your storage system, or change some logic.

Your adapter must be locateable by `lithium\core\Libraries::locate('adapter.storage.filesystem');`
and inherits from `li3_filesystem\storage\filesystem\Source`.
`Source` abstract class has defined abstarct methods that all adapters must implement `ls`, `mkdir`,
`upload`, `copy`, `move`, `remove` to enable proper functionality with base model
`li3_filesystem\storage\FS` and filesystem entity `li3_filesystem\storage\filesystem\Entity`.