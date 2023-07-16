<?php

require_once('../vendor/autoload.php');

$app = new \Villermen\Toolbox\App();
?>
<?= $app->renderView('header.phtml', [
    'title' => '<img src="./toolbox.png" alt="Toolbox logo" class="align-baseline" style="height: 27px;" /> Toolbox',
]); ?>
<div class="text-center">
    <p class="lead">An assortment of snappy tools you didn't know you needed. You probably still don't!</p>
    <div class="row">
        <div class="col-sm-4 col-md-3 col-xl-2 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">DECHEX</h5>
                    <p class="card-text">Convert decimal to hexadecimal and vice versa.</p>
                    <a href="dechex/" class="btn btn-primary stretched-link mt-auto">DECHEX</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-md-3 col-xl-2 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Pace</h5>
                    <p class="card-text">Convert running paces and speeds.</p>
                    <a href="pace/" class="btn btn-primary stretched-link mt-auto">Pace</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-md-3 col-xl-2 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">BPM</h5>
                    <p class="card-text">Tap to determine the BPM of a song!</p>
                    <a href="bpm/" class="btn btn-primary stretched-link mt-auto">BPM</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-md-3 col-xl-2 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Work</h5>
                    <p class="card-text">Focus on your work, not your time registration.</p>
                    <a href="work/" class="btn btn-primary stretched-link mt-auto">Work</a>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-md-3 col-xl-2 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Bingo Bongo</h5>
                    <p class="card-text">Suspiciously specific bingo card generator.</p>
                    <a href="bingo/" class="btn btn-primary stretched-link mt-auto">Bingo Bongo</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $app->renderView('footer.phtml', [
    'toolboxButton' => false,
    'content' => '<a class="btn btn-link" href="https://github.com/villermen/toolbox">Source</a>',
]); ?>
