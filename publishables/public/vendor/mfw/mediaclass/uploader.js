/*jshint esversion: 11 */

/**
 * depends on /vendor/mfw/js/commons.js
 */

const MediaclassUploader = {
  // Cache common jQuery selectors
  template() {
    return $('#mediaclass-file-upload');
  },
  uploadable(selector) {
    return selector.closest('.mediaclass-uploadable');
  },
  uploadableContainer(selector) {
    return this.uploadable(selector).find('.mediaclass-upload-container').first();
  },
  fileupload(uploadContainer) {
    return uploadContainer.find('.mediaclass-fileupload').first();
  },
  messages() {
    return $('.mediaclass-messages');
  },
  progress() {
    return $('.mediaclass-progress');
  },

  // Constants
  defaultFileSize: 16000000,
  langs: {
    'fr': 'Français',
  },
  positions_tags: ['left', 'up', 'down', 'right'],

  // Helper methods
  calculateMaxFileSize(size) {
    if (!size || (!size.includes('KB') && !size.includes('MB'))) {
      return this.defaultFileSize;
    }

    const value = Number(size.replace(/\D+/g, ''));

    if (size.includes('KB')) {
      return value * 1024;
    }
    if (size.includes('MB')) {
      return value * 1024 * 1024;
    }

    return this.defaultFileSize;
  },

  // Check if uploader limit has been reached
  isLimitReached(uploadable) {
    const limit = Number(uploadable.data('limit'));
    if (limit <= 0) {
      return false; // No limit defined
    }

    const currentCount = uploadable.find('.uploaded > div.mediaclass.unlinkable').length;
    return currentCount >= limit;
  },

  // Event handlers
  unlinkable() {
    $('.unlink').off().on('click', function() {
      const selector = $(this).closest('.unlinkable');
      const container = selector.closest('.uploaded');
      const uploadable = container.closest('.mediaclass-uploadable');
      const formData = `action=delete&id=${selector.attr('data-id')}&model=${uploadable.attr('data-model')}`;

      ajax(formData, MediaclassUploader.template());

      $(document).ajaxSuccess(function() {
        selector.remove();
        if (container.find('.unlinkable').length < 1) {
          const $alerts = $('.mediaclass-alerts');
          $alerts.html(`<div class="alert alert-info">${$alerts.data('msg')}</div>`);
        }

        // Re-enable uploader button if we're now below the limit
        const uploadableParent = container.closest('.mediaclass-uploadable');
        if (!MediaclassUploader.isLimitReached(uploadableParent)) {
          uploadableParent.find('span.mediaclass-uploader').removeClass('disabled');
        }
      });
    });
  },

  uploaderCall() {
    $('span.mediaclass-uploader').off().on('click', function() {
      const instantiator = $(this).closest('.mediaclass-uploadable');
      const uploadContainer = MediaclassUploader.uploadableContainer($(this));

      // Check if we've reached the upload limit
      if (MediaclassUploader.isLimitReached(instantiator)) {
        // Optional: Show a message that the limit has been reached
        const limit = Number(instantiator.data('limit'));
        $('.mediaclass-alerts').html(`<div class="alert alert-warning">Limite de ${limit} fichier(s) atteinte</div>`);
        return; // Don't show the uploader
      }

      if (uploadContainer.find('.fileupload-container').length < 1) {
        uploadContainer.html(MediaclassUploader.template().html())
            .attr('data-description', instantiator.data('description'));

        MediaclassUploader.initFileupload(uploadContainer);
        MediaclassUploader.uploaderOptions(uploadContainer);
      } else {
        uploadContainer.html('');
      }
    });

    // Immediately disable uploader buttons where limit is already reached
    $('.mediaclass-uploadable').each(function() {
      const $this = $(this);
      if (MediaclassUploader.isLimitReached($this)) {
        $this.find('span.mediaclass-uploader').addClass('disabled');
      }
    });
  },

  uploaderOptions(uploadContainer) {
    const fileuploadContainer = this.fileupload(uploadContainer);
    const uploadable = this.uploadable(uploadContainer);
    const limit = Number(uploadable.data('limit'));
    const inputFileSize = uploadable.data('maxfilesize');
    const maxFileSize = this.calculateMaxFileSize(inputFileSize);
    const messagesUI = uploadable.find('.ui-messages');

    fileuploadContainer.fileupload('option', {
      previewMaxWidth: 220,
      previewMaxHeight: 220,
      acceptFileTypes: /(\.|\/)(jpe?g|png|svg|pdf)$/i,
      maxFileSize: maxFileSize,
      autoUpload: false,
      maxNumberOfFiles: limit > 0 ? limit : null,
      messages: {
        maxNumberOfFiles: `${messagesUI.find('.maxNumberOfFiles').first().text()} ${limit}`,
        acceptFileTypes: 'Type de fichier non autorisé',
        maxFileSize: `${messagesUI.find('.maxFileSize').first().text()} ${inputFileSize || ((this.defaultFileSize / 1024 / 1024) + 'MB')}`,
      },
    });
  },

  positions(uploadable) {
    uploadable.find('.positions i').off().on('click', function() {
      const $this = $(this);
      const positionsContainer = $this.closest('.positions');

      positionsContainer.find('i').removeClass('active');
      $this.addClass('active');
      positionsContainer.find('input').val($this.data('position'));
    });
  },

  initFileupload(uploadContainer) {
    const fileuploadContainer = this.fileupload(uploadContainer);
    const uploadable = this.uploadable(fileuploadContainer);
    const hideDescription = Number(uploadable.attr('data-has-description')) !== 1;

    // Only destroy existing fileupload instance if it exists to prevent conflicts
    if (fileuploadContainer.data('blueimp-fileupload') || fileuploadContainer.data('fileupload')) {
      fileuploadContainer.fileupload('destroy');
    }

    // Your original event handler for fileuploadadd
    fileuploadContainer.off('fileuploadadd fileuploadsubmit');

    fileuploadContainer.on('fileuploadadd', function() {
      fileuploadContainer.find('.uploadables').removeClass('d-none');

      setTimeout(() => {
        if (uploadable.data('positions') !== 1) {
          uploadable.find('.positions').addClass('d-none');
        }
        if (hideDescription) {
          uploadable.find('.description').addClass('d-none');
        }
        MediaclassUploader.positions(uploadable);
      }, 1);
    }).fileupload({
      url: MediaclassUploader.template().data('ajax'),
      dataType: 'json',
      context: fileuploadContainer[0],
      sequentialUploads: true,
      type: 'POST',
      done: () => {
        MediaclassUploader.progress().hide();
      },
      success: (data) => {
        const $alerts = $('.mediaclass-alerts').html('');

        if (data.hasOwnProperty('errors')) {
          notificator(data.errors, 'danger', MediaclassUploader.messages());
          return;
        }

        // Remove old files display
        uploadable.find('.files').delay(500).fadeOut(function() {
          $(this).html('').show();
        });

        // Create HTML for uploaded file
        const html = MediaclassUploader.buildUploadedFileHTML(data, hideDescription);

        // Add to container
        const uploadedFilesContainer = uploadable.find('.uploaded');
        uploadedFilesContainer.append(html);

        // Initialize events and check if we need to close uploader
        MediaclassUploader.unlinkable();

        // Check if we've reached the limit after adding this file
        if (MediaclassUploader.isLimitReached(uploadable)) {
          // If limit reached, disable the uploader button
          uploadable.find('span.mediaclass-uploader').addClass('disabled');
          MediaclassUploader.uploadableContainer(uploadable).html('');
        } else if (uploadedFilesContainer.find('> div.mediaclass.unlinkable').length === Number(data.count_files)) {
          MediaclassUploader.uploadableContainer(uploadable).html('');
        }

        MediaclassUploader.modalCrop();
      },
      error: (xhr, ajaxOptions, thrownError) => {
        console.error('Upload error:', xhr, thrownError);
        MediaclassUploader.messages().html('<div class="alert alert-danger">Une erreur est survenu lors du téléchargement de votre fichier</div>');
      },
      start: () => {
        MediaclassUploader.messages().html('');
        MediaclassUploader.progress().show();
      },
    });

    fileuploadContainer.bind('fileuploadsubmit', (e, data) => {
      MediaclassUploader.messages().html('');

      // Count valid files
      let validFiles = 0;
      uploadable.find('.files > div').each(function() {
        if ($(this).find('.error').first().text().length < 1) {
          validFiles += 1;
        }
      });

      // Set form data
      data.formData = [
        { name: '_token', value: token() },
        { name: 'action', value: 'upload' },
        { name: 'group', value: uploadable.data('group') },
        { name: 'subgroup', value: uploadable.data('subgroup') },
        { name: 'positions', value: uploadable.data('positions') },
        { name: 'model', value: uploadable.data('model') },
        { name: 'model_id', value: uploadable.data('model-id') },
        { name: 'mediaclass_temp_id', value: $('input[name="mediaclass_temp_id"]').first().val() ?? '' },
        { name: 'count_files', value: validFiles },
        { name: 'cropable', value: uploadable.data('cropable') }
      ];

      // Add form fields
      data.context.find('textarea, input').each(function() {
        data.formData.push({
          name: $(this).attr('name'),
          value: $(this).val()
        });
      });
    });
  },

  buildUploadedFileHTML(data, hideDescription) {
    const { uploaded, filetype, preview, link, cropable_link, sizes, has_positions } = data;

    let html = `
      <div class="mediaclass unlinkable uploaded-image my-2" data-id="${uploaded.id}" id="mediaclass-${uploaded.id}">
        <span class="unlink"><i class="bi bi-x-circle-fill"></i></span>
        <div class="row m-0">
          <div class="col-sm-3 impImg p-0 position-relative preview ${filetype}" style="background-image: url(${preview}); background-repeat: no-repeat">
            <div class="actions">
              <a target="_blank" href="${link}" class="zoom"><i class="fa-sharp fa-solid fa-magnifying-glass"></i></a>
              ${filetype === 'image' ? cropable_link : ''}
            </div>
            ${filetype === 'image' ? `<div class="sizes">${sizes}</div>` : ''}
          </div>
          <div class="col-sm-9 impFileName">
            <div class="row infos">
              <div class="col-sm-12"><p class="name">${uploaded.original_filename}</p></div>
            </div>
            <div class="row params mt-2">
              <div class="col-sm-7 description no-multilang${hideDescription ? ' d-none' : ''}">
    `;

    // Add descriptions - handle case where description might be null/undefined
    const descriptions = uploaded.description || {};
    for (const [key, value] of Object.entries(descriptions)) {
      html += `
        <b>Description <span class="lang">${this.langs[key]}</span></b>
        <textarea name="mediaclass[${uploaded.id}][description][${key}]" type="text" class="mt-2 form-control description">${value !== null ? value : ''}</textarea>
      `;
    }

    html += `
              </div>
              <div class="col-sm-5 positions text-center ps-2${has_positions === true ? '' : ' d-none'}">
                <b>Positions par rapport au contenu</b>
                <div class="choices pt-2">
    `;

    // Add position buttons
    for (const position of this.positions_tags) {
      html += `<i class="bi bi-arrow-${position}-square-fill active" data-position="${position}"></i>`;
    }

    html += `
                  <input type="hidden" name="mediaclass[${uploaded.id}][position]" value="${uploaded.position}">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

    return html;
  },

  modalCrop() {
    const $modalCrop = $('#mediaclass-crop');

    $modalCrop.off().on('show.bs.modal', function(e) {
      const link = $(e.relatedTarget);
      $(this).find('.modal-body').load(link.attr('href'));
    });

    $('body').on('hidden.bs.modal', '.modal', function() {
      $modalCrop.find('.modal-body').html('');
    });
  },

  hideModal() {
    setTimeout(() => {
      const $modalCrop = $('#mediaclass-crop');
      $modalCrop.modal('hide');

      $('body').on('hidden.bs.modal', '.modal', function() {
        $modalCrop.find('.modal-body').html('');
      });
    }, 1500);
  },

  cropped(result) {
    const { uploaded, urls, sizes } = result;
    const media = $(`#mediaclass-${uploaded.id}`);

    media.find('.preview').attr('style', `background:url(${urls.xl}); background-repeat: no-repeat; background-size: contain;`);
    media.find('.sizes').html(sizes);
    media.find('.zoom').attr('href', urls.xl);
    media.find('.crop').remove();

    this.hideModal();
  },

  init() {
    // Initialize positions for all uploadable elements
    $('.mediaclass-uploadable').each(function() {
      MediaclassUploader.positions($(this));
    });

    // Setup event handlers
    this.uploaderCall();
    this.unlinkable();
    this.modalCrop();
  },
};

// Initialize the module
MediaclassUploader.init();
