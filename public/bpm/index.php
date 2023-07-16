<?php

require_once('../../vendor/autoload.php');

$app = new \Villermen\Toolbox\App();
?>
<?= $app->renderView('header.phtml', [
    'title' => 'BPM checker',
    'script' => $app->createPath('bpm/script.js'),
]); ?>
<div class="card m-auto text-bg-light no-tap-zone">
    <div class="card-header">
        Press space, click or tap anywhere on this page to the beat to check BPM.
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <tbody>
                <tr>
                    <td class="w-25">BPM</td>
                    <td id="bpmResult"></td>
                </tr>
                <tr>
                    <td>BPS</td>
                    <td id="bpsResult"></td>
                </tr>
                <tr>
                    <td>Time</td>
                    <td id="timeResult"></td>
                </tr>
                <tr>
                    <td>Taps</td>
                    <td id="tapResult"></td>
                </tr>
            </tbody>
        </table>
        <button id="resetButton" class="btn btn-secondary float-end">Reset (Esc)</button>
    </div>
</div>
<?= $app->renderView('footer.phtml'); ?>
