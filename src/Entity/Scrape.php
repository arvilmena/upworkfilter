<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScrapeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Scrape
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hostname;

    /**
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     */
    private $status_code;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\Column(type="datetime")
     */
    private $crawled_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $scrape_id;

    /**
     * @throws \Exception
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        if ( empty($this->getCrawledAt()) ) {
            $this->crawled_at = new \DateTime();
        }

        if ( empty($this->hostname) ) {
            $hostname = parse_url($this->url, PHP_URL_HOST);
            if ( ! empty($hostname) ) {
                $this->hostname = ltrim($hostname, 'www.');
            }
        }

    }

    public function __construct(string $scrape_id, string $url)
    {
        $this->scrape_id = $scrape_id;
        $this->url = $url;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->status_code;
    }

    public function setStatusCode(?int $status_code): self
    {
        $this->status_code = $status_code;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getCrawledAt(): ?\DateTimeInterface
    {
        return $this->crawled_at;
    }

    public function getScrapeId(): ?string
    {
        return $this->scrape_id;
    }

    public function setScrapeId(string $scrape_id): self
    {
        $this->scrape_id = $scrape_id;

        return $this;
    }
}
