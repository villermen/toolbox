<?php

use Villermen\Toolbox\Work\WorkApp;

require_once('../../vendor/autoload.php');

$app = new WorkApp();
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
            <?php if ($app->getAuthenticatedProfile()): ?>
                <?php dump($app->getAuthenticatedProfile()); ?>
            <?php else: ?>
                <div class="text-center">
                    <a href="oauth.php" class="btn btn-primary">Log in with Google</a>
                </div>
            <?php endif; ?>
        </div>
    </body>
</html>
