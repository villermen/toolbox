<?php

namespace Villermen\Toolbox\Work;

use Villermen\Toolbox\Profile;

class CheckinService
{
    public function getWorkday(Profile $profile, \DateTimeInterface $date): Workday
    {
        $dayStart = \DateTime::createFromInterface($date);
        $dayStart->setTimezone($profile->getTimezone());
        $dayStart->setTime(0, 0, 0);
        $dayEnd = clone $dayStart;
        $dayEnd->setTime(23, 59, 59);

        $checkinsOnDay = array_values(array_filter($profile->getCheckins(), fn (\DateTimeInterface $checkin): bool => (
            $checkin >= $dayStart && $checkin <= $dayEnd
        )));

        return new Workday($profile, $dayStart, $checkinsOnDay);
    }

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
            if ($profile->getAutoBreak() && $previousDay === $currentDay) {
                $breakStart = \DateTime::createFromInterface($time);
                $breakStart->setTime(12, 45);
                $breakEnd = \DateTime::createFromInterface($time);
                $breakEnd->setTime(13, 15);

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
