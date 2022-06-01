<?php declare(strict_types=1);

namespace VendreEcommerce\EloquentMysqli\Eloquent;

use Illuminate\Database\Connection;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use VendreEcommerce\EloquentMysqli\Connections\MySQLiConnection;
use VendreEcommerce\EloquentMysqli\Connections\MySQLiConnector;
use mysqli;

final class EloquentBooter extends ServiceProvider
{
    private static $container;
    private static $capsule;

    public function __construct()
    {
        if (!static::$container) {
            static::$container = new Container();
        }
        
        parent::__construct(static::$container);
    }

    public function bootEloquent(mysqli $mysqliConnection, array $config): void
    {
        Connection::resolverFor('mysqli', function ($connection, $database, $prefix, $config) {
            return new MySQLiConnection(
                $connection(), $database, $prefix, $config
            );
        });

        $this->app->bind('db.connector.mysqli', function() {
            return new MySQLiConnector();
        });

        // Need to boot up the Schema as well in order to use the SchemaBuilder
        Schema::setFacadeApplication($this->app);

        if (!static::$capsule) {
            static::$capsule = new Capsule($this->app);
        }
        
        static::$capsule->addConnection([
            'driver'        => 'mysqli',
            'connection'    => $mysqliConnection,
            'database'      => $config['database'],
            'host'          => $config['host'],
        ], $config['connectionName']);
        static::$capsule->setAsGlobal();
        static::$capsule->bootEloquent();

        $this->app['db'] = static::$capsule;
    }

    public function destroyContainer()
    {
        static::$container = null;
    }
}
