<?php

namespace Villermen\Toolbox\Work;

use Webmozart\Assert\Assert;

class WorkSettings
{
    public static function createDefault(\DateTimeZone $timezone): self
    {
        return new self(
            $timezone,
            true,
            new \DateTimeImmutable('12:45', $timezone),
            new \DateTimeImmutable('13:15', $timezone),
            [8, 8, 8, 8, 8, 0, 0],
        );
    }

    private \DateTimeImmutable $autoBreakStart;

    private \DateTimeImmutable $autoBreakEnd;

    /** @var int[] */
    private array $schedule;

    /**
     * @param int[] $schedule
     */
    public function __construct(
        private \DateTimeZone $timezone,
        private bool $autoBreakEnabled,
        \DateTimeInterface $autoBreakStart,
        \DateTimeInterface $autoBreakEnd,
        array $schedule
    ) {
        $this->setAutoBreakRange($autoBreakStart, $autoBreakEnd);
        $this->setSchedule($schedule);
    }

    public function isAutoBreakEnabled(): bool
    {
        return $this->autoBreakEnabled;
    }

    public function setAutoBreakEnabled(bool $enabled): void
    {
        $this->autoBreakEnabled = $enabled;
    }

    public function setAutoBreakRange(\DateTimeInterface $start, \DateTimeInterface $end): void
    {
        $start = \DateTimeImmutable::createFromFormat('H:i', $start->format('H:i'), $this->timezone);
        $end = \DateTimeImmutable::createFromFormat('H:i', $end->format('H:i'), $this->timezone);
        Assert::notFalse($start, 'Auto break start must be a valid time.');
        Assert::notFalse($end, 'Auto break start must be a valid time.');
        Assert::lessThan($start, $end, 'Auto break start must be before end.');

        $this->autoBreakStart = $start;
        $this->autoBreakEnd = $end;
    }

    public function getAutoBreakStart(?\DateTimeInterface $date = null): \DateTimeImmutable
    {
        $date = ($date
            ? \DateTimeImmutable::createFromInterface($date)->setTimezone($this->timezone)
            : new \DateTimeImmutable('today', $this->timezone)
        );

        return $date->modify($this->autoBreakStart->format('H:i'));
    }

    public function getAutoBreakEnd(?\DateTimeInterface $date = null): \DateTimeImmutable
    {
        $date = ($date
            ? \DateTimeImmutable::createFromInterface($date)->setTimezone($this->timezone)
            : new \DateTimeImmutable('today', $this->timezone)
        );

        return $date->modify($this->autoBreakEnd->format('H:i'));
    }

    /**
     * @return int[]
     */
    public function getSchedule(): array
    {
        return $this->schedule;
    }

    /**
     * @param int[] $schedule
     */
    public function setSchedule(array $schedule): void
    {
        Assert::allInteger($schedule, 'Work schedule must consist of integer values.');
        Assert::allRange($schedule, 0, 12, 'Work schedule must consist of sane values.');
        // TODO: Assert::oneOf(count($schedule), [7, 14], 'Work schedule must contain 7 or 14 entries.');
        Assert::oneOf(count($schedule), [7], 'Work schedule must contain 7 entries. Support for 14 will be added _eventually_.');
        $this->schedule = $schedule;
    }
}