<?php

namespace Villermen\Toolbox\Work;

use League\OAuth2\Client\Grant\AuthorizationCode;
use League\OAuth2\Client\Provider\Google;
use Symfony\Component\Yaml\Yaml;

class WorkApp
{
    private string $publicPath;

    private string $publicProtocol;

    private string $publicUrl;

    private array $config;

    private ?array $authenticatedProfile = null;

    public function __construct()
    {
        $this->loadConfig();
        $this->startSession();
    }

    public function getAuthenticatedProfile(): ?array
    {
        if (!$this->authenticatedProfile) {
            $this->authenticatedProfile = (isset($_SESSION['auth']) ? $this->loadProfile($_SESSION['auth']) : null);
        }

        return $this->authenticatedProfile;
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticate(): string
    {
        $provider = new Google([
            'clientId' => $this->config['googleClientId'],
            'clientSecret' => $this->config['googleClientSecret'],
            'redirectUri' => sprintf('%soauth.php', $this->publicUrl),
        ]);

        if (!empty($_GET['error'])) {
            throw new AuthenticationException(sprintf('Google error: %s', $_GET['error']));
        }

        if (empty($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['authstate'] = $provider->getState(); // Note: Becomes available after retrieving authUrl.
            return $authUrl;
        }
        
        if (empty($_SESSION['authstate']) || ($_GET['state'] !== $_SESSION['authstate'])) {
            unset($_SESSION['authstate']);
            throw new AuthenticationException('Invalid state.');
        }

        $token = $provider->getAccessToken(new AuthorizationCode(), [
            'code' => $_GET['code']
        ]);

        try {
            /** @var GoogleUser $ownerDetails */
            $ownerDetails = $provider->getResourceOwner($token);

            $auth = sha1(sprintf('google:%s', $ownerDetails->getId()));
            $this->mergeProfile($auth, [
                'user' => [
                    'type' => 'google',
                    'id' => $ownerDetails->getId(),
                    'email' => $ownerDetails->getEmail(),
                    'name' => $ownerDetails->getName(),
                    'imageUrl' => $ownerDetails->getAvatar(),
                    // Refresh token currently not needed. Can be fixed by adding 'accessType' => 'offline'.
                    // 'refreshToken' => $token->getRefreshToken(),
                ],
            ]);

            $_SESSION['auth'] = $auth;
            unset($_SESSION['authstate']);
            return $this->publicUrl;
        } catch (\Exception $e) {
            throw new AuthenticationException('Error retrieving user details.', 0, $e);
        }
    }

    private function loadConfig(): void
    {
        chdir(__DIR__ . '/../../');

        $this->publicPath = mb_substr($_SERVER['REQUEST_URI'], 0, mb_strrpos($_SERVER['REQUEST_URI'], '/') + 1);
        $this->publicProtocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
        $this->publicUrl = sprintf(
            '%s://%s%s',
            $this->publicProtocol,
            $_SERVER['HTTP_HOST'],
            $this->publicPath,
        );

        $this->config = Yaml::parseFile('config/secrets.yaml');
    }

    private function startSession(): void
    {
        $sessionName = 'WORKSESSION';
        // Note: Using /tmp requires PrivateTmp=false in apache2 unit or session will be cleared on restart.
        $sessionPath = sprintf('%s/%s', sys_get_temp_dir(), $sessionName);
        if (!file_exists($sessionPath)) {
            mkdir($sessionPath);
        }
        // Refresh session lifetime every visit.
        if ($_COOKIE[$sessionName] ?? null) {
            session_id($_COOKIE[$sessionName]);
        }
        session_start([
            'save_path' => $sessionPath,
            'name' => $sessionName,
            'gc_maxlifetime' => 604800, // week
            'cookie_lifetime' => 604800,
            'cookie_path' => $this->publicPath,
            'cookie_httponly' => true,
            'cookie_secure' => $this->publicProtocol === 'https',
        ]);
    }

    private function loadProfile(string $auth): ?array
    {
        $file = @file_get_contents(sprintf('data/profile-%s.json', $auth));
        if (!$file) {
            return null;
        }

        return json_decode($file, true);
    }

    private function mergeProfile(string $auth, array $profileDelta): void
    {
        $userData = $this->loadProfile($auth) ?? [];
        $userData = array_merge($userData, $profileDelta); // TODO: Recursive? (similar to ArrayUtils::merge())

        if (!file_put_contents(sprintf('data/profile-%s.json', $auth), json_encode($userData))) {
            throw new \Exception('Failed to save profile data.');
        }
    }
}
