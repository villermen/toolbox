<?php

use Villermen\Toolbox\Work\WorkApp;

require_once('../../vendor/autoload.php');

$app = new WorkApp();

$profile = $app->getAuthenticatedProfile();
if (!$profile) {
    $app->addFlashMessage('danger', 'You must be authenticated before you can add any checkins.');
    header(sprintf('Location: %s', $app->createUrl('work/')));
    return;
}

$action = ($_GET['action'] ?? null);
$date = ($_GET['date'] ?? null);
$date = ($date ? \DateTimeImmutable::createFromFormat('Ymd', $date, $profile->getTimezone()) : null);
$start = ($_GET['start'] ?? null);
$start = ($start ? (int)str_replace(':', '', $start) : null);
$end = ($_GET['end'] ?? null);
$end = ($end ? (int)str_replace(':', '', $end) : null);

if ($action === 'addFullDay') {
    $action = 'addRange';
    $start = 830;
    $end = 1700;
}

$start = ($date && $start < 2400 && $start % 100 < 60 ? $date->setTime((int)($start / 100), $start % 100) : null);
$end = ($date && $end < 2400 && $end % 100 < 60 ? $date->setTime((int)($end / 100), $end % 100) : null);

if ($action === 'checkin') {
    $now = new \DateTimeImmutable('now', $profile->getTimezone());
    if ($app->addCheckin($profile, $now)) {
        $workday = $app->getWorkday($profile, $now);
        $app->addFlashMessage('success', sprintf('You were checked %s.', ($workday->isComplete() ? 'out' : 'in')));
    } else {
        $app->addFlashMessage('success', 'Failed to check in/out. Did you scan twice by accident?');
    }
} elseif ($action === 'addRange') {
    // TODO: Verify no overlap with existing ranges?
    $workday = $app->getWorkday($profile, $date);
    if ($workday->isComplete()) {
        if ($start && $end && $start < $end) {
            $app->addCheckin($profile, $start);
            $app->addCheckin($profile, $end);
            $app->addFlashMessage('success', sprintf('Added range for %s.', $date->format('j-n-Y')));
        } else {
            $app->addFlashMessage('danger', 'Please specify a valid time range.');
        }
    } else {
        if ($end && $end > $workday->getLastCheckin()) {
            $app->addCheckin($profile, $end);
            $app->addFlashMessage('success', sprintf('Added end time for %s.', $date->format('j-n-Y')));
        } else {
            $app->addFlashMessage('danger', 'Please specify a valid end time.');
        }
    }
} elseif ($action === 'addHoliday') {
    $app->addFlashMessage('danger', 'Logging holidays is not implemented yet. Add a full day instead.');
} elseif ($action === 'clearCheckins') {
    $workday = $app->getWorkday($profile, $date);
    $app->clearWorkday($workday);
    $app->addFlashMessage('success', sprintf('Cleared checkins for %s.', $date->format('j-n-Y')));
} elseif ($action === 'removeBreak') {
    $workday = $app->getWorkday($profile, $date);
    if ($app->removeBreak($workday)) {
        $app->addFlashMessage('success', sprintf('Removed break for %s.', $date->format('j-n-Y')));
    } else {
        $app->addFlashMessage('danger', sprintf('Failed to remove break for %s.', $date->format('j-n-Y')));
    }
} else {
    $app->addFlashMessage('danger', 'Invalid action specified.');
}

header(sprintf('Location: %s', $app->createUrl('work/')));
?>
