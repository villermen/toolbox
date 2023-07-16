// TODO: Save settings in localStorage.
const { jsPDF } = window.jspdf;

const bongoForm = document.getElementById('bongoForm');
const optionsInput = document.getElementById('optionsInput');
const optionCount = document.getElementById('optionCount');
const backgroundImageInput = document.getElementById('backgroundImageInput');
const freeSpotImageInput = document.getElementById('freeSpotImageInput');
const fontSelect = document.getElementById('fontSelect');
const loadingIndicator = document.getElementById('loadingIndicator');
const previewEmbed = document.getElementById('previewEmbed');

/** @type {HTMLImageElement|null} */
let backgroundImage = null;
/** @type {HTMLImageElement|null} */
let freeSpotImage = null;
/** @type {string|null} */
let font = null;

let fallbackSeed = createRandomSeed();

const placeholderOptions = [];
for (let i = 1; i <= 24; i++) {
    placeholderOptions.push(`Artist${i} - Song${i}`);    
}
optionsInput.placeholder = placeholderOptions.join('\n');

/** @type {number|null} */
let renderTimeoutId = null;
/** @type {string|null} */
let pdfObjectUrl = null;

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
 * @param {string} fontName 
 * @returns {Promise<string>}
 */
function loadFont(fontName) {
    let fontFile = null;
    switch (fontName) {
        case 'cabinsketch':
            fontFile = 'CabinSketch-Bold.ttf';
            break;
        default:
            throw Error('Invalid font name specified!');
    }

    return new Promise((resolve, reject) => {
        fetch(`./${fontFile}`).then((response) => {
            response.blob().then((blob) => {
                const fileReader = new FileReader();
                fileReader.readAsDataURL(blob);
                fileReader.onloadend = () => {
                    resolve(fileReader.result.replace(/^data:font\/ttf;base64,/, ''));
                }
            });
        });
    });
}

/**
 * https://stackoverflow.com/a/47593316
 *
 * @param {string} seed
 * @returns {() => number}
 */
