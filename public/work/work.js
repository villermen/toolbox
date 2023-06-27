const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

document.querySelector('[name=autoBreakEnabled]').addEventListener('change', (event) => {
    document.querySelector('#autoBreakRangeInputs').classList.toggle('d-none', !event.currentTarget.checked);
});
