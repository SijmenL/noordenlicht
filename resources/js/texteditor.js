let optionsButtons = document.querySelectorAll(".option-button");
let advancedOptionButton = document.querySelectorAll(".adv-option-button");
let formatBlock = document.getElementById("formatBlock");
let linkButton = document.getElementById("createLink");
let alignButtons = document.querySelectorAll(".align");
let orderedListButton = document.getElementById('insertOrderedList');
let unorderedListButton = document.getElementById('insertUnorderedList');
let spacingButtons = document.querySelectorAll(".spacing");
let formatButtons = document.querySelectorAll(".format");
let scriptButtons = document.querySelectorAll(".script");
let mediaButons = document.querySelectorAll(".media");
let inputFields = document.querySelectorAll(".text-input");
let textInput = document.getElementById('text-input')
let message = document.getElementById('content');
let characters = document.getElementById('characters');
let body = document.getElementById('app')
let imageUpload = document.getElementById('insertImage');
let pdfUpload = document.getElementById('insertPdf');
let videoUpload = document.getElementById('insertYouTube');
let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let addComments = document.querySelectorAll('.add-comment');
let commentForms = document.querySelectorAll('.comment-form');
let popUp;
let html = document.querySelector('html')


//Initial Settings
const initializer = () => {
    console.log(inputFields)

    //function calls for highlighting buttons
    //No highlights for link, unlink,lists, undo,redo since they are one time operations
    highlighter(alignButtons, true);
    // highlighter(spacingButtons, true);
    highlighter(formatButtons, false);
    highlighter(scriptButtons, true);

    if (characters) {
        characters.innerHTML = `${textInput.innerHTML.toString().length}/60000`;
    }

    if (textInput) {
        textInput.addEventListener('input', function () {
            // Call the function when the content changes
            editText();
        });
    }

    document.addEventListener('selectionchange', updateFormatBlock);

    document.getElementById('clear').addEventListener('click', function () {
        document.execCommand('removeFormat', false, null);
        document.execCommand('formatBlock', false, 'p');
    });


    document.addEventListener("DOMContentLoaded", function () {
        // Find and remove all o:wrapblock elements
        let wrapblocks = document.getElementsByTagName("o:wrapblock");
        let editButtons = document.querySelectorAll('.edit-button');

        for (let i = 0; i < wrapblocks.length; i++) {
            let wrapblock = wrapblocks[i];
            wrapblock.parentNode.removeChild(wrapblock);
        }

        let likeButtons = document.querySelectorAll('.like-button');

        likeButtons.forEach(function (button) {
            button.addEventListener('click', () => likeButton(button));
        });

        editButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default click behavior

                const comment = button.closest('.comment');
                const content = comment.querySelector('.content');
                const editForm = comment.querySelector('.editable-content');
                const editableDiv = editForm.querySelector('.text-input');
                const cancelButton = editForm.querySelector('.cancel-button');
                const saveButton = editForm.querySelector('.save-button');

                console.log(content);

                content.style.display = 'none';
                editForm.style.display = 'block';
                editableDiv.focus();

                cancelButton.addEventListener('click', function () {
                    content.style.display = 'block';
                    editForm.style.display = 'none';
                });

                saveButton.addEventListener('click', function () {
                    event.preventDefault();

                    saveButton.innerHTML = '<span class="save-button material-symbols-rounded rotating">progress_activity</span>';

                    const comment = button.closest('.comment');
                    const content = comment.querySelector('.content');
                    const commentContent = comment.querySelector('.comment-content');
                    const editableDiv = editForm.querySelector('.text-input');

                    const commentId = comment.dataset.commentId;

                    const updatedContent = editableDiv.innerHTML; // Get the edited content from the editable div

                    // Send AJAX request to update the comment
                    fetch(`/comments/${commentId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({content: updatedContent})
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Failed to update comment');
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Update the content of the comment
                            commentContent.innerHTML = updatedContent;
                            content.style.display = 'block';
                            editForm.style.display = 'none';
                            saveButton.innerHTML = '<span class="save-button material-symbols-rounded">save</span>';
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                });
            });
        });

        if (imageUpload) {
            imageUpload.addEventListener('click', () => {
                addImage()
            })
        }

        if (pdfUpload) {
            pdfUpload.addEventListener('click', () => {
                addPdf()
            })
        }

        if (videoUpload) {
            videoUpload.addEventListener('click', () => {
                addYouTubeVideo()
            })
        }

        inputFields.forEach(function (field) {
            field.addEventListener('paste', function (event) {
                // Prevent the default paste behavior
                event.preventDefault();

                // Check if clipboard data is available
                const clipboardData = event.clipboardData || window.clipboardData;
                if (!clipboardData) {
                    console.error('Clipboard data not available');
                    return;
                }

                // Get the HTML from the clipboard
                const html = clipboardData.getData('text/html');
                const plainText = clipboardData.getData('text/plain');
                if (!html && !plainText) {
                    console.error('No HTML or plain text found in clipboard data');
                    return;
                }

                // Create a temporary container for the HTML content
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html || plainText;

                // Function to clean unwanted styles
                function cleanStyles(element) {
                    const tagsToKeep = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'P'];  // Add any other tags you want to preserve

                    // Skip cleaning styles if the tag is in the 'tagsToKeep' list
                    if (!tagsToKeep.includes(element.tagName)) {
                        if (element.style) {
                            element.removeAttribute('style');  // Safely remove inline styles instead of resetting the style object
                        }
                    }

                    // Recursively clean the children
                    for (let i = 0; i < element.children.length; i++) {
                        cleanStyles(element.children[i]);
                    }
                }


                // Function to remove classes from elements
                function removeClasses(element) {
                    if (element.classList) {
                        element.className = ''; // For regular HTML elements
                    }
                    for (let i = 0; i < element.children.length; i++) {
                        removeClasses(element.children[i]);
                    }
                }

                // Function to remove ids from elements
                function removeIds(element) {
                    if (element.id) {
                        element.removeAttribute('id'); // Remove id attribute
                    }
                    for (let i = 0; i < element.children.length; i++) {
                        removeIds(element.children[i]);
                    }
                }

                // Function to remove disallowed elements
                function removeDisallowedElements(element) {
                    const disallowedTags = [
                        'input', 'nav', 'select', 'script', 'footer', 'iframe', 'button',
                        'textarea', 'form', 'style', 'link', 'label', 'header', 'aside',
                        'article', 'embed', 'object', 'svg', 'canvas', 'video', 'audio', 'img',
                    ];
                    if (disallowedTags.includes(element.tagName.toLowerCase())) {
                        element.remove();
                        return;
                    }
                    for (let i = 0; i < element.children.length; i++) {
                        removeDisallowedElements(element.children[i]);
                    }
                }

                // Clean styles, remove classes, remove ids, and remove disallowed elements
                function cleanElement(element) {
                    cleanStyles(element);
                    removeClasses(element);
                    removeIds(element);
                    removeDisallowedElements(element);
                }

                // Recursively clean all elements within the tempDiv
                function cleanAllElements(element) {
                    cleanElement(element);
                    for (let i = 0; i < element.children.length; i++) {
                        cleanAllElements(element.children[i]);
                    }
                }

                cleanAllElements(tempDiv);

                // Insert the cleaned HTML into the editable div at the current cursor position
                const cleanedHtml = tempDiv.innerHTML;
                document.execCommand('insertHTML', false, cleanedHtml);

                console.log('Cleaned HTML pasted:', cleanedHtml);
                editText();
            });


            field.addEventListener('dragover', function (event) {
                event.preventDefault();
            });

            field.addEventListener('drop', function (event) {
                event.preventDefault();
            });

            field.addEventListener('dragenter', function (event) {
                event.preventDefault();
            });
        });

        if (addComments) {
            addComments.forEach(function (addCommentButton) {
                addCommentButton.addEventListener('click', function () {
                    // Find the parent element of the button
                    const parentElement = addCommentButton.closest('.content');
                    console.log('Parent element:', parentElement);

                    // Find the form within the parent element
                    const form = parentElement.querySelector('.comment-form');
                    if (form) {
                        form.classList.toggle('show-form'); // Define a CSS class to control the visibility
                    } else {
                        console.error('Form not found');
                    }
                });
            });
        }


        if (commentForms) {
            commentForms.forEach(function (form) {
                const textInput = form.querySelector('.text-input');
                const contentInput = form.querySelector('.content-input');

                textInput.addEventListener('input', function () {
                    contentInput.value = textInput.textContent;
                });
            });
        }

        document.addEventListener('selectionchange', function (event) {
            const activeElement = document.activeElement;

            if (activeElement === textInput || textInput.contains(activeElement)) {
                // Update format buttons (bold, italic, etc.)
                formatButtons.forEach(button => {
                    const command = button.id;
                    updateButtonState(button, command);
                });

                // Update alignment buttons (left, right, etc.)
                alignButtons.forEach(button => {
                    const command = button.id;
                    updateButtonState(button, command);
                });

                updateButtonState(orderedListButton, 'insertOrderedList');
                updateButtonState(unorderedListButton, 'insertUnorderedList');
            }
        });

    })
}

function updateButtonState(button, command) {
    const isActive = document.queryCommandState(command);
    if (isActive) {
        button.classList.add('active-button');
    } else {
        button.classList.remove('active-button');
    }
}

const likeButton = (button) => {

    button.innerHTML = `<span class="material-symbols-rounded rotating">progress_activity</span>`

    let likeType = button.dataset.postType
    let postId = button.dataset.postId;


    // Send the AJAX request with the CSRF token
    fetch(`/posts/${postId}/${likeType}/toggle-like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the request headers
        },
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Handle response data
            console.log(data);
            button.innerHTML = `${data.likeCount} <span
                                                class="material-symbols-rounded">favorite</span>`

            if (data.isLiked === true) {
                button.classList.add('liked')
                button.classList.add('user-liked')
            } else {
                button.classList.remove('liked')
                button.classList.remove('user-liked')
            }
        })
        .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
        });
};


