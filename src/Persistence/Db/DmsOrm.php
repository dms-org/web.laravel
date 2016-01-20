<?php

namespace Dms\Web\Laravel\Persistence\Db;

use Dms\Common\Structure\CommonOrm;
use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Web\Laravel\Auth\Persistence\AuthOrm;
use Dms\Web\Laravel\File\Persistence\TempFileOrm;

/**
 * The standard dms orm.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DmsOrm extends Orm
{
    /**
     * Defines the object mappers registered in the orm.
     *
     * @param OrmDefinition $orm
     *
     * @return void
     */
    protected function define(OrmDefinition $orm)
    {
        $orm->encompassAll([
            new CommonOrm(),
            new AuthOrm(),
            new TempFileOrm(),
        ]);
    }
}