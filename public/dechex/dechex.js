document.addEventListener('DOMContentLoaded', () => {
    const queryInput = document.querySelector('[name=query]');
    const systemInputs = Array.from(document.querySelectorAll('[name=system]'));
    const endiannessInputs = Array.from(document.querySelectorAll('[name=endianness]'));
    const allInputs = [queryInput, ...systemInputs, ...endiannessInputs];

    let query = '';
    let system = 'dec';
    let endianness = 'big';
    function updateInputs() {
        queryInput.value = query;
        systemInputs.forEach((input) => {
            input.checked = input.value === system;
        });
        endiannessInputs.forEach((input) => {
            input.checked = input.value === endianness;
        });
    }
    updateInputs();

    function parseInputs() {
        query = queryInput.value;
        system = systemInputs.find((input) => input.checked).value;
        endianness = endiannessInputs.find((input) => input.checked).value;
    }

    function updateResults() {
        let queryInt = null;
        if (system === 'dec') {
            queryInt = BigInt(query); // TODO: Parse.
        } else if (system === 'hex') {
            queryInt = BigInt('0x' + query); // TODO: Parse.
        } else if (system === 'bin') {
            queryInt = BigInt('0b' + query); // TODO: Parse.
        } else {
            throw Error('Invalid number system.');
        }

        let resultDec = 0n;
        let resultDecFormatted = '';
        let resultHex = '';
        let resultHexFormatted = '';
        let resultBin = '';
        let resultBinFormatted = '';

        let divisor = 1n;
        do {
            const remainder = divisor * 256n;
            const byteDec =  queryInt % remainder / divisor;
            resultDec += byteDec * divisor;
            
            const byteHex = byteDec.toString(16).toUpperCase().padStart(2, '0');
            resultHex = byteHex + resultHex;
            resultHexFormatted = byteHex + ' ' + resultHexFormatted;
            
            const byteBin = byteDec.toString(2).padStart(8, '0');
            resultBin = byteBin + resultBin;
            resultBinFormatted = byteBin + ' ' + resultBinFormatted;

            divisor = remainder;
        } while (divisor < queryInt);

        document.getElementById('resultDec').innerText = resultDec;
        document.getElementById('resultDecFormatted').innerText = resultDecFormatted;
        document.getElementById('resultHex').innerText = resultHex;
        document.getElementById('resultHexFormatted').innerText = resultHexFormatted;
        document.getElementById('resultBin').innerText = resultBin;
        document.getElementById('resultBinFormatted').innerText = resultBinFormatted;
    }

    allInputs.forEach((input) => {
        input.addEventListener('input', () => {
            parseInputs();

            if (input === queryInput) {
                // Automatically switch base.
                if (system === 'dec' && /[A-F]/i.test(query)) {
                    system = 'hex';
                    updateInputs();
                }
            }

            updateResults();
        });
    });
});
