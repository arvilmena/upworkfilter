<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * @ORM\Column(type="boolean")
     */
    private $should_bid;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $posted_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $client_name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $client_review_count;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $client_review_rating;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $platform;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $raw_html;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $has_been_read;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $has_been_read_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $budget;

    public function __construct(string $url, string $title)
    {
        $this->url = $url;
        $this->title = $title;
        $this->has_been_read = false;

        $platformHostname = parse_url($url, PHP_URL_HOST);
        if ( ! empty($platformHostname) ) {
            $this->platform = ltrim($platformHostname, 'www.');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getShouldBid(): ?bool
    {
        return $this->should_bid;
    }

    public function setShouldBid(bool $should_bid): self
    {
        $this->should_bid = $should_bid;

        return $this;
    }

    public function getPostedAt(): ?\DateTimeInterface
    {
        return $this->posted_at;
    }

    public function setPostedAt(?\DateTimeInterface $posted_at): self
    {
        $this->posted_at = $posted_at;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->client_name;
    }

    public function setClientName(?string $client_name): self
    {
        $this->client_name = $client_name;

        return $this;
    }

    public function getClientReviewCount(): ?int
    {
        return $this->client_review_count;
    }

    public function setClientReviewCount(?int $client_review_count): self
    {
        $this->client_review_count = $client_review_count;

        return $this;
    }

    public function getClientReviewRating(): ?int
    {
        return $this->client_review_rating;
    }

    public function setClientReviewRating(?int $client_review_rating): self
    {
        $this->client_review_rating = $client_review_rating;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getRawHtml(): ?string
    {
        return $this->raw_html;
    }

    public function setRawHtml(string $raw_html): self
    {
        $this->raw_html = $raw_html;

        return $this;
    }

    public function getHasBeenRead(): ?bool
    {
        return $this->has_been_read;
    }

    public function setHasBeenRead(bool $has_been_read): self
    {
        $this->has_been_read = $has_been_read;

        return $this;
    }

    public function getHasBeenReadAt(): ?\DateTimeInterface
    {
        return $this->has_been_read_at;
    }

    public function setHasBeenReadAt(?\DateTimeInterface $has_been_read_at): self
    {
        $this->has_been_read_at = $has_been_read_at;

        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(?string $budget): self
    {
        $this->budget = $budget;

        return $this;
    }
}
