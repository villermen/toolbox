<?php

require_once('../../vendor/autoload.php');

$app = new \Villermen\Toolbox\Work\WorkApp();
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
    'I wouldn\'t ask anyone else.',
];
mt_srand((int)date('Ymd'));
$subtitle = $subtitles[mt_rand(0, count($subtitles) - 1)];
mt_srand();

// TODO: In progress on current day, simulate to give an impression? Will be hard with auto breaking.
$createBarRanges = function (\Villermen\Toolbox\Work\Workday $workday) use ($profile): array {
    $visibleDayStart = \DateTime::createFromInterface($workday->getDate());
    $visibleDayStart->setTime(7, 0);
    $visibleDayEnd = \DateTime::createFromInterface($workday->getDate());
    $visibleDayEnd->setTime(20, 0);
    $visibleDaySeconds = ($visibleDayEnd->getTimestamp() - $visibleDayStart->getTimestamp());

    $x = 0;
    $barRanges = [];
    foreach ($workday->getRanges() as $range) {
        // Skip invisible ranges.
        if (($range->getEnd() ?? $range->getStart()) < $visibleDayStart || $range->getStart() > $visibleDayEnd) {
            continue;
        }

        $visibleStart = max($range->getStart(), $visibleDayStart);
        if ($range->getEnd()) {
            $visibleEnd = $range->getEnd();
        } else {
            $now = new \DateTimeImmutable('now', $profile->getTimezone());
            
            if ($workday->getDate()->format('Ymd') === $now->format('Ymd')) {
                $visibleEnd = $now;
            } else {
                $visibleEnd = \DateTime::createFromInterface($visibleStart);
                $visibleEnd->modify('+ 1 hour');
            }
        } 
        $visibleEnd = min($visibleEnd, $visibleDayEnd);

        $marginLeft = ($visibleStart->getTimestamp() - $visibleDayStart->getTimestamp()) / $visibleDaySeconds * 100.0 - $x;
        $width = ($visibleEnd->getTimestamp() - $range->getStart()->getTimestamp()) / $visibleDaySeconds * 100.0;

        if ($range->getType() === \Villermen\Toolbox\Work\WorkrangeType::WORK) {
            $startFormatted = ($visibleStart === $range->getStart() ? $range->getStart()->format('H:i') : '~');
            $endFormatted = ($range->getEnd() && $visibleEnd === $range->getEnd()
                ? $range->getEnd()->format('H:i')
                : '~'
            );
            $colorClass = ($range->getEnd() ? 'bg-primary' : 'bg-warning');
        } else {
            $startFormatted = match ($range->getType()) {
                \Villermen\Toolbox\Work\WorkrangeType::HOLIDAY => 'Holiday',
                \Villermen\Toolbox\Work\WorkrangeType::SICK_LEAVE => 'Sick leave',
                \Villermen\Toolbox\Work\WorkrangeType::SPECIAL_LEAVE => 'Special leave',
                default => 'Unknown range type',
            };
            $endFormatted = '';
            $colorClass = 'bg-info';
        }

        $barRanges[] = [
            'startFormatted' => $startFormatted,
            'endFormatted' => $endFormatted,
            'rangeFormatted' => sprintf(
                '%s - %s',
                $range->getStart()->format('H:i'),
                ($range->getEnd() ? $range->getEnd()->format('H:i') : '???')
            ),
            'marginLeft' => sprintf('%s%%', $marginLeft),
            'width' => sprintf('%s%%', $width),
            'colorClass' => $colorClass,
        ];

        $x += $marginLeft + $width;
    }

    return $barRanges;
};
$getInvisibleRanges = function (\Villermen\Toolbox\Work\Workday $workday): array {
    $visibleDayStart = \DateTime::createFromInterface($workday->getDate());
    $visibleDayStart->setTime(7, 0);
    $visibleDayEnd = \DateTime::createFromInterface($workday->getDate());
    $visibleDayEnd->setTime(20, 0);

    $invisibleRanges = [];
    foreach ($workday->getRanges() as $range) {
        // Only include invisible ranges.
        if (($range->getEnd() ?? $range->getStart()) < $visibleDayStart || $range->getStart() > $visibleDayEnd) {
            $invisibleRanges[] = [
                'startFormatted' => $range->getStart()->format('H:i'),
                'endFormatted' => ($range->getEnd() ? $range->getEnd()->format('H:i') : '???'),
            ];
        }
    }

    return $invisibleRanges;
};