function addImage() {
    // Open file upload dialog
    let input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function (event) {
        let file = event.target.files[0];

        // Create FormData object to upload file
        let formData = new FormData();
        formData.append('image', file);

        // Add CSRF token to FormData
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        imageUpload.innerHTML = '<span class="save-button material-symbols-rounded rotating">progress_activity</span>';

        // Send AJAX request to upload image
        let xhr = new XMLHttpRequest();
        xhr.open('POST', '/upload-image', true);
        xhr.onload = () => {
            if (xhr.status === 200) {
                // Log the response from the server
                console.log(xhr.responseText);
                // Image uploaded successfully
                let imageUrl = xhr.responseText;
                insertImageIntoEditor(imageUrl);
            } else {
                // Handle error
                invalidImage();
                console.error('Image upload failed');
            }
        };
        xhr.onerror = () => {
            // Handle network errors
            console.error('Network error during image upload');
        };
        xhr.send(formData);
    };
    input.click();
}

function invalidImage() {
    imageUpload.classList.add('invalid')
    imageUpload.innerHTML = '<span class="material-symbols-rounded">image</span>';

    setTimeout(function () {
        imageUpload.classList.remove('invalid');
    }, 500);
}


function insertImageIntoEditor(imageUrl) {
    console.log(imageUrl)
    imageUpload.innerHTML = '<span class="material-symbols-rounded">image</span>';
    let image = document.createElement('img')
    let url = JSON.parse(imageUrl);
    image.src = url.imageUrl
    image.alt = 'Afbeelding'
    image.classList.add('forum-image')
    textInput.appendChild(image)
    editText()
}

