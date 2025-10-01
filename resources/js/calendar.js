window.addEventListener('load', init);

let isMobile;
let html;

function init() {
    console.log('loaded');

    html = document.querySelector('html');

    // Gather all event elements
    const events = document.querySelectorAll('.calendar-event');

    // Create a lookup object to store events by composite key (id + start)
    const eventsByKey = {};
    events.forEach(event => {
        // Use a composite key based on event id and start date

        const compositeKey = event.dataset.eventId + '-' + event.dataset.eventStartDate;
        if (!eventsByKey[compositeKey]) {
            eventsByKey[compositeKey] = [];
        }
        eventsByKey[compositeKey].push(event);
    });

    // Add event listeners to each event using composite keys for lookup
    events.forEach(event => {
        event.addEventListener('mouseover', () => {
            const compositeKey = event.dataset.eventId + '-' + event.dataset.eventStartDate;
            eventsByKey[compositeKey].forEach(e => {
                e.classList.add('calendar-event-hovered'); // Add a class for styling
            });
        });

        event.addEventListener('mouseout', () => {
            const compositeKey = event.dataset.eventId + '-' + event.dataset.eventStartDate;
            eventsByKey[compositeKey].forEach(e => {
                e.classList.remove('calendar-event-hovered');
            });
        });
    });
}

function positionPopup(event) {
    const popup = document.getElementById('event-popup');

    popup.style.transform = 'unset'; // Center the popup both horizontally and vertically
    popup.style.position = 'absolute';

    // Calculate the popup dimensions
    const popupWidth = popup.offsetWidth;
    const popupHeight = popup.offsetHeight;

    // Get the viewport dimensions and scroll positions
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const scrollTop = window.scrollY;
    const scrollLeft = window.scrollX;

    // Calculate the mouse position relative to the viewport
    let mouseX = event.clientX + scrollLeft;
    let mouseY = event.clientY + scrollTop;

    // Default positioning (right and below the cursor)
    let popupLeft = mouseX + 15;
    let popupTop = mouseY + 15;

    // Check for right overflow
    if ((popupLeft + popupWidth) > viewportWidth + scrollLeft) {
        popupLeft = mouseX - popupWidth - 15;
    }

    // Check for bottom overflow
    if ((popupTop + popupHeight) > viewportHeight + scrollTop) {
        popupTop = mouseY - popupHeight - 15;
    }

    // Check for left overflow (when flipped)
    if (popupLeft < scrollLeft) {
        popupLeft = mouseX + 15;
    }

    // Check for top overflow (when flipped)
    if (popupTop < scrollTop) {
        popupTop = mouseY + 15;
    }

    // Avoid navbar or other top-fixed elements
    const navbarOffset = 200; // Adjust based on your navbar height
    if (mouseY < navbarOffset) {
        popupTop = mouseY + 15;
    }

    // Apply the calculated positions
    popup.style.left = popupLeft + 'px';
    popup.style.top = popupTop + 'px';
}

// Attach event listeners to all calendar events for moving the popup
document.querySelectorAll('.calendar-event').forEach(eventDiv => {
    eventDiv.addEventListener('mousemove', function (event) {
        isMobile = window.innerWidth < 768;
        if (!isMobile) {
            openDisplay(eventDiv);
            positionPopup(event);
        }
    });

    eventDiv.addEventListener('mouseout', function () {
        isMobile = window.innerWidth < 768;
        if (!isMobile) {
            // Hide the popup when the mouse leaves the event
            const popup = document.getElementById('event-popup');
            popup.style.display = 'none';
        }
    });
});

function openDisplay(eventDiv) {
    console.log(isMobile);
    const image = eventDiv.getAttribute('data-image');
    const title = eventDiv.getAttribute('data-title');
    const content = eventDiv.getAttribute('data-content');
    const start = eventDiv.getAttribute('data-event-start');
    const end = eventDiv.getAttribute('data-event-end');

    // Update popup content
    if (image) {
        document.getElementById('popup-image').src = image;
        document.getElementById('popup-image').style.display = "block";
    } else {
        document.getElementById('popup-image').style.display = "none";
    }
    document.getElementById('popup-title').textContent = title;
    document.getElementById('popup-content').textContent = content;
    document.getElementById('date-start').textContent = start;
    document.getElementById('date-end').textContent = end;

    // Display the popup
    const popup = document.getElementById('event-popup');
    popup.style.display = 'block';
}
