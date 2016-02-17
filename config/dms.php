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
                Dms\Web\Laravel\Action\ResultHandler\NullResultHandler::class,
                Dms\Web\Laravel\Action\ResultHandler\MessageResultHandler::class,
                Dms\Web\Laravel\Action\ResultHandler\FileResultHandler::class,
            ],
            'exception-handlers' => [
                Dms\Web\Laravel\Action\ExceptionHandler\UserForbiddenExceptionHandler::class,
                Dms\Web\Laravel\Action\ExceptionHandler\InvalidFormSubmissionExceptionHandler::class,
            ],
        ],

        'renderers' => [
            'form-fields' => [
                Dms\Web\Laravel\Renderer\Form\Field\ArrayOfOptionsFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\ArrayOfFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\BoolFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\DateOrTimeFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\DateOrTimeRangeFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\DecimalFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\InnerFormFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\IntFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\RadioOptionsFieldRender::class,
                Dms\Web\Laravel\Renderer\Form\Field\SelectOptionsFieldRender::class,
                Dms\Web\Laravel\Renderer\Form\Field\RgbaColourFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\RgbColourFieldRenderer::class,
                Dms\Web\Laravel\Renderer\Form\Field\StringFieldRenderer::class,
            ],
            'table'       => [
                'columns'           => [
                    Dms\Web\Laravel\Renderer\Table\Column\DefaultColumnRendererAndFactory::class,
                ],
                'column-components' => [
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
                Dms\Web\Laravel\Renderer\Module\DefaultModuleRenderer::class,
                Dms\Web\Laravel\Renderer\Module\ReadModuleRenderer::class,
            ],
            'packages'    => [
                Dms\Web\Laravel\Renderer\Package\DefaultPackageRenderer::class,
            ],
        ],
    ],

    'keywords' => [
        'danger'    => ['delete', 'remove', 'trash', 'drop', 'cancel'],
        'success'   => ['confirm', 'approve', 'accept', 'verify'],
        'info'      => ['download', 'stats', 'display'],
        'overrides' => [
            'example-name' => 'danger',
        ],
    ],

    'front-end' => [
        'stylesheets' => [
            asset('vendor/dms/css/all.css'),
        ],
        'scripts'     => [
            asset('vendor/dms/js/all.js'),
        ],
    ],
];