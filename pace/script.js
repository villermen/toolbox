const halfDistance = 21.0975;
const marathonDistance = 42.195;
const schoolSchootingConversionRate = 1.609344;

document.addEventListener('DOMContentLoaded', () => {
    const paceInput = document.querySelector('[name=pace]');
    const burgerKingPaceInput = document.querySelector('[name=burgerKingPace]');
    const speedInput = document.querySelector('[name=speed]');
    const fiveTimeInput = document.querySelector('[name=fiveTime]');
    const tenTimeInput = document.querySelector('[name=tenTime]');
    const halfTimeInput = document.querySelector('[name=halfTime]');
    const marathonTimeInput = document.querySelector('[name=marathonTime]');
    const feedbackButton = document.getElementById('feedbackButton');

    function update(speed, inputWeDontTouch) {
        let paceFormatted = '???';
        let speedFormatted = '???';
        let burgerKingPaceFormatted = '???';
        let fiveTimeFormatted = '???';
        let tenTimeFormatted = '???';
        let halfTimeFormatted = '???';
        let marathonTimeFormatted = '???';

        if (isFinite(speed) && speed > 0.0) {
            const paceSeconds = Math.floor(3600.0 / speed);
            const burgerKingSpeed = speed / schoolSchootingConversionRate;

            paceFormatted = formatTime(paceSeconds);
            speedFormatted = String(Math.round(speed * 100.0) / 100.0);
            burgerKingPaceFormatted = Math.floor(60.0 / burgerKingSpeed) + ":" + String(Math.floor(60.0 % burgerKingSpeed)).padStart(2, '0') ;
            fiveTimeFormatted = formatTime(paceSeconds * 5.0);
            tenTimeFormatted = formatTime(paceSeconds * 10.0);
            halfTimeFormatted = formatTime(paceSeconds * halfDistance);
            marathonTimeFormatted = formatTime(paceSeconds * marathonDistance);
        }
        
        if (speedInput !== inputWeDontTouch) {
            speedInput.value = speedFormatted;
        }
        if (paceInput !== inputWeDontTouch) {
            paceInput.value = paceFormatted;
        }
        if (burgerKingPaceInput !== inputWeDontTouch) {
            burgerKingPaceInput.value = burgerKingPaceFormatted;
        }
        if (fiveTimeInput !== inputWeDontTouch) {
            fiveTimeInput.value = fiveTimeFormatted;
        }
        if (tenTimeInput !== inputWeDontTouch) {
            tenTimeInput.value = tenTimeFormatted;
        }
        if (halfTimeInput !== inputWeDontTouch) {
            halfTimeInput.value = halfTimeFormatted;
        }
        if (marathonTimeInput !== inputWeDontTouch) {
            marathonTimeInput.value = marathonTimeFormatted;
        }
    }

    function parseTime(value) {
        const parts = value.split(':').reverse();
        if (parts.length === 0) {
            return NaN;
        }

        let seconds = Number(parts[0]);
        if (parts.length > 1) {
            seconds += Number(parts[1]) * 60;
        }
        if (parts.length > 2) {
            seconds += Number(parts[2]) * 3600;
        }

        return (seconds || NaN);
    }

    function formatTime(value) {
        let result = '';
        const hours = Math.floor(value / 3600);
        if (hours > 0) {
            result += String(hours).padStart(2, '0') + ':';
        }

        value %= 3600;
        const minutes = Math.floor(value / 60);
        if (hours > 0 || minutes > 0) {
            result += String(minutes).padStart(2, '0') + ':';
        }

        value %= 60;
        const seconds = Math.floor(value);
        if (hours > 0 || minutes > 0 || seconds > 0) {
            result += String(seconds).padStart(2, '0');
        }

        return result;
    }

    speedInput.addEventListener('input', () => {
        update(Number(speedInput.value), speedInput);
    });
    paceInput.addEventListener('input', () => {
        update(3600 / parseTime(paceInput.value), paceInput);
    });
    burgerKingPaceInput.addEventListener('input', () => {
        update(3600 / parseTime(burgerKingPaceInput.value) * schoolSchootingConversionRate, burgerKingPaceInput);
    });
    fiveTimeInput.addEventListener('input', () => {
        update(3600 / (parseTime(fiveTimeInput.value) / 5.0), fiveTimeInput);
    });
    tenTimeInput.addEventListener('input', () => {
        update(3600 / (parseTime(tenTimeInput.value) / 10.0), tenTimeInput);
    });
    halfTimeInput.addEventListener('input', () => {
        update(3600 / (parseTime(halfTimeInput.value) / halfDistance), halfTimeInput);
    });
    marathonTimeInput.addEventListener('input', () => {
        update(3600 / (parseTime(marathonTimeInput.value) / marathonDistance), marathonTimeInput);
    });
    feedbackButton.addEventListener('click', () => {
        const audio = new Audio('./wefwkc.wav');
        audio.play();
    });

    update(15.0, null);
});
