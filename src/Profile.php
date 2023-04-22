<?php

namespace Villermen\Toolbox;

use DateTimeImmutable;
use DateTimeInterface;

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

    private function __construct(
        private string $profileId,
        private ?array $auth,
        private ?array $settings,
        /** @var int[] */
        private ?array $checkins
    ) {
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

    /**
     * @return \DateTimeInterface[]
     */
    public function getCheckins(): array
    {
        if (!$this->checkins) {
            return [];
        }

        return array_map(fn (int $checkin): \DateTimeImmutable => (
            new DateTimeImmutable(sprintf('@%s', $checkin))
        ), $this->checkins);
    }

    public function addCheckin(DateTimeInterface $time): void
    {
        $this->checkins[] = $time->getTimestamp();
    }

    public function getName(): ?string
    {
        return ($this->auth['name'] ?? null);
    }

    public function getAvatar(): ?string
    {
        return ($this->auth['avatar'] ?? null);
    }

    public function save(): void
    {
        $data = [
            'auth' => $this->auth,
            'settings' => $this->settings,
            'checkins' => $this->checkins,
        ];

        if (!file_put_contents(self::getPath($this->profileId), json_encode($data))) {
            throw new \Exception('Failed to save profile data.');
        }
    }
}
