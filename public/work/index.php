<?php

use Villermen\Toolbox\Work\WorkApp;
use Villermen\Toolbox\Work\Workday;

require_once('../../vendor/autoload.php');

$app = new WorkApp();
$profile = $app->getAuthenticatedProfile();

$subtitles = [
    'Get to it.',
    'You got this.',
    'It\'s all you.',
    'Go on.',
    'The thing you excel at.',
    'Get it done.',
    'You know what to do.',
    'Go shine.',
    'You look lovely today.',
];
mt_srand((int)date('Ymd'));
$subtitle = $subtitles[mt_rand(0, count($subtitles) - 1)];
mt_srand();

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
    $currentWorkday = null;
    $months = [];
    $day = new \DateTime('today', $profile->getTimezone());
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
        $months[$month]['paidSeconds'] += ($profile->getSchedule()[(int)$day->format('N') - 1] * 3600);

        if (!$currentWorkday) {
            $currentWorkday = $workday;
        }

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
        <script src="work.js" defer></script>
    </head>
    <body>
        <div class="container mt-5 mb-5">
            <?php foreach($app->popFlashMessages() as $flashMessage): ?>
                <div class="<?= sprintf('alert alert-dismissible alert-%s fade show', $flashMessage['color']); ?>">
                    <?= htmlspecialchars($flashMessage['message']); ?>
                    <button type="button" class="btn btn-sm btn-close" data-bs-dismiss="alert" aria-label="close"></button>
                </div>
            <?php endforeach; ?>
            <?php if ($profile): ?>
                <div class="clearfix">
                    <img
                        class="border border-dark rounded-circle float-end align-middle"
                        style="height: 48px;"
                        src="<?= htmlspecialchars($profile->getAvatar()); ?>"
                        alt="<?= htmlspecialchars($profile->getName()); ?>"
                    />
                    <h1 class="d-inline-block me-3">Work</h1>
                    <div class="lead d-inline-block"><?= $subtitle; ?></span>
                </div>
                <hr />
                <a class="btn btn-primary btn-sm float-end align-text-bottom" href="<?= $app->createUrl('work/form.php', [
                    'action' => 'checkin',
                ]); ?>"><?= sprintf('Check %s now', $currentWorkday->isComplete() ? 'in' : 'out'); ?></a>
                <p>
                    Auto break: <?= $profile->getAutoBreak() ? 'enabled' : 'disabled'; ?>.<br />
                    Schedule: <?= implode(',', $profile->getSchedule()); ?>.<br />
                    Timezone: <?= $profile->getTimezone()->getName(); ?> (+<?= $profile->getTimezone()->getOffset(new \DateTime('now', new DateTimeZone('UTC'))) / 3600; ?>h)<br />
                </p>
                <?php foreach ($months as ['workdays' => $workdays, 'workSeconds' => $workSeconds, 'paidSeconds' => $paidSeconds]): ?>
                    <h2>
                        <small class="text-muted float-end">
                            <?= sprintf('%.2Fh', $workSeconds / 3600); ?>
                            (<?= sprintf('%+.2Fh', ($workSeconds - $paidSeconds) / 3600); ?>)
                        </small>
                        <?= reset($workdays)->getDate()->format('F Y'); ?>
                    </h2>
                    <?php foreach ($workdays as $workday): ?>
                        <?php $dateValue = (int)$workday->getDate()->format('Ymd'); ?>
                        <div>
                            <span class="float-end">
                                <?php if ($workday->getTotalDuration() > 0): ?>
                                    <?= sprintf('%sh', round($workday->getTotalDuration() / 3600, 2)); ?>
                                <?php endif; ?>
                            </span>
                            <?= $workday->getDate()->format('l d'); ?>
                        </div>
                        <div class="d-flex" style="height: 32px;">
                            <div class="progress flex-grow-1 rounded-end-0 h-100">
                                <?php foreach ($createBarRanges($workday) as $range): ?>
                                    <div
                                        class="progress-bar text-start flex-row justify-content-between align-items-center p-2 rounded gap-3 <?= $range['colorClass']; ?>"
                                        role="progressbar"
                                        style="margin-left: <?= $range['marginLeft']; ?>; width: <?= $range['width']; ?>;"
                                        data-bs-toggle="tooltip"
                                        title="<?= sprintf('%s - %s', $range['startFormatted'], $range['endFormatted']); ?>"
                                    >
                                        <span><?= $range['startFormatted']; ?></span>
                                        <span><?= $range['endFormatted']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="dropdown">
                                <button
                                    class="btn btn-sm btn-secondary dropdown-toggle rounded-start-0 h-100"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                ></button>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header text-center"><?= $workday->getDate()->format('l d-n-Y'); ?></h6></li>
                                    <div class="dropdown-divider"></div>
                                    <form class="ps-2 pe-2" method="get" action="<?= $app->createUrl('work/form.php'); ?>">
                                        <input type="hidden" name="date" value="<?= $dateValue; ?>" />
                                        <div class="mb-2 text-center d-flex align-items-center gap-1">
                                            <input type="time" name="start" class="form-control form-control-sm d-inline-block" style="flex: 1 0 40px;" required>
                                            <div>-</div>
                                            <input type="time" name="end" class="form-control form-control-sm d-inline-block" style="flex: 1 0 40px;" required>
                                            <button type="submit" name="action" value="addRange" class="btn btn-primary btn-sm">Add</button>
                                        </div>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <li>
                                        <div 
                                            <?php if ($workday->getRanges()): ?>
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="left"
                                                title="Only available when there aren't any checkins yet. Clear them first."
                                            <?php endif; ?>
                                        >
                                            <a
                                                <?php if ($workday->getRanges()): ?>
                                                    class="dropdown-item disabled"
                                                <?php else: ?>
                                                    class="dropdown-item"
                                                    href="<?= $app->createUrl('work/form.php', [
                                                        'date' => $dateValue,
                                                        'action' => 'addFullDay',
                                                    ]); ?>"
                                                <?php endif; ?>
                                            >Full day</a>
                                        </div>
                                    </li>
                                    <li>
                                        <div 
                                            <?php if ($workday->getRanges()): ?>
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="left"
                                                title="Only available when there aren't any checkins yet. Clear them first."
                                            <?php endif; ?>
                                        >
                                            <a
                                                <?php if ($workday->getRanges()): ?>
                                                    class="dropdown-item disabled"
                                                <?php else: ?>
                                                    class="dropdown-item"
                                                    href="<?= $app->createUrl('work/form.php', [
                                                        'date' => $dateValue,
                                                        'action' => 'addHoliday',
                                                    ]); ?>"
                                                <?php endif; ?>
                                            >Holiday</a>
                                        </div>
                                    </li>
                                    <li>
                                        <div 
                                            <?php if (count($workday->getRanges()) < 2): ?>
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="left"
                                                title="Only available when there are multiple ranges."
                                            <?php endif; ?>
                                        >
                                            <a
                                                <?php if (count($workday->getRanges()) >= 2): ?>
                                                    class="dropdown-item"
                                                    href="<?= $app->createUrl('work/form.php', [
                                                        'date' => $dateValue,
                                                        'action' => 'removeBreak',
                                                    ]); ?>"
                                                <?php else: ?>
                                                    class="dropdown-item disabled"
                                                <?php endif; ?>
                                            >Remove break</a>
                                        </div>
                                    </li>
                                    <li>
                                        <div 
                                            <?php if (!$workday->getRanges()): ?>
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="left"
                                                title="Only available when there are any checkins."
                                            <?php endif; ?>
                                        >
                                            <a
                                                <?php if ($workday->getRanges()): ?>
                                                    class="dropdown-item"
                                                    href="<?= $app->createUrl('work/form.php', [
                                                        'date' => $dateValue,
                                                        'action' => 'clearCheckins',
                                                    ]); ?>"
                                                <?php else: ?>
                                                    class="dropdown-item disabled"
                                                <?php endif; ?>
                                            >Clear day</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-2">
                            <div class="text-muted">
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
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <hr />
                <div class="text-end">
                    <a href="<?= $app->createUrl('auth.php', [
                        'logout' => '1',
                        'redirect' => $app->createPath('work/'),
                    ]); ?>" class="btn btn-link">Log out</a>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <a href="<?= $app->createUrl('auth.php', [
                        'redirect' => $app->createPath('work/'),
                    ]); ?>" class="btn btn-primary">Log in with Google</a>
                </div>
            <?php endif; ?>
        </div>
    </body>
</html>
