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
                <div class="clearfix">
                    <img
                        class="border border-dark rounded-circle float-end align-middle"
                        style="height: 48px;"
                        src="<?= htmlspecialchars($profile->getAvatar()); ?>"
                        alt="<?= htmlspecialchars($profile->getName()); ?>"
                    />
                    <h1 class="d-inline-block me-3">Work</h1>
                    <div class="lead d-inline-block">Get to it.</span>
                </div>
                <hr />
                <h2>Checkins</h2>
                <p>Auto break: <?= $profile->getAutoBreak() ? 'enabled' : 'disabled'; ?>.</p>
                <ul>
                    <?php foreach ($profile->getCheckins() as $timestamp): ?>
                        <?php 
                        $date = new \DateTime(sprintf('@%s', $timestamp));
                        $date->setTimezone(new \DateTimeZone('Europe/Amsterdam'));
                        ?>
                        <li><?= $date->format('Y-m-d H:i'); ?></li>
                    <?php endforeach; ?>
                </ul>
                <hr />
                <div class="text-end">
                    <a href="<?= $app->createUrl('auth.php', [
                        'logout' => '1',
                        'redirect' => $app->createPath('work/index.php'),
                    ]); ?>" class="btn btn-link">Log out</a>
                </div>
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
