<?php

namespace Villermen\Toolbox;

use Webmozart\Assert\Assert;

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

    /**
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable}|null
     */
    public function getAutoBreak(?\DateTimeInterface $date = null): ?array
    {
        $date = ($date
            ? \DateTimeImmutable::createFromInterface($date)->setTimezone($this->getTimezone())
            : new \DateTimeImmutable('today', $this->getTimezone())
        );

        return [
            $date->modify('12:45'),
            $date->modify('13:15'),
        ];
    }

    // public function setAutoBreak(?\DateTimeInterface $start, ?\DateTimeInterface $end): void
    // {
    //     if ($start) {
    //         Assert::eq($start->format('Ymd'), $end->format('Ymd'));
    //         Assert::lessThan($start, $end);
    //         $this->settings['autoBreak'] = [$start->format('h:i'), $end->format('h:i')];
    //     } else {
    //         unset($this->settings['autoBreak']);
    //     }
    // }

    /**
     * @return \DateTimeImmutable[]
     */
    public function getCheckins(): array
    {
        if (!$this->checkins) {
            return [];
        }

        return array_map(fn (int $checkin): \DateTimeImmutable => (
            (new \DateTimeImmutable(sprintf('@%s', $checkin)))->setTimezone($this->getTimezone())
        ), $this->checkins);
    }

    public function addCheckin(\DateTimeInterface $time): void
    {
        $this->checkins[] = $time->getTimestamp();
        sort($this->checkins);
    }

    public function removeCheckin(\DateTimeInterface $time): void
    {
        $this->checkins = array_values(array_filter($this->checkins, fn (int $checkin): bool => (
            $checkin !== $time->getTimestamp()
        )));
    }

    public function getName(): ?string
    {
        return ($this->auth['name'] ?? null);
    }

    public function getAvatar(): ?string
    {
        return ($this->auth['avatar'] ?? null);
    }

    public function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone('Europe/Amsterdam');
    }

    /**
     * @return int[]
     */
    public function getSchedule(): array
    {
        return [8, 0, 8, 8, 8, 0, 0]; // TODO: What about 2-weekly?
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
