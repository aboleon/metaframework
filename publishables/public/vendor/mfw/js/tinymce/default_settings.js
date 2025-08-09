function mfw_default_tinymce_settings(targets) {
    let baseHref = location.protocol + '//' + window.location.hostname + '/';
    return {
        selector: targets,
        theme: 'silver',
        width: '100%',
        height: 480,
        menubar: false,
        entity_encoding: 'raw',
        branding: false,
        plugins: 'advlist autolink autosave link code lists media searchreplace anchor wordcount fullscreen nonbreaking table directionality',
        toolbar1: 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | blocks fontsize forecolor backcolor',
        toolbar2: 'cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor media',
        toolbar3: 'table | hr removeformat | subscript superscript | fullscreen | ltr rtl | nonbreaking restoredraft code',
        image_title: true,
        automatic_uploads: true,
        language: 'fr_FR',
        language_url: baseHref + 'vendor/mfw/js/tinymce/langs/fr_FR.js',
        document_base_url: baseHref,
        relative_urls: false,
        remove_script_host: true,
        setup: function (editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
            editor.on('init', function() {
                var textarea = editor.getElement();
                var height = textarea.getAttribute('height');

                if (height) {
                    height = parseInt(height, 10);

                    // Override the .tox-tinymce container min-height
                    var toxTinymce = editor.getContainer().closest('.tox-tinymce');
                    if (toxTinymce) {
                        toxTinymce.style.minHeight = height + 'px';
                        toxTinymce.style.height = height + 'px';
                    }

                }
            });
        },

    };
}
