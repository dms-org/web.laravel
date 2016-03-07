<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Document;

use Dms\Common\Structure\DateTime\DateTime;
use Dms\Common\Structure\Field;
use Dms\Common\Structure\FileSystem\File;
use Dms\Common\Structure\FileSystem\PathHelper;
use Dms\Common\Structure\FileSystem\RelativePathCalculator;
use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Common\Crud\CrudModule;
use Dms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Dms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Dms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\File\IFile;
use Dms\Core\File\IUploadedFile;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Builder\StagedForm;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Web\Laravel\Util\FileSizeFormatter;

/**
 * The public file module.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PublicFileModule extends CrudModule
{
    /**
     * @var mixed
     */
    protected $rootDirectory;

    /**
     * @var RelativePathCalculator
     */
    protected $relativePathCalculator;

    /**
     * @var DirectoryTree
     */
    protected $directoryTree;

    public function __construct(DirectoryTree $directory, IAuthSystem $authSystem)
    {
        $this->rootDirectory          = $directory->directory->getFullPath();
        $this->directoryTree          = $directory;
        $this->relativePathCalculator = new RelativePathCalculator();

        parent::__construct($this->directoryTree->getAllFiles(), $authSystem);
    }

    /**
     * @return DirectoryTree
     */
    public function getDirectoryTree()
    {
        return $this->directoryTree;
    }

    /**
     * @return mixed
     */
    public function getRootDirectory()
    {
        return $this->rootDirectory;
    }

    /**
     * @param DirectoryTree $directory
     *
     * @return PublicFileModule
     */
    public function forDirectory(DirectoryTree $directory) : self
    {
        return new self($directory, $this->authSystem);
    }


    protected function getRelativePath(string $path) : string
    {
        return $this->relativePathCalculator->getRelativePath($this->rootDirectory, $path);
    }

    /**
     * Defines the structure of this module.
     *
     * @param CrudModuleDefinition $module
     */
    protected function defineCrudModule(CrudModuleDefinition $module)
    {
        $module->name('files');

        $module->labelObjects()->fromCallback(function (File $file) {
            return $this->relativePathCalculator->getRelativePath($this->rootDirectory, $file->getFullPath());
        });

        $module->action('upload-files')
            ->authorizeAll([self::VIEW_PERMISSION, self::EDIT_PERMISSION])
            ->form(Form::create()->section('Upload Files', [
                Field::create('folder', 'Folder')->string()->required()
                    ->oneOf($this->getAllDirectoryOptions()),
                Field::create('files', 'Files')->arrayOf(
                    Field::element()->file()->required()
                )->required(),
            ]))
            ->handler(function (ArrayDataObject $input) {
                foreach ($input['files'] as $file) {
                    /** @var IUploadedFile $file */
                    $file->moveTo(PathHelper::combine($this->rootDirectory, $input['folder'], $file->getClientFileNameWithFallback()));
                }
            });

        $module->action('create-folder')
            ->authorizeAll([self::VIEW_PERMISSION, self::EDIT_PERMISSION])
            ->form(Form::create()->section('Create Folder', [
                Field::create('folder', 'Folder')->string()->required(),
            ]))
            ->handler(function (ArrayDataObject $input) {
                @mkdir(PathHelper::combine($this->rootDirectory, $input['folder']), 0644, true);
            });

        $module->crudForm(function (CrudFormDefinition $form) {
            $form->dependentOnObject(function (CrudFormDefinition $form, File $file = null) {
                $directoryPath = $file
                    ? $this->getRelativePath($file->getDirectory()->getFullPath())
                    : null;

                $form->section('Details', [
                    $form->field(
                        Field::create('directory', 'Directory')->string()->required()->value($directoryPath)
                    )->withoutBinding(),
                ]);
            }, ['directory']);

            $form->dependentOn(['directory'], function (CrudFormDefinition $form, array $input, File $file = null) {
                $directoryPath = PathHelper::combine($this->rootDirectory, $input['directory']);

                $form->section('File', [
                    $form->field(
                        Field::create('file', 'File')->file()->required()->value($file)
                            ->moveToPathWithClientsFileName($directoryPath)
                    )->withoutBinding(),
                ]);
            });

            $form->dependentOnObject(function (CrudFormDefinition $form, File $file) {
                $form->section('Details', [
                    $form->field(
                        Field::create('created_at', 'Created At')->dateTime()->readonly()->value(
                            new DateTime(new \DateTimeImmutable('@' . $file->getInfo()->getCTime()))
                        )
                    )->withoutBinding(),
                    $form->field(
                        Field::create('modified_at', 'Modified At')->dateTime()->readonly()->value(
                            new DateTime(new \DateTimeImmutable('@' . $file->getInfo()->getMTime()))
                        )
                    )->withoutBinding(),
                ]);
            });

            $form->createObjectType()->fromCallback(function (array $input) : File {
                return $input['file'];
            });

            $form->onSave(function (File $file, array $input) use ($form) {
                if ($form->isEditForm()) {
                    $fullPath = PathHelper::combine($this->rootDirectory, $input['directory'], $file->getName());

                    if ($input['file'] !== $file) {
                        @unlink($file->getFullPath());
                        /** @var IFile $file */
                        $file = $input['file'];
                    } elseif ($file->getFullPath() !== $fullPath) {
                        $file->moveTo($fullPath);
                    }
                }
            });
        });

        $module->objectAction('download')
            ->authorize(self::VIEW_PERMISSION)
            ->returns(File::class)
            ->handler(function (File $file) : File {
                return $file;
            });

        $module->objectAction('move-folder')
            ->authorizeAll([self::VIEW_PERMISSION, self::EDIT_PERMISSION])
            ->form(function (StagedForm $form) {
                return $form->then(function (array $input) {
                    return Form::create()->section('Details', [
                        Field::create('new_folder', 'New Folder')->string()->required()
                            ->oneOf($this->getAllDirectoryOptions())
                            ->value($this->getRelativePath($input['object']->getDirectory()->getFullPath())),
                    ]);
                });
            })
            ->handler(function (File $file, ArrayDataObject $input) {
                $file->moveTo(PathHelper::combine($this->rootDirectory, $input['new_folder'], $file->getName()));
            });

        $module->removeAction()->handler(function (File $file) {
            @unlink($file->getFullPath());
        });

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->mapCallback(function (File $file) {
                return $file;
            })->to(Field::create('preview', 'Preview')->file());

            $table->mapProperty(File::CLIENT_FILE_NAME)->to(Field::create('name', 'Name')->string());

            $table->mapCallback(function (File $file) {
                return FileSizeFormatter::formatBytes($file->getSize());
            })->to(Field::create('size', 'File Size')->string());

            $table->view('all', 'All')
                ->asDefault()
                ->loadAll();
        });
    }

    private function getAllDirectoryOptions() : array
    {
        $options = ['/' => 'home'];

        foreach ($this->directoryTree->getAllDirectories() as $directory) {
            $path           = $this->getRelativePath($directory->getFullPath());
            $options[$path] = $path;
        }

        return $options;
    }

    protected function loadCrudModuleWithDataSource(IMutableObjectSet $dataSource) : ICrudModule
    {
        throw NotImplementedException::method(__METHOD__);
    }
}