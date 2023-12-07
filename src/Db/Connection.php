<?php

namespace Hexlet\Code\Db;

class Connection
{
    private static ?Connection $conn = null;

    /**
     * @param $params
     * @return \PDO
     * @throws \Exception
     */
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
        /** @phpstan-ignore-next-line */
        if (null === static::$conn) {
            /** @phpstan-ignore-next-line */
            static::$conn = new self();
        }
        /** @phpstan-ignore-next-line */
        return static::$conn;
    }

    protected function __construct()
    {
    }
}
