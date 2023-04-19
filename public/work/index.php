<?php

use Villermen\Toolbox\App;

require_once('../../vendor/autoload.php');

$app = new App();
$profile = $app->getAuthenticatedProfile();
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
            <?php if ($profile): ?>
                <?php var_dump($profile, ['autoBreak' => $profile->getAutoBreak()]); ?>
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