if ($profile) {
    $currentWorkday = null;
    /** @var array{workdays: \Villermen\Toolbox\Work\Workday[], workSeconds: int, paidSeconds: int}[] $months */
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

        $workday = $profile->getOrCreateWorkday($day);
        $months[$month]['workdays'][] = $workday;
        $months[$month]['workSeconds'] += $workday->getTotalDuration();
        $months[$month]['paidSeconds'] += ($profile->getWorkSettings()->getSchedule()[(int)$day->format('N') - 1] * 3600);

        if (!$currentWorkday) {
            $currentWorkday = $workday;
        }

        $day->modify('-1 day');
    }
}
?>
<?= $app->renderView('header.phtml', [
    'title' => 'Work',
    'subtitle' => $subtitle,
    'head' => '<script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"
    ></script>',
    'script' => $app->createPath('work/index.js'),
]); ?>
<?php if ($profile): ?>
    <form method="get" action="<?= $app->createUrl('work/form.php'); ?>">
        <input type="hidden" name="action" value="settings" />
        <div class="row mb-2">
            <div class="col-12 col-sm-4 mb-4 mb-sm-0 order-sm-last text-end">
                <a class="btn btn-primary btn-sm d-block d-sm-inline-block w-100" href="<?= $app->createUrl('work/form.php', [
                    'action' => 'checkin',
                ]); ?>"><?= sprintf('Check %s now', $currentWorkday->isComplete() ? 'in' : 'out'); ?></a>
            </div>
            <label for="autoBreakEnabled" class="col-4 col-sm-3 form-label">Auto break</label>
            <div class="col-8 col-sm-5">
                <div class="form-check form-switch">
                    <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="autoBreakEnabled"
                            name="autoBreakEnabled"
                        <?php if ($profile->getWorkSettings()->isAutoBreakEnabled()): ?>
                            checked
                        <?php endif; ?>
                    />
                </div>
                <div id="autoBreakRangeInputs" class="d-flex align-items-center gap-1 <?= ($profile->getWorkSettings()->isAutoBreakEnabled() ? '' : 'd-none'); ?>">
                    <input
                        type="time"
                        id="autoBreakStart"
                        name="autoBreakStart"
                        class="form-control form-control-sm d-inline-block"
                        style="flex: 1 0 40px;"
                        value="<?= $profile->getWorkSettings()->getAutoBreakStart()->format('H:i'); ?>"
                    />
                    <div>-</div>
                    <input
                        type="time"
                        id="autoBreakEnd"
                        name="autoBreakEnd"
                        class="form-control form-control-sm d-inline-block"
                        style="flex: 1 0 40px;"
                        value="<?= $profile->getWorkSettings()->getAutoBreakEnd()->format('H:i'); ?>"
                    />
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <label for="schedule" class="col-4 col-sm-3 form-label">Schedule</label>
            <div class="col-8 col-sm-5">
                <input type="text" id="schedule" name="schedule" class="form-control form-control-sm" value="<?= implode(',', $profile->getWorkSettings()->getSchedule()); ?>" />
            </div>
        </div>
        <div class="row mb-4 gy-2">
            <label class="col-4 col-sm-3 form-label">Timezone</label>
            <div class="col-8 col-sm-5">
                <?= $profile->getTimezone()->getName(); ?> (+<?= $profile->getTimezone()->getOffset(new \DateTime('now', new DateTimeZone('UTC'))) / 3600; ?>h)
            </div>
            <div class="col-12 col-sm-4 text-end">
                <button type="submit" class="btn btn-sm btn-primary d-block d-sm-inline-block w-100">Save settings</button>
            </div>
        </div>
    </form>
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
                    <?php if ($workday->isComplete() && $workday->getTotalDuration() > 0): ?>
                        <?= sprintf('%sh', round($workday->getTotalDuration() / 3600, 2)); ?>
                    <?php elseif (!$workday->isComplete()): ?>
                        ???
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
                            title="<?= $range['rangeFormatted']; ?>"
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
                                <?php if ($workday->isComplete()): ?>
                                    <input type="time" name="start" class="form-control form-control-sm d-inline-block" style="flex: 1 0 40px;" required>
                                <?php else: ?>
                                    <div><?= $workday->getIncompleteRange()->getStart()->format('H:i'); ?></div>
                                <?php endif; ?>
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
                                            'action' => 'addFullDay',
                                            'type' => \Villermen\Toolbox\Work\WorkrangeType::HOLIDAY->value,
                                        ]); ?>"
                                    <?php endif; ?>
                                >Holiday</a>
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
                                            'action' => 'addFullDay',
                                            'type' => \Villermen\Toolbox\Work\WorkrangeType::SICK_LEAVE->value,
                                        ]); ?>"
                                    <?php endif; ?>
                                >Sick leave</a>
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
                                            'action' => 'addFullDay',
                                            'type' => \Villermen\Toolbox\Work\WorkrangeType::SPECIAL_LEAVE->value,
                                        ]); ?>"
                                    <?php endif; ?>
                                >Special leave</a>
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
<?php else: ?>
    <div class="text-center">
        <a href="<?= $app->createUrl('auth.php', [
            'redirect' => $app->createPath('work/'),
        ]); ?>" class="btn btn-primary">Log in with Google</a>
    </div>
<?php endif; ?>
<?= $app->renderView('footer.phtml'); ?>