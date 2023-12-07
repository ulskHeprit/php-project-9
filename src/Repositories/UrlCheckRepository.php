<?php

namespace Hexlet\Code\Repositories;

use Hexlet\Code\Db\Db;
use Hexlet\Code\Models\Url;
use Hexlet\Code\Models\UrlCheck;

/**
 * UrlRepository
 */
class UrlCheckRepository
{
    private Db $db;

    /**
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * @param UrlCheck $urlCheck
     * @return bool
     */
    public function save(UrlCheck $urlCheck)
    {
        if ($urlCheck->getId() && $this->getById($urlCheck->getId())) {
            return false;
        }

        try {
            $this->db->exec(
                sprintf(
                    "INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)"
                    . " VALUES (%d, %d, '%s', '%s', '%s', '%s');",
                    $urlCheck->getUrlId(),
                    $urlCheck->getStatusCode(),
                    $urlCheck->getH1(),
                    $urlCheck->getTitle(),
                    $urlCheck->getDescription(),
                    $urlCheck->getCreatedAt()
                )
            );
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * @param int $url_id
     * @return array
     */
    public function getByUrlId(int $url_id)
    {
        $urlChecks = [];
        $arrays = $this->db->fetchAll(
            sprintf('SELECT * FROM url_checks WHERE url_id = %d ORDER BY created_at DESC', $url_id)
        );

        foreach ($arrays as $row) {
            $urlChecks[] = new UrlCheck($row);
        }

        return $urlChecks;
    }

    /**
     * @param int $id
     * @return false|UrlCheck
     */
    public function getById(int $id)
    {
        $data = $this->db->fetch(sprintf('SELECT * FROM url_checks WHERE id = %d', $id));

        if ($data) {
            return new UrlCheck($data);
        }

        return false;
    }

    /**
     * @param int $url_id
     * @return false|UrlCheck
     */
    public function getLastByUrlId(int $url_id)
    {
        $data = $this->db->fetch(
            sprintf('SELECT * FROM url_checks WHERE url_id = %d ORDER BY created_at LIMIT 1', $url_id)
        );

        if ($data) {
            return new UrlCheck($data);
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $urlChecks = [];
        $arrays = $this->db->fetchAll('SELECT * FROM url_checks ORDER BY created_at DESC');

        foreach ($arrays as $row) {
            $urlChecks[] = new UrlCheck($row);
        }

        return $urlChecks;
    }
}
