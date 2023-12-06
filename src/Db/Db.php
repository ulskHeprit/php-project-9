<?php
/**
 *
 */

namespace Hexlet\Code\Db;

/**
 *
 */
class Db
{
    private static $instance = null;
    private static $pdo = null;

    /**
     * @param $params
     * @return null
     * @throws \Exception
     */
    public static function get($params = [])
    {
        if (is_null(static::$instance)) {
            $pdo = Connection::get()->connect($params);
            static::$instance = new self($pdo);
        }

        return static::$instance;
    }

    /**
     * @param $sql
     * @return mixed
     */
    public function query($sql)
    {
        return static::$pdo->query($sql);
    }

    /**
     * @param $sql
     * @return mixed
     */
    public function exec($sql)
    {
        return static::$pdo->exec($sql);
    }

    /**
     * @param $sql
     * @return mixed
     */
    public function fetchAll($sql)
    {
        return $this->query($sql)->fetchAll();
    }

    /**
     * @param $sql
     * @return mixed
     */
    public function fetch($sql)
    {
        return $this->query($sql)->fetch();
    }

    /**
     * @param $pdo
     */
    protected function __construct($pdo)
    {
        static::$pdo = $pdo;
    }
}
