<?php

namespace App\Connection;

use Doctrine\DBAL\Connection;

class DoctrineMultidatabaseConnection extends Connection
{
    public function changeDatabase(string $dbName): bool
    {
        $params = $this->getParams();
        if ($params['dbname'] != $dbName) {
            if ($this->isConnected()) {
                $this->close();
            }

            $params['url'] = "mysql://" . $params['user'] . ":" . $params['password'] . "@" . $params['host'] . ":" . $params['port'] . "/" . $dbName;
            $params['dbname'] = $dbName;

            $this->__construct($params, $this->driver, $this->_config);
            return true;
        }
        return false;
    }

    public function getDatabases(string $prefix = 'app_')
    {
        $dbs = $this->fetchAllAssociative('show databases;');
        $res = [];

        foreach ($dbs as $key => $dbName) {
            if (strpos($dbName['Database'], $prefix) === 0) {
                $res[] = $dbName['Database'];
            }
        }

        return $res;
    }
}
