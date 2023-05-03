<?php

namespace Villermen\Toolbox\Work;

use Villermen\Toolbox\App;
use Villermen\Toolbox\Profile;

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
}
