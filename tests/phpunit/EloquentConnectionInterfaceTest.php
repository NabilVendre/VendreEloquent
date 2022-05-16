<?php declare(strict_types=1);

use VendreEcommerce\EloquentMysqli\Testing\Traits\PHPUnitDatabaseAndModelSupport;
use VendreEcommerce\EloquentMysqli\Testing\PHPUnitTestModel as TestModel;
use Illuminate\Database\QueryException;

/**
 * We use this test to test all the functions in Illuminate\Database\ConnectionInterface.
 * We are using the PHPUnitDatabaseAndModelSupport-trait to automatically create a test table that we can run the test towards.
 */
final class EloquentConnectionInterfaceTest extends \PHPUnit\Framework\TestCase
{
    use PHPUnitDatabaseAndModelSupport;

    public function testCanPerformTransaction()
    {
        $model = $this->createNewTestModel();

        $model->getConnection()->beginTransaction();

        $model->name = 'asdasdasd';
        $model->save();

        $model->getConnection()->commit();

        $searchModel = TestModel::where(['name' => 'asdasdasd'])->first();

        $this->assertTrue($searchModel->id === $model->id);
    }

    public function testCanRollbackTransaction()
    {
        // Create a model outside of the transaction
        $model = $this->createNewTestModel();

        $model->getConnection()->beginTransaction();

        try {
            // Create a model inside of the transaction
            $model2 = $this->createNewTestModel();
            // Forcing query error
            $model->assd = 'asd';
            $model->save();
            $model->getConnection()->commit();
        } catch(\Illuminate\Database\QueryException $e) {
            // Rolling back, should make the model2 disappear as it was created inside the transaction
            $model->getConnection()->rollback();
        }

        // Look up on both models. Model 1 should be found, model2 should not be found
        $searchModel = TestModel::find($model->id);
        $searchModel2 = TestModel::find($model2->id);

        $this->assertTrue($searchModel->id === $model->id && $searchModel2 === null);
    }

    public function testCanBeginTableQuery()
    {
        $model = $this->createNewTestModel();

        $tableQuery = $model->getConnection()->table($this->testTableName);

        $this->assertTrue($tableQuery instanceof \Illuminate\Database\Query\Builder);
    }

    public function testCanRunFullTableQuery()
    {
        $model = $this->createNewTestModel();

        $tableQuery = $model->getConnection()->table($this->testTableName)->select(['type'])->get()->first();
        
        $expectedResult = [
            'type' => 1
        ];

        $this->assertTrue($expectedResult === $tableQuery);
    }

    public function testCanRunRawExpression()
    {
        $model = $this->createNewTestModel();

        $rawExpression = $model->getConnection()->raw('testValue')->getValue();

        $this->assertTrue($rawExpression === 'testValue');
    }

    public function testCanSelectOne()
    {
        $model = $this->createNewTestModel();

        $query = "SELECT * FROM " . $this->testTableName . " WHERE id = ?";
        $bindings = [$model->id];

        $expectedResult = [
            'id'    => $model->id,
            'name'  => $model->name,
            'type'  => $model->type,
        ];
        
        $this->assertTrue($model->getConnection()->selectOne($query, $bindings) === $expectedResult);
    }

    public function testShouldRaiseQueryExceptionWhenSelectingFromUnknownTable()
    {
        $unknownTableName = 't' . time() . rand();
        $query = "SELECT * FROM " . $unknownTableName;

        $this->expectException(QueryException::class);
        (new TestModel())->getConnection()->selectOne($query);
    }

    public function testCanSelect()
    {
        $model = $this->createNewTestModel();

        $query = "SELECT * FROM " . $this->testTableName . " WHERE id = ?";
        $bindings = [$model->id];

        $expectedResult = [
            0 => [
                'id'    => $model->id,
                'name'  => $model->name,
                'type'  => $model->type,
            ]
        ];
        $this->assertTrue($model->getConnection()->select($query, $bindings) === $expectedResult);
    }

    public function testSelectWithCursorReturnsGenerator()
    {
        $totalModels = 10;
        $models = [];
        $modelIds = [];

        for($i = 0; $i <= $totalModels; $i++) {
            $models[$i] = $this->createNewTestModel();
            $modelIds[] = $models[$i]->id;
        }

        $query = "SELECT * FROM " . $this->testTableName . " WHERE id IN(" . str_repeat('?,', count($modelIds)-1) . "?)";
        $bindings = $modelIds;

        $result = $models[0]->getConnection()->cursor($query, $bindings);
        $this->assertTrue($result instanceof Generator);
    }

    public function testCanSelectWithCursor()
    {
        $totalModels = 10;
        $models = [];
        $modelIds = [];

        for($i = 0; $i <= $totalModels; $i++) {
            $models[$i] = $this->createNewTestModel();
            $modelIds[] = $models[$i]->id;
        }

        $query = "SELECT * FROM " . $this->testTableName . " WHERE id IN(" . str_repeat('?,', count($modelIds)-1) . "?)";
        $bindings = $modelIds;

        $passed = true;
        foreach ($models[0]->getConnection()->cursor($query, $bindings) as $index => $modelRow) {
            if ($models[$index]->id !== $modelRow->id) {
                $passed = false;
            }
        }

        $this->assertTrue($passed);
    }

