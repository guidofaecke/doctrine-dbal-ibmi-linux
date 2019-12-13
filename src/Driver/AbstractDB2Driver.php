<?php

namespace DoctrineDbalIbmiLinux\Driver;

use Doctrine\DBAL\Driver;
use DoctrineDbalIbmiLinux\Platform\DB2IBMiLinuxPlatform;
use DoctrineDbalIbmiLinux\Schema\DB2LUWIBMiLinuxSchemaManager;

abstract class AbstractDB2Driver implements Driver
{
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
        return new DB2IBMiLinuxPlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return new DB2LUWIBMiLinuxSchemaManager($conn);
    }
}
