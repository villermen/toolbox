<?php

namespace Villermen\Toolbox\Work;

class Workday
{
    private \DateTimeImmutable $date;

    /** @var array{start: \DateTimeInterface, end: \DateTimeInterface|null}[] */
    private array $ranges;

    public function __construct(
        \DateTimeInterface $date,
        array $checkins,
    ) {
        $this->date = \DateTimeImmutable::createFromInterface($date);

        $this->ranges = [];
        for ($i = 0; $i < count($checkins); $i += 2) {
            $this->ranges[] = [
                'start' => $checkins[$i],
                'end' => ($checkins[$i + 1] ?? null),
            ];
        }
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
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
}
