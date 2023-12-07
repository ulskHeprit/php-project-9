<?php

namespace Hexlet\Code\Repositories;

use Hexlet\Code\Db\Db;
use Hexlet\Code\Models\Url;

/**
 * UrlRepository
 */
class UrlRepository
{
    private $db;

    /**
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * @param Url $url
     * @return false|int|mixed|null
     */
    public function save(Url $url)
    {
        if ($url->getId() && $this->getById($url->getId())) {
            return false;
        }

        if ($url->getName() && $this->getByName($url->getName())) {
            return false;
        }

        try {
            $this->db->exec(
                sprintf(
                    "INSERT INTO urls (name, created_at) VALUES ('%s', '%s');",
                    $url->getName(),
                    $url->getCreatedAt()
                )
            );
        } catch (\Exception $e) {
            return false;
        }

        return $this->getByName($url->getName())->getId();
    }


    /**
     * @param string $name
     * @return false|Url
     */
    public function getByName(string $name)
    {
        $data = $this->db->fetch(sprintf("SELECT * FROM urls WHERE name = '%s'", $name));

        if ($data) {
            return new Url($data);
        }

        return false;
    }

    /**
     * @param int $id
     * @return false|Url
     */
    public function getById(int $id)
    {
        $data = $this->db->fetch(sprintf('SELECT * FROM urls WHERE id = %d', $id));

        if ($data) {
            return new Url($data);
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $urls = [];
        $arrays = $this->getAllArray();

        foreach ($arrays as $row) {
            $urls[] = new Url($row);
        }

        return $urls;
    }

    /**
     * @return array
     */
    public function getAllArray()
    {
        return $this->db->fetchAll('SELECT * FROM urls ORDER BY created_at DESC');
    }
}
