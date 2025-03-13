<?php

namespace App\Controller;

use App\Entity\Courses;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CourseImageController extends AbstractController
{
    #[Route('/api/courses/{id}/upload', name: 'api_course_image_upload', methods: ['POST', 'OPTIONS'])]
    public function uploadImage(
        Request $request, 
        ?Courses $course = null, 
        ?EntityManagerInterface $entityManager = null,
        ?SerializerInterface $serializer = null
    ): Response {
        // Handle preflight OPTIONS request for CORS
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', Response::HTTP_OK, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                'Access-Control-Max-Age' => '3600'
            ]);
        }
        
        // Check permissions for POST request
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Get the uploaded file
        $uploadedFile = $request->files->get('imageFile');
        
        // Debug the request
        $debug = [
            'files_count' => count($request->files),
            'hasImageFile' => $request->files->has('imageFile'),
            'all_files' => array_keys($request->files->all()),
            'post_params' => array_keys($request->request->all()),
        ];
        
        if (!$uploadedFile) {
            return $this->json([
                'error' => 'No file uploaded', 
                'debug' => $debug
            ], Response::HTTP_BAD_REQUEST, [
                'Access-Control-Allow-Origin' => '*'
            ]);
        }
        
        try {
            // Set the image file on the course entity
            $course->setImageFile($uploadedFile);
            
            // Save the entity - VichUploader will do the file uploading
            $entityManager->flush();
            
            // Return the updated course with proper serialization groups
            $jsonCourse = $serializer->serialize($course, 'json', ['groups' => 'course:read']);
            
            return new Response($jsonCourse, Response::HTTP_OK, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*'
            ]);
        } catch (\Exception $e) {
            // Return detailed error message
            return $this->json([
                'error' => 'Failed to process the uploaded image',
                'message' => $e->getMessage(),
                'file' => $uploadedFile ? $uploadedFile->getClientOriginalName() : 'none',
                'debug' => $debug
            ], Response::HTTP_BAD_REQUEST, [
                'Access-Control-Allow-Origin' => '*'
            ]);
        }
    }
} 