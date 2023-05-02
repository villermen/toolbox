<?php

namespace Villermen\Toolbox\Work;

use Webmozart\Assert\Assert;

class Workrange
{
    private ?\DateTimeImmutable $start;

    private ?\DateTimeImmutable $end = null;

    public function __construct(
        private WorkrangeType $type,
        \DateTimeInterface $start,
        ?\DateTimeInterface $end,
    ) {
        $this->start = \DateTimeImmutable::createFromInterface($start);
        if ($end) {
            $this->setEnd($end);
        }
    }

    public function getType(): WorkrangeType
    {
        return $this->type;
    }

    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): void
    {
        Assert::eq($end->format('Ymd'), $this->getStart()->format('Ymd'));
        $this->end = \DateTimeImmutable::createFromInterface($end);
    }

    public function getDuration(): int
    {
        if (!$this->getEnd()) {
            return 0;
        }

        return ($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp());
    }
}
