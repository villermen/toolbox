<?php

require_once('../../vendor/autoload.php');

$app = new \Villermen\Toolbox\Work\WorkApp();

$profile = $app->getAuthenticatedProfile();
if (!$profile) {
    $app->addFlashMessage('danger', 'You must be authenticated before you can add any checkins.');
    header(sprintf('Location: %s', $app->createUrl('work/')));
    return;
}

$action = ($_GET['action'] ?? null);
$date = ($_GET['date'] ?? '');
$start = ($_GET['start'] ?? '');
$start = (\DateTimeImmutable::createFromFormat('Ymd H:i', sprintf('%s %s', $date, $start), $profile->getTimezone()) ?: null);
$end = ($_GET['end'] ?? '');
$end = (\DateTimeImmutable::createFromFormat('Ymd H:i', sprintf('%s %s', $date, $end), $profile->getTimezone()) ?: null);
$date = \DateTimeImmutable::createFromFormat('Ymd', $date, $profile->getTimezone());
$rangeType = \Villermen\Toolbox\Work\WorkrangeType::tryFrom((int)$_GET['type']) ?? \Villermen\Toolbox\Work\WorkrangeType::WORK;

$allowAutoBreak = true;
if ($action === 'checkinNoBreak') {
    $action = 'checkin';
    $allowAutoBreak = false;
}

if ($action === 'addRange' && (!$start || !$end)) {
    $action = 'checkin';
}

if ($action === 'checkin') {
    $now = ($start ?? $end ?? new \DateTimeImmutable('now', $profile->getTimezone()));

    try {
        $workday = $app->addCheckin($profile, $now, $allowAutoBreak);
        $app->addFlashMessage('success', sprintf('You were checked %s.', ($workday->isComplete() ? 'out' : 'in')));
    } catch (\InvalidArgumentException $exception) {
        $app->addFlashMessage('danger', $exception->getMessage());
    }
} elseif ($action === 'addRange') {
    try {
        $workday = $app->addCheckin($profile, $start, $allowAutoBreak);
        $workday = $app->addCheckin($profile, $end, $allowAutoBreak);
        $app->addFlashMessage('success', sprintf('Added range for %s.', $workday->getDate()->format('j-n-Y')));
    } catch (\InvalidArgumentException $exception) {
        $app->addFlashMessage('danger', $exception->getMessage());
    }
} elseif ($action === 'addFullDay') {
    try {
        $workday = $app->addFullDay($profile, $date, $rangeType);
        $app->addFlashMessage('success', sprintf('Added range for %s.', $workday->getDate()->format('j-n-Y')));
    } catch (\InvalidArgumentException $exception) {
        $app->addFlashMessage('danger', $exception->getMessage());
    }
} elseif ($action === 'clearCheckins') {
    $workday = $app->clearWorkday($profile, $date);
    $app->addFlashMessage('success', sprintf('Cleared checkins for %s.', $workday->getDate()->format('j-n-Y')));
} elseif ($action === 'removeBreak') {
    $workday = $app->removeBreak($profile, $date);
    $app->addFlashMessage('success', sprintf('Removed break for %s.', $workday->getDate()->format('j-n-Y')));
} elseif ($action === 'settings') {
    try {
        $app->updateSettings($profile, $_GET);
        $app->addFlashMessage('success', 'Settings updated successfully.');
    } catch (\InvalidArgumentException $exception) {
        $app->addFlashMessage('danger', $exception->getMessage());
    }
} else {
    $app->addFlashMessage('danger', 'Invalid action specified.');
}

header(sprintf('Location: %s', $app->createUrl('work/')));
?>
