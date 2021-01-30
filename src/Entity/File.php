<?php

namespace App\Entity;

use App\Document\CsvFile;
use App\Repository\FileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
    public const STATUS_NEW = 'new';
    public const STATUS_PARSE = 'parse';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_FAIL = 'fail';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $file_name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $status;

    /**
     * @ORM\Column(type="text")
     */
    private $file_path;

    /**
     * @ORM\OneToMany(targetEntity=FileData::class, mappedBy="file", orphanRemoval=true)
     */
    private $fileData;

    public function __construct()
    {
        $this->fileData = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

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

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(string $file_path): self
    {
        $this->file_path = $file_path;

        return $this;
    }

    /**
     * @return Collection|FileData[]
     */
    public function getFileData(): Collection
    {
        return $this->fileData;
    }

    public function addFileData(FileData $fileData): self
    {
        if (!$this->fileData->contains($fileData)) {
            $this->fileData[] = $fileData;
            $fileData->setFile($this);
        }

        return $this;
    }

    public function removeFileData(FileData $fileData): self
    {
        if ($this->fileData->removeElement($fileData)) {
            // set the owning side to null (unless already changed)
            if ($fileData->getFile() === $this) {
                $fileData->setFile(null);
            }
        }

        return $this;
    }

    public static function create(string $fileName, string $filePath) {
        $instance = new self();
        $instance->setFileName($fileName);
        $instance->setFilePath($filePath);
        $instance->setStatus(self::STATUS_NEW);
        $instance->setCreatedAt(new \DateTime());

        return $instance;
    }
}
