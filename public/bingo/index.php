<?php

require_once('../../vendor/autoload.php');

$app = new \Villermen\Toolbox\App();
?>
<?= $app->renderView('header.phtml', [
    'title' => 'Bingo Bongo',
    'head' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" defer></script>',
    'script' => $app->createPath('bingo/script.js'),
]); ?>
<div class="row">
    <div class="col-md-6">
        <h3>Options</h3>
        <form id="bongoForm">
            <div class="form-group mb-2">
                <label class="form-label" for="backgroundImageInput">Background (1x1.4142, JPEG = fastest)</label>
                <input class="form-control" type="file" id="backgroundImageInput" accept="image/*">
            </div>
            <div class="form-group mb-2">
                <label class="form-label" for="freeSpotImageInput">Free spot</label>
                <input class="form-control" type="file" id="freeSpotImageInput" accept="image/*">
            </div>
            <div class="row mb-2">
                <label class="col-form-label col-4" for="pageSizeSelect">Page size</label>
                <div class="col-8">
                    <select class="form-control" name="pageSize" id="pageSizeSelect">
                        <option value="a4">A4</option>
                        <option value="a5">A5</option>
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-form-label col-4" for="fontSelect">Font</label>
                <div class="col-8">
                    <select class="form-control" name="font" id="fontSelect">
                        <option value="cabinsketch">Cabin Sketch Bold</option>
                    </select>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-form-label col-4" for="startXInput">Horizontal offset</label>
                <div class="col-8">
                    <div class="input-group">
                        <input class="form-control" type="number" name="startX" id="startXInput" min="0" max="100" step="any" value="6.00" />
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-form-label col-4" for="startYInput">Vertical offset</label>
                <div class="col-8">
                    <div class="input-group">
                        <input class="form-control" type="number" name="startY" id="startYInput" min="0" max="100" step="any" value="20.60" />
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-form-label col-4" for="tileSizeInput">Tile size (width and height)</label>
                <div class="col-8">
                    <div class="input-group">
                        <input class="form-control" type="number" name="tileSize" id="tileSizeInput" min="0" max="100" step="any" value="15.10" />
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-form-label col-4" for="tileSpacingInput">Tile spacing (gutter)</label>
                <div class="col-8">
                    <div class="input-group">
                        <input class="form-control" type="number" name="tileSpacing" id="tileSpacingInput" min="0" max="100" step="any" value="3.10" />
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <label class="form-label col-4" for="overlayCheckbox">Debug overlay</label>
                <div class="col-8">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="overlayCheckbox"
                            name="overlayEnabled"
                            checked
                        />
                    </div>
                </div>
            </div>
            <div class="form-group mb-2">
                <label class="form-label" for="optionsInput">
                    Options (<span id="optionCount">#</span>/24)
                </label>
                <textarea
                    class="form-control"
                    name="options"
                    id="optionsInput"
                    style="height: 400px; resize: vertical;"
                ></textarea>
            </div>
            <div class="row mb-2">
                <label class="col-form-label col-4" for="seedInput">Seed (value or range)</label>
                <div class="col-8">
                    <input class="form-control" type="text" name="seed" id="seedInput" placeholder="Random!" />
                </div>
            </div>
            <div class="row mb-2">
                <label class="col-form-label col-4" for="footerInput">Footer</label>
                <div class="col-8">
                    <input class="form-control" type="text" name="footer" id="footerInput" value="B01-{page}-{seed}" />
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="bongoButton">Bongo!</button>
        </form>
    </div>
    <div class="col-md-6">
        <h3>Preview</h3>
        <div class="text-center" style="min-height: 500px; height: calc(100% - 41px);">
            <div id="loadingIndicator" class="d-flex align-items-center justify-content-center gap-2">
                <div id="loadingIndicator" class="spinner-border" role="status"></div>
                Rendering...
            </div>
            <embed id="previewEmbed" type="application/pdf" class="d-none" style="width:100%; height: 100%;" />
        </div>
    </div>
</div>
<?= $app->renderView('footer.phtml'); ?>
