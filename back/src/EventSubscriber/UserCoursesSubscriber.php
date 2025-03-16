<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\UserCourses;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserCoursesSubscriber implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['setCurrentUserForUserCourses', EventPriorities::PRE_WRITE],
                ['filterUserCoursesCollection', EventPriorities::PRE_READ]
            ],
        ];
    }

    public function setCurrentUserForUserCourses(ViewEvent $event): void
    {
        $userCourses = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$userCourses instanceof UserCourses || Request::METHOD_POST !== $method) {
            return;
        }

        $user = $this->security->getUser();
        // Set the current user as the owner if not already set
        if (null === $userCourses->getUserid()) {
            $userCourses->setUserid($user);
        }
    }
    
    public function filterUserCoursesCollection(ViewEvent $event): void
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $route = $event->getRequest()->attributes->get('_route');
        
        // Only apply to GET collection of UserCourses
        if (
            $method !== Request::METHOD_GET || 
            $route !== 'api_user_courses_get_collection'
        ) {
            return;
        }
        
        // We're handling a collection
        if (is_iterable($result) && !$result instanceof UserCourses) {
            // The filter will be automatically applied with the ApiFilter annotation
            // This method could be used for more complex filtering if needed
        }
    }
} 