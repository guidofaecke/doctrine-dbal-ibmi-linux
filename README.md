# doctrine-dbal-ibmi-linux

I needed a solution to use Doctrine within an Zend-Expressive application, while moving the app onto a Linux server and keep using DB2 on the IBMi and all I had was ODBC.

- Keep using Doctrine
- Don't spend a fortune on IBM's Connect licenses
- Utilize ODBC

# Usage

## Prerequisites
For your Linux server you will need the IBM i Access Client Solutions.
A good guide can be found here -> https://www.ibmsystemsmag.com/Power-Systems/08/2019/ODBC-Driver-for-IBM-i

## Install
Composer:
```
$ composer require guidofaecke/doctrine-dbal-ibmi-linux
```

## Configuration
For Doctrine itself, just follow these instructions -> https://github.com/DASPRiD/container-interop-doctrine

In your connection configuration, for example `config/autoload/doctrine.local.php` use this specific `DB2IBMiLinuxDriver` class:

```php
<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \DoctrineDbalIbmiLinux\Driver\DB2IBMiLinuxDriver::class,
                'params' => [
                    'host'       => 'my_host',
                    'user'       => 'my_user',
                    'password'   => 'my_password',
                    'dbname'     => 'my_db', <-- can be found via DSPRDBDIRE or ask your admin
                    'persistent' => true,
                    'naming'     => 1, <-- 1=system naming, 0=sql naming
                ],
            ],
        ],
    ],
];
```

The `naming` parameter essentially dictates if you use the library list defined in the JOBD for the user (naming = 1) or you have to provide the library name for every table/entity (naming = 0).

## Known problems
I don't deliver this package with a working unit-test.
Use it at your own risk.
