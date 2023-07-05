// TODO: Save settings in localStorage.
const { jsPDF } = window.jspdf;

const bongoForm = document.getElementById('bongoForm');
const bongoButton = document.getElementById('bongoButton');
const optionCount = document.getElementById('optionCount');
const backgroundImageInput = document.getElementById('backgroundImageInput');
const freeSpotImageInput = document.getElementById('freeSpotImageInput');
const previewEmbed = document.getElementById('previewEmbed');

/** @type {HTMLImageElement|null} */
let backgroundImage = null;
/** @type {HTMLImageElement|null} */
let freeSpotImage = null;

let fallbackSeed = createRandomSeed();

// TODO: This can hopefully be simplified.
// TODO: Wait for load before initial render.
let font = null;
fetch('./CabinSketch-Regular.ttf').then((response) => {
    response.blob().then((blob) => {
        const fileReader = new FileReader();
        fileReader.readAsDataURL(blob);
        fileReader.onloadend = () => {
            font = fileReader.result.replace(/^data:font\/ttf;base64,/, '');
        }
    });
});

/**
 * @param {File} file
 * @returns {Promise<HTMLImageElement>}
 */
function loadImage(file) {
    const fileReader = new FileReader();
    fileReader.readAsDataURL(file);

    return new Promise((resolve, reject) => {
        fileReader.addEventListener('load', () => {
            const image = document.createElement('img');
            image.src = fileReader.result;
            image.addEventListener('load', () => {
                resolve(image);
            });
            image.addEventListener('error', () => {
                reject('Could not load image!');
            });
        })
        fileReader.addEventListener('error', () => {
            reject('Could not read file!');
        });
    });
}

/**
 * https://stackoverflow.com/a/47593316
 *
 * @param {string} seed
 */
function createRandom(seed) {
    function xmur3(str) {
        let h = 1779033703 ^ str.length;
        for (let i = 0; i < str.length; i++) {
            h = Math.imul(h ^ str.charCodeAt(i), 3432918353);
            h = h << 13 | h >>> 19;
        }
        return function() {
            h = Math.imul(h ^ (h >>> 16), 2246822507);
            h = Math.imul(h ^ (h >>> 13), 3266489909);
            return (h ^= h >>> 16) >>> 0;
        }
    }
    function sfc32(a, b, c, d) {
        return function() {
            a >>>= 0; b >>>= 0; c >>>= 0; d >>>= 0;
            let t = (a + b) | 0;
            a = b ^ b >>> 9;
            b = c + (c << 3) | 0;
            c = (c << 21 | c >>> 11);
            d = d + 1 | 0;
            t = t + d | 0;
            c = c + t | 0;
            return (t >>> 0) / 4294967296;
        }
    }

    seed = xmur3(seed);
    return sfc32(seed(), seed(), seed(), seed());
}

function createRandomSeed() {
    const seedCharacters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    let seed = ''
    for (let i = 0; i < 8; i++) {
        seed += seedCharacters[Math.floor(Math.random() * seedCharacters.length)]
    }

    return seed;
}

/**
 * Splits text very subjectively.
 *
 * @param {string} text
 * @param {number} [maxLines]
 */
function splitText(text, maxLines) {
    let lines = [text.replace(/\s+/, ' ').trim()];
    let linesLeft = maxLines - 1;

    // Split standalone separators.
    const split = text.split(/ (\W) /);
    if (split.length <= maxLines) {
        lines = split;
    }

    while (lines.length < maxLines) {
        // Split longest splittable line (long enough and contains space).
        const splitLine = lines
            .filter((line) => line.length >= 12 && line.match(/ /) !== null)
            .reduce((previous, current) => {
                if (previous === null) {
                    return current;
                }

                return current.length > previous.length ? current : previous;
            }, null);

        if (splitLine !== null) {
            lines = lines.map((line) => {
                if (line !== splitLine) {
                    return line;
                }

                // Split line into two balanced groups, preferring the first part.
                const words = line.split(/ /);
                let part1 = words[0];
                let part2 = words.slice(1).join(' ');
                while (part1.length < part2.length && part2.match(/ /) !== null) {
                    const part2Words = part2.split(/ /);
                    part1 += ` ${part2Words[0]}`;
                    part2 = part2Words.slice(1).join(' ');
                }

                return [part1, part2];
            }).flat();
            continue;
        }

        // No more splits possible. End.
        break;
    }

    return lines;
}

/**
 *
 * @param {number|string} mm
 * @return {number}
 */
function mmToPt(mm) {
    return Number(mm) * 0.75;
}

