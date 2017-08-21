<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Persistence\Infrastructure;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IndependentValueObjectMapper;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestDateTimeRangeValueObject;


/**
 * The Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain\TestDateTimeRangeValueObject value object mapper.
 */
class TestDateTimeRangeValueObjectMapper extends IndependentValueObjectMapper
{
    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(TestDateTimeRangeValueObject::class);

        /* TODO: TestDateTimeRangeValueObject::DATE_TIME_RANGE */;

        /* TODO: TestDateTimeRangeValueObject::NULLABLE_DATE_TIME_RANGE */;

        /* TODO: TestDateTimeRangeValueObject::DATE_RANGE */;

        /* TODO: TestDateTimeRangeValueObject::NULLABLE_DATE_RANGE */;

        /* TODO: TestDateTimeRangeValueObject::TIME_RANGE */;

        /* TODO: TestDateTimeRangeValueObject::NULLABLE_TIME_RANGE */;

        /* TODO: TestDateTimeRangeValueObject::TIMEZONED_DATE_TIME_RANGE */;

        /* TODO: TestDateTimeRangeValueObject::NULLABLE_TIMEZONED_DATE_TIME_RANGE */;


    }
}