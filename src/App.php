<?php

namespace Villermen\Toolbox;

class App
{
    private Config $config;

    private Session $session;

    private Authentication $authentication;

    private ProfileService $profileService;

    private ?Profile $authenticatedProfile = null;


    public function __construct()
    {
        chdir(__DIR__ . '/../');

        $this->config = Config::load();
        $this->session = Session::start($this->config);
        $this->profileService = new ProfileService();
        $this->authentication = new Authentication($this->config, $this->session, $this->profileService);
    }

    public function getAuthenticatedProfile(): ?Profile
    {
        if (!$this->authenticatedProfile) {
            $profileId = $this->session->get(Authentication::SESSION_KEY_PROFILE);
            $this->authenticatedProfile = ($profileId ? $this->profileService->loadProfile($profileId) : null);
        }

        return $this->authenticatedProfile;
    }

    public function saveProfile(Profile $profile): void
    {
        $this->profileService->saveProfile($profile);
    }

    public function authenticate(): string
    {
        return $this->authentication->authenticate();
    }

    public function logout(): string
    {
        return $this->authentication->logout();
    }

    public function createUrl(string $path, ?array $query = null): string
    {
        return $this->config->createUrl($path, $query);
    }

    public function createPath(string $path, ?array $query = null): string
    {
        return $this->config->createPath($path, $query);
    }

    // TODO: Turn into service.
    public function addFlashMessage(string $color, string $message): void
    {
        $flashMessages = $this->session->get('flashMessages') ?? [];
        $flashMessages[] = ['color' => $color, 'message' => $message];
        $this->session->set('flashMessages', $flashMessages);
    }

    /**
     * @return array{color: string, message: string}[]
     */
    public function popFlashMessages(): array
    {
        $flashMessages = $this->session->get('flashMessages') ?? [];
        $this->session->set('flashMessages', null);
        return $flashMessages;
    }

    public function renderView(string $template, array $parameters = []): string
    {
        extract($parameters);
        ob_start();
        require(sprintf('view/%s', $template));
        return ob_get_clean();
    }
}
