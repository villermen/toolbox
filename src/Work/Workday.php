<?php

namespace Villermen\Toolbox\Work;

use Villermen\Toolbox\Profile;

class Workday
{
    private \DateTimeImmutable $date;

    /** @var array{start: \DateTimeInterface, end: \DateTimeInterface|null}[] */
    private array $ranges;

    /**
     * @param \DateTimeInterface[] $checkins
     */
    public function __construct(
        private Profile $profile,
        \DateTimeInterface $date,
        private array $checkins,
    ) {
        $this->checkins = array_values($checkins);
        $this->date = \DateTimeImmutable::createFromInterface($date);

        $this->ranges = [];
        for ($i = 0; $i < count($this->checkins); $i += 2) {
            $this->ranges[] = [
                'start' => $this->checkins[$i],
                'end' => ($this->checkins[$i + 1] ?? null),
            ];
        }
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @return \DateTimeInterface[]
     */
    public function getCheckins(): array
    {
        return $this->checkins;
    }

    /**
     * @return array{start: \DateTimeInterface, end: \DateTimeInterface|null}[]
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    public function getTotalDuration(): int
    {
        $duration = 0;
        foreach ($this->ranges as ['start' => $start, 'end' => $end]) {
            if (!$end) {
                continue;
            }

            $duration += $end->getTimestamp() - $start->getTimestamp();
        }

        return $duration;
    }

    public function isComplete(): bool
    {
        return (count($this->getCheckins()) % 2 === 0);
    }

    public function getLastCheckin(): ?\DateTimeInterface
    {
        $checkins = $this->getCheckins();
        return (end($checkins) ?: null);
    }
}
