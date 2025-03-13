<?php

namespace App\Serializer;

use App\Entity\User;
use App\Entity\UserCourses;
use App\Entity\Courses;

class MaxDepthHandler
{
    public function __invoke($object, string $format = null, array $context = [])
    {
        if ($object instanceof User) {
            // Return only essential data for User objects
            return [
                'id' => $object->getId(),
                'username' => $object->getUsername()
            ];
        }
        
        if ($object instanceof UserCourses) {
            // Return only the ID for UserCourses objects
            return [
                'id' => $object->getId()
            ];
        }
        
        if ($object instanceof Courses) {
            // Return only essential data for Courses objects
            return [
                'id' => $object->getId(),
                'title' => $object->getTitle()
            ];
        }
        
        // For other objects, if they have an ID, return it
        if (method_exists($object, 'getId')) {
            return $object->getId();
        }
        
        // Last resort fallback
        return 'Max depth reached for ' . get_class($object);
    }
} 