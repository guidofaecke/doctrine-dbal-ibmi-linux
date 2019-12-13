<?php

namespace DoctrineDbalIbmiLinux\Driver;

class DB2IBMiLinuxDriver extends AbstractDB2Driver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        if ( ! isset($params['protocol'])) {
            $params['protocol'] = 'TCPIP';
        }

        if ( ! isset($params['naming'])) {
            $params['naming'] = 1;
        }

        if ($params['host'] !== 'localhost' && $params['host'] != '127.0.0.1') {
            // if the host isn't localhost, use extended connection params
            $params['dbname'] = 'DRIVER={IBM i Access ODBC DRIVER}' .
                ';DATABASE=' . $params['dbname'] .
                ';SYSTEM='   . $params['host'] .
                ';PROTOCOL=' . $params['protocol'] .
                ';UID='      . $username .
                ';PWD='      . $password . ';' .
                ';NAMING='   . $params['naming'];
            if (isset($params['port'])) {
                $params['dbname'] .= 'PORT=' . $params['port'];
            }

            $username = null;
            $password = null;
        }

        return new DB2IBMiLinuxIBMiConnection($params, $username, $password, $driverOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ibmi_db2_linux';
    }
}
