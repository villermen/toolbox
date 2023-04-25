<?php

use Villermen\Toolbox\Work\WorkApp;

require_once('../../vendor/autoload.php');

$app = new WorkApp();

header(sprintf('Location: %s', $app->createUrl('work/form.php', [
    'action' => 'checkin',
])));