    public function testCanPerformInsert()
    {
        $insertName = 'testCanPerformInsert';
        $insertType = 99123;
        $query = "INSERT INTO " . $this->testTableName . " (name, type) VALUES (?, ?)";
        $bindings = [
            $insertName,
            $insertType,
        ];

        (new TestModel)->getConnection()->insert($query, $bindings);

        $model = TestModel::where([
            'name' => $insertName,
            'type' => $insertType,
        ])->first();

        $this->assertTrue($model instanceof TestModel && $model->name === $insertName && $model->type === $insertType);
    }

    public function testCanPerformUpdate()
    {
        $model = $this->createNewTestModel();

        $updateName = 'testCanPerformUpdate';
        $query = "UPDATE " . $this->testTableName . " SET name = ? WHERE id = ?";
        $bindings = [
            $updateName,
            $model->id,
        ];

        (new TestModel)->getConnection()->update($query, $bindings);

        $searchModel = TestModel::find($model->id);

        $this->assertTrue($searchModel instanceof TestModel && $searchModel->name === $updateName);
    }

    public function testCanPerformDelete()
    {
        $model = $this->createNewTestModel();

        $query = "DELETE FROM " . $this->testTableName . " WHERE id = ?";
        $bindings = [
            $model->id,
        ];

        (new TestModel)->getConnection()->delete($query, $bindings);

        $searchModel = TestModel::find($model->id);

        $this->assertTrue($searchModel === null);
    }

    public function testCanPerformStatement()
    {
        $this->createNewTestModel();

        $query = "SELECT * FROM " . $this->testTableName . " LIMIT ?";
        $bindings = [1];

        $this->assertTrue((new TestModel())->getConnection()->statement($query, $bindings));
    }

    public function testCanPerformAffectingStatement()
    {
        $this->createNewTestModel();

        $query = "SELECT * FROM " . $this->testTableName . " LIMIT ?";
        $bindings = [1];

        $this->assertEquals(1, (new TestModel())->getConnection()->affectingStatement($query, $bindings));
    }

    public function testCanPerformUnprepared()
    {
        $this->createNewTestModel();
        
        $query = "SELECT * FROM " . $this->testTableName . " LIMIT 1";

        $this->assertTrue((new TestModel())->getConnection()->unprepared($query));
    }

    public function testCanPerformPrepareBindings()
    {
        /**
         * Prepare bindings will
         * - Convert false to 0
         * - Format a carbon instance to correct format for the column
         * So we will pass in these and check that the preparation was successfull
         */
        $bindings = [
            0 => false,
            1 => new \Carbon\Carbon(),
        ];

        $connection = (new TestModel())->getConnection();

        $expectedResult = [
            0 => 0,
            1 => $bindings[1]->format($connection->getQueryGrammar()->getDateFormat()),
        ];

        $this->assertTrue($connection->prepareBindings($bindings) === $expectedResult);
    }

    public function testTransactionLevelEquals0()
    {
        $connection = (new TestModel)->getConnection();
        
        $this->assertTrue($connection->transactionLevel() === 0);
    }

    public function testPretendIsInPretendingMode()
    {
        $connection = (new TestModel)->getConnection();

        $connection->pretend(function($connection) {
            $this->assertTrue($connection->pretending() === true);
        });
    }

    public function testPretendSelectReturnsEmptyArray()
    {
        $connection = (new TestModel)->getConnection();

        $connection->pretend(function($connection) {
            $this->assertTrue($connection->select($query = "", $bindings = []) === []);
        });
    }

    public function testPretendCursorReturnsEmptyArray()
    {
        $connection = (new TestModel)->getConnection();

        $connection->pretend(function($connection) {
            foreach($connection->cursor($query = "", $bindings = []) as $pretendingReturn) {
                $this->assertTrue($pretendingReturn === []);
            }
        });
    }

    public function testPretendStatementReturnsTrue()
    {
        $connection = (new TestModel)->getConnection();

        $connection->pretend(function($connection) {
            $this->assertTrue($connection->statement($query = "", $bindings = []) === true);
        });
    }

    public function testPretendAffectingStatementReturnsZero()
    {
        $connection = (new TestModel)->getConnection();

        $connection->pretend(function($connection) {
            $this->assertTrue($connection->affectingStatement($query = "", $bindings = []) === 0);
        });
    }

    public function testPretendUnpreparedReturnsTrue()
    {
        $connection = (new TestModel)->getConnection();

        $connection->pretend(function($connection) {
            $this->assertTrue($connection->unprepared($query = "") === true);
        });
    }

    public function testCanGetDatabaseName()
    {
        $connection = (new TestModel)->getConnection();

        $this->assertTrue(strlen($connection->getDatabaseName()) > 1);
    }
}
