<?php

namespace DoctrineDbalIbmiLinux\Driver;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\IBMDB2\DB2Exception;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\ParameterType;
use stdClass;
use function odbc_commit;
use function odbc_error;
use function odbc_errormsg;
use function odbc_exec;
use function odbc_num_rows;
use function odbc_pconnect;
use function odbc_prepare;
use function func_get_args;

class DB2IBMiConnection implements Connection, ServerInfoAwareConnection
{
    /** @var resource */
    private $conn = null;

    /**
     * @param mixed[] $params
     * @param string  $username
     * @param string  $password
     * @param mixed[] $driverOptions
     *
     * @throws DB2Exception
     */
    public function __construct(array $params, $username, $password)
    {
        $isPersistent = (isset($params['persistent']) && $params['persistent'] === true);

        if ($isPersistent) {
//            $conn = db2_pconnect($params['dbname'], $username, $password, $driverOptions);
            $conn = odbc_pconnect($params['dbname'], $username, $password);
        } else {
//            $conn = db2_connect($params['dbname'], $username, $password, $driverOptions);
            $conn = odbc_connect($params['dbname'], $username, $password);
        }

        if ($conn === false) {
            throw new DB2Exception(odbc_errormsg());
        }

        $this->conn = $conn;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerVersion()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresQueryForServerVersion()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($sql)
    {
        $stmt = @odbc_prepare($this->conn, $sql);
        if (! $stmt) {
            throw new DB2Exception(odbc_errormsg());
        }

        return new DB2IBMiLinuxStatement($stmt);
    }

    /**
     * {@inheritdoc}
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
    public function quote($input, $type = ParameterType::STRING)
    {
//        $input = db2_escape_string($input);

        if ($type === ParameterType::INTEGER) {
            return $input;
        }

        return "'" . $input . "'";
    }

    /**
     * {@inheritdoc}
     */
    public function exec($statement)
    {
        $stmt = @odbc_exec($this->conn, $statement);

        if ($stmt === false) {
            throw new DB2Exception(odbc_errormsg());
        }

        return odbc_num_rows($stmt);
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null)
    {
        return ''; //db2_last_insert_id($this->conn);
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
        odbc_autocommit($this->conn, false);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if (! odbc_commit($this->conn)) {
            throw new DB2Exception(odbc_errormsg($this->conn));
        }
        odbc_autocommit($this->conn, true);
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
        if (! odbc_rollback($this->conn)) {
            throw new DB2Exception(odbc_errormsg($this->conn));
        }
        odbc_autocommit($this->conn, true);
    }

    /**
     * {@inheritdoc}
     */
    public function errorCode()
    {
        return odbc_error($this->conn);
    }

    /**
     * {@inheritdoc}
     */
    public function errorInfo()
    {
        return [
            0 => odbc_errormsg($this->conn),
            1 => $this->errorCode(),
        ];
    }
}
