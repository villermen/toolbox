<?php

namespace Villermen\Toolbox;

use Villermen\Toolbox\Work\Workday;
use Webmozart\Assert\Assert;

class Profile
{
    /** @var array<string, Workday> */
    private array $workdays = [];

    public function __construct(
        private string $id,
        private ?array $auth,
        private ?array $settings,
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

    public function getSettings(): ?array
    {
        return $this->settings;
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

    /**
     * @return int[]
     */
    public function getSchedule(): array
    {
        return [8, 0, 8, 8, 8, 0, 0]; // TODO: What about 2-weekly?
    }
}