function addPdf() {
    // Open file upload dialog
    let input = document.createElement('input');
    input.type = 'file';
    input.accept = 'application/pdf';
    input.onchange = function (event) {
        let file = event.target.files[0];

        // Create FormData object to upload file
        let formData = new FormData();
        formData.append('pdf', file);

        // Add CSRF token to FormData
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        pdfUpload.innerHTML = '<span class="save-button material-symbols-rounded rotating">progress_activity</span>';

        // Send AJAX request to upload image
        let xhr = new XMLHttpRequest();
        xhr.open('POST', '/upload-pdf', true);
        xhr.onload = () => {
            if (xhr.status === 200) {
                // Log the response from the server
                console.log(xhr.responseText);
                // Image uploaded successfully
                let pdfUrl = xhr.responseText;
                insertPdfIntoEditor(pdfUrl);
            } else {
                // Handle error
                invalidPdf();
                console.error('Pdf upload failed');
            }
        };
        xhr.onerror = () => {
            // Handle network errors
            console.error('Network error during image upload');
        };
        xhr.send(formData);
    };
    input.click();
}

function invalidPdf() {
    pdfUpload.classList.add('invalid')
    pdfUpload.innerHTML = '<span class="material-symbols-rounded">picture_as_pdf</span>';

    setTimeout(function () {
        pdfUpload.classList.remove('invalid');
    }, 500);
}


