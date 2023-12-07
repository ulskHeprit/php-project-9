<?php
/**
 *
 */

namespace Hexlet\Code\Models;

/**
 * Url
 */
class UrlCheck
{
    protected ?int $id;
    protected int $url_id;
    protected ?int $status_code;
    protected ?string $h1;
    protected ?string $title;
    protected ?string $description;
    protected string $created_at;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->id = $data['id'] ?? null;
        $this->url_id = $data['url_id'];
        $this->status_code = $data['status_code'] ?? null;
        $this->h1 = $data['h1'] ?? null;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->created_at = $data['created_at'];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUrlId(): int
    {
        return $this->url_id;
    }

    /**
     * @return int|mixed|null
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @return mixed|string|null
     */
    public function getH1()
    {
        return $this->h1;
    }

    /**
     * @return mixed|string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string|mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
}
