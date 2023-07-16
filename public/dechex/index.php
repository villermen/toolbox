<?php

require_once('../../vendor/autoload.php');

$app = new \Villermen\Toolbox\App();
?>
<?= $app->renderView('header.phtml', [
    'title' => 'DECHEX',
    'script' => $app->createPath('dechex/script.js'),
]); ?>
<form>
    <input name="query" type="text" />
    <p>From</p>
    <label>
        <input name="system" type="radio" value="dec" />
        Decimal
    </label><br />
    <label>
        <input name="system" type="radio" value="hex" />
        Hexadecimal
    </label><br />
    <label>
        <input name="system" type="radio" value="bin" />
        Binary
    </label><br />
    <p>Endianness (out)</p>
    <label>
        <input name="endianness" type="radio" value="big" />
        Big-endian (BE, significant-first)
    </label><br />
    <label>
        <input name="endianness" type="radio" value="little" />
        Little-endian (LE, significant-last)
    </label>
</form>
<p>Result</p>
<table>
    <tbody>
        <tr>
            <td>Decimal</td>
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
