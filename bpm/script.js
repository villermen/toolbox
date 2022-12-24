document.addEventListener('DOMContentLoaded', () => {
    const resetButton = document.getElementById('resetButton');

    let firstTapTime = null;
    let lastTapTime = null;
    let tapCount = 0;
    function tap(time) {
        if (firstTapTime === null) {
            firstTapTime = time;
        }

        lastTapTime = time;
        tapCount++;

        updateResults();
    }

    function reset() {
        firstTapTime = null;
        elapsedTime = null;
        tapCount = 0;

        updateResults();
    }

    function updateResults() {
        let bpmResult = 0.0;
        const elapsedTime = (firstTapTime !== null ? Math.round(lastTapTime - firstTapTime) : 0);
        if (tapCount > 1) {
            // Last tap is removed because that beat has only just started.
            bpmResult = (tapCount - 1) / elapsedTime * 60000.0;
        }

        document.getElementById('bpmResult').innerText = String(Math.round(bpmResult * 100) / 100);
        document.getElementById('bpsResult').innerText = String(Math.round(bpmResult / 60 * 100) / 100);
        document.getElementById('timeResult').innerText = String(elapsedTime / 1000) + 's';
        document.getElementById('tapResult').innerText = String(tapCount);
    }
    
    document.addEventListener('mousedown', (event) => {
        if (!['HTML', 'BODY'].includes(event.target.tagName) || event.button !== 0) {
            return;
        }

        event.preventDefault();
        tap(event.timeStamp);
    });
    document.addEventListener('touchstart', (event) => {
        if (!['HTML', 'BODY'].includes(event.target.tagName)) {
            return;
        }

        tap(event.timeStamp);
    });
    document.addEventListener('touchend', (event) => {
        if (!['HTML', 'BODY'].includes(event.target.tagName)) {
            return;
        }

        // Will prevent mousedown triggering in touch+mouse setups.
        event.preventDefault();
    });
    document.addEventListener('keydown', (event) => {
        if ([' ', 'Enter'].includes(event.key) && !event.repeat) {
            tap(event.timeStamp);
        }

        if (event.key === 'Escape') {
            reset();
        }
    });
    resetButton.addEventListener('click', () => {
        console.log('click');
        // Note: Triggers after processing mousedown.
        reset();
    });

    reset();
});
