<?php

require_once('../vendor/autoload.php');

use Villermen\Toolbox\App;
use Villermen\Toolbox\Exception\AuthenticationException;

$app = new App();
try {
    if (isset($_GET['logout'])) {
        $redirectUrl = $app->logout();
    } else {
        $redirectUrl = $app->authenticate();
    }
    header(sprintf('Location: %s', $redirectUrl));
} catch (AuthenticationException $exception) {
    echo sprintf(sprintf('Error: %s', htmlspecialchars($exception->getMessage())));
}
