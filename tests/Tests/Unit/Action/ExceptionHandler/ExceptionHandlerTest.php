<?php

namespace Dms\Web\Laravel\Tests\Unit\Action\ExceptionHandler;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\IActionExceptionHandler;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Tests\Unit\UnitTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ExceptionHandlerTest extends UnitTest
{
    /**
     * @var IActionExceptionHandler
     */
    protected $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->buildHandler();
    }

    abstract protected function buildHandler() : IActionExceptionHandler;

    abstract public function exceptionsHandlingTests() : array;

    abstract public function unhandleableExceptionTests() : array;

    protected function mockAction()
    {
        return $this->getMockForAbstractClass(IAction::class);
    }

    public function testAcceptException()
    {
        foreach ($this->exceptionsHandlingTests() as list($action, $exception, $response)) {
            $this->assertTrue($this->handler->accepts($this->mockModuleContext(), $action, $exception));
        }

        foreach ($this->unhandleableExceptionTests() as list($action, $exception)) {
            $this->assertFalse($this->handler->accepts($this->mockModuleContext(), $action, $exception));
        }
    }

    /**
     * @dataProvider unhandleableExceptionTests
     */
    public function testHandleThrowsOnInvalidException(IAction $action, \Exception $exception)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->handler->handle($this->mockModuleContext(),$action, $exception);
    }

    /**
     * @dataProvider exceptionsHandlingTests
     */
    public function testHandleException(IAction $action, \Exception $exception, $response)
    {
        $this->assertResponsesMatch(
            $response,
            $this->handler->handle($this->mockModuleContext(),$action, $exception)
        );
    }

    protected function assertResponsesMatch($expected, $actual)
    {
        $this->assertEquals($expected, $actual);
    }

    protected function mockModuleContext() : ModuleContext
    {
        return $this->createMock(ModuleContext::class);
    }
}