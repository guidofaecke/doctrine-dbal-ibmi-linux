<?php

namespace DoctrineDbalIbmiLinux\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use DoctrineDbalIbmiLinux\Platform\DB2IBMiLinuxPlatform;
use DoctrineDbalIbmiLinux\Schema\DB2LUWIBMiLinuxSchemaManager;

abstract class AbstractDB2Driver implements Driver
{
    /**
     * {@inheritdoc}
     */
    public function getDatabase(Connection $conn)
    {
        $params = $conn->getParams();

        return $params['dbname'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new DB2IBMiLinuxPlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        return new DB2LUWIBMiLinuxSchemaManager($conn);
    }
}
