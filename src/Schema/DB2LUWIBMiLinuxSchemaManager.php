<?php

namespace DoctrineDbalIbmiLinux\Schema;

class DB2LUWIBMiLinuxSchemaManager extends DB2IBMiLinuxSchemaManager
{
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $columnDefinition = parent::_getPortableTableColumnDefinition($tableColumn);

        if ($columnDefinition->getNotnull() === true && empty($columnDefinition->getDefault())) {
            $columnDefinition->setDefault(null);
        }

        return $columnDefinition;
    }
}
