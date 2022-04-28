<?php declare(strict_types=1);

namespace VendreEcommerce\EloquentMysqli\Extensions;

use Exception;
use mysqli;
use mysqli_sql_exception;

/**
 * The Eloquent ORM is built to support PDO only, so using MySQLi with Eloquent causes some miss-match in function calls.
 * This class will help resolve those conflicts.
 */
final class PdoToMySQLiExtension extends mysqli
{
    /**
     * PDO lastInsertId() -> MySQLi insert_id
     * 
     * @return int
     */
    public function lastInsertId(): int
    {
        return $this->insert_id;
    }

    /**
     * PDO beginTransaction() -> MySQLi begin_transaction()
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->begin_transaction();
    }

    /**
     * PDO commit() -> MySQLi commit()
     * PDO::commit has no function arguments, while mysqli::commit does, so we need to default the arguments
     * 
     * @return bool
     */
    public function commit($flags = 0, $name = null): bool
    {
        if (!parent::commit(...func_get_args())) {
            throw new Exception('MySQLi commit failed');
        }

        return true;
    }

    /**
     * PDO exec() -> mysqli exec
     * mysqli::exec doesn't exist, so we create the same behaviour as PDO::exec
     * 
     * @return int|false
     */
    public function exec($statement)
    {
        try {
            $result = $this->query($statement);
        } catch(mysqli_sql_exception $e) {
            throw new Exception($e->getMessage());
        }

        if ($result === true) {
            return true;
        } elseif ($result instanceof mysqli_result) {
            return $result->num_rows();
        }

        return false;
    }
}
