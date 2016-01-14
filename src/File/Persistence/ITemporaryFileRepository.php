<?php

namespace Dms\Web\Laravel\File\Persistence;

use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ISpecification;
use Dms\Core\Persistence\IRepository;
use Dms\Web\Laravel\File\TemporaryFile;

/**
 * The temporary file repository
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITemporaryFileRepository extends IRepository
{
    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function getAll();

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile
     */
    public function get($id);

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function getAllById(array $ids);

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile|null
     */
    public function tryGet($id);

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function tryGetAll(array $ids);

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function matching(ICriteria $criteria);

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function satisfying(ISpecification $specification);
}