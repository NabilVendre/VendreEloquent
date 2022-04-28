<?php declare(strict_types=1);

namespace VendreEcommerce\EloquentMysqli\Testing;

use Illuminate\Database\Eloquent\Model;

/**
 * Test model class to be used stand-alone in a test environment, so you don't need to configure an eloquent model to run the tests
 */
final class PHPUnitTestModel extends Model
{
    protected $table = 'phpunit_test_table_xxxakpsdpoqwopdqj';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'type',
    ];

    public $timestamps = false;
}
