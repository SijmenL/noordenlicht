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
let textInput = document.getElementById('text-input');
let message = document.getElementById('content');
let characters = document.getElementById('characters');
let body = document.getElementById('app');
let imageUpload = document.getElementById('insertImage');
let pdfUpload = document.getElementById('insertPdf');
let videoUpload = document.getElementById('insertYouTube');
let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let addComments = document.querySelectorAll('.add-comment');
let commentForms = document.querySelectorAll('.comment-form');
let popUp;
let html = document.querySelector('html');

// State to track cursor position
let savedRange = null;

//Initial Settings
const initializer = () => {
    //function calls for highlighting buttons
    highlighter(alignButtons, true);
    highlighter(formatButtons, false);
    highlighter(scriptButtons, true);

    if (characters) {
        characters.innerHTML = `${textInput.innerHTML.toString().length}/60000`;
    }

    if (textInput) {
        textInput.addEventListener('input', function () {
            editText();
        });

        // IMPORTANT: Only save selection when user interacts with the editor
        // We do NOT want to save selection when they click a toolbar button,
        // because that might register a selection at the start of the body.
        textInput.addEventListener('keyup', saveSelection);
        textInput.addEventListener('mouseup', saveSelection);
        textInput.addEventListener('click', saveSelection);
    }

    // Monitor selection changes to update UI (Colors, Headings, Buttons)
    document.addEventListener('selectionchange', updateToolbarState);

    document.getElementById('clear').addEventListener('click', function () {
        restoreSelection();
        document.execCommand('removeFormat', false, null);
        document.execCommand('formatBlock', false, 'p');
    });

    // Initialize Color Pickers logic
    initColorPickers();

    document.addEventListener("DOMContentLoaded", function () {
        // Clean up wrapblocks
        let wrapblocks = document.getElementsByTagName("o:wrapblock");
        for (let i = 0; i < wrapblocks.length; i++) {
            let wrapblock = wrapblocks[i];
            wrapblock.parentNode.removeChild(wrapblock);
        }

        // --- EXISTING LOGIC FOR COMMENTS AND LIKES ---
        let editButtons = document.querySelectorAll('.edit-button');
        let likeButtons = document.querySelectorAll('.like-button');

        likeButtons.forEach(function (button) {
            button.addEventListener('click', () => likeButton(button));
        });

        editButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const comment = button.closest('.comment');
                const content = comment.querySelector('.content');
                const editForm = comment.querySelector('.editable-content');
                const editableDiv = editForm.querySelector('.text-input');
                const cancelButton = editForm.querySelector('.cancel-button');
                const saveButton = editForm.querySelector('.save-button');

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

                    const commentId = comment.dataset.commentId;
                    const commentContent = comment.querySelector('.comment-content');
                    const updatedContent = editableDiv.innerHTML;

                    fetch(`/comments/${commentId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({content: updatedContent})
                    })
                        .then(response => {
                            if (!response.ok) throw new Error('Failed to update comment');
                            return response.json();
                        })
                        .then(data => {
                            commentContent.innerHTML = updatedContent;
                            content.style.display = 'block';
                            editForm.style.display = 'none';
                            saveButton.innerHTML = '<span class="save-button material-symbols-rounded">save</span>';
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });

        if (imageUpload) {
            imageUpload.addEventListener('click', () => {
                // Ensure we have a valid range before opening file dialog
                if(!savedRange) saveSelection();
                addImage();
            })
        }

        if (pdfUpload) {
            pdfUpload.addEventListener('click', () => {
                if(!savedRange) saveSelection();
                addPdf();
            })
        }

        if (videoUpload) {
            videoUpload.addEventListener('click', () => {
                if(!savedRange) saveSelection();
                addYouTubeVideo();
            })
        }

        // Paste Handling
        inputFields.forEach(function (field) {
            field.addEventListener('paste', function (event) {
                event.preventDefault();
                const clipboardData = event.clipboardData || window.clipboardData;
                if (!clipboardData) return;

                const htmlData = clipboardData.getData('text/html');
                const plainText = clipboardData.getData('text/plain');
                if (!htmlData && !plainText) return;

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlData || plainText;

                cleanAllElements(tempDiv);

                const cleanedHtml = tempDiv.innerHTML;
                document.execCommand('insertHTML', false, cleanedHtml);
                editText();
            });

            field.addEventListener('dragover', e => e.preventDefault());
            field.addEventListener('drop', e => e.preventDefault());
        });

        if (addComments) {
            addComments.forEach(function (addCommentButton) {
                addCommentButton.addEventListener('click', function () {
                    const parentElement = addCommentButton.closest('.content');
                    const form = parentElement.querySelector('.comment-form');
                    if (form) form.classList.toggle('show-form');
                });
            });
        }

        if (commentForms) {
            commentForms.forEach(function (form) {
                const ti = form.querySelector('.text-input');
                const ci = form.querySelector('.content-input');
                ti.addEventListener('input', function () {
                    ci.value = ti.innerHTML;
                });
            });
        }
    })
}

// --- HELPER FUNCTIONS FOR SELECTION ---
function saveSelection() {
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
        const range = selection.getRangeAt(0);
        // Only save if the range is actually inside our editor
        if (textInput.contains(range.commonAncestorContainer)) {
            savedRange = range.cloneRange();
        }
    }
}

function restoreSelection() {
    textInput.focus();
    if (savedRange) {
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(savedRange);
    }
}

// --- COLOR PICKER LOGIC ---
// Helper to convert rgb(r, g, b) to #hex
function rgbToHex(rgb) {
    if (!rgb || rgb === 'transparent' || rgb === 'rgba(0, 0, 0, 0)') return '#000000';
    if (rgb.startsWith('#')) return rgb;

    const sep = rgb.indexOf(",") > -1 ? "," : " ";
    const rgbArr = rgb.substr(4).split(")")[0].split(sep);

    let r = (+rgbArr[0]).toString(16),
        g = (+rgbArr[1]).toString(16),
        b = (+rgbArr[2]).toString(16);

    if (r.length == 1) r = "0" + r;
    if (g.length == 1) g = "0" + g;
    if (b.length == 1) b = "0" + b;

    return "#" + r + g + b;
}

function initColorPickers() {
    const foreColorInput = document.getElementById('foreColor');
    const foreColorIcon = document.getElementById('foreColorIcon');
    const backColorInput = document.getElementById('backColor');
    const backColorIcon = document.getElementById('backColorIcon');

    // Text Color
    foreColorInput.addEventListener('input', (e) => {
        let color = e.target.value;
        restoreSelection(); // Restore BEFORE applying
        document.execCommand('foreColor', false, color);
        // Save again immediately so subsequent edits are correct
        saveSelection();
        editText();
        // Update UI immediately
        foreColorIcon.style.color = color;
    });

    // Background Color
    backColorInput.addEventListener('input', (e) => {
        let color = e.target.value;
        restoreSelection();
        document.execCommand('hiliteColor', false, color);
        saveSelection();
        editText();
        backColorIcon.style.color = color;
    });
}

// --- UI STATE MANAGEMENT (Colors, Format, Buttons) ---
function updateToolbarState() {
    const selection = window.getSelection();
    if (selection.rangeCount === 0) return;

    const range = selection.getRangeAt(0);
    const activeElement = document.activeElement;

    // Only update if selection is inside our editor
    if (textInput.contains(activeElement) || textInput.contains(range.commonAncestorContainer)) {

        // 1. Update Block Format (H1, P, etc)
        let selectedElement = range.commonAncestorContainer;
        if (selectedElement.nodeType === 3) selectedElement = selectedElement.parentElement; // Text node to Element

        while (selectedElement && selectedElement !== textInput && !selectedElement.tagName.match(/^H[1-6]$/) && selectedElement.tagName !== 'P') {
            selectedElement = selectedElement.parentElement;
        }
        if (selectedElement && selectedElement.tagName.match(/^H[1-6]$/)) {
            formatBlock.value = selectedElement.tagName;
        } else {
            formatBlock.value = 'p';
        }

        // 2. Update Toggle Buttons (Bold, Italic, Align)
        formatButtons.forEach(button => updateButtonState(button, button.id));
        alignButtons.forEach(button => updateButtonState(button, button.id));
        updateButtonState(orderedListButton, 'insertOrderedList');
        updateButtonState(unorderedListButton, 'insertUnorderedList');

        // 3. Update Color Icons based on current selection
        // We use queryCommandValue to get the computed style at the cursor
        let currentForeColor = document.queryCommandValue('foreColor');
        let currentBackColor = document.queryCommandValue('hiliteColor');

        // Update Text Color Icon
        const foreColorIcon = document.getElementById('foreColorIcon');
        const foreColorInput = document.getElementById('foreColor');
        if(foreColorIcon && foreColorInput) {
            // Browsers return RGB, input needs Hex
            const hexColor = rgbToHex(currentForeColor);
            foreColorIcon.style.color = currentForeColor;
            foreColorInput.value = hexColor;
        }

        // Update Background Color Icon
        const backColorIcon = document.getElementById('backColorIcon');
        const backColorInput = document.getElementById('backColor');
        if(backColorIcon && backColorInput) {
            // Background is often 'transparent' by default
            const hexBack = rgbToHex(currentBackColor);
            // If transparent, show black or grey, else show color
            if(currentBackColor === 'transparent' || currentBackColor === 'rgba(0, 0, 0, 0)') {
                backColorIcon.style.color = '#000000';
                backColorInput.value = '#ffffff';
            } else {
                backColorIcon.style.color = currentBackColor;
                backColorInput.value = hexBack;
            }
        }
    }
}

function updateButtonState(button, command) {
    if(!button) return;
    try {
        const isActive = document.queryCommandState(command);
        if (isActive) {
            button.classList.add('active-button');
        } else {
            button.classList.remove('active-button');
        }
    } catch(e) {
        // Some commands might throw errors in certain contexts
        button.classList.remove('active-button');
    }
}

document.getElementById('formatBlock').addEventListener('change', function() {
    restoreSelection();
    document.execCommand('formatBlock', false, this.value);
    editText();
});


// --- MEDIA FUNCTIONS ---
function addImage() {
    let input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = function (event) {
        let file = event.target.files[0];
        if(!file) return;

        let formData = new FormData();
        formData.append('image', file);
        formData.append('_token', csrfToken);

        imageUpload.innerHTML = '<span class="save-button material-symbols-rounded rotating">progress_activity</span>';

        let xhr = new XMLHttpRequest();
        xhr.open('POST', '/upload-image', true);
        xhr.onload = () => {
            imageUpload.innerHTML = '<span class="material-symbols-rounded">image</span>';
            if (xhr.status === 200) {
                let imageUrl = xhr.responseText;
                insertImageIntoEditor(imageUrl);
            } else {
                invalidImage();
            }
        };
        xhr.onerror = () => {
            imageUpload.innerHTML = '<span class="material-symbols-rounded">image</span>';
        };
        xhr.send(formData);
    };
    input.click();
}

function insertImageIntoEditor(imageUrl) {
    let urlObj = JSON.parse(imageUrl);
    let src = urlObj.imageUrl;
    restoreSelection();
    const htmlToInsert = `<img src="${src}" alt="Afbeelding" class="forum-image"><br>`;
    document.execCommand('insertHTML', false, htmlToInsert);
    editText();
}

function addPdf() {
    let input = document.createElement('input');
    input.type = 'file';
    input.accept = 'application/pdf';
    input.onchange = function (event) {
        let file = event.target.files[0];
        if(!file) return;

        let formData = new FormData();
        formData.append('pdf', file);
        formData.append('_token', csrfToken);

        pdfUpload.innerHTML = '<span class="save-button material-symbols-rounded rotating">progress_activity</span>';

        let xhr = new XMLHttpRequest();
        xhr.open('POST', '/upload-pdf', true);
        xhr.onload = () => {
            pdfUpload.innerHTML = '<span class="material-symbols-rounded">picture_as_pdf</span>';
            if (xhr.status === 200) {
                let pdfUrl = xhr.responseText;
                insertPdfIntoEditor(pdfUrl);
            } else {
                invalidPdf();
            }
        };
        xhr.onerror = () => {
            pdfUpload.innerHTML = '<span class="material-symbols-rounded">picture_as_pdf</span>';
        };
        xhr.send(formData);
    };
    input.click();
}

function insertPdfIntoEditor(pdfResponse) {
    let urlObj = JSON.parse(pdfResponse);
    let url = urlObj.pdfUrl;

    let filenameWithNumber = url.substring(url.lastIndexOf('/') + 1);
    let filename = filenameWithNumber.replace(/^\d+-/, '').replace(/-/g, ' ');

    restoreSelection();
    const htmlToInsert = `<a href="${url}" target="_blank" class="forum-pdf">${filename}</a>&nbsp;`;
    document.execCommand('insertHTML', false, htmlToInsert);
    editText();
}

function addYouTubeVideo() {
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
    popUpTitle.innerText = `Geef de url naar de YouTube video`;
    popUpBody.appendChild(popUpTitle);

    let inputLabel = document.createElement('label');
    inputLabel.classList.add('form-label');
    inputLabel.htmlFor = 'youtube-input';
    inputLabel.innerText = 'Video';
    popUpBody.appendChild(inputLabel);

    let inputField = document.createElement('input');
    inputField.classList.add('form-control');
    inputField.id = 'youtube-input';
    popUpBody.appendChild(inputField);

    let buttonContainer = document.createElement('div');
    buttonContainer.classList.add('button-container');
    popUpBody.appendChild(buttonContainer);

    let continueButton = document.createElement('a');
    continueButton.classList.add('btn');
    continueButton.classList.add('btn-success');
    continueButton.innerText = 'Toevoegen';
    buttonContainer.appendChild(continueButton);

    let cancelButton = document.createElement('a');
    cancelButton.classList.add('btn');
    cancelButton.classList.add('btn-outline-danger');
    cancelButton.innerText = 'Annuleren';
    buttonContainer.appendChild(cancelButton);

    inputField.focus();

    continueButton.addEventListener('click', (e) => {
        e.preventDefault(); // Stop button from changing selection
        addYouTubeVideoToDiv(inputField.value);
        popUp.remove();
        html.classList.remove('no-scroll');
    });

    cancelButton.addEventListener('click', (e) => {
        e.preventDefault();
        popUp.remove();
        html.classList.remove('no-scroll');
        restoreSelection();
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

    if (!videoId) {
        invalidVideo();
        return;
    }

    let embedUrl = 'https://www.youtube.com/embed/' + videoId;
    restoreSelection();
    const htmlToInsert = `
        <iframe width="560" height="315" src="${embedUrl}"
        class="forum-image" frameborder="0" allowfullscreen></iframe><br>
    `;
    document.execCommand('insertHTML', false, htmlToInsert);
    editText();
}

function invalidImage() {
    imageUpload.classList.add('invalid');
    setTimeout(() => imageUpload.classList.remove('invalid'), 500);
}

function invalidPdf() {
    pdfUpload.classList.add('invalid');
    setTimeout(() => pdfUpload.classList.remove('invalid'), 500);
}

function invalidVideo() {
    videoUpload.classList.add('invalid');
    setTimeout(() => videoUpload.classList.remove('invalid'), 500);
}

// --- BASIC OPERATIONS ---
const modifyText = (command, defaultUi, value) => {
    restoreSelection();
    document.execCommand(command, defaultUi, value);
    editText();
};

optionsButtons.forEach((button) => {
    // Exclude special buttons handled separately
    if (!['insertImage', 'insertPdf', 'insertYouTube', 'createLink', 'textColorButton', 'highlightColorButton'].includes(button.id)) {
        button.addEventListener("click", () => {
            modifyText(button.id, false, null);
        });
    }
});

advancedOptionButton.forEach((button) => {
    if(!button.classList.contains('color-picker')) {
        button.addEventListener("change", () => {
            modifyText(button.id, false, button.value);
        });
    }
});


// --- LINK LOGIC ---
if (linkButton) {
    linkButton.addEventListener("click", () => {
        // We do NOT save selection here because the click itself might have moved focus.
        // We rely on the selection saved from the last keyup/mouseup in the editor.
        if (!savedRange) {
            // Fallback: If no range saved, try to get current if in editor
            saveSelection();
        }

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
        popUpTitle.innerText = `Voeg een hyperlink toe`;
        popUpBody.appendChild(popUpTitle);

        let inputLabelName = document.createElement('label');
        inputLabelName.classList.add('form-label');
        inputLabelName.htmlFor = 'link-display';
        inputLabelName.innerText = 'Tekst om weer te geven';
        popUpBody.appendChild(inputLabelName);

        let inputFieldName = document.createElement('input');
        inputFieldName.classList.add('form-control');
        inputFieldName.id = 'link-display';

        // Use savedRange to get the text, not window.getSelection()
        // because focus is already shifting to buttons
        let currentSelection = "";
        if(savedRange) {
            currentSelection = savedRange.toString();
        }
        inputFieldName.value = currentSelection;

        popUpBody.appendChild(inputFieldName);

        let inputLabel = document.createElement('label');
        inputLabel.classList.add('form-label');
        inputLabel.htmlFor = 'link-input';
        inputLabel.innerText = 'Hyperlink';
        popUpBody.appendChild(inputLabel);

        let inputField = document.createElement('input');
        inputField.classList.add('form-control');
        inputField.id = 'link-input';
        popUpBody.appendChild(inputField);

        let buttonContainer = document.createElement('div');
        buttonContainer.classList.add('button-container');
        popUpBody.appendChild(buttonContainer);

        let continueButton = document.createElement('a');
        continueButton.classList.add('btn');
        continueButton.classList.add('btn-success');
        continueButton.innerText = 'Toevoegen';
        buttonContainer.appendChild(continueButton);

        let cancelButton = document.createElement('a');
        cancelButton.classList.add('btn');
        cancelButton.classList.add('btn-outline-danger');
        cancelButton.innerText = 'Annuleren';
        buttonContainer.appendChild(cancelButton);

        continueButton.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent focus loss
            addLink(inputFieldName.value, inputField.value);
            popUp.remove();
            html.classList.remove('no-scroll');
        });

        cancelButton.addEventListener('click', (e) => {
            e.preventDefault();
            popUp.remove();
            html.classList.remove('no-scroll');
            restoreSelection();
        });
    });
}

function addLink(userText, userLink) {
    restoreSelection();

    if (userLink) {
        if (!userLink.startsWith("https://") && !userLink.startsWith("http://")) {
            userLink = "https://" + userLink;
        }

        let textToShow = userText || userLink;

        // This command replaces whatever is currently selected (highlighted)
        // with the new HTML. If savedRange was correct, it overwrites old text.
        const htmlToInsert = `<a href="${userLink}" target="_blank">${textToShow}</a>`;
        document.execCommand('insertHTML', false, htmlToInsert);
    }
    editText();
}

// --- UI UTILS ---

const highlighter = (className, needsRemoval) => {
    className.forEach((button) => {
        button.addEventListener("click", () => {
            if (needsRemoval) {
                let alreadyActive = button.classList.contains("active-button");
                highlighterRemover(className);
                if (!alreadyActive) {
                    button.classList.add("active-button");
                }
            } else {
                button.classList.toggle("active-button");
            }
        });
    });
};

const highlighterRemover = (className) => {
    className.forEach((button) => {
        button.classList.remove("active-button");
    });
};

// --- CLEANING UTILS (For Paste) ---
function cleanStyles(element) {
    const tagsToKeep = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'P', 'B', 'I', 'U', 'A', 'IMG', 'IFRAME', 'UL', 'OL', 'LI'];
    if (!tagsToKeep.includes(element.tagName)) {
        if (element.style) element.removeAttribute('style');
    }
    for (let i = 0; i < element.children.length; i++) {
        cleanStyles(element.children[i]);
    }
}

function removeClasses(element) {
    if (!element.classList.contains('forum-image') && !element.classList.contains('forum-pdf')) {
        element.className = '';
    }
    for (let i = 0; i < element.children.length; i++) {
        removeClasses(element.children[i]);
    }
}

function removeIds(element) {
    element.removeAttribute('id');
    for (let i = 0; i < element.children.length; i++) {
        removeIds(element.children[i]);
    }
}

function removeDisallowedElements(element) {
    const disallowedTags = [
        'input', 'nav', 'select', 'script', 'footer', 'button',
        'textarea', 'form', 'style', 'link', 'label', 'header', 'aside',
        'article', 'embed', 'object', 'svg', 'canvas', 'video', 'audio'
    ];
    if (disallowedTags.includes(element.tagName.toLowerCase())) {
        element.remove();
        return;
    }
    for (let i = 0; i < element.children.length; i++) {
        removeDisallowedElements(element.children[i]);
    }
}

function cleanAllElements(element) {
    cleanStyles(element);
    removeClasses(element);
    removeIds(element);
    removeDisallowedElements(element);
}

function editText() {
    if(message && textInput) {
        message.value = textInput.innerHTML;
        characters.innerHTML = `${textInput.innerHTML.length}/60000`;
        characters.style.color = textInput.innerHTML.length > 60000 ? 'red' : 'black';
    }
}

const likeButton = (button) => {
    button.innerHTML = `<span class="material-symbols-rounded rotating">progress_activity</span>`;
    let likeType = button.dataset.postType;
    let postId = button.dataset.postId;

    fetch(`/posts/${postId}/${likeType}/toggle-like`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
    })
        .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
        .then(data => {
            button.innerHTML = `${data.likeCount} <span class="material-symbols-rounded">favorite</span>`;
            if (data.isLiked) {
                button.classList.add('liked', 'user-liked');
            } else {
                button.classList.remove('liked', 'user-liked');
            }
        })
        .catch(error => console.error('Fetch error:', error));
};

window.onload = initializer();
