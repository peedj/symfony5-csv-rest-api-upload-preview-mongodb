<?php

namespace App\Entity;

use App\Repository\FileDataRepository;
use Doctrine\Inflector\Inflector;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Inflector\EnglishInflector;
use function symfony\component\string\u;

/**
 * @ORM\Entity(repositoryClass=FileDataRepository::class)
 */
class FileData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=File::class, inversedBy="fileData")
     * @ORM\JoinColumn(nullable=false)
     */
    private $file;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $client;

    /**
     * @ORM\Column(type="integer")
     */
    private $sign_smartid;

    /**
     * @ORM\Column(type="integer")
     */
    private $sign_mobile;

    /**
     * @ORM\Column(type="integer")
     */
    private $sign_sc;

    /**
     * @ORM\Column(type="integer")
     */
    private $authorize_smartid;

    /**
     * @ORM\Column(type="integer")
     */
    private $authorize_mobile;

    /**
     * @ORM\Column(type="integer")
     */
    private $authorize_sc;

    /**
     * @ORM\Column(type="integer")
     */
    private $ocsp;

    /**
     * @ORM\Column(type="integer")
     */
    private $crl;


    private ?string $group_month = "";
    private ?int $authorizations = 0;
    private ?int $signs = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getSignSmartid(): ?int
    {
        return $this->sign_smartid;
    }

    public function setSignSmartid(int $sign_smartid): self
    {
        $this->sign_smartid = $sign_smartid;

        return $this;
    }

    public function getSignMobile(): ?int
    {
        return $this->sign_mobile;
    }

    public function setSignMobile(int $sign_mobile): self
    {
        $this->sign_mobile = $sign_mobile;

        return $this;
    }

    public function getSignSc(): ?int
    {
        return $this->sign_sc;
    }

    public function setSignSc(int $sign_sc): self
    {
        $this->sign_sc = $sign_sc;

        return $this;
    }

    public function getAuthorizeSmartid(): ?int
    {
        return $this->authorize_smartid;
    }

    public function setAuthorizeSmartid(int $authorize_smartid): self
    {
        $this->authorize_smartid = $authorize_smartid;

        return $this;
    }

    public function getAuthorizeMobile(): ?int
    {
        return $this->authorize_mobile;
    }

    public function setAuthorizeMobile(int $authorize_mobile): self
    {
        $this->authorize_mobile = $authorize_mobile;

        return $this;
    }

    public function getAuthorizeSc(): ?int
    {
        return $this->authorize_sc;
    }

    public function setAuthorizeSc(int $authorize_sc): self
    {
        $this->authorize_sc = $authorize_sc;

        return $this;
    }

    public function getOcsp(): ?int
    {
        return $this->ocsp;
    }

    public function setOcsp(int $ocsp): self
    {
        $this->ocsp = $ocsp;

        return $this;
    }

    public function getCrl(): ?int
    {
        return $this->crl;
    }

    public function setCrl(int $crl): self
    {
        $this->crl = $crl;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGroupMonth(): ?string
    {
        return $this->group_month;
    }

    /**
     * @param string|null $group_month
     */
    public function setGroupMonth(?string $group_month): void
    {
        $this->group_month = $group_month;
    }

    /**
     * @return int|null
     */
    public function getAuthorizations(): ?int
    {
        return $this->authorizations;
    }

    /**
     * @param int|null $authorizations
     */
    public function setAuthorizations(?int $authorizations): void
    {
        $this->authorizations = $authorizations;
    }

    /**
     * @return int|null
     */
    public function getSigns(): ?int
    {
        return $this->signs;
    }

    /**
     * @param int|null $signs
     */
    public function setSigns(?int $signs): void
    {
        $this->signs = $signs;
    }

}
