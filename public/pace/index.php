<?php

require_once('../../vendor/autoload.php');

$app = new \Villermen\Toolbox\App();
?>
<?= $app->renderView('header.phtml', [
    'title' => 'Running pace converter',
    'script' => $app->createPath('pace/script.js'),
]); ?>
<p class="lead">Change a value in any of the inputs below and the other values will automatically update to match.<p>
<form class="pace-form">
    <div class="row mb-2">
        <label for="paceInput" class="col-sm-3 col-lg-2 col-form-label">Pace</label>
        <div class="col-sm-9 col-lg-10">
            <div class="input-group">
                <input id="paceInput" name="pace" type="text" class="form-control" />
                <label class="input-group-text" for="paceInput">/ km</label>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <label for="paceInput" class="col-sm-3 col-lg-2 col-form-label">Speed</label>
        <div class="col-sm-9 col-lg-10">
            <div class="input-group">
                <input id="speedInput" name="speed" type="text" class="form-control" />
                <label class="input-group-text" for="speedInput">km/h</label>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <label for="paceInput" class="col-sm-3 col-lg-2 col-form-label">Burger King®️ pace</label>
        <div class="col-sm-9 col-lg-10">
            <div class="input-group">
                <input id="burgerKingPaceInput" name="burgerKingPace" type="text" class="form-control" />
                <label class="input-group-text" for="burgerKingPaceInput">/ mi</label>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <label for="fiveTimeInput" class="col-sm-3 col-lg-2 col-form-label">5K time</label>
        <div class="col-sm-9 col-lg-10">
            <div class="input-group">
                <input id="fiveTimeInput" name="fiveTime" type="text" class="form-control" />
                <label class="input-group-text" for="fiveTimeInput">/ 5 km</label>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <label for="tenTimeInput" class="col-sm-3 col-lg-2 col-form-label">10K time</label>
        <div class="col-sm-9 col-lg-10">
            <div class="input-group">
                <input id="tenTimeInput" name="tenTime" type="text" class="form-control" />
                <label class="input-group-text" for="tenTimeInput">/ 10 km</label>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <label for="halfTimeInput" class="col-sm-3 col-lg-2 col-form-label">Half marathon time</label>
        <div class="col-sm-9 col-lg-10">
            <div class="input-group">
                <input id="halfTimeInput" name="halfTime" type="text" class="form-control" />
                <label class="input-group-text" for="halfTimeInput">/ 21.0975 km</label>
            </div>
        </div>
    </div>
    <div class="row mb-2">
        <label for="marathonTimeInput" class="col-sm-3 col-lg-2 col-form-label">Marathon time</label>
        <div class="col-sm-9 col-lg-10">
            <div class="input-group">
                <input id="marathonTimeInput" name="marathonTime" type="text" class="form-control" />
                <label class="input-group-text" for="marathonTimeInput">/ 42.195 km</label>
            </div>
        </div>
    </div>
</form>
<?= $app->renderView('footer.phtml', [
    'content' => '<button type="button" class="btn btn-link" id="feedbackButton">Feedback?</button>',
]); ?>
