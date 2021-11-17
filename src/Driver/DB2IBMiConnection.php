<?php

namespace DoctrineDbalIbmiLinux\Driver;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\IBMDB2\DB2Exception;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\ParameterType;
use PDO;
use PDOException;

use function func_get_args;

class DB2IBMiConnection implements Connection, ServerInfoAwareConnection
{
    /** @var ?PDO */
    private $conn = null;

    /**
     * @param mixed[] $params
     * @param string  $username
     * @param string  $password
     */
    public function __construct(array $params, string $username, string $password)
    {
//        $isPersistent = (isset($params['persistent']) && $params['persistent'] === true);

//        if ($isPersistent) {
//            $conn = odbc_pconnect($params['connectionString'], $username, $password);
//        } else {
            $conn = new PDO($params['connectionString'], $username, $password);
//        }

//        if ($conn === false) {
//            throw new DBALException(odbc_errormsg());
//        }

        $this->conn = $conn;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerVersion(): string
    {
        // return '';
        $this->conn->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresQueryForServerVersion(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($sql)
    {
        $stmt = $this->conn->prepare($sql);
//        $stmt = @odbc_prepare($this->conn, $sql);
        if (! $stmt) {
//            throw new \Doctrine\DBAL\DBALException(odbc_errormsg());
            $errorInfo = $this->conn->errorInfo();
            throw new PDOException(sprintf('%s-%d-%s', $errorInfo[0], $errorInfo[1], $errorInfo[2]));
        }

        return new DB2IBMiLinuxStatement($stmt);
    }

    /**
     * {@inheritdoc}
     *
     * @throws DB2Exception
     */
    public function query()
    {
        $args = func_get_args();
        $sql  = $args[0];
        $stmt = $this->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    /**
     * {@inheritdoc}
     */
    public function quote($value, $type = ParameterType::STRING): string
    {
//        $input = db2_escape_string($input);

        if ($type === ParameterType::INTEGER) {
            return $value;
        }

        return "'" . $value . "'";
    }

    /**
     * {@inheritdoc}
     */
    public function exec($statement)
    {
//        $stmt = @odbc_exec($this->conn, $statement);
        $stmt = $this->conn->exec($statement);

        if ($stmt === false) {
            $errorInfo = $this->conn->errorInfo();
            throw new PDOException(sprintf('%s-%d-%s', $errorInfo[0], $errorInfo[1], $errorInfo[2]));
        }

        return $stmt;
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null)
    {
        return $this->conn->lastInsertId();
//        return ''; //db2_last_insert_id($this->conn);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        $this->conn->beginTransaction();
//        odbc_autocommit($this->conn, false);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
//        $commited = ;
        if (! $this->conn->commit()) {
            $errorInfo = $this->conn->errorInfo();
            throw new PDOException('%s-%d-%s', $errorInfo[0], $errorInfo[1], $errorInfo[2]);
        }
//        odbc_autocommit($this->conn, true);
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
//        $rollback = ;
        if (! $this->conn->rollBack()) {
            $errorInfo = $this->conn->errorInfo();
            throw new PDOException('%s-%d-%s', $errorInfo[0], $errorInfo[1], $errorInfo[2]);
        }
//        odbc_autocommit($this->conn, true);
    }

    /**
     * {@inheritdoc}
     */
    public function errorCode()
    {
        return $this->conn->errorCode();
//        return odbc_error($this->conn);
    }

    /**
     * {@inheritdoc}
     */
    public function errorInfo()
    {
        return $this->conn->errorInfo();
//        return [
//            0 => odbc_errormsg($this->conn),
//            1 => $this->errorCode(),
//        ];
    }
}
