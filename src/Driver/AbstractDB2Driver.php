<?php

namespace DoctrineDbalIbmiLinux\Driver;

use Doctrine\DBAL\Driver;
use DoctrineDbalIbmiLinux\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmiLinux\Schema\DB2IBMiSchemaManager;
use DoctrineDbalIbmiLinux\Schema\DB2LUWSchemaManager;

abstract class AbstractDB2Driver implements Driver
{
    const SYSTEM_IBMI = 'AIX';

    /**
     * {@inheritdoc}
     */
    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        $params = $conn->getParams();

        return $params['dbname'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new DB2IBMiPlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return new DB2LUWSchemaManager($conn);
    }
}