function createRandom(seed) {
    function cyrb128(str) {
        let h1 = 1779033703, h2 = 3144134277,
            h3 = 1013904242, h4 = 2773480762;
        for (let i = 0, k; i < str.length; i++) {
            k = str.charCodeAt(i);
            h1 = h2 ^ Math.imul(h1 ^ k, 597399067);
            h2 = h3 ^ Math.imul(h2 ^ k, 2869860233);
            h3 = h4 ^ Math.imul(h3 ^ k, 951274213);
            h4 = h1 ^ Math.imul(h4 ^ k, 2716044179);
        }
        h1 = Math.imul(h3 ^ (h1 >>> 18), 597399067);
        h2 = Math.imul(h4 ^ (h2 >>> 22), 2869860233);
        h3 = Math.imul(h1 ^ (h3 >>> 17), 951274213);
        h4 = Math.imul(h2 ^ (h4 >>> 19), 2716044179);
        return [(h1^h2^h3^h4)>>>0, (h2^h1)>>>0, (h3^h1)>>>0, (h4^h1)>>>0];
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

    const seedParts = cyrb128(seed);
    return sfc32(seedParts[0], seedParts[1], seedParts[2], seedParts[3]);
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
 * @param {string} value 
 * @returns {string[]}
 */
function parseSeedValue(value) {
    if (!value) {
        return [fallbackSeed];
    }

    let seeds = [];
    value.split(',').forEach((subvalue) => {
        const rangeMatches = value.match(/(\d+)-(\d+)/)
        if (!rangeMatches) {
            seeds.push(subvalue);
            return;
        }

        const rangeMin = Math.min(Number(rangeMatches[1]), Number(rangeMatches[2]));
        const rangeMax = Math.max(Number(rangeMatches[1]), Number(rangeMatches[2]));

        if (rangeMax - rangeMin > 1000) {
            console.warn(`Not expanding seed range ${rangeMin}-${rangeMax} because it exceeds 1000 page limit.`);
            seeds.push(subvalue);
            return;
        }

        for (let i = rangeMin; i <= rangeMax; i++) {
            seeds.push(String(i));
        }
    });

    return seeds;
}

/**
 * Splits text very subjectively.
 *
 * @param {string} text
 * @param {number} [maxLines]
 */
function splitText(text, maxLines) {
    let lines = [text.replace(/\s+/, ' ').trim()];

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

function render() {
    previewEmbed.classList.add('d-none');
    loadingIndicator.classList.remove('d-none');

    // Parse form values.
    const formData = new FormData(bongoForm);

    // TODO: Load images/font here (with cache).
    let options = formData.get('options');
    if (options) {
        options = formData.get('options').split(/\n/).filter((line) => line.trim().length > 0);
    } else {
        options = placeholderOptions;
    }

    const seeds = parseSeedValue(formData.get('seed'));
    const overlayEnabled = formData.get('overlayEnabled');
    const footerFormat = formData.get('footer');
    const pageSize = formData.get('pageSize');

    optionCount.innerText = options.length;

    // ID is not cleared because we reuse it.
    if (renderTimeoutId) {
        window.clearTimeout(renderTimeoutId);
    }
    if (pdfObjectUrl) {
        window.URL.revokeObjectURL(pdfObjectUrl);
    }

    // Rendering happens in timeout to update loading state before hanging.
    renderTimeoutId = window.setTimeout(() => {
        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'pt', // Native unit of PDF.
            format: pageSize,
            compress: true,
        });
        pdf.addFileToVFS('bingo.ttf', font);
        pdf.addFont('bingo.ttf', 'bingo', '');

        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();

        const startX = Number(formData.get('startX')) / 100.0 * pageWidth;
        const startY = Number(formData.get('startY')) / 100.0 * pageHeight;
        // Note: Size and space use page width in both directions to stay square.
        const tileSize = Number(formData.get('tileSize')) / 100.0 * pageWidth;
        const tileSpacing = Number(formData.get('tileSpacing')) / 100.0 * pageWidth;

        seeds.forEach((seed, i) => {
            if (i > 0) {
                pdf.addPage(pageSize, 'portrait');
            }

            if (backgroundImage) {
                pdf.addImage(backgroundImage, null, 0, 0, pageWidth, pageHeight, 'background');
            }

            // Debug information.
            if (overlayEnabled) {
                pdf.setFont('Helvetica', '');
                pdf.setFontSize(10);
                pdf.setTextColor(255, 0, 0);

                let debugTextY = 10;

                /** @param {string|null} [message=null] */
                function drawDebugLine(message = null) {
                    if (message) {
                        pdf.text(message, pageWidth - 10, debugTextY, {
                            baseline: 'top',
                            align: 'right',
                        });
                    }
                    debugTextY += 13;
                }

                drawDebugLine(`Seed: ${seed}`);
                if (!backgroundImage) {
                    drawDebugLine('No background image!');
                } else {
                    const idealHeight =  Math.round(backgroundImage.width * Math.sqrt(2));
                    if (Math.abs(backgroundImage.height - idealHeight) > 1) {
                        drawDebugLine(`Stretching background (${backgroundImage.width}x${backgroundImage.height}). Ideal height: ${idealHeight}px.`)
                    }
                }
                if (options.length < 24) {
                    drawDebugLine(`Not enough options (${options.length}/24)!`);
                }
                drawDebugLine('');
                drawDebugLine(`Page size: ${pageWidth}x${pageHeight}pt`);
                drawDebugLine(`Tile offset: ${startX}x${startY}pt`);
                drawDebugLine(`Tile size+spacing: ${tileSize}+${tileSpacing}pt`);
            }

            // Shuffle and limit options.
            const random = createRandom(seed);
            let remainingOptions = [...options];
            const shuffledOptions = [];
            while (remainingOptions.length > 0 && shuffledOptions.length < 24) {
                shuffledOptions.push(remainingOptions.splice(Math.floor(random() * remainingOptions.length), 1)[0]);
            }

            // Draw tiles.
            const fontSize = (tileSize / 6);
            pdf.setFont('bingo', '');
            pdf.setFontSize(fontSize);
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
                    pdf.setLineWidth(0.75); // 1px
                    pdf.rect(tileX, tileY, tileSize, tileSize);
                }

                const optionIndex = (tileId < 12 ? tileId : tileId -1);
                if (optionIndex < shuffledOptions.length) {
                    const lines = splitText(shuffledOptions[optionIndex], 6);
                    let lineY = (tileY + (tileSize / 2)) - ((lines.length - 1) / 2) * fontSize;
                    lines.forEach((line) => {
                        pdf.text(line, tileX + (tileSize / 2), lineY, {
                            // maxWidth: tileSize, Option is out there, but we've already taken (better) manual control.
                            align: 'center',
                            baseline: 'middle',
                        });
                        lineY += fontSize;
                    });
                }
            }

            // Footer
            if (footerFormat) {
                const footer = footerFormat.replace('{seed}', seed).replace('{page}', i + 1);

                // https://stackoverflow.com/a/67185656/1871016
                pdf.saveGraphicsState();
                pdf.setFont('Helvetica', '');
                pdf.setFontSize(10);
                pdf.setTextColor(0, 0, 0);
                pdf.setGState(new pdf.GState({opacity: 0.1}));
                pdf.text(footer, 5, pageHeight - 5, {
                    baseline: 'bottom',
                    align: 'left',
                });
                pdf.restoreGraphicsState();
            }
        }, 0);

        const pdfData = pdf.output('blob');

        pdfObjectUrl = window.URL.createObjectURL(pdfData);
        previewEmbed.src = pdfObjectUrl;

        loadingIndicator.classList.add('d-none');
        previewEmbed.classList.remove('d-none');
    }, 0);
}

// Event listeners
bongoForm.addEventListener('submit', (event) => {
    event.preventDefault();

    // Force refresh fallback seed so it stays the same unless the power of the bongo is wielded.
    fallbackSeed = createRandomSeed();
    render();
});

backgroundImageInput.addEventListener('change', async () => {
    backgroundImage = await loadImage(backgroundImageInput.files[0]);
});
freeSpotImageInput.addEventListener('change', async () => {
    freeSpotImage = await loadImage(freeSpotImageInput.files[0]);
});
fontSelect.addEventListener('change', async () => {
    font = await loadFont(fontSelect.value);
});

// Initial render.
loadFont(fontSelect.value).then((loadedFont) => {
    font = loadedFont;
    render();
});
