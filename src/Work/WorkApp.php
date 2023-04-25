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

    public function addCheckin(Profile $profile, \DateTimeInterface $time): bool
    {
        return $this->checkinService->addCheckin($profile, $time);
    }

    /**
     * @return Workday
     */
    public function getWorkday(Profile $profile, \DateTimeInterface $date): Workday
    {
        return $this->checkinService->getWorkday($profile, $date);
    }

    public function clearWorkday(Workday $workday): void
    {
        $this->checkinService->clearWorkday($workday);
    }

    public function removeBreak(Workday $workday): bool
    {
        return $this->checkinService->removeBreak($workday);
    }
}
