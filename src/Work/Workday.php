<?php

namespace Villermen\Toolbox\Work;

use Webmozart\Assert\Assert;

class Workday
{
    private \DateTimeImmutable $date;

    /** @var Workrange[] */
    private array $ranges = [];

    public function __construct(
        \DateTimeInterface $date,
    ) {
        $this->date = \DateTimeImmutable::createFromInterface($date);
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return Workrange[]
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    public function addRange(Workrange $range): void
    {
        Assert::true($this->isComplete(), 'Can\'t add range for incomplete day.');
        Assert::eq(
            $range->getStart()->format('Ymd'),
            $this->getDate()->format('Ymd'),
            'Range must be for same day as workday.'
        );
        $this->assertNoOverlap($range->getStart(), $range->getEnd());

        $this->ranges[] = $range;
        usort($this->ranges, fn (Workrange $range1, Workrange $range2): int => (
            $range1->getStart() <=> $range2->getStart()
        ));
    }

    public function finishRange(\DateTimeInterface $end): void
    {
        $incompleteRange = $this->getIncompleteRange();
        Assert::notNull($incompleteRange, 'Can\'t finish range of complete day.');
        $this->assertNoOverlap($incompleteRange->getStart(), $end);
        $incompleteRange->setEnd($end);
    }

    public function getTotalDuration(): int
    {
        $duration = 0;
        foreach ($this->getRanges() as $range) {
            $duration += $range->getDuration();
        }

        return $duration;
    }

    public function isComplete(): bool
    {
        return !$this->getIncompleteRange();
    }

    public function getIncompleteRange(): ?Workrange
    {
        foreach ($this->getRanges() as $range) {
            if (!$range->getEnd()) {
                return $range;
            }
        }

        return null;
    }

    public function clear(): void
    {
        $this->ranges = [];
    }

    private function assertNoOverlap(\DateTimeInterface $start, ?\DateTimeInterface $end): void
    {
        $end = ($end ?? $start);

        foreach ($this->getRanges() as $range) {
            Assert::true(
                $end < $range->getStart() || !$range->getEnd() || $start > $range->getEnd(),
                'Range overlaps with existing range.'
            );
        }
    }
}
