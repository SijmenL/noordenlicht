window.addEventListener('load', init);

let exportButton;
let html;
let popUp
let body;
let cancelButton

function init() {
    exportButton = document.getElementById('export-button');
    html = document.querySelector('html')
    body = document.getElementById('app')
    popUp = document.getElementById('popUp');
    cancelButton = document.getElementById('cancelButton')

    exportButton.addEventListener('click', function () {

        const scrollPosition = window.scrollY;
        html.classList.add('no-scroll');
        window.scrollTo(0, scrollPosition);

        popUp.classList.remove('d-none')
    })

    cancelButton.addEventListener('click', () => {
        popUp.classList.add('d-none')
        html.classList.remove('no-scroll')
    });
}
