<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/react' => [[['_route' => 'app_react', '_controller' => 'App\\Controller\\ReactController::index'], null, null, null, false, false, null]],
        '/api/login' => [[['_route' => 'api_login', '_controller' => 'App\\Controller\\SecurityController::login'], null, ['POST' => 0], null, false, false, null]],
        '/api/logout' => [[['_route' => 'api_logout', '_controller' => 'App\\Controller\\SecurityController::logout'], null, ['POST' => 0], null, false, false, null]],
        '/api/users' => [
            [['_route' => 'api_user_index', '_controller' => 'App\\Controller\\UserController::index'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'api_user_add', '_controller' => 'App\\Controller\\UserController::add'], null, ['POST' => 0], null, false, false, null],
        ],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/api(?'
                    .'|/(?'
                        .'|\\.well\\-known/genid/([^/]++)(*:46)'
                        .'|errors/(\\d+)(*:65)'
                        .'|validation_errors/([^/]++)(*:98)'
                    .')'
                    .'|(?:/(index)(?:\\.([^/]++))?)?(*:134)'
                    .'|/(?'
                        .'|docs(?:\\.([^/]++))?(*:165)'
                        .'|c(?'
                            .'|o(?'
                                .'|ntexts/([^.]+)(?:\\.(jsonld))?(*:210)'
                                .'|urses(?'
                                    .'|/([^/\\.]++)(?:\\.([^/]++))?(?'
                                        .'|(*:255)'
                                    .')'
                                    .'|(?:\\.([^/]++))?(?'
                                        .'|(*:282)'
                                    .')'
                                    .'|/([^/]++)/upload(*:307)'
                                .')'
                            .')'
                            .'|hapters(?'
                                .'|/([^/\\.]++)(?:\\.([^/]++))?(?'
                                    .'|(*:356)'
                                .')'
                                .'|(?:\\.([^/]++))?(*:380)'
                            .')'
                        .')'
                        .'|validation_errors/([^/]++)(?'
                            .'|(*:419)'
                        .')'
                        .'|user(?'
                            .'|_courses(?'
                                .'|(?:\\.([^/]++))?(*:461)'
                                .'|/([^/\\.]++)(?:\\.([^/]++))?(*:495)'
                                .'|(?:\\.([^/]++))?(*:518)'
                                .'|/([^/\\.]++)(?:\\.([^/]++))?(?'
                                    .'|(*:555)'
                                .')'
                            .')'
                            .'|s/([^/]++)(?'
                                .'|(*:578)'
                            .')'
                        .')'
                    .')'
                .')'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:618)'
                .'|/react/([^/]++)/([^/]++)(*:650)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        46 => [[['_route' => 'api_genid', '_controller' => 'api_platform.action.not_exposed', '_api_respond' => 'true'], ['id'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        65 => [[['_route' => 'api_errors', '_controller' => 'api_platform.action.error_page'], ['status'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        98 => [[['_route' => 'api_validation_errors', '_controller' => 'api_platform.action.not_exposed'], ['id'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        134 => [[['_route' => 'api_entrypoint', '_controller' => 'api_platform.action.entrypoint', '_format' => '', '_api_respond' => 'true', 'index' => 'index'], ['index', '_format'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        165 => [[['_route' => 'api_doc', '_controller' => 'api_platform.action.documentation', '_format' => '', '_api_respond' => 'true'], ['_format'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        210 => [[['_route' => 'api_jsonld_context', '_controller' => 'api_platform.jsonld.action.context', '_format' => 'jsonld', '_api_respond' => 'true'], ['shortName', '_format'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        255 => [
            [['_route' => '_api_/courses/{id}{._format}_get', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Courses', '_api_operation_name' => '_api_/courses/{id}{._format}_get'], ['id', '_format'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_/courses/{id}{._format}_put', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Courses', '_api_operation_name' => '_api_/courses/{id}{._format}_put'], ['id', '_format'], ['PUT' => 0], null, false, true, null],
        ],
        282 => [
            [['_route' => '_api_/courses{._format}_get_collection', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Courses', '_api_operation_name' => '_api_/courses{._format}_get_collection'], ['_format'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_/courses{._format}_post', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Courses', '_api_operation_name' => '_api_/courses{._format}_post'], ['_format'], ['POST' => 0], null, false, true, null],
        ],
        307 => [[['_route' => 'upload_course_image', '_controller' => 'App\\Controller\\CourseImageController::uploadImage'], ['id'], ['POST' => 0], null, false, false, null]],
        356 => [
            [['_route' => '_api_/chapters/{id}{._format}_get', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Chapter', '_api_operation_name' => '_api_/chapters/{id}{._format}_get'], ['id', '_format'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_/chapters/{id}{._format}_put', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Chapter', '_api_operation_name' => '_api_/chapters/{id}{._format}_put'], ['id', '_format'], ['PUT' => 0], null, false, true, null],
        ],
        380 => [[['_route' => '_api_/chapters{._format}_post', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Chapter', '_api_operation_name' => '_api_/chapters{._format}_post'], ['_format'], ['POST' => 0], null, false, true, null]],
        419 => [
            [['_route' => '_api_validation_errors_problem', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_problem'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_validation_errors_hydra', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_hydra'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_validation_errors_jsonapi', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_jsonapi'], ['id'], ['GET' => 0], null, false, true, null],
        ],
        461 => [[['_route' => '_api_/user_courses{._format}_get_collection', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\UserCourses', '_api_operation_name' => '_api_/user_courses{._format}_get_collection'], ['_format'], ['GET' => 0], null, false, true, null]],
        495 => [[['_route' => '_api_/user_courses/{id}{._format}_get', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\UserCourses', '_api_operation_name' => '_api_/user_courses/{id}{._format}_get'], ['id', '_format'], ['GET' => 0], null, false, true, null]],
        518 => [[['_route' => '_api_/user_courses{._format}_post', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\UserCourses', '_api_operation_name' => '_api_/user_courses{._format}_post'], ['_format'], ['POST' => 0], null, false, true, null]],
        555 => [
            [['_route' => '_api_/user_courses/{id}{._format}_put', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\UserCourses', '_api_operation_name' => '_api_/user_courses/{id}{._format}_put'], ['id', '_format'], ['PUT' => 0], null, false, true, null],
            [['_route' => '_api_/user_courses/{id}{._format}_delete', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\UserCourses', '_api_operation_name' => '_api_/user_courses/{id}{._format}_delete'], ['id', '_format'], ['DELETE' => 0], null, false, true, null],
        ],
        578 => [
            [['_route' => 'api_user_show', '_controller' => 'App\\Controller\\UserController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'api_user_edit', '_controller' => 'App\\Controller\\UserController::edit'], ['id'], ['PUT' => 0], null, false, true, null],
            [['_route' => 'api_user_delete', '_controller' => 'App\\Controller\\UserController::delete'], ['id'], ['DELETE' => 0], null, false, true, null],
        ],
        618 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        650 => [
            [['_route' => 'app_react_course', '_controller' => 'App\\Controller\\ReactController::courseChapter'], ['courseId', 'chapterId'], ['GET' => 0, 'POST' => 1], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
