<?php

use Villermen\Toolbox\Work\WorkApp;

require_once('../../vendor/autoload.php');

$app = new WorkApp();

$profile = $app->getAuthenticatedProfile();
if ($profile) {
    $app->addCheckin($profile);
    $redirectUrl = $app->createUrl('work/index.php');
} else {
    $redirectUrl = $app->createUrl('auth.php', [
        'redirect' => $app->createPath('work/checkin.php'),
    ]);
}

header(sprintf('Location: %s', $redirectUrl));
?>