function insertPdfIntoEditor(pdfUrl) {
    console.log(pdfUrl)
    pdfUpload.innerHTML = '<span class="material-symbols-rounded">picture_as_pdf</span>';
    let pdf = document.createElement('a')
    let url = JSON.parse(pdfUrl);
    pdf.href = url.pdfUrl

    let filenameWithNumber = url.pdfUrl.substring(url.pdfUrl.lastIndexOf('/') + 1);
    let filename = filenameWithNumber.replace(/^\d+-/, '');
    filename = filename.replace(/-/g, ' ');

    pdf.innerText = `${filename}`
    pdf.classList.add('forum-pdf')
    pdf.target = '_blank';
    textInput.appendChild(pdf)
    editText()
}

function addYouTubeVideo() {
    const scrollPosition = window.scrollY;
    html.classList.add('no-scroll');
    window.scrollTo(0, scrollPosition);


    // Prompt the user to input the YouTube video ID
    popUp = document.createElement('div');
    popUp.classList.add('popup')
    body.appendChild(popUp)

    let popUpBody = document.createElement('div')
    popUpBody.classList.add('popup-body')
    popUp.appendChild(popUpBody)

    let popUpTitle = document.createElement('h2')
    popUpTitle.innerText = `Geef de url naar de YouTube video`;
    popUpBody.appendChild(popUpTitle)

    let inputLabel = document.createElement('label')
    inputLabel.classList.add('form-label')
    inputLabel.htmlFor = 'youtube-input'
    inputLabel.innerText = 'Video'
    popUpBody.appendChild(inputLabel)

    let inputField = document.createElement('input')
    inputField.classList.add('form-control')
    inputField.id = 'youtube-input';
    popUpBody.appendChild(inputField)

    let buttonContainer = document.createElement('div')
    buttonContainer.classList.add('button-container')
    popUpBody.appendChild(buttonContainer)


    let continueButton = document.createElement('a')
    continueButton.classList.add('btn')
    continueButton.classList.add('btn-success')
    continueButton.innerText = 'Toevoegen'
    buttonContainer.appendChild(continueButton)

    let cancelButton = document.createElement('a')
    cancelButton.classList.add('btn')
    cancelButton.classList.add('btn-outline-danger')
    cancelButton.innerText = 'Annuleren'
    buttonContainer.appendChild(cancelButton)

    continueButton.addEventListener('click', () => {
        addYouTubeVideoToDiv(inputField.value)
        popUp.remove()
        html.classList.remove('no-scroll')
    });

    cancelButton.addEventListener('click', () => {
        popUp.remove();
        html.classList.remove('no-scroll')
    });
}

