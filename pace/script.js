const freedomUnitConversionRate = 1.609344;

document.addEventListener('DOMContentLoaded', () => {
    const paceInput = document.querySelector('[name=pace]');
    const burgerKingPaceInput = document.querySelector('[name=burgerKingPace]');
    const speedInput = document.querySelector('[name=speed]');
    const feedbackButton = document.getElementById('feedbackButton');

    function update(speed, inputWeDontTouch) {
        if (isFinite(speed) && speed > 0) {
            pace = Math.floor(60.0 / speed) + ":" + String(Math.floor(60.0 % speed)).padStart(2, '0') ;

            burgerKingSpeed = speed / freedomUnitConversionRate;
            burgerKingPace = Math.floor(60.0 / burgerKingSpeed) + ":" + String(Math.floor(60.0 % burgerKingSpeed)).padStart(2, '0') ;
        } else {
            pace = '???';
            burgerKingPace = '???';
        }
        
        if (speedInput !== inputWeDontTouch) {
            speedInput.value = speed;
        }
        if (paceInput !== inputWeDontTouch) {
            paceInput.value = pace;
        }
        if (burgerKingPaceInput !== inputWeDontTouch) {
            burgerKingPaceInput.value = burgerKingPace;
        }
    }

    speedInput.addEventListener('input', () => {
        const speed = Number(speedInput.value);
        update(speed, speedInput);
    });
    paceInput.addEventListener('input', () => {
        const [minutes, seconds] = paceInput.value.split(':');
        update(3600 / (Number(minutes) * 60 + Number(seconds)), paceInput);
    });
    burgerKingPaceInput.addEventListener('input', () => {
        const [minutes, seconds] = burgerKingPaceInput.value.split(':');
        update(3600 / (Number(minutes) * 60 + Number(seconds)) * freedomUnitConversionRate, burgerKingPaceInput);
    });
    feedbackButton.addEventListener('click', () => {
        const audio = new Audio('./wefwkc.wav');
        audio.play();
    });

    update(25, null);
});
