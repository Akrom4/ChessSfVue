<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/chapter' => [[['_route' => 'app_chapter_index', '_controller' => 'App\\Controller\\ChapterController::index'], null, ['GET' => 0], null, true, false, null]],
        '/chapter/new' => [[['_route' => 'app_chapter_new', '_controller' => 'App\\Controller\\ChapterController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/courses' => [[['_route' => 'app_courses_index', '_controller' => 'App\\Controller\\CoursesController::index'], null, ['GET' => 0], null, true, false, null]],
        '/courses/new' => [[['_route' => 'app_courses_new', '_controller' => 'App\\Controller\\CoursesController::new'], null, ['GET' => 0, 'POST' => 1], null, false, false, null]],
        '/courseslist' => [[['_route' => 'app_courses_list', '_controller' => 'App\\Controller\\CoursesListController::index'], null, null, null, true, false, null]],
        '/' => [[['_route' => 'app_homepage', '_controller' => 'App\\Controller\\HomepageController::index'], null, null, null, false, false, null]],
        '/react' => [[['_route' => 'app_react', '_controller' => 'App\\Controller\\ReactController::index'], null, null, null, false, false, null]],
        '/api/login' => [[['_route' => 'api_login', '_controller' => 'App\\Controller\\SecurityController::login'], null, ['POST' => 0], null, false, false, null]],
        '/api/logout' => [[['_route' => 'api_logout', '_controller' => 'App\\Controller\\SecurityController::logout'], null, ['POST' => 0], null, false, false, null]],
        '/api/users' => [
            [['_route' => 'api_user_index', '_controller' => 'App\\Controller\\UserController::index'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'api_user_add', '_controller' => 'App\\Controller\\UserController::add'], null, ['POST' => 0], null, false, false, null],
        ],
        '/usercourses' => [[['_route' => 'app_user_courses', '_controller' => 'App\\Controller\\UserCoursesController::index'], null, null, null, false, false, null]],
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
                                .')'
                            .')'
                            .'|hapters(?'
                                .'|/([^/\\.]++)(?:\\.([^/]++))?(?'
                                    .'|(*:332)'
                                .')'
                                .'|(?:\\.([^/]++))?(*:356)'
                            .')'
                        .')'
                        .'|validation_errors/([^/]++)(?'
                            .'|(*:395)'
                        .')'
                        .'|user(?'
                            .'|_courses(?'
                                .'|/([^/\\.]++)(?:\\.([^/]++))?(?'
                                    .'|(*:451)'
                                .')'
                                .'|(?:\\.([^/]++))?(*:475)'
                            .')'
                            .'|s/([^/]++)(?'
                                .'|(*:497)'
                            .')'
                        .')'
                    .')'
                .')'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:537)'
                .'|/c(?'
                    .'|hapter/([^/]++)(?'
                        .'|/edit(*:573)'
                        .'|(*:581)'
                    .')'
                    .'|ourses(?'
                        .'|/([^/]++)(?'
                            .'|/edit(*:616)'
                            .'|(*:624)'
                        .')'
                        .'|list/([^/]++)/chapters(*:655)'
                    .')'
                .')'
                .'|/react/([^/]++)/([^/]++)(*:689)'
                .'|/usercourses/([^/]++)(?'
                    .'|(*:721)'
                    .'|/remove(*:736)'
                .')'
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
        332 => [
            [['_route' => '_api_/chapters/{id}{._format}_get', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Chapter', '_api_operation_name' => '_api_/chapters/{id}{._format}_get'], ['id', '_format'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_/chapters/{id}{._format}_put', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Chapter', '_api_operation_name' => '_api_/chapters/{id}{._format}_put'], ['id', '_format'], ['PUT' => 0], null, false, true, null],
        ],
        356 => [[['_route' => '_api_/chapters{._format}_post', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Chapter', '_api_operation_name' => '_api_/chapters{._format}_post'], ['_format'], ['POST' => 0], null, false, true, null]],
        395 => [
            [['_route' => '_api_validation_errors_problem', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_problem'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_validation_errors_hydra', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_hydra'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_validation_errors_jsonapi', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_jsonapi'], ['id'], ['GET' => 0], null, false, true, null],
        ],
        451 => [
            [['_route' => '_api_/user_courses/{id}{._format}_get', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\UserCourses', '_api_operation_name' => '_api_/user_courses/{id}{._format}_get'], ['id', '_format'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_/user_courses/{id}{._format}_put', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\UserCourses', '_api_operation_name' => '_api_/user_courses/{id}{._format}_put'], ['id', '_format'], ['PUT' => 0], null, false, true, null],
        ],
        475 => [[['_route' => '_api_/user_courses{._format}_post', '_controller' => 'api_platform.symfony.main_controller', '_format' => null, '_stateless' => true, '_api_resource_class' => 'App\\Entity\\UserCourses', '_api_operation_name' => '_api_/user_courses{._format}_post'], ['_format'], ['POST' => 0], null, false, true, null]],
        497 => [
            [['_route' => 'api_user_show', '_controller' => 'App\\Controller\\UserController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'api_user_edit', '_controller' => 'App\\Controller\\UserController::edit'], ['id'], ['PUT' => 0], null, false, true, null],
            [['_route' => 'api_user_delete', '_controller' => 'App\\Controller\\UserController::delete'], ['id'], ['DELETE' => 0], null, false, true, null],
        ],
        537 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        573 => [[['_route' => 'app_chapter_edit', '_controller' => 'App\\Controller\\ChapterController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        581 => [
            [['_route' => 'app_chapter_show', '_controller' => 'App\\Controller\\ChapterController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'app_chapter_delete', '_controller' => 'App\\Controller\\ChapterController::delete'], ['id'], ['POST' => 0], null, false, true, null],
        ],
        616 => [[['_route' => 'app_courses_edit', '_controller' => 'App\\Controller\\CoursesController::edit'], ['id'], ['GET' => 0, 'POST' => 1], null, false, false, null]],
        624 => [
            [['_route' => 'app_courses_show', '_controller' => 'App\\Controller\\CoursesController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'app_courses_delete', '_controller' => 'App\\Controller\\CoursesController::delete'], ['id'], ['POST' => 0], null, false, true, null],
        ],
        655 => [[['_route' => 'app_courses_chapters', '_controller' => 'App\\Controller\\CoursesListController::chapters'], ['id'], null, null, false, false, null]],
        689 => [[['_route' => 'app_react_course', '_controller' => 'App\\Controller\\ReactController::courseChapter'], ['courseId', 'chapterId'], ['GET' => 0, 'POST' => 1], null, false, true, null]],
        721 => [[['_route' => 'app_follow_courses', '_controller' => 'App\\Controller\\UserCoursesController::follow'], ['courseId'], null, null, false, true, null]],
        736 => [
            [['_route' => 'app_unfollow_courses', '_controller' => 'App\\Controller\\UserCoursesController::remove'], ['courseId'], null, null, false, false, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
