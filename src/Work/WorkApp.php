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

    public function addCheckin(Profile $profile): bool
    {
        return $this->checkinService->addCheckin($profile, new \DateTimeImmutable('now'));
    }

    /**
     * @return Workday
     */
    public function getWorkday(Profile $profile, \DateTimeInterface $date): Workday
    {
        return $this->checkinService->getWorkday($profile, $date);
    }
}
