<?php

use Villermen\Toolbox\Work\WorkApp;
use Villermen\Toolbox\Work\Workday;

require_once('../../vendor/autoload.php');

$app = new WorkApp();
$profile = $app->getAuthenticatedProfile();

// TODO: In progress on current day, simulate to give an impression? Will be hard with auto breaking.
// TODO: Start and end can exceed bounds.
$createBarRanges = function (Workday $workday): array {
    $visibleDayStart = 7 * 3600 + $workday->getDate()->getTimestamp();
    $visibleDayEnd = 20 * 3600 + $workday->getDate()->getTimestamp();
    $visibleDaySeconds = ($visibleDayEnd - $visibleDayStart);

    $x = 0;
    $barRanges = [];
    foreach ($workday->getRanges() as ['start' => $start, 'end' => $end]) {
        if ($end && $end->getTimestamp() < $visibleDayStart || $start->getTimestamp() > $visibleDayEnd) {
            continue;
        }

        $marginLeft = ($start->getTimestamp() - $visibleDayStart) / $visibleDaySeconds * 100.0 - $x;
        if ($end) {
            $width = ($end->getTimestamp() - $start->getTimestamp()) / $visibleDaySeconds * 100.0;
        } else {
            $width = 10.0;
        }

        $barRanges[] = [
            'startFormatted' => $start->format('H:i'),
            'endFormatted' => ($end ? $end->format('H:i') : '???'),
            'marginLeft' => sprintf('%s%%', $marginLeft),
            'width' => sprintf('%s%%', $width),
            'colorClass' => ($end ? 'bg-primary' : 'bg-warning'),
        ];

        $x += $marginLeft + $width;
    }

    return $barRanges;
};
$getInvisibleRanges = function (Workday $workday): array {
    $visibleDayStart = 6 * 3600 + $workday->getDate()->getTimestamp();
    $visibleDayEnd = 20 * 3600 + $workday->getDate()->getTimestamp();

    $invisibleRanges = [];
    foreach ($workday->getRanges() as ['start' => $start, 'end' => $end]) {
        if ($end && $end->getTimestamp() < $visibleDayStart || $start->getTimestamp() > $visibleDayEnd) {
            $invisibleRanges[] = [
                'startFormatted' => $start->format('H:i'),
                'endFormatted' => ($end ? $end->format('H:i') : '???'),
            ];
        }
    }

    return $invisibleRanges;
};

if ($profile) {
    $day = new \DateTime('today', $profile->getTimezone());

    $months = [];
    while (true) {
        $month = (int)$day->format('Ym');
        if (!isset($months[$month])) {
            if (count($months) === 3) {
                break;
            }

            $months[$month] = [
                'workdays' => [],
                'workSeconds' => 0,
                'paidSeconds' => 0,
            ];
        }

        $workday = $app->getWorkday($profile, $day);
        $months[$month]['workdays'][] = $workday;
        $months[$month]['workSeconds'] += $workday->getTotalDuration();
        // TODO: Holidays are strange.
        // if ((int)$day->format('N') <= 5) {
        //     $months[$month]['paidSeconds'] += ($profile->getFte() * 8 * 3600);
        // }

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
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
            crossorigin="anonymous"
        ></script>
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
                <a class="btn btn-primary btn-sm float-end align-text-bottom" href="<?= $app->createUrl('work/checkin.php'); ?>">Add checkin</a>
                <h2>Checkins</h2>
                <p>
                    Auto break: <?= $profile->getAutoBreak() ? 'enabled' : 'disabled'; ?>.<br />
                    FTE: <?= $profile->getFte(); ?><br />
                    Timezone: <?= $profile->getTimezone()->getName(); ?> (+<?= $profile->getTimezone()->getOffset(new \DateTime('now', new DateTimeZone('UTC'))) / 3600; ?>h)<br />
                </p>
                <?php foreach ($months as ['workdays' => $workdays, 'workSeconds' => $workSeconds]): ?>
                    <div class="row">
                        <div class="col-11">
                            <h3>
                                <small class="text-muted float-end"><?= round($workSeconds / 3600, 2); ?>h</small>
                                <?= reset($workdays)->getDate()->format('F Y'); ?>
                            </h3>
                        </div>
                    </div>
                    <?php foreach ($workdays as $workday): ?>
                        <?php /* TODO: Probably don't use columns for this. */ ?>
                        <div class="row mb-2 gx-2">
                            <div class="col-11">
                                <div>
                                    <span class="float-end">
                                        <?php if ($workday->getTotalDuration() > 0): ?>
                                            <strong><?= round($workday->getTotalDuration() / 3600, 2); ?>h</strong>
                                        <?php else: ?>
                                            0h
                                        <?php endif; ?>
                                    </span>
                                    <?= $workday->getDate()->format('l d'); ?>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <?php foreach ($createBarRanges($workday) as $range): ?>
                                        <div
                                            class="progress-bar text-start flex-row justify-content-between align-items-center p-2 rounded <?= $range['colorClass']; ?>"
                                            role="progressbar"
                                            style="margin-left: <?= $range['marginLeft']; ?>; width: <?= $range['width']; ?>;"
                                        >
                                            <span><?= $range['startFormatted']; ?></span>
                                            <span><?= $range['endFormatted']; ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-muted text-end">
                                    <?php $invisibleRanges = $getInvisibleRanges($workday); ?>
                                    <?php if ($invisibleRanges): ?>
                                        Not shown:
                                        <?php foreach ($invisibleRanges as $range): ?>
                                            <?= $range['startFormatted']; ?> -
                                            <?= $range['endFormatted']; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-1 d-flex flex-column justify-content-center">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-secondary dropdown-toggle"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside"
                                        aria-expanded="false"
                                    ></button>
                                    <ul class="dropdown-menu">
                                        <li><h6 class="dropdown-header text-center"><?= $workday->getDate()->format('l d-n-Y'); ?></h6></li>
                                        <div class="dropdown-divider"></div>
                                        <form class="ps-2 pe-2" method="post">
                                            <div class="mb-2 text-center d-flex align-items-center gap-1">
                                                <input type="time" name="addRangeStart" class="form-control d-inline-block" style="flex: 1 0 80px;" required>
                                                <div>-</div>
                                                <input type="time" name="addRangeEnd" class="form-control d-inline-block" style="flex: 1 0 80px;" required>
                                                <button type="submit" name="addRange" class="btn btn-primary">Add</button>
                                            </div>
                                        </form>
                                        <div class="dropdown-divider"></div>
                                        <li><a class="dropdown-item" href="#">Full day</a></li>
                                        <li><a class="dropdown-item" href="#">Holiday</a></li>
                                        <li><a class="dropdown-item" href="#">Clear checkins</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
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
