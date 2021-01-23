<?php

namespace App\Document;

use App\Repository\CsvFileRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass=CsvFileRepository::class)
 */
class CsvFile
{
    public const STATUS_NEW = 'new';
    public const STATUS_PARSE = 'parse';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_FAIL = 'fail';

    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\Field(type="string")
     */
    private $file_name;

    /**
     * @MongoDB\Field(type="timestamp")
     */
    private $date_created;

    /**
     * @MongoDB\Field(type="string")
     */
    private $file_path;

    /**
     * @MongoDB\Field(type="string")
     */
    private $status;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): self
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getDateCreated()
    {
        return $this->date_created;
    }

    public function setDateCreated($date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(string $file_path): self
    {
        $this->file_path = $file_path;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'fileName' => $this->getFileName(),
            'dateCreated' => $this->getDateCreated(),
            'getStatus' => $this->getStatus(),
        ];
    }
}
