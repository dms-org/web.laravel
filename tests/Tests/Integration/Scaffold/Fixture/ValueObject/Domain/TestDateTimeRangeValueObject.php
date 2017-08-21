<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain;

use Dms\Common\Structure\DateTime\DateRange;
use Dms\Common\Structure\DateTime\DateTimeRange;
use Dms\Common\Structure\DateTime\TimeRange;
use Dms\Common\Structure\DateTime\TimezonedDateTimeRange;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestDateTimeRangeValueObject extends ValueObject
{
    const DATE_TIME_RANGE = 'dateTimeRange';
    const NULLABLE_DATE_TIME_RANGE = 'nullableDateTimeRange';
    const DATE_RANGE = 'dateRange';
    const NULLABLE_DATE_RANGE = 'nullableDateRange';
    const TIME_RANGE = 'timeRange';
    const NULLABLE_TIME_RANGE = 'nullableTimeRange';
    const TIMEZONED_DATE_TIME_RANGE = 'timezonedDateTimeRange';
    const NULLABLE_TIMEZONED_DATE_TIME_RANGE = 'nullableTimezonedDateTimeRange';

    /**
     * @var DateTimeRange
     */
    public $dateTimeRange;

    /**
     * @var DateTimeRange|null
     */
    public $nullableDateTimeRange;

    /**
     * @var DateRange
     */
    public $dateRange;

    /**
     * @var DateRange|null
     */
    public $nullableDateRange;

    /**
     * @var TimeRange
     */
    public $timeRange;

    /**
     * @var TimeRange|null
     */
    public $nullableTimeRange;

    /**
     * @var TimezonedDateTimeRange
     */
    public $timezonedDateTimeRange;

    /**
     * @var TimezonedDateTimeRange|null
     */
    public $nullableTimezonedDateTimeRange;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->dateTimeRange)->asObject(DateTimeRange::class);

        $class->property($this->nullableDateTimeRange)->nullable()->asObject(DateTimeRange::class);

        $class->property($this->dateRange)->asObject(DateRange::class);

        $class->property($this->nullableDateRange)->nullable()->asObject(DateRange::class);

        $class->property($this->timeRange)->asObject(TimeRange::class);

        $class->property($this->nullableTimeRange)->nullable()->asObject(TimeRange::class);

        $class->property($this->timezonedDateTimeRange)->asObject(TimezonedDateTimeRange::class);

        $class->property($this->nullableTimezonedDateTimeRange)->nullable()->asObject(TimezonedDateTimeRange::class);
    }
}