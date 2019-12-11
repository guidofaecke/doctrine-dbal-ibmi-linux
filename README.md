# doctrine-dbal-ibmi-linux

Just an idea to access DB2 on IBMi via external Linux server running on ODBC.
- Keep using Doctrine
- Don't spend a fortune on IBM's Connect licenses

# Usage

First, install with Composer:

```
$ composer require guidofaecke/doctrine-dbal-ibmi-linux
```

## Configuration

In your connection configuration, use this specific `DB2Driver` class, for
example, when configuring for a Zend Expressive application:

```php
<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \DoctrineDbalIbmiLinux\Driver\DB2Driver::class,
                'params' => [
                    'host'       => '...',
                    'user'       => '...',
                    'password'   => '...',
                    'dbname'     => '...',
                    'persistent' => true,
                    'naming'     => 1,
                ],
            ],
        ],
    ],
];
```
