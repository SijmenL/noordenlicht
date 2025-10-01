window.addEventListener('load', init);

let userSelectWindows;
let userSelectSearch;
let userSelectWindowPopup;
let userList;
let userSelectResult;
let body;
let displayListItems = [];
let forum;

function init() {
    console.log('searchuser code active')

    userSelectWindows = document.querySelectorAll('.user-select-window');
    userSelectSearch = document.querySelectorAll('.user-select-search');
    userSelectWindowPopup = document.querySelectorAll('.user-select-window-popup');
    userSelectResult = document.querySelectorAll('.user-select-result');
    userList = document.querySelectorAll('.user-list');
    forum = document.querySelectorAll('.user-select-forum-submit')

    body = document.getElementById('app');


    userSelectWindows.forEach(function (select, index) {
    if (userSelectSearch[0].dataset.stayopen !== 'true') {
        document.addEventListener('click', function (event) {
            // Iterate over each user-select-window input
            userSelectWindows.forEach(function (select, index) {
                // Check if the click event target is not the input field or its corresponding popup
                if (event.target !== select && !isDescendant(event.target, userSelectWindowPopup[index])) {
                    // Close the popup if it's open
                    userSelectWindowPopup[index].classList.add('d-none');
                    userSelectSearch[index].value = '';
                    userList[index].innerHTML = '<div class="w-100 h-100 d-flex justify-content-center align-items-center"><span class="material-symbols-rounded rotating" style="font-size: xxx-large">progress_activity</span></div>';
                }
            });
        });
    } else {
        searchUsers(' ', index);
    }

        userSelectSearch[index].addEventListener('keypress', function (event) {
            if (event.key === "Enter") {
                event.preventDefault(); // Prevent form submission
            }
        });

        // Add event listener to user-select-search
        userSelectSearch[index].addEventListener('input', function (event) {
            handleSearchInput(index, event);
        });

        userSelectSearch[index].addEventListener('blur', function(event) {
            handleSearchInput(index, event);
        });

        select.addEventListener('click', function (event) {
            event.preventDefault();

            let currentSearch = userSelectSearch[index];
            let currentPopup = userSelectWindowPopup[index];

            currentPopup.classList.toggle('d-none')

            searchUsers(' ', index);
        });
    });
}

function searchUsers(searchTerm, index) {

    let selectedIndex;
    if (index) {
        selectedIndex = userSelectWindows[index].value
    }

    fetch('/user-search', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            search: searchTerm,
            selected: selectedIndex
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);

            // Clear existing user list
            userList[index].innerHTML = '';

            // Populate userList with received user data
            data.users.forEach(user => {
                let listItem = document.createElement('div');

                displayListItems.push(listItem);

                listItem.classList.add('user-list-item')
                listItem.dataset.userId = user.id;

                let userValueData = userSelectWindows[index].value ? userSelectWindows[index].value.split(',').map(id => id.trim()) : [];
                let userIdIndex = userValueData.indexOf(user.id.toString());

                if (userIdIndex !== -1) {
                    listItem.classList.add('user-selected');
                }

                listItem.textContent = `${user.name}${user.infix ? ' ' + user.infix : ''} ${user.last_name}`;
                listItem.addEventListener('click', () => {
                    if (userSelectSearch[index].dataset.type === 'multiple') {
                        toggleUserId(index, user.id, listItem);
                    }
                    if (userSelectSearch[index].dataset.type === 'single') {
                        setUserId(index, user.id, listItem);
                    }
                });

                // Append listItem to the userList
                userList[index].appendChild(listItem);

                let profilePicture = document.createElement('img')
                profilePicture.src = `${user.profile_picture_url}`
                profilePicture.alt = 'Profiel foto'
                profilePicture.classList.add('profle-picture')
                listItem.appendChild(profilePicture)
            });

            if (data.remainingUsersCount > 0) {
                let listItem = document.createElement('div');

                listItem.classList.add('user-list-item')
                listItem.classList.add('user-list-item-more')
                userList[index].appendChild(listItem);

                let more = document.createElement('h2')
                more.innerText = `+${data.remainingUsersCount}`;
                listItem.appendChild(more)
            }

            if (data.users < 1) {
                let warning = document.createElement('div');

                warning.classList.add('alert')
                warning.classList.add('alert-warning')
                warning.classList.add('d-flex')
                warning.classList.add('align-items-center')
                warning.role = 'alert';
                warning.innerHTML = '<span class="material-symbols-rounded me-2">person_off</span>Geen gebruikers gevonden...';
                userList[index].appendChild(warning);
            }
        })
        .catch(error => console.error('Error fetching users:', error));
}

function toggleUserId(index, userId, listItem) {
    let userList = userSelectWindows[index].value ? userSelectWindows[index].value.split(',').map(id => id.trim()) : [];

    let userIdIndex = userList.indexOf(userId.toString());

    if (userIdIndex === -1) {
        userList.push(userId);
        listItem.classList.add('user-selected')
    } else {
        userList.splice(userIdIndex, 1);
        listItem.classList.remove('user-selected')
    }

    userSelectWindows[index].value = userList.join(', ');
}

function setUserId(index, userId, listItem) {
    if (userSelectWindows[index].value !== userId.toString()) {
        displayListItems.forEach(item => {
            item.classList.remove('user-selected')
        })
        listItem.classList.add('user-selected')
        userSelectWindows[index].value = userId;
        forum[0].submit()
    } else {
        listItem.classList.remove('user-selected')
        userSelectWindows[index].value = '';
        forum[0].submit()
    }
}


// Function to handle search input
function handleSearchInput(index, event) {
    console.log(event)

    event.preventDefault()

        const searchTerm = event.target.value.trim();

        console.log(searchTerm)

        if (searchTerm !== '') {
            searchUsers(searchTerm, index);
        } else {
            // If search term is empty, reset user list or do nothing
            userList.innerHTML = ''; // Reset user list
            // Optionally, you can load all users here
            searchUsers('', index);

        }
}


function isDescendant(child, parent) {
    // Traverse up the DOM tree from the child element
    while (child !== null) {
        if (child === parent) {
            return true; // Found the parent
        }
        child = child.parentNode; // Move up to the parent node
    }
    return false; // Not found
}
