<?php

namespace Villermen\Toolbox;

class App
{
    private Config $config;

    private Session $session;

    private Authentication $authentication;

    private ?Profile $authenticatedProfile = null;

    public function __construct()
    {
        chdir(__DIR__ . '/../');

        $this->config = Config::load();
        $this->session = Session::start($this->config);
        $this->authentication = new Authentication($this->config, $this->session);
    }

    public function getAuthenticatedProfile(): ?Profile
    {
        if (!$this->authenticatedProfile) {
            $profileId = $this->session->get(Authentication::SESSION_KEY_PROFILE);
            $this->authenticatedProfile = ($profileId ? Profile::load($profileId) : null);
        }

        return $this->authenticatedProfile;
    }

    public function authenticate(): string
    {
        return $this->authentication->authenticate();
    }

    public function createUrl(string $path, ?array $query = null): string
    {
        return $this->config->createUrl($path, $query);
    }

    public function createPath(string $path, ?array $query = null): string
    {
        return $this->config->createPath($path, $query);
    }
}
