<?php

namespace Villermen\Toolbox;

use Villermen\Toolbox\Work\Workday;
use Villermen\Toolbox\Work\Workrange;
use Villermen\Toolbox\Work\WorkrangeType;
use Webmozart\Assert\Assert;

class Profile
{
    public static function load(string $profileId): self
    {
        $data = @file_get_contents(self::getPath($profileId));
        $data = ($data ? json_decode($data, true) : null);

        $profile = new self(
            $profileId,
            $data['auth'] ?? null,
            $data['settings'] ?? null,
        );

        // Migrate checkins.
        $checkins = ($data['checkins'] ?? []);
        if ($checkins) {
            foreach ($checkins as $checkin) {
                $checkinTime = new \DateTime(sprintf('@%s', $checkin));
                $checkinTime->setTimezone($profile->getTimezone());

                $workday = $profile->getWorkday($checkinTime);
                if (!$workday) {
                    $workday = new Workday($checkinTime);
                    $profile->addWorkday($workday);
                }

                $range = $workday->getIncompleteRange();
                if ($range) {
                    $range->setEnd($checkinTime);
                } else {
                    $range = new Workrange(WorkrangeType::WORK, $checkinTime, null);
                    $workday->addRange($range);
                }
            }
        }

        foreach ($data['workdays'] ?? [] as $date => $rangesData) {
            $ranges = [];
            foreach ($rangesData as $rangeData) {
                $type = WorkrangeType::from($rangeData[0]);
                $start = new \DateTime(sprintf('@%s', $rangeData[1]));
                $start->setTimezone($profile->getTimezone());
                $end = null;
                if ($rangeData[2]) {
                    $end = new \DateTime(sprintf('@%s', $rangeData[2]));
                    $end->setTimezone($profile->getTimezone());
                }

                $ranges[] = new Workrange($type, $start, $end);
            }

            $profile->addWorkday(new Workday(\DateTimeImmutable::createFromFormat('Ymd', $date, $profile->getTimezone())));
        }

        return $profile;
    }

    private static function getPath(string $profileId): string
    {
        return sprintf('data/profile-%s.json', $profileId);
    }

    /** @var array<string, Workday> */
    private array $workdays = [];

    private function __construct(
        private string $profileId,
        private ?array $auth,
        private ?array $settings,
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

    public function getWorkday(\DateTimeInterface $date): ?Workday
    {
        return ($this->getWorkdays()[$date->format('Ymd')] ?? null);
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

    public function save(): void
    {
        $workdays = [];
        foreach ($this->getWorkdays() as $date => $workday) {
            if (!$workday->getRanges()) {
                continue;
            }

            $workdays[$date] = array_map(fn (Workrange $workrange): array => [
                $workrange->getType()->value,
                $workrange->getStart()->getTimestamp(),
                $workrange->getEnd()?->getTimestamp(),
            ], $workday->getRanges());
        }

        $data = [
            'auth' => $this->auth,
            'settings' => $this->settings,
            'workdays' => (object)$workdays,
        ];

        if (!file_put_contents(self::getPath($this->profileId), json_encode($data))) {
            throw new \Exception('Failed to save profile data.');
        }
    }
}
