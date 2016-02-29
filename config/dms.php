<?php

return [

    'auth' => [
        'login' => [
            'max-attempts' => 999,
            'lockout-time' => 60,
        ],
    ],

    'contact' => [
        'website' => 'http://contact-us...',
        'company' => 'Company Inc.',
    ],

    'storage' => [
        'temp-files' => [
            'dir'             => storage_path('dms/temp-uploads/'),
            'upload-expiry'   => 3600,
            'download-expiry' => 3600,
        ],
    ],

    'services' => [
        'actions' => [
            'input-transformers' => [
                Dms\Web\Laravel\Action\InputTransformer\SymphonyToDmsUploadedFileTransformer::class,
                Dms\Web\Laravel\Action\InputTransformer\TempUploadedFileToUploadedFileTransformer::class,
            ],
            'result-handlers'    => [
                Dms\Web\Laravel\Action\ResultHandler\ViewDetailsResultHandler::class,
                Dms\Web\Laravel\Action\ResultHandler\CreatedEntityResultHandler::class,
                Dms\Web\Laravel\Action\ResultHandler\EditedEntityResultHandler::class,
                Dms\Web\Laravel\Action\ResultHandler\DeletedEntityResultHandler::class,
                Dms\Web\Laravel\Action\ResultHandler\NullResultHandler::class,
                Dms\Web\Laravel\Action\ResultHandler\MessageResultHandler::class,
                Dms\Web\Laravel\Action\ResultHandler\FileResultHandler::class,
            ],
            'exception-handlers' => [
                Dms\Web\Laravel\Action\ExceptionHandler\UserForbiddenExceptionHandler::class,
                Dms\Web\Laravel\Action\ExceptionHandler\InvalidFormSubmissionExceptionHandler::class,
                Dms\Web\Laravel\Action\ExceptionHandler\EntityOutOfSyncExceptionHandler::class,
            ],
        ],

        'renderers' => [
            'form-fields' => [
                Dms\Web\Laravel\Renderer\Form\Field\AddressFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\ArrayOfFilesFieldsRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\ArrayOfOptionsFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\ArrayOfFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\BoolFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\DateOrTimeFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\DateOrTimeRangeFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\DecimalFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\InnerFormFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\IntFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\FileFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\RadioOptionsFieldRender::class,
                Dms\Web\Laravel\Renderer\Form\Field\SelectOptionsFieldRender::class,
                Dms\Web\Laravel\Renderer\Form\Field\RgbaColourFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\RgbColourFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\StringFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\TextareaFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\WysiwygFieldRenderer::class,
            ],
            'table'       => [
                'columns'           => [
                    Dms\Web\Laravel\Renderer\Table\Column\DefaultColumnRendererAndFactory::class,
                ],
                'column-components' => [
                    Dms\Web\Laravel\Renderer\Table\Column\Component\OptimizedScalarValueComponentRenderer::class,
                    // Will default to field renderers
                ],
            ],
            'charts'      => [
                Dms\Web\Laravel\Renderer\Chart\GraphChartRenderer::class,
                Dms\Web\Laravel\Renderer\Chart\PieChartRenderer::class,
            ],
            'widgets'     => [
                Dms\Web\Laravel\Renderer\Widget\UnparameterizedActionWidgetRenderer::class,
                Dms\Web\Laravel\Renderer\Widget\ParameterizedActionWidgetRenderer::class,
                Dms\Web\Laravel\Renderer\Widget\TableWidgetRenderer::class,
                Dms\Web\Laravel\Renderer\Widget\ChartWidgetRenderer::class,
            ],
            'modules'     => [
                Dms\Web\Laravel\Renderer\Module\ReadModuleRenderer::class,
                Dms\Web\Laravel\Renderer\Module\DefaultModuleRenderer::class,
            ],
            'packages'    => [
                Dms\Web\Laravel\Renderer\Package\DefaultPackageRenderer::class,
            ],
        ],
    ],

    'actions' => [
        'safe' => [
            Dms\Core\Common\Crud\Action\Crud\ViewDetailsAction::class,
        ],
    ],

    'keywords' => [
        'danger'    => ['delete', 'remove', 'trash', 'drop', 'cancel', 'reset'],
        'success'   => ['confirm', 'approve', 'accept', 'verify'],
        'info'      => ['download', 'stats', 'display', 'details', 'view'],
        'primary'   => ['edit'],
        'overrides' => [
            'example-name' => 'danger',
        ],
    ],

    'front-end' => [
        'global' => [
            'stylesheets' => [
                'vendor/dms/css/all.css',
            ],
            'scripts'     => [
                'vendor/dms/js/all.js',
            ],
        ],
        'forms' => [
            'stylesheets' => [
                'vendor/dms/wysiwyg/wysiwyg.css',
            ],
            'scripts'     => [
                'vendor/dms/wysiwyg/wysiwyg.js',
                'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places',
            ],
        ],
        'tables' => [
            'stylesheets' => [
                //
            ],
            'scripts'     => [
                //
            ],
        ],
        'charts' => [
            'stylesheets' => [
                //
            ],
            'scripts'     => [
                //
            ],
        ],
    ],
];