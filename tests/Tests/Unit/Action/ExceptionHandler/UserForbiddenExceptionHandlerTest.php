<?php

namespace Dms\Web\Laravel\Tests\Unit\Action\ExceptionHandler;

use Dms\Core\Auth\UserForbiddenException;
use Dms\Web\Laravel\Action\ExceptionHandler\UserForbiddenExceptionHandler;
use Dms\Web\Laravel\Action\IActionExceptionHandler;
use Illuminate\Http\JsonResponse;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UserForbiddenExceptionHandlerTest extends ExceptionHandlerTest
{
    protected function buildHandler() : IActionExceptionHandler
    {
        return new UserForbiddenExceptionHandler();
    }

    public function exceptionsHandlingTests() : array
    {
        return [
            [
                $this->mockAction(),
                $this->getMockForAbstractClass(UserForbiddenException::class, [], '', false),
                new JsonResponse([
                    'message' => 'The current account is forbidden from running this action',
                ], 403),
            ],
        ];
    }

    public function unhandleableExceptionTests() : array
    {
        return [
            [$this->mockAction(), new \Exception()],
        ];
    }

    protected function assertResponsesMatch($expected, $actual)
    {
        /** @var JsonResponse $expected */
        /** @var JsonResponse $actual */
        $this->assertEquals(json_decode($expected->content(), true), json_decode($actual->content(), true));
    }
}