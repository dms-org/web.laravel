<?php

namespace Dms\Web\Laravel\Tests\Integration;

use Dms\Common\Structure\Web\EmailAddress;
use Dms\Core\ICms;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Web\Laravel\Auth\Password\HashedPassword;
use Dms\Web\Laravel\Auth\User;
use Dms\Web\Laravel\DmsServiceProvider;
use Dms\Web\Laravel\Tests\Integration\Fixtures\DmsFixture;
use Illuminate\Routing\RouteCollection;
use Orchestra\Testbench\TestCase;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class CmsIntegrationTest extends TestCase
{
    /**
     * @var DmsFixture
     */
    protected static $fixture;

    /**
     * @var bool
     */
    protected static $isSetUp = false;

    /**
     * @return DmsFixture
     * @throws \Exception
     */
    protected static function getFixture()
    {
        throw new \Exception('Please implement the ' . get_called_class() . '::' . __FUNCTION__ . ' method');
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$fixture = static::getFixture();
    }

    public function setUp()
    {
        parent::setUp();

        if (!static::$isSetUp) {
            static::$fixture->setUpBeforeClass($this->app);
            static::$isSetUp = true;
        }

        static::$fixture->setUp($this->app);
    }

    /**
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $routes = $app->make('router')->getRoutes();
        if ($routes instanceof RouteCollection) {
            $routes->refreshNameLookups();
        }

        return $app;
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [DmsServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->singleton(ICms::class, static::$fixture->getCmsClass());
        $app->singleton(IOrm::class, static::$fixture->getOrmClass());
    }

    /**
     * Resolve application HTTP exception handler.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', \Illuminate\Foundation\Exceptions\Handler::class);
    }

    protected function actingAsUser(User $user = null)
    {
        $this->actingAs($user ?: $this->getMockUser(), 'dms');
    }

    /**
     * @return User
     */
    protected function getMockUser()
    {
        return new User(
                new EmailAddress('test@test.com'),
                'admin',
                new HashedPassword('some-hash', 'algo', 10)
        );
    }
}