function addYouTubeVideoToDiv(videoURL) {
    let videoId = '';

    if (videoURL.includes('youtube.com') && videoURL.includes('v=')) {
        let urlParams = new URLSearchParams(new URL(videoURL).search);
        videoId = urlParams.get('v');
    } else if (videoURL.includes('youtu.be/')) {
        videoId = videoURL.split('youtu.be/')[1];
    }

    if (videoId === '') {
        invalidVideo();
        console.error('No YouTube video detected.')
    }

    if (videoId) {
        // Construct the embeddable YouTube video URL
        let videoUrl = 'https://www.youtube.com/embed/' + videoId;

        // Create an iframe element
        let iframe = document.createElement('iframe');
        iframe.width = '560'; // Set iframe width (optional)
        iframe.height = '315'; // Set iframe height (optional)
        iframe.src = videoUrl;
        iframe.classList.add('forum-image')
        iframe.setAttribute('allowfullscreen', ''); // Allow fullscreen mode
        iframe.setAttribute('frameborder', '0'); // Remove iframe border

        // Append the iframe to the container
        textInput.appendChild(iframe);
        editText();
    }
}

function invalidVideo() {
    videoUpload.classList.add('invalid')

    setTimeout(function () {
        videoUpload.classList.remove('invalid');
    }, 500);
}

//main logic
const modifyText = (command, defaultUi, value) => {
    //execCommand executes command on selected text
    document.execCommand(command, defaultUi, value);
};

//For basic operations which don't need value parameter
optionsButtons.forEach((button) => {
    if (button.id !== 'insertImage' && button.id !== 'createLink') {
        button.addEventListener("click", () => {
            modifyText(button.id, false, null);
        });
    }
});

//options that require value parameter (e.g colors, fonts)
advancedOptionButton.forEach((button) => {
    button.addEventListener("change", () => {
        modifyText(button.id, false, button.value);
    });
});

//link
if (linkButton) {
    linkButton.addEventListener("click", () => {
        const scrollPosition = window.scrollY;
        html.classList.add('no-scroll');
        window.scrollTo(0, scrollPosition);

        // Prompt the user to input the YouTube video ID
        popUp = document.createElement('div');
        popUp.classList.add('popup')
        body.appendChild(popUp)

        let popUpBody = document.createElement('div')
        popUpBody.classList.add('popup-body')
        popUp.appendChild(popUpBody)

        let popUpTitle = document.createElement('h2')
        popUpTitle.innerText = `Voeg een hyperlink toe`;
        popUpBody.appendChild(popUpTitle)

        let inputLabelName = document.createElement('label')
        inputLabelName.classList.add('form-label')
        inputLabelName.htmlFor = 'link-display'
        inputLabelName.innerText = 'Tekst om weer te geven'
        popUpBody.appendChild(inputLabelName)

        let inputFieldName = document.createElement('input')
        inputFieldName.classList.add('form-control')
        inputFieldName.id = 'link-display';
        let selection = window.getSelection()
        inputFieldName.value = selection.toString();
        document.execCommand("delete", false, null);
        popUpBody.appendChild(inputFieldName)

        let inputLabel = document.createElement('label')
        inputLabel.classList.add('form-label')
        inputLabel.htmlFor = 'link-input'
        inputLabel.innerText = 'Hyperlink'
        popUpBody.appendChild(inputLabel)

        let inputField = document.createElement('input')
        inputField.classList.add('form-control')
        inputField.id = 'link-input';
        popUpBody.appendChild(inputField)

        let buttonContainer = document.createElement('div')
        buttonContainer.classList.add('button-container')
        popUpBody.appendChild(buttonContainer)


        let continueButton = document.createElement('a')
        continueButton.classList.add('btn')
        continueButton.classList.add('btn-success')
        continueButton.innerText = 'Toevoegen'
        buttonContainer.appendChild(continueButton)

        let cancelButton = document.createElement('a')
        cancelButton.classList.add('btn')
        cancelButton.classList.add('btn-outline-danger')
        cancelButton.innerText = 'Annuleren'
        buttonContainer.appendChild(cancelButton)

        continueButton.addEventListener('click', () => {
            addLink(inputFieldName.value, inputField.value)
            popUp.remove()
            html.classList.remove('no-scroll')
        });

        cancelButton.addEventListener('click', () => {
            popUp.remove();
            document.execCommand("undo", false, null);
            html.classList.remove('no-scroll')
        });

    });
}

