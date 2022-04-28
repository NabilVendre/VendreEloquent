# eloquent-mysqli
An extension to handle a MySQLi connection with Eloquent

This project is based off https://github.com/shakahl/laravel-eloquent-mysqli.

Currently supports:
```
PHP >=7.3.
Illuminate\Database(Eloquent) 8.83. Higher versions of Eloquent requires higher PHP version.
```
Tested with:
```
PHP 7.3
PHP 8.0
```

# Installation
Rename .env.example and configure your database settings.

You can create a test.php file to try it out.
You can also use this code for inspiration on how to install this into your own system.
Remember that `Dotenv` is not required for this library, that's why its under composers 'require-dev'.
```php
<?php

// Configure to your own composer autoload file
require_once __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;
use VendreEcommerce\EloquentMysqli\Extensions\PdoToMySQLiExtension;
use VendreEcommerce\EloquentMysqli\Eloquent\EloquentBooter;

// Load up the .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create a new mysqli connection.
$mysqliConnection = new PdoToMySQLiExtension();

// Set up the Eloquent config
$eloquentConfig = [
    'connectionName'    => 'default',
    'host'              => env('DB_HOST'),
    'database'          => env('DB_DATABASE'),
    'username'          => env('DB_USERNAME'),
    'password'          => env('DB_PASSWORD'),
    'port'              => env('DB_PORT'),
];

// Create a mysqli connection
$mysqliConnection->real_connect(
    $eloquentConfig['host'],
    $eloquentConfig['username'],
    $eloquentConfig['password'],
    $eloquentConfig['database'],
    $eloquentConfig['port'],
    null,
    0
);

// Boot up Eloquent with the mysqli connection and eloquent config.
$eloquentBooter = new EloquentBooter();
$eloquentBooter->bootEloquent($mysqliConnection, $eloquentConfig);

```

Run `composer install`

# Testing
We have included some tests in /tests/phpunit.

You can run these to see if everything works fine after you installed it.

You need to add a `--bootstrap` flag to your command line. This bootstrap file will be used to boot up Eloquent.

Example of how you can use it, with the `test.php` file as a bootstrap.
```
./vendor/bin/phpunit tests/phpunit --bootstrap test.php --testdox
```
