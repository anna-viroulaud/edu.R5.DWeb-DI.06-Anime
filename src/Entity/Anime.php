<?php

namespace App\Entity;

use App\Repository\AnimeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimeRepository::class)]
class Anime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $synopsis = null;

    #[ORM\Column(nullable: true)]
    private ?int $release_year = null;

    #[ORM\Column(nullable: true)]
    private ?int $episodes_count = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster_image = null;

    #[ORM\Column]
    private ?bool $is_featured = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deleted_at = null;

    // Le studio pointe bien vers l'entité Studio (et non User)
    #[ORM\ManyToOne(inversedBy: 'animes')]
    private ?Studio $studio = null;

    // L'utilisateur qui a créé l'anime
    #[ORM\ManyToOne]
    private ?User $createdBy = null;

    /**
     * @var Collection<int, Genre>
     */
    #[ORM\ManyToMany(targetEntity: Genre::class, inversedBy: 'animes')]
    private Collection $genres;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'anime')]
    private Collection $reviews;

    /**
     * @var Collection<int, Tracker>
     */
    #[ORM\OneToMany(targetEntity: Tracker::class, mappedBy: 'anime')]
    private Collection $trackers;

    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->trackers = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->title ?? 'Anime #' . $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(?string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getReleaseYear(): ?int
    {
        return $this->release_year;
    }

    public function setReleaseYear(?int $release_year): static
    {
        $this->release_year = $release_year;

        return $this;
    }

    public function getEpisodesCount(): ?int
    {
        return $this->episodes_count;
    }

    public function setEpisodesCount(?int $episodes_count): static
    {
        $this->episodes_count = $episodes_count;

        return $this;
    }

    public function getPosterImage(): ?string
    {
        return $this->poster_image;
    }

    public function setPosterImage(?string $poster_image): static
    {
        $this->poster_image = $poster_image;

        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->is_featured;
    }

    public function setIsFeatured(bool $is_featured): static
    {
        $this->is_featured = $is_featured;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

    public function getStudio(): ?Studio
    {
        return $this->studio;
    }

    public function setStudio(?Studio $studio): static
    {
        $this->studio = $studio;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): static
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): static
    {
        $this->genres->removeElement($genre);

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setAnime($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getAnime() === $this) {
                $review->setAnime(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tracker>
     */
    public function getTrackers(): Collection
    {
        return $this->trackers;
    }

    public function addTracker(Tracker $tracker): static
    {
        if (!$this->trackers->contains($tracker)) {
            $this->trackers->add($tracker);
            $tracker->setAnime($this);
        }

        return $this;
    }

    public function removeTracker(Tracker $tracker): static
    {
        if ($this->trackers->removeElement($tracker)) {
            if ($tracker->getAnime() === $this) {
                $tracker->setAnime(null);
            }
        }

        return $this;
    }
}