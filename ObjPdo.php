<?php


namespace xooooooox\db;


use \PDO;
use \PDOException;


/**
 * Class ObjPdo
 * @package xooooooox\db
 */
class ObjPdo {

    /**
     * @var PDO
     */
    public PDO $pdo;

    /**
     * Dbc constructor.
     * @param string $dsn
     * @param string $user
     * @param string $pass
     * @param array $options
     */
    public function __construct(string $dsn, string $user, string $pass, array $options = []) {
        if ($options === []){
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
        }
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    /**
     * query a sql, return query result(two dimensional array or empty array)
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function query(string $sql, array $params = []) : array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * query a sql, return query result(one dimensional array or empty array)
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function first(string $sql, array $params = []) : array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetchAll();
        if (isset($result[0])){
            return $result[0];
        }
        return [];
    }

    /**
     * execute a sql, return the number of rows affected
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function execute(string $sql, array $params = []) : int {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * execute a transaction, return the execution result(true or false)
     * @param callable $transaction
     * @param callable $exception
     * @param int $attempts
     * @return bool
     */
    public function transaction(callable $transaction, callable $exception, int $attempts = 1) : bool {
        if ($attempts > 3){
            $attempts = 3;
        }
        if ($attempts <= 0) {
            return false;
        }
        try {
            $attempts--;
            $this->pdo->beginTransaction();
            $transaction($this);
            $this->pdo->commit();
        } catch(PDOException $e) {
            $this->pdo->rollBack();
            $exception($e);
            if ($attempts > 0) {
                return $this->transaction($transaction ,$exception, $attempts);
            }
            return false;
        }
        return true;
    }

}
