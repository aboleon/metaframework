(function() {
    function escapeSelector(value) {
        return value.replace(/([ #;?%&,.+*~':"!^$\[\]()=>|\/])/g, '\\$1');
    }

    function resolveEditor(textarea) {
        if (!textarea || textarea.tagName.toLowerCase() !== 'textarea') {
            return null;
        }
        if (!window.tinymce || !textarea.id) {
            return null;
        }
        return tinymce.get(textarea.id) || null;
    }

    function readValue(element) {
        const editor = resolveEditor(element);
        if (editor) {
            return editor.getContent({ format: 'html' }) || '';
        }
        return element.value || '';
    }

    function isHtmlLike(value) {
        return /<[^>]+>/.test(value);
    }

    function stripInvalidTagOpenings(value) {
        return value.replace(/<(?!\/?[A-Za-z][A-Za-z0-9-]*(\s|\/?>))/g, '');
    }

    function normalizeTranslation(sourceValue, translatedValue) {
        if (translatedValue === null || translatedValue === undefined) {
            return '';
        }

        const trimmed = String(translatedValue).trim();
        if (trimmed === '') {
            return '';
        }

        const sourceIsHtml = isHtmlLike(String(sourceValue || ''));
        if (!sourceIsHtml) {
            return translatedValue;
        }

        if (!isHtmlLike(trimmed)) {
            return '<p>' + trimmed + '</p>';
        }

        const sanitized = stripInvalidTagOpenings(trimmed);
        return sanitized;
    }

    function writeValue(element, value) {
        const editor = resolveEditor(element);
        if (editor) {
            editor.setContent(value ?? '');
            editor.save();
        } else {
            element.value = value ?? '';
        }
        if (window.jQuery) {
            window.jQuery(element).trigger('input').trigger('change');
        }
    }

    function normalizePayload(container, locales, sourceLocale) {
        const payload = [];
        const inputs = container.querySelectorAll('input[name], textarea[name]');

        inputs.forEach(function(element) {
            if (!element.name) {
                return;
            }
            if (element.type === 'radio' || element.type === 'checkbox' || element.type === 'hidden') {
                return;
            }

            const match = element.name.match(/\[([^\[\]]+)\]$/);
            if (!match) {
                return;
            }

            const locale = match[1];
            if (!locales.includes(locale) || locale !== sourceLocale) {
                return;
            }

            const value = readValue(element).trim();
            if (value === '') {
                return;
            }

            const key = element.name.replace(/\[[^\[\]]+\]$/, '');
            payload.push({
                key: key,
                value: value,
            });
        });

        return payload;
    }

    function syncTargets(container, sourceLocale) {
        const targets = container.querySelectorAll('input[data-mfw-translate-target]');
        targets.forEach(function(target) {
            const shouldDisable = target.value === sourceLocale;
            if (shouldDisable) {
                target.checked = false;
            }
            target.disabled = shouldDisable;
        });
    }

    function initContainer(container) {
        const locales = JSON.parse(container.getAttribute('data-locales') || '[]');
        const sourceInputs = container.querySelectorAll('input[data-mfw-translate-from]');
        const targetInputs = container.querySelectorAll('input[data-mfw-translate-target]');
        const button = container.querySelector('button[data-mfw-translate-button]');
        const spinner = button ? button.querySelector('.mfw-translate-spinner') : null;
        const targetRequiredMessage = container.getAttribute('data-translate-target-required') || '';

        if (!button || !locales.length || !sourceInputs.length) {
            return;
        }

        const getSourceLocale = function() {
            const selected = container.querySelector('input[data-mfw-translate-from]:checked');
            return selected ? selected.value : container.getAttribute('data-selected-locale');
        };

        const getTargetLocales = function() {
            const selected = [];
            targetInputs.forEach(function(target) {
                if (target.checked && !target.disabled) {
                    selected.push(target.value);
                }
            });
            return selected;
        };

        sourceInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                syncTargets(container, getSourceLocale());
            });
        });

        syncTargets(container, getSourceLocale());

        button.addEventListener('click', function() {
            const sourceLocale = getSourceLocale();
            const targetLocales = getTargetLocales();
            const payload = normalizePayload(container, locales, sourceLocale);

            if (!targetLocales.length) {
                if (window.MfwAjax && targetRequiredMessage) {
                    const $container = window.jQuery(container);
                    if ($container.find('.messages').length < 1) {
                        $container.append('<div class="messages"></div>');
                    }
                    window.MfwAjax.alertDispatcher(targetRequiredMessage, $container.find('.messages'), 'danger', true);
                    window.MfwAjax.dismissable();
                }
                return;
            }

            const data = {
                action: 'translate_translatables',
                source_locale: sourceLocale,
                target_locales: targetLocales,
                payload: payload,
            };

            if (spinner) {
                spinner.style.display = 'inline-flex';
            }
            button.disabled = true;

            const request = mfwAjax(data, window.jQuery(container), {
                successHandler: function(result) {
                    if (!result || result.error || !result.translations) {
                        return true;
                    }

                    Object.keys(result.translations).forEach(function(field) {
                        const translations = result.translations[field] || {};
                        Object.keys(translations).forEach(function(locale) {
                            const selector = '[name="' + escapeSelector(field + '[' + locale + ']') + '"]';
                            const element = container.querySelector(selector);
                            if (element) {
                                const sourceSelector = '[name="' + escapeSelector(field + '[' + sourceLocale + ']') + '"]';
                                const sourceElement = container.querySelector(sourceSelector);
                                const sourceValue = sourceElement ? readValue(sourceElement) : '';
                                const normalized = normalizeTranslation(sourceValue, translations[locale]);
                                writeValue(element, normalized);
                            }
                        });
                    });

                    return true;
                },
            });

            if (request && typeof request.always === 'function') {
                request.always(function() {
                    if (spinner) {
                        spinner.style.display = 'none';
                    }
                    button.disabled = false;
                });
            } else {
                if (spinner) {
                    spinner.style.display = 'none';
                }
                button.disabled = false;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-mfw-translatable-tabs]').forEach(initContainer);
    });
})();
