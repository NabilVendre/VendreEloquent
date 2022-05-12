<?php declare(strict_types=1);

use VendreEcommerce\EloquentMysqli\Testing\Traits\PHPUnitDatabaseAndModelSupport;
use VendreEcommerce\EloquentMysqli\Testing\PHPUnitTestModel as TestModel;

final class EloquentModelTest extends \PHPUnit\Framework\TestCase
{
    use PHPUnitDatabaseAndModelSupport;

    public function testUpdatingOneModelReturns1()
    {
        $model = $this->createNewTestModel();

        $name = $model->name . ' + new name';

        $result = TestModel::where('id', $model->id)->update(['name' => $name]);

        $this->assertEquals(1, $result);
    }

    public function testUpdatingTwoModelsReturns2()
    {
        $model = $this->createNewTestModel();
        $this->createNewTestModel();

        $name = $model->name . ' + new name';

        $result = TestModel::where('name', $model->name)->update(['name' => $name]);
        $this->assertEquals(2, $result);
    }

    public function testUpdatingNoModelReturns0()
    {
        $result = TestModel::where('name', 'unknownmodelname')->update(['name' => 'newname']);
        $this->assertEquals(0, $result);
    }
}
