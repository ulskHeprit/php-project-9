<?php

namespace Hexlet\Code\Db;

/**
 *
 */
class Db
{
    private static ?Db $instance = null;
    private static ?\PDO $pdo = null;

    /**
     * @param $params
     * @return self
     * @throws \Exception
     */
    public static function get(array $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (is_null(static::$instance)) {
            $pdo = Connection::get()->connect($params);
            /** @phpstan-ignore-next-line */
            static::$instance = new self($pdo);
        }
        /** @phpstan-ignore-next-line */
        return static::$instance;
    }

    /**
     * @param string $sql
     * @return mixed
     */
    public function query(string $sql)
    {
        /** @phpstan-ignore-next-line */
        return static::$pdo->query($sql);
    }

    /**
     * @param string $sql
     * @return mixed
     */
    public function exec(string $sql)
    {
        /** @phpstan-ignore-next-line */
        return static::$pdo->exec($sql);
    }

    /**
     * @param string $sql
     * @return mixed
     */
    public function fetchAll(string $sql)
    {
        return $this->query($sql)->fetchAll();
    }

    /**
     * @param string $sql
     * @return mixed
     */
    public function fetch(string $sql)
    {
        return $this->query($sql)->fetch();
    }

    /**
     * @param \PDO $pdo
     */
    protected function __construct(\PDO $pdo)
    {
        /** @phpstan-ignore-next-line */
        static::$pdo = $pdo;
    }
}
