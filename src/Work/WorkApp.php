<?php

namespace Villermen\Toolbox\Work;

use Villermen\Toolbox\App;
use Villermen\Toolbox\Profile;
use Webmozart\Assert\Assert;

class WorkApp extends App
{
    private CheckinService $checkinService;

    public function __construct()
    {
        parent::__construct();
        
        $this->checkinService = new CheckinService();
    }

    public function addCheckin(Profile $profile, \DateTimeInterface $time): Workday
    {
        $workday = $this->checkinService->addCheckin($profile, $time);
        $this->saveProfile($profile);
        return $workday;
    }

    public function addFullDay(Profile $profile, \DateTimeInterface $date, WorkrangeType $type): Workday
    {
        $workday = $this->checkinService->addFullDay($profile, $date, $type);
        $this->saveProfile($profile);
        return $workday;
    }

    public function clearWorkday(Profile $profile, \DateTimeInterface $date): Workday
    {
        $workday = $this->checkinService->clearWorkday($profile, $date);
        $this->saveProfile($profile);
        return $workday;
    }

    public function removeBreak(Profile $profile, \DateTimeInterface $date): Workday
    {
        $workday = $this->checkinService->removeBreak($profile, $date);
        $this->saveProfile($profile);
        return $workday;
    }

    public function updateSettings(Profile $profile, array $parameters): void
    {
        $workSettings = $profile->getWorkSettings();

        $autoBreakEnabled = (bool)($parameters['autoBreakEnabled'] ?? false);
        $autoBreakStart = ($parameters['autoBreakStart'] ?? null);
        $autoBreakEnd = ($parameters['autoBreakEnd'] ?? null);
        $schedule = ($parameters['schedule'] ?? null);

        $workSettings->setAutoBreakEnabled($autoBreakEnabled);
        if ($autoBreakEnabled) {
            $autoBreakStart = \DateTimeImmutable::createFromFormat('H:i', $autoBreakStart, $profile->getTimezone());
            $autoBreakEnd = \DateTimeImmutable::createFromFormat('H:i', $autoBreakEnd, $profile->getTimezone());
            Assert::notFalse($autoBreakStart, 'Invalid auto break start time given.');
            Assert::notFalse($autoBreakEnd, 'Invalid auto break end time given.');

            $workSettings->setAutoBreakRange($autoBreakStart, $autoBreakEnd);
        }

        if ($schedule) {
            $schedule = array_map(fn (string $value) => (
                intval(trim($value))
            ), explode(',', $schedule));

            $workSettings->setSchedule($schedule);
        }

        $this->saveProfile($profile);
    }
}
