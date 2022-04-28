<?php declare(strict_types=1);

namespace VendreEcommerce\EloquentMysqli\Testing\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use VendreEcommerce\EloquentMysqli\Testing\PHPUnitTestModel AS TestModel;

/**
 * A support trait that you can use on your php-unit tests.
 * This will automatically create a table that will be removed automatically once mysqli-connection is closed.
 */
trait PHPUnitDatabaseAndModelSupport
{
    private $testModelParameters = [
        'name' => 'Test model',
        'type' => 1,
    ];

    private $testTableName;

    public function setUp(): void
    {
        $this->createTestTable((new TestModel)->getTable(), [
            'id',
            'name' => 'string',
            'type' => 'integer',
        ]);
    }

    private function createNewTestModel()
    {
        $model = new TestModel();

        foreach($this->testModelParameters as $column => $value) {
            $model->$column = $value;
        }

        $model->save();

        return $model;
    }

    public function createTestTable(string $tableName, array $tableConfig)
    {
        Schema::create($tableName, function(Blueprint $table) use ($tableConfig) {
            foreach($tableConfig as $columnName => $columnType) {
                if (is_integer($columnName) && is_string($columnType)) {
                    $table->$columnType();
                } else {
                    $table->$columnType($columnName);
                }

                $table->temporary();
            }
        });

        $this->testTableName = $tableName;
    }
}
