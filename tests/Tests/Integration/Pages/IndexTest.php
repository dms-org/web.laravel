<?php

namespace Dms\Web\Laravel\Tests\Integration\Pages;

use Dms\Web\Laravel\Tests\Integration\CmsIntegrationTest;
use Dms\Web\Laravel\Tests\Integration\Fixtures\Demo\DemoFixture;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IndexTest extends CmsIntegrationTest
{
    protected static function getFixture()
    {
        return new DemoFixture();
    }

    public function testUnauthenticatedIndexRedirectsToLoginPage()
    {
        $response = $this->call('GET', route('dms::index'));

        $response->assertRedirect(route('dms::auth.login'));
    }

    public function testAuthenticatedIndexPageShowsDashboard()
    {
        $this->actingAsUser();

        $response = $this->call('GET', route('dms::index'));

        $response->assertSee('Dashboard');
    }
}