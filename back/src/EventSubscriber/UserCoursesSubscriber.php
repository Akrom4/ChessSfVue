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
    public function __construct(
        private Security $security
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                ['setUserForUserCourses', EventPriorities::PRE_VALIDATE],
                ['includeUserInfoInResponse', EventPriorities::PRE_SERIALIZE],
            ],
        ];
    }

    public function setUserForUserCourses(ViewEvent $event): void
    {
        $userCourses = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$userCourses instanceof UserCourses || Request::METHOD_POST !== $method) {
            return;
        }

        // Set the current user as the owner if not already set
        if ($userCourses->getUserid() === null) {
            $user = $this->security->getUser();
            if ($user) {
                $userCourses->setUserid($user);
            }
        }
    }
    
    public function includeUserInfoInResponse(ViewEvent $event): void
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        
        // Only apply to GET requests
        if ($method !== Request::METHOD_GET) {
            return;
        }
        
        // Make sure user info is included in context
        $request = $event->getRequest();
        if (!$request->attributes->has('_api_normalization_context')) {
            $request->attributes->set('_api_normalization_context', []);
        }
        
        $context = $request->attributes->get('_api_normalization_context');
        if (!isset($context['groups'])) {
            $context['groups'] = [];
        }
        
        // Add groups that include user info
        if (!in_array('user_course:read', $context['groups'])) {
            $context['groups'][] = 'user_course:read';
        }
        
        $request->attributes->set('_api_normalization_context', $context);
    }
} 