<?php

namespace DoctrineDbalIbmiLinux\Schema;

class DB2LUWSchemaManager extends DB2SchemaManager
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
