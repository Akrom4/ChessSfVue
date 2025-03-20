<?php

namespace App\Entity;


use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CoursesRepository;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: CoursesRepository::class)]
#[ApiResource( 
    operations: [
        new Get(),
        new Put(
            security: "is_granted('ROLE_ADMIN')"),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
    normalizationContext: [
        'groups' => ['course:read'],
        'enable_max_depth' => true
    ],
    denormalizationContext: [
        'groups' => ['course:write']
    ]
)]
#[GetCollection]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'title' => 'partial', 'description' => 'partial'])]
class Courses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['course:read', 'course:write', 'user:read', 'user_course:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['course:read', 'course:write', 'user:read'])]
    private ?string $title = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['course:read'])]
    private ?\DateTimeImmutable $createdat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['course:read'])]
    private ?\DateTimeInterface $updatedat = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Chapter::class)]
    #[Groups(['course:read'])]
    #[MaxDepth(1)]
    private Collection $chapters;

    #[Vich\UploadableField(mapping: "course_images", fileNameProperty: "image")]
    private ?File $imageFile = null;

    #[ORM\OneToMany(mappedBy: 'courseid', targetEntity: UserCourses::class, orphanRemoval: true)]
    #[Groups(['course:read'])]
    #[MaxDepth(2)]
    private Collection $userCourses;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $author = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['course:read', 'course:write'])]
    private ?string $colorside = null;

    public function __construct()
    {
        $this->chapters = new ArrayCollection();
        $this->userCourses = new ArrayCollection();
    }
    
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedat = new \DateTimeImmutable();
        }

        return $this;
    }
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCreatedat(): ?\DateTimeImmutable
    {
        return $this->createdat;
    }

    #[ORM\PrePersist]
    public function setCreatedat(): self
    {
        $this->createdat = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedat(): ?\DateTimeInterface
    {
        return $this->updatedat;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedat(): self
    {
        $this->updatedat = new \DateTime();

        return $this;
    }

    /**
     * @return Collection<int, Chapter>
     */
    public function getChapters(): Collection
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): self
    {
        if (!$this->chapters->contains($chapter)) {
            $this->chapters->add($chapter);
            $chapter->setCourse($this);
        }

        return $this;
    }

    public function removeChapter(Chapter $chapter): self
    {
        if ($this->chapters->removeElement($chapter)) {
            // set the owning side to null (unless already changed)
            if ($chapter->getCourse() === $this) {
                $chapter->setCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserCourses>
     */
    public function getUserCourses(): Collection
    {
        return $this->userCourses;
    }

    public function addUserCourse(UserCourses $userCourse): self
    {
        if (!$this->userCourses->contains($userCourse)) {
            $this->userCourses->add($userCourse);
            $userCourse->setCourseid($this);
        }

        return $this;
    }

    public function removeUserCourse(UserCourses $userCourse): self
    {
        if ($this->userCourses->removeElement($userCourse)) {
            // set the owning side to null (unless already changed)
            if ($userCourse->getCourseid() === $this) {
                $userCourse->setCourseid(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getColorside(): ?string
    {
        return $this->colorside;
    }

    public function setColorside(?string $colorside): self
    {
        $this->colorside = $colorside;

        return $this;
    }
}