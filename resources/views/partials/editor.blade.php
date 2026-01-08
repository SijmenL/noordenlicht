@section('editor')

    <div class="options flex-row-responsive">
        <div class="options-container">
            <select id="formatBlock" class="form-select" style="max-width: 100px; width: unset">
                <option value="H1" class="h1">Titel</option>
                <option value="H2" class="h2">Kop 2</option>
                <option value="H3" class="h3">Kop 3</option>
                <option value="H4" class="h4">Kop 4</option>
                <option value="H5" class="h5">Kop 5</option>
                <option value="p" selected>Tekst</option>
            </select>

            <div class="wysiwyg-button">
                <button type="button" id="bold"
                        class="option-button format d-flex justify-content-center bold" title="vet">
                    <span class="material-symbols-rounded">format_bold</span>
                </button>
                <button type="button" title="cursief" id="italic"
                        class="option-button format d-flex justify-content-center italic">
                    <span class="material-symbols-rounded">format_italic</span>
                </button>
                <button type="button" title="onderstreept" id="underline"
                        class="option-button format d-flex justify-content-center underline">
                    <span class="material-symbols-rounded">format_underlined</span>
                </button>
                <button type="button" title="doorgehaald" id="strikethrough"
                        class="option-button format d-flex justify-content-center strikethrough">
                    <span class="material-symbols-rounded">strikethrough_s</span>
                </button>
                <button type="button" title="superscript" id="superscript"
                        class="option-button script d-flex justify-content-center superscript">
                    <span class="material-symbols-rounded">superscript</span>
                </button>
                <button type="button" title="subscript" id="subscript"
                        class="option-button script d-flex justify-content-center subscript">
                    <span class="material-symbols-rounded">subscript</span>
                </button>
            </div>

            <div class="wysiwyg-button">
                <div class="input-wrapper">
                    <input type="color" id="foreColor" class="adv-option-button color-picker" value="#000000"/>
                    <button type="button" title="tekst kleur" id="textColorButton"
                            class="option-button color d-flex justify-content-center">
                        <label for="foreColor" class="d-flex align-items-center justify-content-center w-100 h-100 color-label" style="cursor: pointer;">
                            <span id="foreColorIcon" class="material-symbols-rounded">format_color_text</span>
                        </label>
                    </button>
                </div>
                <div class="input-wrapper">
                    <input type="color" id="backColor" class="adv-option-button color-picker" value="#ffffff"/>
                    <button type="button" title="markeer kleur" id="highlightColorButton"
                            class="option-button color d-flex justify-content-center overflow-hidden">
                        <label for="backColor" class="d-flex align-items-center justify-content-center w-100 h-100 color-label" style="cursor: pointer;">
                            <span id="backColorIcon" class="material-symbols-rounded">format_ink_highlighter</span>
                        </label>
                    </button>
                </div>
            </div>

            <div class="wysiwyg-button">
                <button type="button" title="nummering" id="insertOrderedList"
                        class="option-button d-flex justify-content-center insertOrderedList">
                    <span class="material-symbols-rounded">format_list_numbered</span>
                </button>
                <button type="button" title="opsommingstekens" id="insertUnorderedList"
                        class="option-button d-flex justify-content-center insertUnorderedList">
                    <span class="material-symbols-rounded">format_list_bulleted</span>
                </button>
            </div>

            <div class="wysiwyg-button">
                <button type="button" title="links uitlijnen" id="justifyLeft"
                        class="option-button align justify-content-center justifyLeft active-button">
                    <span class="material-symbols-rounded">format_align_left</span>
                </button>
                <button type="button" title="midden uitlijnen" id="justifyCenter"
                        class="option-button align justify-content-center justifyCenter">
                    <span class="material-symbols-rounded">format_align_center</span>
                </button>
                <button type="button" title="rechts uitlijnen" id="justifyRight"
                        class="option-button align justify-content-center justifyRight">
                    <span class="material-symbols-rounded">format_align_right</span>
                </button>
                <button type="button" title="uitvullen" id="justifyFull"
                        class="option-button align justify-content-center justifyFull">
                    <span class="material-symbols-rounded">format_align_justify</span>
                </button>
                <button type="button" title="inspringing vergroten" id="indent"
                        class="option-button spacing justify-content-center indent">
                    <span class="material-symbols-rounded">format_indent_increase</span>
                </button>
                <button type="button" title="inspringing verkleinen" id="outdent"
                        class="option-button spacing justify-content-center outdent">
                    <span class="material-symbols-rounded">format_indent_decrease</span>
                </button>
            </div>

            <div class="wysiwyg-button">
                <button type="button" title="verwijder stijling" id="clear"
                        class="option-button d-flex justify-content-center bold">
                    <span class="material-symbols-rounded">format_clear</span>
                </button>
            </div>
            <div class="wysiwyg-button">
                <button type="button" title="afbeelding" id="insertImage"
                        class="option-button d-flex justify-content-center insertImage">
                    <span class="material-symbols-rounded">image</span>
                </button>
                <button type="button" title="pdf bestand" id="insertPdf"
                        class="option-button d-flex justify-content-center insertPdf">
                    <span class="material-symbols-rounded">picture_as_pdf</span>
                </button>
                <button type="button" title="YouTube video" id="insertYouTube"
                        class="option-button d-flex justify-content-center insertYouTube">
                    <span class="material-symbols-rounded">youtube_activity</span>
                </button>
            </div>
            <div class="wysiwyg-button">
                <button type="button" title="hyperlink" id="createLink"
                        class="option-button d-flex justify-content-center createLink">
                    <span class="material-symbols-rounded">link</span>
                </button>
                <button type="button" title="hyperlink verwijderen" id="unlink"
                        class="option-button d-flex justify-content-center unlink">
                    <span class="material-symbols-rounded">link_off</span>
                </button>
            </div>
        </div>
    </div>
@endsection
