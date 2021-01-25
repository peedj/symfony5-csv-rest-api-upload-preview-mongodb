<?php

namespace App\Document;

use App\Repository\CsvFileRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class CsvFileData
{
    /**
     * @MongoDB\Id
     */
    private $id;

    /**
     * @MongoDB\Field(type="string")
     */
    private $csvFileId;

    /**
     * @MongoDB\Field(type="collection")
     */
    private $data = [];

    public function getCsvFileId(): ?string
    {
        return $this->csvFileId;
    }

    public function setCsvFileId(string $csvFileId): self
    {
        $this->csvFileId = $csvFileId;

        return $this;
    }


    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }


}
