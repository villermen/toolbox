<?php

require_once('../../vendor/autoload.php');

$app = new \Villermen\Toolbox\App();
?>
<?= $app->renderView('header.phtml', [
    'title' => 'DECHEX',
    'script' => $app->createPath('dechex/script.js'),
]); ?>
<form>
    <div class="mb-3">
        <label class="form-label" for="queryInput">Convert</label>
        <input class="form-control" name="query" type="text" id="queryInput" />
    </div>
    <div class="mb-3">
        <label class="form-label" for="systemRadioDec">From</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="system" value="dec" id="systemRadioDec" checked>
            <label class="form-check-label" for="systemRadioDec">
                Decimal
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="system" value="hex" id="systemRadioHex">
            <label class="form-check-label" for="systemRadioHex">
                Hexadecimal
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="system" value="bin" id="systemRadioBin">
            <label class="form-check-label" for="systemRadioBin">
                Binary
            </label>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="endianRadioBig">Endianness (out)</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="endianness" value="big" id="endianRadioBig" checked>
            <label class="form-check-label" for="endianRadioBig">
                Big-endian (BE, significant-first)
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="endianness" value="little" id="endianRadioLittle" checked>
            <label class="form-check-label" for="endianRadioLittle">
                Little-endian (LE, significant-last)
            </label>
        </div>
    </div>
</form>
<p><strong>Result</strong></p>
<table class="table">
    <tbody>
        <tr>
            <td style="width: 170px;">Decimal</td>
            <td id="resultDec"></td>
            <td id="resultDecFormatted"></td>
        </tr>
        <tr>
            <td>Hexadecimal (0x)</td>
            <td id="resultHex"></td>
            <td id="resultHexFormatted"></td>
        </tr>
        <tr>
            <td>Binary (0b)</td>
            <td id="resultBin"></td>
            <td id="resultBinFormatted"></td>
        </tr>
    </tbody>
</table>
<?= $app->renderView('footer.phtml'); ?>
