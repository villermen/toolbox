<?php

namespace Villermen\Toolbox;

use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use Villermen\Toolbox\Exception\AuthenticationException;

class Authentication
{
    public const SESSION_KEY_PROFILE = 'profileId';
    private const SESSION_KEY_REDIRECT = 'authredirect';
    private const SESSION_KEY_STATE = 'authstate';

    public function __construct(
        private Config $config,
        private Session $session,
        private ProfileService $profileService
    ) {
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticate(): string
    {
        if (!$this->config->googleClientId || !$this->config->googleClientSecret) {
            throw new AuthenticationException('Authentication not configured.');
        }

        $provider = new Google([
            'clientId' => $this->config->googleClientId,
            'clientSecret' => $this->config->googleClientSecret,
            'redirectUri' => $this->config->createUrl('auth.php'),
        ]);

        if (!empty($_GET['error'])) {
            throw new AuthenticationException(sprintf('Google error: %s', $_GET['error']));
        }

        if (empty($_GET['code'])) {
            if (empty($_GET['redirect']) || $_GET['redirect'][0] !== '/') {
                throw new AuthenticationException('Redirect not set or invalid.');
            }

            $authUrl = $provider->getAuthorizationUrl();
            $this->session->set(self::SESSION_KEY_REDIRECT, $_GET['redirect']);
            // Note: State becomes available after retrieving authUrl.
            $this->session->set(self::SESSION_KEY_STATE, $provider->getState());
            return $authUrl;
        }

        if (
            !$this->session->get(self::SESSION_KEY_STATE) ||
            !$this->session->get(self::SESSION_KEY_REDIRECT) ||
            $_GET['state'] !== $this->session->get(self::SESSION_KEY_STATE)
        ) {
            $this->session->set(self::SESSION_KEY_STATE, null);
            $this->session->set(self::SESSION_KEY_REDIRECT, null);
            throw new AuthenticationException('Invalid state.');
        }

        $token = $provider->getAccessToken(new AuthorizationCode(), [
            'code' => $_GET['code']
        ]);

        try {
            /** @var GoogleUser $ownerDetails */
            $ownerDetails = $provider->getResourceOwner($token);

            $profileId = sha1(sprintf('google:%s', $ownerDetails->getId()));
            $profile = $this->profileService->loadProfile($profileId);
            $profile->setAuth([
                'type' => 'google',
                'id' => $ownerDetails->getId(),
                'email' => $ownerDetails->getEmail(),
                'name' => $ownerDetails->getName(),
                'avatar' => $ownerDetails->getAvatar(),
                // Refresh token currently not needed. Can be fixed by adding 'accessType' => 'offline'.
                // 'refreshToken' => $token->getRefreshToken(),
            ]);
            $this->profileService->saveProfile($profile);

            $redirectUrl = $this->session->get(self::SESSION_KEY_REDIRECT);
            $this->session->set(self::SESSION_KEY_PROFILE, $profileId);
            $this->session->set(self::SESSION_KEY_STATE, null);
            $this->session->set(self::SESSION_KEY_REDIRECT, null);
            return $redirectUrl;
        } catch (\Exception $e) {
            throw new AuthenticationException('Error retrieving user details.', 0, $e);
        }
    }

    public function logout(): string
    {
        if (empty($_GET['redirect']) || $_GET['redirect'][0] !== '/') {
            throw new AuthenticationException('Redirect not set or invalid.');
        }

        $this->session->set(self::SESSION_KEY_PROFILE, null);

        return $_GET['redirect'];
    }
}
