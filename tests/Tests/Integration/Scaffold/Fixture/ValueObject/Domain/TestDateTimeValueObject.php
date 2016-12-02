<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObject\Domain;

use Dms\Common\Structure\DateTime\Date;
use Dms\Common\Structure\DateTime\DateTime;
use Dms\Common\Structure\DateTime\TimeOfDay;
use Dms\Common\Structure\DateTime\TimezonedDateTime;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestDateTimeValueObject extends ValueObject
{
    const DATE_TIME = 'dateTime';
    const DATE = 'date';
    const TIME_OF_DAY = 'timeOfDay';
    const TIMEZONED_DATE_TIME = 'timezonedDateTime';

    /**
     * @var DateTime
     */
    public $dateTime;

    /**
     * @var Date
     */
    public $date;

    /**
     * @var TimeOfDay
     */
    public $timeOfDay;

    /**
     * @var TimezonedDateTime
     */
    public $timezonedDateTime;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->dateTime)->asObject(DateTime::class);

        $class->property($this->date)->asObject(Date::class);

        $class->property($this->timeOfDay)->asObject(TimeOfDay::class);

        $class->property($this->timezonedDateTime)->asObject(TimezonedDateTime::class);
    }
}