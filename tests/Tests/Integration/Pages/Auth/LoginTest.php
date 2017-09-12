<?php

namespace Dms\Web\Laravel\Tests\Integration\Pages\Auth;

use Dms\Web\Laravel\Tests\Integration\CmsIntegrationTest;
use Dms\Web\Laravel\Tests\Integration\Fixtures\Demo\DemoFixture;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoginTest extends CmsIntegrationTest
{
    protected static function getFixture()
    {
        return new DemoFixture();
    }
    
    public function testLoginPageShowsForm()
    {
        $response = $this->call('GET', route('dms::auth.login'));

        $response->assertStatus(200);
    }

    public function testInvalidLoginAttempt()
    {
        $this->call('GET', route('dms::auth.login'));
        $response = $this->call('POST', route('dms::auth.login'));

        $response->assertRedirect(route('dms::auth.login'));
        $response->assertSessionHasErrors(['username', 'password']);
    }

    public function testBeingLoggedAndGoingToLoginPageRedirectsToIndex()
    {
        $this->actingAsUser();

        $response = $this->call('GET', route('dms::auth.login'));
        $response->assertRedirect(route('dms::index'));
    }

    public function testInvalidCredentials()
    {
        $this->call('GET', route('dms::auth.login'));
        $response = $this->call('POST', route('dms::auth.login'), [
                'username' => 'test',
                'password' => 'test',
        ]);

        $response->assertRedirect(route('dms::auth.login'));
        $response->assertSessionHasErrors(['username' => trans('dms::auth.failed')]);
    }

    public function testValidCredentialsLogsIn()
    {
        $response = $this->call('POST', route('dms::auth.login'), [
                'username' => 'admin',
                'password' => 'admin',
        ]);

        $response->assertRedirect(route('dms::index'));
    }
}
