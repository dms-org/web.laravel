<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain;

use Dms\Common\Structure\FileSystem\File;
use Dms\Common\Structure\FileSystem\Image;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestFileValueObject extends ValueObject
{
    const FILE = 'file';
    const IMAGE = 'image';

    /**
     * @var File
     */
    public $file;

    /**
     * @var Image
     */
    public $image;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->file)->asObject(File::class);

        $class->property($this->image)->asObject(Image::class);
    }
}