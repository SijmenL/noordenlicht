import './bootstrap.js';

window.addEventListener('load', init);

let hamburgerIcon;
let closeHamburgerIcon;
let hamburgerMenu;
let body;
let select;
let buttonContainer;
let html;

function init() {
    deleteButtons()

    hamburgerIcon = document.getElementById('hamburger-icon');
    closeHamburgerIcon = document.getElementById('hamburger-close-icon');
    hamburgerMenu = document.getElementById('hamburger-menu');

    body = document.getElementById('app')
    html = document.querySelector('html')

    if (document.getElementById('select-roles')) {
        select = document.getElementById('select-roles');
        buttonContainer = document.getElementById('button-container');
        editRoles();
    }

    if (hamburgerMenu) {
        hamburgerMenu.addEventListener('click', hamburger)
    }

    if (document.getElementsByClassName('forum-image').length !== 0 || document.getElementsByClassName('zoomable-image').length !== 0) {
        setupImageZoom(); // Call the image zoom setup function
    }
}

function hamburger() {
    hamburgerIcon.classList.toggle('d-none');
    closeHamburgerIcon.classList.toggle('d-none');
}

function deleteButtons() {
    let allButtons = document.querySelectorAll('a[class^=delete-button]');
    let popUp;
    for (let i = 0; i < allButtons.length; i++) {
        allButtons[i].addEventListener('click', function (e) {

            const scrollPosition = window.scrollY;
            html.classList.add('no-scroll');
            window.scrollTo(0, scrollPosition);

            popUp = document.createElement('div');
            popUp.classList.add('popup');
            body.appendChild(popUp);

            let popUpBody = document.createElement('div');
            popUpBody.classList.add('popup-body');
            popUp.appendChild(popUpBody);

            let popUpTitle = document.createElement('h2');
            let deleteName = allButtons[i].getAttribute('data-name');
            popUpTitle.innerText = `Weet je zeker dat je ${deleteName} wilt verwijderen?`;
            popUpBody.appendChild(popUpTitle);

            let popUpUnderTitle = document.createElement('p');
            popUpUnderTitle.innerText = `Deze actie kan niet ongedaan gemaakt worden.`;
            popUpUnderTitle.classList.add('text-danger');
            popUpBody.appendChild(popUpUnderTitle);

            let buttonContainer = document.createElement('div');
            buttonContainer.classList.add('button-container');
            popUpBody.appendChild(buttonContainer);

            let continueButton = document.createElement('a');
            continueButton.classList.add('btn', 'btn-success');
            continueButton.innerText = 'Ja, verwijderen';
            buttonContainer.appendChild(continueButton);

            let cancelButton = document.createElement('a');
            cancelButton.classList.add('btn', 'btn-outline-danger');
            cancelButton.innerText = 'Nee, annuleren';
            buttonContainer.appendChild(cancelButton);

            continueButton.addEventListener('click', () => {
                window.location.href = allButtons[i].dataset.link;
                html.classList.remove('no-scroll');
            });

            cancelButton.addEventListener('click', () => {
                popUp.remove();
                html.classList.remove('no-scroll');
            });
        });
    }
}

function editRoles() {
    select.querySelectorAll('option').forEach(option => {
        const button = document.createElement('p');
        const autoSubmit = document.getElementById("auto-submit");
        button.title = option.getAttribute('data-description');
        button.textContent = option.textContent;
        button.textContent = option.textContent;
        button.classList.add('btn', 'btn-secondary');
        button.dataset.value = option.value;

        if (option.selected) {
            button.classList.add('btn-primary', 'text-white');
            button.classList.remove('btn-secondary');
        } else {
            button.classList.remove('btn-primary', 'text-white');
            button.classList.add('btn-secondary');
        }

        button.addEventListener('click', () => {
            if (option.selected) {
                option.selected = false;
                button.classList.remove('btn-primary', 'text-white');
                button.classList.add('btn-secondary');
                autoSubmit.submit();
            } else {
                option.selected = true;
                button.classList.add('btn-primary', 'text-white');
                button.classList.remove('btn-secondary');
                autoSubmit.submit();
            }
        });

        buttonContainer.appendChild(button);
    });
}

function setupImageZoom() {
    // Create overlay element
    const overlay = document.createElement('div');
    overlay.className = 'overlay';
    document.body.appendChild(overlay);

    // Create enlarged image container
    const enlargedImageContainer = document.createElement('div');
    enlargedImageContainer.className = 'enlarged-image-container';
    document.body.appendChild(enlargedImageContainer);

    // Create the enlarged image
    const enlargedImage = document.createElement('img');
    enlargedImage.className = 'enlarged-image';
    enlargedImageContainer.appendChild(enlargedImage);

    // Handle clicks on images
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('zoomable-image')) {
            const image = event.target;
            let imageSrc = image.src;

            // Replace '/compressed/' with '/uncompressed/' if found
            if (imageSrc.includes('/compressed/')) {
                imageSrc = imageSrc.replace('/compressed/', '/');
            }

            enlargedImage.src = imageSrc;

            // Show the overlay and the enlarged image
            overlay.style.opacity = '1';
            overlay.style.pointerEvents = 'auto';
            overlay.style.zIndex = '99999';
            enlargedImageContainer.style.display = 'block';
            enlargedImageContainer.style.zIndex = '100000';

            const scrollPosition = window.scrollY;
            html.classList.add('no-scroll');
            window.scrollTo(0, scrollPosition);
        }
    });

    // Handle click on the overlay to close the image view
    overlay.addEventListener('click', discardOverlay);
    enlargedImageContainer.addEventListener('click', discardOverlay);

    function discardOverlay(e) {
        e.preventDefault();
        // Hide the enlarged image and overlay
        enlargedImageContainer.style.display = 'none';
        enlargedImage.src = '';
        overlay.style.opacity = '0';
        overlay.style.pointerEvents = 'none';
        html.classList.remove('no-scroll');
    }
}





