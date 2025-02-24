<?php

namespace App\Controller;

use App\Entity\Courses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CourseImageController extends AbstractController
{
    #[Route('/api/courses/{id}/upload', name: 'upload_course_image', methods: ['POST'])]
    public function uploadImage(Request $request, Courses $course, EntityManagerInterface $entityManager): Response
    {
        // Check if the user is logged in
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to upload an image.');
        }

        // Check if the user has the right role
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to upload images.');
        }

        // Handle the file upload
        $file = $request->files->get('imageFile');
        if ($file) {
            $course->setImageFile($file);
            $entityManager->persist($course);
            $entityManager->flush();

            return new Response('Image uploaded successfully', Response::HTTP_OK);
        }

        return new Response('No image file provided', Response::HTTP_BAD_REQUEST);
    }
} 