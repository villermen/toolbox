<?php

namespace Villermen\Toolbox\Work;

use Villermen\Toolbox\Profile;

class CheckinService
{
    // /**
    //  * @return Workday[]
    //  */
    // public function getWorkdays(Profile $profile): array
    // {
    //     $checkins = $profile->getCheckins();
    //     if (!$checkins) {
    //         return [];
    //     }

    //     // while ($i < count($c))

    //     // $firstCheckin = start($checkins);
    //     // $lastCheckin = end($checkins);

    //     // // Don't include Saturday/Sunday unless worked on?
    //     // $workday = new Workday();
    //     // $workday->addRange();

    //     // TODO: How to correct missed checkouts? Show ??? total time?
    // }

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

        return new Workday($dayStart, $checkinsOnDay);
    }

    public function addCheckin(Profile $profile, \DateTimeInterface $time): bool
    {
        $time = \DateTime::createFromInterface($time);
        $time->setTimezone($profile->getTimezone());

        $checkins = $profile->getCheckins();

        $previousCheckin = null;
        $nextCheckin = null;
        foreach (array_reverse($checkins) as $checkin) {
            if ($checkin > $time) {
                $nextCheckin = $checkin;
            } else {
                $previousCheckin = $checkin;
                break;
            }
        }
        if ($previousCheckin) {
            $previousCheckin = \DateTime::createFromInterface($previousCheckin);
            $previousCheckin->setTimezone($profile->getTimezone());
        }
        if ($nextCheckin) {
            $nextCheckin = \DateTime::createFromInterface($nextCheckin);
            $nextCheckin->setTimezone($profile->getTimezone());
        }

        if ($previousCheckin && !$nextCheckin) {
            $diff = ($time->getTimestamp() - $previousCheckin->getTimestamp());
            $previousDay = (int)$previousCheckin->format('Ymd');
            $currentDay = (int)$time->format('Ymd');
            
            // Prevent checkins less than a minute apart (double scans).
            if ($diff < 60) {
                return false;
            }

            // Correct forgotten checkins: Checkins that span a day and time difference is big enough.
            if ($previousDay === $currentDay - 1 && $diff >= 6 * 3600) {
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
}
