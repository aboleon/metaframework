$.fn.hasParent = function (e) {
    return !!$(this).parents(e).length;
};
let token = function () {
        return $('meta[name="csrf-token"]').attr('content');
    },
    dev = true,
    spinner = '<i class="core spinner fa fa-cog fa-spin fa-fw"></i>',
    timerDefault = function () {
        return 500;
    },
    setDelay = (function () {
        let timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })(),
    timer = timerDefault(),
    spinout = function () {
        setTimeout(function () {
            $('.spinner').fadeOut(function () {
                $(this).remove();
            });
        }, timer + timerDefault());
    },
    notificationQueue = function (messages) {
        return messages.find(' > div').length;
    },
    notificator = function (status, data, messages, keepMessages, printerOptions) {
      const isDismissable = printerOptions.isDismissable ?? true;
      const $data = $(data);

      if (!keepMessages) {
        messages.empty(); // Clear all previous messages
      }

      switch (status) {
        case 422: // Laravel JSON Validator Messages
          if (data.responseJSON?.errors) {
            $.each(data.responseJSON.errors, function (key, errorMessages) {
              alertDispatcher(errorMessages[0], messages, 'danger', isDismissable);
            });
          }
          break;

        default:
          if (!$data.length) return false; // Exit if no data

          // Append and process each message sequentially
          $data.each(function (index, message) {
            $.each(message, function (key, value) {
              alertDispatcher(value, messages, key, isDismissable);
            });
          });
      }

      dismissable(); // Reapply dismissible behavior
    },
    alertDispatcher = function (message, messages, messageType, isDismissable) {
      const alertHtml = isDismissable
          ? '<div style="opacity: 0; transition: opacity 0.5s;" class="alert alert-dismissible alert-' + messageType + '">' +
          '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
          message + '</div>'
          : '<div style="opacity: 0; transition: opacity 0.5s;" class="alert alert-' + messageType + '">' + message + '</div>';

      // Append the message to the container
      const $newMessage = $(alertHtml).appendTo(messages);

      // Use a timeout to apply fade-in effect via CSS transition
      const currentTimer = timerDefault() * (notificationQueue(messages));
      setTimeout(() => {
        $newMessage.css('opacity', 1); // Triggers the CSS transition
      }, currentTimer);
    },
    ajax = function (formData, selector, options = {}) {
        // Safely get ajax_url with multiple fallbacks
        let ajax_url = null;
        let ajax_url_origin = 'default';
        let metaTag = document.querySelector('meta[name="ajax-route"]');

        // Try to get URL from meta tag first
        if (metaTag && metaTag.content) {
            ajax_url = metaTag.content;
            ajax_url_origin = 'meta tag';
        }

        let formTag = selector.closest('.form');

        let spinner = options.spinner ?? null;
        if (spinner) {
            spinner = selector.find('.ajax-spinner');
            if (!spinner.length) {
                spinner = null;
            }
        }

        let successHandler = options.successHandler ?? null;
        let errorHandler = options.errorHandler ?? null;
        let printerOptions = options.printerOptions ?? false;

        // Check selector for data-ajax attribute (overrides meta tag)
        if (selector[0].hasAttribute('data-ajax')) {
            ajax_url = selector.attr('data-ajax');
            ajax_url_origin = 'selector data-ajax';
        }
        // Check parent form for data-ajax attribute
        else if (formTag.length && formTag[0].hasAttribute('data-ajax')) {
            ajax_url = formTag.attr('data-ajax');
            ajax_url_origin = 'form data-ajax';
        }
        // Check closest container with data-ajax
        else if (!ajax_url && selector.closest('[data-ajax]').length) {
            ajax_url = selector.closest('[data-ajax]').data('ajax');
            ajax_url_origin = 'parent data-ajax';
        }

        // Final fallback based on current path if still no URL
        if (!ajax_url) {
            let currentPath = window.location.pathname;
            if (currentPath.includes('/panel/Seller/')) {
                ajax_url = '/panel/Seller/ajax';
                ajax_url_origin = 'path-based fallback (Seller)';
            } else if (currentPath.includes('/panel/')) {
                ajax_url = '/panel/ajax';
                ajax_url_origin = 'path-based fallback (panel)';
            } else {
                ajax_url = '/ajax';
                ajax_url_origin = 'path-based fallback (root)';
            }
        }

        dev ? console.log('Ajax started on ' + ajax_url + ' with origin ' + ajax_url_origin) : null;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            url: ajax_url,
            type: 'POST',
            dataType: 'json',
        });

        selector = (typeof selector == 'undefined' ? $(this).closest('.form') : selector);
        selector.find('.messages').length < 1 ? selector.append('<div class="messages"></div>') : '';

        let messages = selector.find('.messages'), keepMessages = options.keepMessages ?? false;

        if (spinner) {
            $(spinner).show();
        }

        let messagePrinter = options.messagePrinter ?? function (status, ajax_messages, messages, keepMessages, printerOptions) {
            return ajax_messages.length > 0 ? notificator(status, ajax_messages, messages, keepMessages, printerOptions) : null;
        };

        $.ajax({
            data: formData,
            done: function () {
                messages.html('');
            },
            success: function (result) {

                let hasError = result.error || false;
                let showMessages = true;

                if (hasError && errorHandler) {
                    showMessages = errorHandler(result);
                } else if (!hasError && successHandler) {
                    showMessages = successHandler(result);
                }

                if (showMessages && result.hasOwnProperty('mfw_ajax_messages')) {
                    messagePrinter(200, result.mfw_ajax_messages, messages, keepMessages, printerOptions);
                }
            },
            error: function (xhr) {
                dev ? console.log(xhr) : null;
                notificator(xhr.status, xhr, messages, keepMessages, printerOptions);
            },

        }).always(function (result) {

            let callback = result.hasOwnProperty('callback') ? result.callback : false;
            dev ? console.log(result, 'Result') : null;
            typeof window[callback] === 'function' ? window[callback](result) : null;
            console.log(callback, typeof window[callback] === 'function');

            if (spinner) {
                $(spinner).hide();
            }
            spinout();
        });
    },
    slugify = function (text) {
        return text.toString().toLowerCase().replace(/\s+/g, '-').replace(/[^\u0100-\uFFFF\w\-]/g, '-').replace(/\-\-+/g, '-').replace(/^-+/, '').replace(/-+$/, '');
    },
    guid = function (keylength = 9) {
        let str = '';
        while (str.length < keylength) {
            str += Math.random().toString(36).substr(2);
        }
        return str.substr(0, keylength);
    },
    generateUUID = function () {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    },
    access_key = function (iteration = 10, keylength = 16) {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(keylength)
                .substring(1);
        }

        let hash = '';
        for (let i = 0; i < iteration; ++i) {
            hash = hash.concat(s4());
            //return s4() + s4() + s4() + s4() + s4() + s4() + s4() + s4() + s4() + s4();
        }
        return hash;
    },
    isUrlValid = function (userInput) {
        var regexQuery = '^(https://)?(www\\.)?([-a-z0-9]{1,63}\\.)*?[a-z0-9][-a-z0-9]{0,61}[a-z0-9]\\.[a-z]{2,6}(/[-\\w@\\+\\.~#\\?&/=%]*)?$';
        var url = new RegExp(regexQuery, 'i');
        return url.test(userInput);
    },
    dismissable = function () {
        $('.alert-dismissible button').off().on('click', function () {
            $(this).parent().remove();
        });
    },
    currentDate = function () {
        var today = new Date(),
            dd = today.getDate(),
            mm = today.getMonth() + 1; //January is 0!,
        yyyy = today.getFullYear();
        if (dd < 10) {
            dd = '0' + dd;
        }
        if (mm < 10) {
            mm = '0' + mm;
        }
        return dd + '/' + mm + '/' + yyyy;
    },
    // Téléchargement des images : annuler
    cancel = function () {
        $('#fileupload table').find('tbody').html('').end().hide();
        $('#imp .messages').html('');
    },
    resetIteration = function (container) {
        let iterations = container.find('.iteration');
        console.log('lenght is ' + iterations.length, 'container is ' + container.attr('id'));
        if (iterations.length) {
            $('.iteration.zero').hide();
            iterations.each(function (index) {
                $(this).text(index + 1);
            });
        } else {
            container.parent().find('.iteration.zero').show();
        }
    },
    attributeUpdater = function (target, old_id, new_id) {

        function replace(variable) {
            return variable.replace(old_id, new_id);
        }

        target.attr('data-id', new_id);

        target.find('textarea, input, select, label').each(function () {
            let name = $(this).attr('name'),
                id = $(this).attr('id'),
                label = $(this).attr('for');
            name !== undefined ? $(this).attr('name', replace(name)) : null;
            id === undefined ? $(this).attr('id', $(this).attr('name')) : $(this).attr('id', replace(id));
            label === undefined ? $(this).attr('for', $(this).parent().find('input').attr('id')) : $(this).attr('for', replace(label));
        });
    },
    removable = function () {
        $('a.removable').off().on('click', function (e) {
            e.preventDefault();
            let container = $(this).parents('.removable').parent();
            $(this).parents('.removable').remove();
            resetIteration(container);
        });
    },
    setVeil = function (c) {
        c.prepend('<div class="veil" style="border-radius:25px"><img class="loading" src="/assets/system/loading.svg" width="40" alt="..."></div>');
    },
    removeVeil = function () {
        $('.veil').remove();
    };

const wa_geo_control = {
    reset: function (el) {
        $(el).find($('.g_autocomplete')).on('keyup change', function () {
            console.log('typing in ' + el + 'g_autocomplete');
            $('.wa_geo_lat, .wa_geo_lon').val('');
        });
    },
};
$(function () {
    $('.toggle').click(function () {
        $(this).parent().find('.toggable').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-up');
    });
});


setTimeout(function () {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

}, 500);
