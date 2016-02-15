<?php

namespace Dms\Web\Laravel\Tests\Unit\Action\ExceptionHandler;

use Dms\Core\Auth\UserForbiddenException;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionExceptionHandlerCollection;
use Dms\Web\Laravel\Action\ExceptionHandler\InvalidFormSubmissionExceptionHandler;
use Dms\Web\Laravel\Action\ExceptionHandler\UserForbiddenExceptionHandler;
use Dms\Web\Laravel\Action\UnhandleableActionExceptionException;
use Dms\Web\Laravel\Tests\Mock\Language\MockLanguageProvider;
use Dms\Web\Laravel\Tests\Unit\UnitTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionExceptionHandlerCollectionTest extends UnitTest
{
    /**
     * @var ActionExceptionHandlerCollection
     */
    protected $collection;

    public function setUp()
    {
        $this->collection = new ActionExceptionHandlerCollection([
            new InvalidFormSubmissionExceptionHandler(new MockLanguageProvider()),
            new UserForbiddenExceptionHandler(),
        ]);
    }

    public function testFindHandler()
    {
        $this->assertInstanceOf(
            InvalidFormSubmissionExceptionHandler::class,
            $this->collection->findHandlerFor($this->mockAction(), $this->getMockWithoutInvokingTheOriginalConstructor(InvalidFormSubmissionException::class))
        );

        $this->assertInstanceOf(
            UserForbiddenExceptionHandler::class,
            $this->collection->findHandlerFor($this->mockAction(), $this->getMockWithoutInvokingTheOriginalConstructor(UserForbiddenException::class))
        );
    }

    public function testUnhandleableException()
    {
        $this->expectException(UnhandleableActionExceptionException::class);

        $this->collection->findHandlerFor($this->mockAction(), new \Exception());
    }

    protected function mockAction() : IAction
    {
        return $this->getMockForAbstractClass(IAction::class);
    }
}