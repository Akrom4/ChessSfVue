<?php

namespace App\Controller;

use App\Entity\Courses;
use App\Entity\UserCourses;
use App\Repository\UserCoursesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class UserCoursesController extends AbstractController
{
    private $userCoursesRepository;

    public function __construct(UserCoursesRepository $userCoursesRepository)
    {
        $this->userCoursesRepository = $userCoursesRepository;
    }

    #[Route('/courses/{id}/following', name: 'check_course_following', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function checkFollowingStatus(Courses $course): JsonResponse
    {
        $user = $this->getUser();
        
        $userCourse = $this->userCoursesRepository->findOneBy([
            'userid' => $user,
            'courseid' => $course
        ]);
        
        return $this->json([
            'following' => $userCourse !== null,
            'userCourseId' => $userCourse ? $userCourse->getId() : null
        ]);
    }

    #[Route('/courses/{id}/my-chapters', name: 'get_followed_course_chapters', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getChaptersForFollowedCourse(Courses $course): JsonResponse
    {
        $user = $this->getUser();
        
        $userCourse = $this->userCoursesRepository->findOneBy([
            'userid' => $user,
            'courseid' => $course
        ]);
        
        if (!$userCourse) {
            return $this->json([
                'error' => 'You must follow this course to access its chapters',
                'following' => false
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Get chapters from the course
        $chapters = $course->getChapters();
        
        // Build response with normalized chapter data
        $result = [];
        foreach ($chapters as $chapter) {
            $result[] = [
                'id' => $chapter->getId(),
                'title' => $chapter->getTitle(),
                // Add other chapter properties you need
            ];
        }
        
        return $this->json([
            'following' => true,
            'userCourseId' => $userCourse->getId(),
            'chapters' => $result,
            'completedChapters' => $userCourse->getCompletedChapters()
        ]);
    }
} 