function addLink(userText, userLink) {
    if (userLink !== null && userLink !== '') {
        if (!userLink.startsWith("https://")) {
            userLink = "https://" + userLink;
        }

        if (userText !== null && userText !== '') {
            let hyperlink = document.createElement('a');
            hyperlink.href = userLink;
            hyperlink.target = "_blank";
            hyperlink.innerText = userText;
            textInput.appendChild(hyperlink);
        } else {
            let hyperlink = document.createElement('a');
            hyperlink.href = userLink;
            hyperlink.target = "_blank";
            hyperlink.innerText = userLink;
            textInput.appendChild(hyperlink);
        }
    } else {
        document.execCommand("undo", false, null);
    }
    editText();
}


//Highlight clicked button
const highlighter = (className, needsRemoval) => {
    className.forEach((button) => {
        button.addEventListener("click", () => {
            //needsRemoval = true means only one button should be highlight and other would be normal
            if (needsRemoval) {
                let alreadyActive = false;

                //If currently clicked button is already active
                if (button.classList.contains("active-button")) {
                    alreadyActive = true;
                }

                //Remove highlight from other buttons
                highlighterRemover(className);
                if (!alreadyActive) {
                    //highlight clicked button
                    button.classList.add("active-button");
                }
            } else {
                //if other buttons can be highlighted
                button.classList.toggle("active-button");
            }
        });
    });
};

function updateFormatBlock() {
    const selection = window.getSelection();

    if (selection.rangeCount > 0) {
        let selectedElement = selection.anchorNode.parentElement;

        // Check if the selected element is within the editor
        if (textInput.contains(selectedElement)) {
            // Traverse up the DOM to find the first element that matches H1-H6 or P
            while (selectedElement && !selectedElement.tagName.match(/^H[1-6]$/) && selectedElement.tagName !== 'P') {
                selectedElement = selectedElement.parentElement; // Go up one level
            }

            // Detect and update the select value to reflect the current format
            if (selectedElement && selectedElement.tagName.match(/^H[1-6]$/)) {
                formatBlock.value = selectedElement.tagName;
            } else {
                formatBlock.value = 'p'; // Default to 'p' if no heading is found
            }
        }
    }
}

// Function to apply the selected block format (e.g., H1, H2, or P)
function applyFormatBlock() {
    const selectedFormat = formatBlock.value;

    // Apply the new format block to the selection
    document.execCommand('formatBlock', false, selectedFormat);
}

// Add event listener to the select dropdown to apply the selected format
document.getElementById('formatBlock').addEventListener('change', applyFormatBlock);


const highlighterRemover = (className) => {
    className.forEach((button) => {
        button.classList.remove("active-button");
    });
};

function editText() {
    message.value = textInput.innerHTML.toString()

    characters.innerHTML = `${textInput.innerHTML.toString().length}/60000`;

    if (textInput.innerHTML.toString().length > 60000) {
        characters.style.color = 'red';
    } else {
        characters.style.color = 'black';
    }
}

window.onload = initializer();
