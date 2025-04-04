<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserCoursesRepository;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Filter\UserCoursesFilter;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UserCoursesRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            filters: ["user_courses.user_filter"]
        ),
        new Get(
            security: "is_granted('ROLE_USER') and object.getUserid() == user"
        ),
        new Post(
            security: "is_granted('ROLE_USER')"
        ),
        new Put(
            security: "is_granted('ROLE_USER') and object.getUserid() == user"
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and object.getUserid() == user"
        )
    ],
    normalizationContext: [
        'groups' => ['user_course:read'],
        'enable_max_depth' => true
    ],
    denormalizationContext: [
        'groups' => ['user_course:write']
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['userid' => 'exact', 'courseid' => 'exact'])]
#[ApiFilter(UserCoursesFilter::class)]
class UserCourses
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_course:read', 'course:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userCourses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_course:read', 'user_course:write', 'course:read'])]
    #[MaxDepth(2)]
    private ?User $userid = null;

    #[ORM\ManyToOne(inversedBy: 'userCourses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_course:read', 'user_course:write', 'user:read'])]
    #[MaxDepth(2)]
    private ?Courses $courseid = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    #[Groups(['user_course:read', 'user_course:write', 'course:read', 'user:read'])]
    private array $completedChapters = [];

    #[ORM\Column(nullable: true)]
    #[Groups(['user_course:read', 'user_course:write', 'course:read', 'user:read'])]
    private ?int $completionPercentage = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user_course:read', 'course:read', 'user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['user_course:read', 'course:read', 'user:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserid(): ?User
    {
        return $this->userid;
    }

    public function setUserid(?User $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getCourseid(): ?Courses
    {
        return $this->courseid;
    }

    public function setCourseid(?Courses $courseid): self
    {
        $this->courseid = $courseid;

        return $this;
    }

    public function getCompletedChapters(): array
    {
        return $this->completedChapters;
    }

    public function setCompletedChapters(?array $completedChapters): self
    {
        $this->completedChapters = $completedChapters;

        return $this;
    }

    public function getCompletionPercentage(): ?int
    {
        return $this->completionPercentage;
    }

    public function setCompletionPercentage(?int $completionPercentage): self
    {
        $this->completionPercentage = $completionPercentage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime();

        return $this;
    }
}
