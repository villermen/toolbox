<?php

namespace Villermen\Toolbox;

use Villermen\Toolbox\Work\Workrange;
use Villermen\Toolbox\Work\WorkrangeType;

class ProfileService
{
    public function loadProfile(string $profileId): Profile
    {
        $data = @file_get_contents(self::getPath($profileId));
        $data = ($data ? json_decode($data, true) : null);

        $profile = new Profile(
            $profileId,
            $data['auth'] ?? null,
        );

        if (isset($data['work'])) {
            $profile->getWorkSettings()->setAutoBreakEnabled($data['work']['autoBreakEnabled']);
            $profile->getWorkSettings()->setAutoBreakRange(
                \DateTimeImmutable::createFromFormat('H:i', $data['work']['autoBreakStart'], $profile->getTimezone()),
                \DateTimeImmutable::createFromFormat('H:i', $data['work']['autoBreakEnd'], $profile->getTimezone()),
            );
            $profile->getWorkSettings()->setSchedule($data['work']['schedule']);
        }

        // Migrate checkins.
        $checkins = ($data['checkins'] ?? []);
        if ($checkins) {
            foreach ($checkins as $checkin) {
                $checkinTime = new \DateTime(sprintf('@%s', $checkin));
                $checkinTime->setTimezone($profile->getTimezone());

                $workday = $profile->getOrCreateWorkday($checkinTime);
                $range = $workday->getIncompleteRange();
                if ($range) {
                    $range->setEnd($checkinTime);
                } else {
                    $range = new Workrange(WorkrangeType::WORK, $checkinTime, null);
                    $workday->addRange($range);
                }
            }
        }

        foreach ($data['workdays'] ?? [] as $dateString => $rangesData) {
            $date = \DateTimeImmutable::createFromFormat('Ymd', $dateString, $profile->getTimezone());
            $workday = $profile->getOrCreateWorkday($date);

            foreach ($rangesData as $rangeData) {
                $type = WorkrangeType::from($rangeData[0]);
                $start = new \DateTime(sprintf('@%s', $rangeData[1]));
                $start->setTimezone($profile->getTimezone());
                $end = null;
                if ($rangeData[2]) {
                    $end = new \DateTime(sprintf('@%s', $rangeData[2]));
                    $end->setTimezone($profile->getTimezone());
                }

                $workday->addRange(new Workrange($type, $start, $end));
            }
        }

        return $profile;
    }

    public function saveProfile(Profile $profile): void
    {
        $workdays = [];
        foreach ($profile->getWorkdays() as $date => $workday) {
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
            'auth' => $profile->getAuth(),
            'workdays' => (object)$workdays,
            'work' => [
                'autoBreakEnabled' => $profile->getWorkSettings()->isAutoBreakEnabled(),
                'autoBreakStart' => $profile->getWorkSettings()->getAutoBreakStart()->format('H:i'),
                'autoBreakEnd' => $profile->getWorkSettings()->getAutoBreakEnd()->format('H:i'),
                'schedule' => $profile->getWorkSettings()->getSchedule(),
            ],
        ];

        if (!file_put_contents(self::getPath($profile->getId()), json_encode($data))) {
            throw new \Exception('Failed to save profile data.');
        }
    }

    private static function getPath(string $profileId): string
    {
        return sprintf('data/profile-%s.json', $profileId);
    }
}