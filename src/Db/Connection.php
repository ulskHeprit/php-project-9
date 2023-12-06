<?php

namespace Hexlet\Code\Db;

class Connection
{
    private static ?Connection $conn = null;

    public function connect($params = [])
    {
        if (empty($params)) {
            throw new \Exception("Error reading database configuration file");
        }

        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $params['DB_HOST'],
            $params['DB_PORT'],
            $params['DB_DATABASE'],
            $params['DB_USER'],
            $params['DB_PASSWORD']
        );

        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function get()
    {
        if (null === static::$conn) {
            static::$conn = new self();
        }

        return static::$conn;
    }

    protected function __construct()
    {
    }
}
