<?php

namespace Villermen\Toolbox\Work;

use Villermen\Toolbox\Profile;
use Webmozart\Assert\Assert;

class CheckinService
{
    public function addCheckin(Profile $profile, \DateTimeInterface $time): bool
    {
        $time = \DateTime::createFromInterface($time);
        $time->setTimezone($profile->getTimezone());

        $checkins = $profile->getCheckins();

        $previousCheckin = null;
        $nextCheckinOnDay = null;
        foreach (array_reverse($checkins) as $checkin) {
            if ($checkin <= $time) {
                $previousCheckin = $checkin;
                break;
            } elseif ($checkin->format('Ymd') === $time->format('Ymd')) {
                $nextCheckinOnDay = $checkin;
            }
        }
        if ($nextCheckinOnDay) {
            $nextCheckinOnDay = \DateTime::createFromInterface($nextCheckinOnDay);
            $nextCheckinOnDay->setTimezone($profile->getTimezone());
        }

        if ($previousCheckin && !$nextCheckinOnDay) {
            $diff = ($time->getTimestamp() - $previousCheckin->getTimestamp());
            $previousDay = (int)$previousCheckin->format('Ymd');
            $currentDay = (int)$time->format('Ymd');
            
            // Prevent checkins less than a minute apart (double scans).
            if ($diff < 60) {
                return false;
            }

            // Add end-of-day and start-of-day checkin when checkin spans midnight. Leaves forgotten checkins (when
            // time difference is too great).
            if ($previousDay === $currentDay - 1 && $diff <= 6 * 3600) {
                $dayEnd = \DateTime::createFromInterface($previousCheckin);
                $dayEnd->setTime(23, 59, 59);
                $dayStart = \DateTime::createFromInterface($time);
                $dayStart->setTime(0, 0, 0);

                $profile->addCheckin($dayEnd);
                $profile->addCheckin($dayStart);
            }

            // Auto break.
            // TODO: Will add break when there's a completed range.
            if ($profile->getAutoBreak() && $previousDay === $currentDay) {
                [$breakStart, $breakEnd] = $profile->getAutoBreak($time);

                if ($previousCheckin < $breakStart && $time > $breakEnd) {
                    $profile->addCheckin($breakStart);
                    $profile->addCheckin($breakEnd);
                }
            }
        }

        $profile->addCheckin($time);
        $profile->save();
        return true;
    }

    public function checkIn(WorkrangeType $type, \DateTimeInterface $start): void
    {
        Assert::true($this->isComplete(), 'Can\'t check in to incomplete workday.');
        $this->addRange(new Workrange($type, $start, null));
    }

    public function checkOut(\DateTimeInterface $end): void
    {
        $incompleteRange = $this->getIncompleteRange();
        Assert::notNull($incompleteRange, 'No incomplete range exists for workday.');
        $this->assertNoOverlap($incompleteRange->getStart(), $end);

        $incompleteRange->setEnd($end);
    }

    public function clearWorkday(Workday $workday): void
    {
        $profile = $workday->getProfile();
        foreach ($workday->getCheckins() as $checkin) {
            $profile->removeCheckin($checkin);
        }
        $profile->save();
    }

    public function removeBreak(Workday $workday): bool
    {
        $profile = $workday->getProfile();
        $ranges = $workday->getRanges();
        if (count($ranges) < 2 || !$ranges[0]['end']) {
            return false;
        }

        $profile->removeCheckin($ranges[0]['end']);
        $profile->removeCheckin($ranges[1]['start']);
        $profile->save();
        return true;
    }
}
