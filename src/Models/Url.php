<?php
/**
 *
 */

namespace Hexlet\Code\Models;

/**
 * Url
 */
class Url
{
    protected ?int $id;
    protected string $name;
    protected string $created_at;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->created_at = $data['created_at'];
    }

    /**
     * @return int|mixed|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
}