function render() {
    // Parse form values.
    const formData = new FormData(bongoForm);

    // TODO: Load images here too (with cache).
    const startX = mmToPt(formData.get('startX'));
    const startY = mmToPt(formData.get('startY'));
    const tileSize = mmToPt(formData.get('tileSize'));
    const tileSpacing = mmToPt(formData.get('tileSpacing'));
    const options = formData.get('options').split(/\n/).filter((line) => line.trim().length > 0);
    const seed = (formData.get('seed') || fallbackSeed);
    const overlayEnabled = formData.get('overlayEnabled');

    optionCount.innerText = options.length;

    const fontSize = (tileSize / 6);

    const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'pt', // Native unit of PDF
        format: 'a4',
        compress: true,
    });
    pdf.addFileToVFS('bingo.ttf', font);
    pdf.addFont('bingo.ttf', 'bingo', 'Bold');

    const width = pdf.internal.pageSize.getWidth();
    const height = pdf.internal.pageSize.getHeight();
    console.log(width, height);

    if (backgroundImage) {
        pdf.addImage(backgroundImage, null, 0, 0, width, height, 'background');
    }

    // Debug information.
    if (overlayEnabled) {
        pdf.setFont('Helvetica');
        pdf.setFontSize(15);
        pdf.setTextColor('#000000');

        let debugTextY = 10;

        /** @param {string} message */
        function drawDebugMessage(message) {
            pdf.text(message, width - 10, debugTextY, {
                baseline: 'top',
                align: 'right',
            });
            debugTextY += 20;
        }

        drawDebugMessage(`Seed: ${seed}`);

        if (!backgroundImage) {
            drawDebugMessage('No background image!');
        }
        if (options.length < 24) {
            drawDebugMessage(`Not enough options (${options.length}/24)!`);
        }
    }

    // Shuffle and limit options.
    const random = createRandom(seed);
    let remainingOptions = [...options];
    const shuffledOptions = [];
    while (remainingOptions.length > 0 && shuffledOptions.length < 24) {
        shuffledOptions.push(remainingOptions.splice(Math.floor(random() * remainingOptions.length), 1)[0]);
    }

    // Draw tiles.
    pdf.setFont('bingo', 'Bold');
    pdf.setFontSize(fontSize * 0.75);
    pdf.setTextColor(0, 0, 0);

    for (let tileId = 0; tileId < 25; tileId++) {
        const tileX = startX + ((tileSize + tileSpacing) * (tileId % 5));
        const tileY = startY + ((tileSize + tileSpacing) * Math.floor(tileId / 5));

        if (tileId === 12) {
            // Draw free spot image.
            if (freeSpotImage) {
                pdf.addImage(freeSpotImage, null, tileX, tileY, tileSize, tileSize, 'free_spot');
            }
            continue;
        }

        if (overlayEnabled) {
            pdf.setDrawColor(0, 255, 0);
            pdf.setLineWidth(1);
            pdf.rect(tileX, tileY, tileSize, tileSize);
        }

        const optionIndex = (tileId < 12 ? tileId : tileId -1);
        if (optionIndex < shuffledOptions.length) {
            const lines = splitText(shuffledOptions[optionIndex], 6);
            let lineY = (tileY + (tileSize / 2)) - ((lines.length - 1) / 2) * fontSize;
            lines.forEach((line) => {
                pdf.text(line, tileX + (tileSize / 2), lineY, {
                    maxWidth: tileSize,
                    align: 'center',
                    baseline: 'middle',

                });
                lineY += fontSize;
            });
        }
    }

    const pdfData = pdf.output('datauristring', {
        filename: 'bingo-preview.pdf',
    });
    previewEmbed.src = pdfData;

    // pdf.save('bongo.pdf');
}

// Event listeners
bongoForm.addEventListener('change', () => render());

bongoForm.addEventListener('submit', (event) => {
    event.preventDefault();
    render();
});

overlayCheckbox.addEventListener('change', () => {
    overlayEnabled = overlayCheckbox.checked;
    render();
});

bongoButton.addEventListener('click', () => {
    // Force refresh fallback seed so it stays the same unless the power of the bongo is wielded.
    fallbackSeed = createRandomSeed();
    render();
});

backgroundImageInput.addEventListener('change', async () => {
    backgroundImage = await loadImage(backgroundImageInput.files[0]);
    render();
});
freeSpotImageInput.addEventListener('change', async () => {
    freeSpotImage = await loadImage(freeSpotImageInput.files[0]);
    render();
});

// We need font to be ready before we render (no DOMContentLoaded)
window.addEventListener('load', () => {
    render();
});
