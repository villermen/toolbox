<?php

namespace Villermen\Toolbox;

use Villermen\Toolbox\Work\WorkSettings;
use Villermen\Toolbox\Work\Workday;
use Webmozart\Assert\Assert;

class Profile
{
    /** @var array<string, Workday> */
    private array $workdays = [];

    private ?WorkSettings $workSettings = null;

    public function __construct(
        private string $id,
        private ?array $auth,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAuth(): ?array
    {
        return $this->auth;
    }

    public function setAuth(array $auth): void
    {
        $this->auth = $auth;
    }

    public function getWorkSettings(): WorkSettings
    {
        if (!$this->workSettings) {
            $this->workSettings = WorkSettings::createDefault($this->getTimezone());
        }

        return $this->workSettings;
    }

    public function getOrCreateWorkday(\DateTimeInterface $date): Workday
    {
        $workday = ($this->getWorkdays()[$date->format('Ymd')] ?? null);
        if ($workday) {
            return $workday;
        }

        $workday = new Workday($date);
        $this->addWorkday($workday);
        return $workday;
    }

    /**
     * @return array<string, Workday>
     */
    public function getWorkdays(): array
    {
        return $this->workdays;
    }

    public function addWorkday(Workday $workday): void
    {
        $key = $workday->getDate()->format('Ymd');
        Assert::keyNotExists($this->workdays, $key);

        $this->workdays[$key] = $workday;
        ksort($this->workdays, SORT_NUMERIC);
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
}
