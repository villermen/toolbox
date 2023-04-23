<?php

use Villermen\Toolbox\Work\WorkApp;
use Villermen\Toolbox\Work\Workday;

require_once('../../vendor/autoload.php');

$app = new WorkApp();
$profile = $app->getAuthenticatedProfile();

$clipOffset = function (Workday $workday, \DateTimeInterface $start, ?\DateTimeInterface $end): ?array {
    $visibleDayStart = 6;
    $visibleDayEnd = 20;
    $visibleSeconds = ($visibleDayEnd - $visibleDayStart) * 3600;

    $left = ($start->getTimestamp() - $workday->getDate()->getTimestamp() - $visibleDayStart * 3600) / $visibleSeconds;
    // TODO: !$end + dayend cutoff
    $width = ($end->getTimestamp() - $start->getTimestamp()) / $visibleSeconds;

    $inRange = true;
    if (!$inRange) {
        return null;
    }

    return [
        'left' => $left,
        'width' => $width,
    ];
};

if ($profile) {
    $day = new \DateTime('now', new DateTimeZone('Europe/Amsterdam'));

    /** @var Workday[] $workdays */
    $workdays = [];
    for ($i = 0; $i < 100; $i++) {
        $workdays[] = $app->getWorkday($profile, $day);
        $day->modify('-1 day');
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Work</title>
        <link rel="stylesheet" href="../style.css">
        <script src="work.js"></script>
    </head>
    <body>
        <div class="container mt-5 mb-5">
            <?php if ($profile): ?>
                <div class="clearfix">
                    <img
                        class="border border-dark rounded-circle float-end align-middle"
                        style="height: 48px;"
                        src="<?= htmlspecialchars($profile->getAvatar()); ?>"
                        alt="<?= htmlspecialchars($profile->getName()); ?>"
                    />
                    <h1 class="d-inline-block me-3">Work</h1>
                    <div class="lead d-inline-block">Get to it.</span>
                </div>
                <hr />
                <a class="btn btn-primary btn-sm float-end align-text-bottom" href="...">Add checkin</a>
                <h2>Checkins</h2>
                <p>
                    Auto break: <?= $profile->getAutoBreak() ? 'enabled' : 'disabled'; ?>.<br />
                    FTE: 1.0
                </p>
                <h3>March 12023 <small class="text-muted">(-5.4h)</small></h3>
                <h4>Week 121</h3>
                <?php foreach ($workdays as $workday): ?>
                    <?= $workday->getDate()->format('l'); ?>:
                    <?= round($workday->getTotalDuration() / 3600, 2); ?>
                    <div class="progress" style="height: 25px;">
                        <?php foreach ($workday->getRanges() as $range): ?>
                            <?php
                            $clip = $clipOffset($workday, $range['start'], $range['end']);
                            if (!$clip) {
                                continue;
                            }
                            ?>
                            <div
                                class="progress-bar text-start flex-row justify-content-between align-items-center p-2"
                                role="progressbar"
                                style="margin-left: <?= $clip['left']; ?>%; width: <?= $clip['width']; ?>%;"
                            >
                                <span><?= $range['start']->format('H:i'); ?></span>
                                <span><?= ($range['end'] ? $range['end']->format('H:i') : '???'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <ul>
                    <?php foreach ($profile->getCheckins() as $timestamp): ?>
                        <?php 
                        $date = new \DateTime(sprintf('@%s', $timestamp));
                        $date->setTimezone(new \DateTimeZone('Europe/Amsterdam'));
                        ?>
                        <li><?= $date->format('Y-m-d H:i'); ?></li>
                    <?php endforeach; ?>
                </ul>
                <hr />
                <div class="text-end">
                    <a href="<?= $app->createUrl('auth.php', [
                        'logout' => '1',
                        'redirect' => $app->createPath('work/index.php'),
                    ]); ?>" class="btn btn-link">Log out</a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <a href="<?= $app->createUrl('auth.php', [
                        'redirect' => $app->createPath('work/index.php'),
                    ]); ?>" class="btn btn-primary">Log in with Google</a>
                </div>
            <?php endif; ?>
        </div>
    </body>
</html>
