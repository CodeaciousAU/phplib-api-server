<?php

use Codeacious\ApiServer\Authorization\Adapter\ScopeAclAdapter;
use Codeacious\ApiServer\Authorization\Adapter\ScopeAclAdapterFactory;
use Codeacious\ApiServer\Console\Command;
use Codeacious\ApiServer\Controller;
use Codeacious\ApiServer\Controller\Plugin as ControllerPlugin;
use Codeacious\ApiServer\Listener;
use Codeacious\ApiServer\View\HalJsonStrategy;
use Laminas\Http\Request;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'api' => [
        'allowed_methods' => [
            Controller\SpecController::class => [
                Request::METHOD_GET,
            ],
        ],
        'modules' => [
            /* EXAMPLE
            'CoreApi' => [
                'versions' => [
                    1 => [
                        'models' => [
                            \Entities\User::class => V1\UserMapper::class,
                        ],
                        'routes' => [
                            'api.core/rest.users' => V1\UserMapper::class,
                        ],
                    ],
                ],
                'model_mapper_manager' => [
                    'factories' => [
                        V1\UserMapper::class => V1\UserMapperFactory::class,
                    ],
                ],
            ],
            */
        ],
        'scope_acl' => [
            /* EXAMPLE
            'core:identity' => [
                'routes' => [
                    'api.core/rest.users',
                ],
            ],
            */
        ],
    ],
    'console' => [
        'commands' => [
            'factories' => [
                Command\GenerateDocs::class => Command\GenerateDocsFactory::class,
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\ErrorController::class => InvokableFactory::class,
            Controller\SpecController::class => InvokableFactory::class,
        ],
    ],
    'controller_plugins' => [
        'aliases' => [
            'halCollectionBuilder' => ControllerPlugin\HalCollectionBuilder::class,
            'restModel' => ControllerPlugin\RestModel::class,
            'toHal' => ControllerPlugin\ToHal::class,
            'validationErrorsWithContext' => ControllerPlugin\ValidationErrorsWithContext::class,
        ],
        'factories' => [
            ControllerPlugin\HalCollectionBuilder::class => InvokableFactory::class,
            ControllerPlugin\RestModel::class => InvokableFactory::class,
            ControllerPlugin\ToHal::class => InvokableFactory::class,
            ControllerPlugin\ValidationErrorsWithContext::class => InvokableFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            HalJsonStrategy::class => InvokableFactory::class,
            Listener\AllowedMethodsListener::class => Listener\AllowedMethodsListenerFactory::class,
            Listener\CorsListener::class => Listener\CorsListenerFactory::class,
            Listener\ExceptionObfuscationListener::class => Listener\ExceptionObfuscationListenerFactory::class,
            Listener\InjectMapperListener::class => Listener\InjectMapperListenerFactory::class,
            Listener\TransformResultListener::class => Listener\TransformResultListenerFactory::class,
            Listener\VersionListener::class => InvokableFactory::class,
        ],
    ],
    'authorization' => [
        'adapters' => [
            'factories' => [
                ScopeAclAdapter::class => ScopeAclAdapterFactory::class,
            ],
        ],
    ],
    'view_manager' => [
        'strategies' => [
            HalJsonStrategy::class,
        ],
    ],
    'listeners' => [
        Listener\AllowedMethodsListener::class,
        Listener\CorsListener::class,
        Listener\ExceptionObfuscationListener::class,
        Listener\InjectMapperListener::class,
        Listener\TransformResultListener::class,
        Listener\VersionListener::class,
    ],
];