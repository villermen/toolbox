<?php

namespace Villermen\Toolbox;

class Profile
{
    public static function load(string $profileId): self
    {
        $data = @file_get_contents(self::getPath($profileId));
        $data = ($data ? json_decode($data, true) : null);

        return new self(
            $profileId,
            $data['auth'] ?? null,
            $data['settings'] ?? null,
            $data['checkins'] ?? null
        );
    }

    private static function getPath(string $profileId): string
    {
        return sprintf('data/profile-%s.json', $profileId);
    }

    private function __construct(private string $profileId, private ?array $auth, private ?array $settings, private ?array $checkins)
    {
    }

    public function setAuth(array $auth): void
    {
        $this->auth = $auth;
    }

    public function getAutoBreak(): bool
    {
        return ($this->settings['autoBreak'] ?? true);
    }

    public function setAutoBreak(bool $autoBreak): void
    {
        if ($autoBreak) {
            unset($this->settings['autoBreak']);
        } else {
            $this->settings['autoBreak'] = false;
        }
    }

    public function save(): void
    {
        $data = [
            'auth' => $this->auth,
            'settings' => $this->settings,
        ];

        if (!file_put_contents(self::getPath($this->profileId), json_encode($data))) {
            throw new \Exception('Failed to save profile data.');
        }
    }
}
