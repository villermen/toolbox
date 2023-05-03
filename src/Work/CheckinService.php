<?php

namespace Villermen\Toolbox\Work;

use Villermen\Toolbox\Profile;
use Webmozart\Assert\Assert;

class CheckinService
{
    private const DOUBLE_SCAN_THRESHOLD = 60;

    /**
     * Amount of seconds before a missing checkout on the previous day is no longer considered a late-night work shift
     * but a mistake.
     */
    private const MISSED_CHECKOUT_THRESHOLD = 6 * 3600;

    public function addCheckin(Profile $profile, \DateTimeInterface $time): Workday
    {
        $time = \DateTime::createFromInterface($time);
        $time->setTimezone($profile->getTimezone());

        $workday = $profile->getOrCreateWorkday($time);
        $previousWorkday = $profile->getOrCreateWorkday(\DateTime::createFromInterface($time)->modify('-1 day'));

        // Add end-of-day and start-of-day checkin when checkin spans midnight. Leaves missed checkouts (when
        // time difference is too great).
        $previousIncompleteStart = $previousWorkday->getIncompleteRange()?->getStart();
        if (
            $workday->isComplete() &&
            $previousIncompleteStart &&
            $time->getTimestamp() - $previousIncompleteStart->getTimestamp() <= self::MISSED_CHECKOUT_THRESHOLD
        ) {
            $dayEnd = \DateTime::createFromInterface($previousIncompleteStart);
            $dayEnd->setTime(23, 59, 59);
            $dayStart = \DateTime::createFromInterface($time);
            $dayStart->setTime(0, 0);

            $previousWorkday->finishRange($dayEnd);
            $workday->addRange(new Workrange(WorkrangeType::WORK, $dayStart, null));
        }

        $incompleteRange = $workday->getIncompleteRange();
        if (!$incompleteRange) {
            $ranges = $workday->getRanges();
            $lastRange = (end($ranges) ?: null);
            if ($lastRange) {
                Assert::greaterThanEq(
                    $time->getTimestamp() - $lastRange->getEnd()->getTimestamp(),
                    self::DOUBLE_SCAN_THRESHOLD,
                    sprintf('Prevented double checkin within %s seconds.', self::DOUBLE_SCAN_THRESHOLD)
                );
            }

            // Check in
            $workday->addRange(new Workrange(WorkrangeType::WORK, $time, null));
        } else {
            // Check out
            Assert::greaterThanEq(
                $time->getTimestamp() - $incompleteRange->getStart()->getTimestamp(),
                self::DOUBLE_SCAN_THRESHOLD,
                sprintf('Prevented double checkout within %s seconds.', self::DOUBLE_SCAN_THRESHOLD)
            );

            // Auto break.
            [$breakStart, $breakEnd] = $profile->getAutoBreak($time);
            if ($breakStart && $incompleteRange->getStart() < $breakStart && $time > $breakEnd ) {
                $workday->finishRange($breakStart);
                $workday->addRange(new Workrange(WorkrangeType::WORK, $breakEnd, null));
            }

            $workday->finishRange($time);
        }

        return $workday;
    }

    public function addRange(Profile $profile, \DateTimeInterface $start, \DateTimeInterface $end): Workday
    {
        // TODO: Weird how this doesn't call $workday->addRange(), but otherwise auto break wouldn't trigger.
        $workday = $profile->getOrCreateWorkday($start);
        Assert::true($workday->isComplete());
        $this->addCheckin($profile, $start);
        $this->addCheckin($profile, $end);
        return $workday;
    }

    public function addFullDay(Profile $profile, \DateTimeInterface $date, WorkrangeType $type): Workday
    {
        $scheduleHours = $profile->getSchedule()[(int)$date->format('N') - 1];
        Assert::greaterThan($scheduleHours, 0, 'Can\'t add full day when schedule says you\'re not working that day.');

        $start = \DateTimeImmutable::createFromInterface($date)->setTime(9, 0);
        $end = $start->modify(sprintf('+ %s hours', $scheduleHours));
        $workday = $profile->getOrCreateWorkday($date);
        $workday->addRange(new Workrange($type, $start, $end));
        return $workday;
    }

    public function clearWorkday(Profile $profile, \DateTimeInterface $date): Workday
    {
        $workday = $profile->getOrCreateWorkday($date);
        $workday->clear();
        return $workday;
    }

    public function removeBreak(Profile $profile, \DateTimeInterface $date): Workday
    {
        $workday = $profile->getOrCreateWorkday($date);
        $ranges = $workday->getRanges();
        Assert::minCount($ranges, 2, 'No break detected on date.');

        $workday->clear();
        $workday->addRange(new Workrange($ranges[0]->getType(), $ranges[0]->getStart(), $ranges[1]->getEnd()));
        foreach (array_slice($ranges, 2) as $range) {
            $workday->addRange($range);
        }

        return $workday;
    }
}
