<?php declare(strict_types=1);

use VendreEcommerce\EloquentMysqli\Testing\Traits\PHPUnitDatabaseAndModelSupport;
use VendreEcommerce\EloquentMysqli\Testing\PHPUnitTestModel as TestModel;

final class PdoToMySQLiExtensionTest extends \PHPUnit\Framework\TestCase
{
    use PHPUnitDatabaseAndModelSupport;

    public function testCanPerformExec()
    {
        $connection = (new TestModel)->getConnection();
        $connection->beginTransaction();
        
        $this->assertTrue($connection->getPdo()->exec('SAVEPOINT transactionTest1234'));
        
        // Must rollback/commit the transaction to reset the transaction level
        $connection->rollback();
    }
}
