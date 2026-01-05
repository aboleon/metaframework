$.fn.hasParent = function (e) {
    return !!$(this).parents(e).length;
};

function sendMailFromModalResponse(result) {
    let myModal = $('#' + result.input.modal_id);
    removeVeil();
    myModal.on('hidden.bs.modal', function () {
        $(this).find('.messages').remove();
    });
    setTimeout(function () {
        myModal.find('.btn-close').trigger('click');
    }, 4000);
}

let token = function () {
        return $('meta[name="csrf-token"]').attr('content');
    },
    dev = true,
    spinner = '<i class="core spinner fa fa-cog fa-spin fa-fw"></i>',
    timerDefault = function () {
        return 200;
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
    serializedToObject = function (serializedData) {
        let obj = {};
        let pairs = serializedData.split('&');

        pairs.forEach(function (pair) {
            pair = pair.split('=');
            obj[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
        });

        return obj;
    },
    setVeil = function (c) {
        c.prepend('<div class="veil" style="border-radius:25px"><img class="loading" src="/system/loading.svg" width="40" alt="..."></div>');
    },
    removeVeil = function () {
        $('.veil').remove();
    },
    /**
     * Convert serialized array to object.
     * Usage:
     * let formDataObject = serializedArrayToObject(   $('form').serializeArray()   );
     */
    serializedArrayToObject = function (formDataArray) {
        let formDataObject = {};

        $.each(formDataArray, function (i, field) {
            // Remove trailing "[]" from the field name
            let fieldName = field.name.replace(/\[\]$/, '');

            if (formDataObject[fieldName]) {
                // If the property already exists as an array, push the new value to it
                if ($.isArray(formDataObject[fieldName])) {
                    formDataObject[fieldName].push(field.value);
                }
                // If the property exists but is not an array, create an array with both old and new values
                else {
                    formDataObject[fieldName] = [formDataObject[fieldName], field.value];
                }
            } else {
                formDataObject[fieldName] = field.value;
            }
        });

        return formDataObject;
    },
    activateEventManagerLeftMenuItem = function (itemName) {
        const jMenu = $('#sidebar-menu');
        const jItem = jMenu.find('.item-' + itemName);
        if (jItem.length) {
            jMenu.find('.current-page').removeClass('current-page');
            jItem.addClass('current-page');
            jItem.closest('.child_menu').show();
        } else {
            const jStandalone = jMenu.find('.standalone-' + itemName);
            if (jStandalone.length) {
                jStandalone.addClass('current-page');
            }
        }
    },
    ajax = function (formData, selector, options = {}) {
        let ajax_url = document.querySelector('meta[name="ajax-route"]').content ?? null,
            ajax_url_origin,
            formTag = selector.closest('.form');

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

        if (selector[0].hasAttribute('data-ajax')) {
            ajax_url = selector.attr('data-ajax');
            ajax_url_origin = 'selector data-ajax';
        } else if (formTag.length) {
            if (formTag[0].hasAttribute('data-ajax')) {
                ajax_url = formTag.attr('data-ajax');
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

                // Handle console logging for messages_log
                ['mfw_ajax_messages_log', 'mfw_messages_log'].forEach(function(logKey) {
                    if (result.hasOwnProperty(logKey)) {
                        console.log(logKey + ':', result[logKey]);
                        result[logKey].forEach(function(logEntry) {
                            Object.keys(logEntry).forEach(function(logType) {
                                console.log('MFW LOG [' + logType.toUpperCase() + ']', logEntry[logType]);
                            });
                        });
                    }
                });
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
    guid = (keyLength = 9) => Math.random().toString(36).slice(2, 2 + keyLength),
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
    removeTabCookieRedirect = function (id) {
        Cookies.set('mfw_tab_redirect_' + id, '', {expires: 0});
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
    formatDate = function (dateString) {
        var dateObj = new Date(dateString);
        var day = dateObj.getDate();
        var month = dateObj.getMonth() + 1;
        var year = dateObj.getFullYear();
        day = day < 10 ? '0' + day : day;
        month = month < 10 ? '0' + month : month;
        year = year.toString();
        return day + '/' + month + '/' + year;
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
    produceNumberFromInput = function (input) {
        if (typeof input == 'string') {
            input = input.replace(/\s+/g, '');
        }
        let value = Number(input);
        return isNaN(value) ? 0 : value;
    };

if ('undefined' === typeof window.redrawDataTable) {
    window.redrawDataTable = function () {
        if ($.fn.DataTable) {
            $('.dt').DataTable().ajax.reload();
        }
    };
}

if ('undefined' === typeof window.debounce) {
    window.debounce = function (fn, delay = 150) {
        let timeoutID;
        return function (...args) {
            const context = this;
            if (timeoutID) {
                clearTimeout(timeoutID);
            }
            timeoutID = setTimeout(() => {
                fn.apply(context, args);
            }, delay);
        };
    };
}

// Usage with your search event
$('#input-search-event').on('keyup', debounce(function () {
    let value = $(this).val().toLowerCase();
    console.log('typed value', value);
}));

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

// Get the hash value from the URL
const tab_hash = window.location.hash;

// If there's a hash value, activate the corresponding tab
if (tab_hash) {
    let targetTab = $('#nav-tab button[data-bs-target="' + tab_hash + '"]').first();
    if (targetTab.length) {
        targetTab.click();
    }
}
// Mass checkable
$('.meta-checkable :checkbox').click(function () {
    let li = $(this).closest('li');
    if (!li.hasClass('child')) {
        li.closest('ul').find('li[data-parent=' + li.data('id') + ']').find(':checkbox').prop('checked', $(this).is(':checked'));
    }
});
setTimeout(function () {
    let tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    let tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

}, 1500);


// When modal is shown, manage topbar z-index
document.body.addEventListener('shown.bs.modal', function () {
    $('#topbar').removeClass('sticky-top');
});

// When modal is hidden
document.body.addEventListener('hidden.bs.modal', function () {
    $('#topbar').addClass('sticky-top');
});
