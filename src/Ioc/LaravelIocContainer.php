<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Ioc;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Ioc\IIocContainer;
use Dms\Core\Util\Debug;
use Illuminate\Contracts\Container\Container;
use Monii\Interop\Container\Laravel\LaravelContainer;

/**
 * The laravel ioc container.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LaravelIocContainer extends LaravelContainer implements IIocContainer
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->container = $container;
    }


    /**
     * Binds the supplied class or interface to the supplied
     * concrete class name.
     *
     * @param string $scope
     * @param string $abstract
     * @param string $concrete
     *
     * @return void
     */
    public function bind(string $scope, string $abstract, string $concrete)
    {
        $this->validateScope(__METHOD__, $scope);

        if ($scope === self::SCOPE_INSTANCE_PER_RESOLVE) {
            $this->container->bind($abstract, $concrete);
        } else {
            $this->container->singleton($abstract, $concrete);
        }
    }

    /**
     * Binds the supplied class or interface to the return value
     * of the supplied callback.
     *
     * @param string   $scope
     * @param string   $abstract
     * @param callable $factory
     *
     * @return void
     */
    public function bindCallback(string $scope, string $abstract, callable $factory)
    {
        $this->validateScope(__METHOD__, $scope);

        if ($scope === self::SCOPE_INSTANCE_PER_RESOLVE) {
            $this->container->bind($abstract, $factory);
        } else {
            $this->container->singleton($abstract, $factory);
        }
    }

    /**
     * Binds the supplied abstract class or interface to the supplied value.
     *
     * @param string $abstract
     * @param mixed $concrete
     *
     * @return void
     */
    public function bindValue(string $abstract, $concrete)
    {
        $this->container->instance($abstract, $concrete);
    }

    private function validateScope(string $method, string $scope)
    {
        $validScopes = [self::SCOPE_INSTANCE_PER_RESOLVE, self::SCOPE_SINGLETON];

        if (!in_array($scope, $validScopes, true)) {
            throw InvalidArgumentException::format(
                'Invalid scope supplied to %s: expecting one of (%s), \'%s\' given',
                $method, Debug::formatValues($validScopes), $scope
            );
        }
    }
}