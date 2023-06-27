<?php

namespace Villermen\Toolbox\Work;

use Villermen\Toolbox\Profile;
use Webmozart\Assert\Assert;

class ScheduleService
{
    public function setAutoBreakEnabled(Profile $profile, bool $autoBreakEnabled): void
    {
        // TODO: Default is defined on model too. Better to put getter in service, but then there's no easy access from view...
        $autoBreak = ($profile->getSetting('autoBreak') ?? [true, '12:45', '13:15']);
        $autoBreak[0] = $autoBreakEnabled;
        $profile->setSetting('autoBreak', $autoBreak);
    }

     public function setAutoBreak(Profile $profile, \DateTimeInterface $start, \DateTimeInterface $end): void
     {
         Assert::eq($start->format('Ymd'), $end->format('Ymd'));
         Assert::lessThan($start, $end);

         $profile->setSetting('autoBreak', [
             $profile->getSetting('autoBreak')[0],
             $start->format('h:i'),
             $end->format('h:i')
         ]);
     }
}