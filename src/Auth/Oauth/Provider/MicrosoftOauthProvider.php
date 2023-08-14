<?php

namespace Dms\Web\Laravel\Auth\Oauth\Provider;

use Dms\Common\Structure\Web\EmailAddress;
use Dms\Web\Laravel\Auth\Oauth\AdminAccountDetails;
use Dms\Web\Laravel\Auth\Oauth\OauthProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

class MicrosoftOauthProvider extends OauthProvider
{
    /**
     * @var Microsoft
     */
    protected $provider;

    /**
     * @param string $clientId
     * @param string $clientSecret
     *
     * @return AbstractProvider
     */
    protected function loadProvider(string $clientId, string $clientSecret) : AbstractProvider
    {
        return new \Stevenmaguire\OAuth2\Client\Provider\Microsoft([
            // Required
            'clientId'                  => $clientId,
            'clientSecret'              => $clientSecret,
            'redirectUri'               => route('dms::auth.oauth.response', $this->name),
            'enabled'   	=> '1',
            'directory' 	=> 'common',
            // Optional
            'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
        ]);
    }

    /**
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return AdminAccountDetails
     */
    public function getAdminDetailsFromResourceOwner(ResourceOwnerInterface $resourceOwner) : AdminAccountDetails
    {
        /** @var GoogleUser $resourceOwner */
        return new AdminAccountDetails(
            $resourceOwner->getFirstName() . ' ' . $resourceOwner->getLastName(),
            $resourceOwner->getEmail(),
            new EmailAddress($resourceOwner->getEmail())
        );
    }
}
