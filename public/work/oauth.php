<?php

require_once('../../vendor/autoload.php');

use Villermen\Toolbox\Work\WorkApp;

$app = new WorkApp();
try {
    $redirectUrl = $app->authenticate();
    header(sprintf('Location: %s', $redirectUrl));
} catch (\Exception $exception) {
    echo sprintf(sprintf('Error: %s', htmlspecialchars($exception->getMessage())));
}
