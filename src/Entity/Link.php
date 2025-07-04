<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Entity(repositoryClass: LinkRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt')]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1024)]
    #[Assert\Url(
        message: 'Некорректный URL. Пример: https://example.com'
    )]

    private ?string $fullUrl = null;

    #[ORM\Column(length: 6)]
    private ?string $shortUrl = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?int $visitCount = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $lastUsedAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isOneTime = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\GreaterThan('today', message: 'Дата должна в будущем')]
    private ?\DateTime $expiresAt = null;

    #[ORM\Column(name: "deletedAt", type: 'datetime', nullable: true)]
    private ?\DateTime $deletedAt = null;

    public function getDeletedAt(): ?\DateTime {
        return $this->deletedAt;
    }
    public function setDeletedAt(?\DateTime $deletedAt): static {
        $this->deletedAt = $deletedAt;
        return $this;
    }
    public function isOneTime(): bool { return $this->isOneTime; }
    public function setIsOneTime(bool $isOneTime): static {
        $this->isOneTime = $isOneTime;
        return $this;
    }

    public function getExpiresAt(): ?\DateTime {
        return $this->expiresAt;
    }
    public function setExpiresAt(?\DateTime $expiresAt): static {
        $this->expiresAt = $expiresAt;
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getFullUrl(): ?string
    {
        return $this->fullUrl;
    }

    public function setFullUrl(string $fullUrl): static
    {
        $this->fullUrl = $fullUrl;

        return $this;
    }

    public function getShortUrl(): ?string
    {
        return $this->shortUrl;
    }

    public function setShortUrl(string $shortUrl): static
    {
        $this->shortUrl = $shortUrl;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getVisitCount(): ?int
    {
        return $this->visitCount;
    }

    public function setVisitCount(int $visitCount): static
    {
        $this->visitCount = $visitCount;

        return $this;
    }

    public function getLastUsedAt(): ?\DateTime
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(\DateTime $lastUsedAt): static
    {
        $this->lastUsedAt = $lastUsedAt;

        return $this;
    }
}
