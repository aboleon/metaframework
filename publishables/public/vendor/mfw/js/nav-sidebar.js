(function ($) {
    'use strict';

    const currentUrl = window.location.href.split('#')[0].split('?')[0];
    const body = $('body');
    const menuToggle = $('#mfw-nav-menu-toggle');
    const mobileClose = $('.mfw-nav-mobile-close');
    const sidebarMenu = $('#mfw-nav-menu');
    const sidebar = $('#mfw-nav-sidebar');

    if (!sidebar.length || !sidebarMenu.length || !menuToggle.length) {
        return;
    }

    const mobileNavigation = window.matchMedia('(max-width: 991px)');

    const updateToggleAccessibility = function () {
        const isExpanded = mobileNavigation.matches
            ? body.hasClass('nav-sm')
            : body.hasClass('nav-md');

        menuToggle.attr('aria-expanded', isExpanded ? 'true' : 'false');
        mobileClose.prop('hidden', !mobileNavigation.matches || !body.hasClass('nav-sm'));
    };

    const closeMobileNavigation = function () {
        if (!mobileNavigation.matches || !body.hasClass('nav-sm')) {
            return;
        }

        body.removeClass('nav-sm').addClass('nav-md');
        updateToggleAccessibility();
        menuToggle.trigger('focus');
    };

    const closeAllMenus = function () {
        sidebarMenu.find('.mfw-nav-item').removeClass('mfw-nav-active mfw-nav-active-sm mfw-nav-open-sm');
        sidebarMenu.find('.mfw-nav-submenu').stop(true, true).hide();
    };

    const positionCollapsedSubmenu = function ($item) {
        const submenu = $item.children('.mfw-nav-submenu:first');

        if (!submenu.length) {
            return;
        }

        const itemTop = $item[0].getBoundingClientRect().top;
        const submenuHeight = submenu.outerHeight();
        const maxTop = window.innerHeight - submenuHeight - 10;
        const top = Math.max(10, Math.min(itemTop, maxTop));

        submenu.css('top', top + 'px');
    };

    sidebarMenu.find('.mfw-nav-link').on('click', function (event) {
        const item = $(this).parent('.mfw-nav-item');
        const hasSubmenu = item.children('.mfw-nav-submenu').length > 0;

        if (!hasSubmenu) {
            return;
        }

        event.preventDefault();

        if (body.hasClass('nav-sm')) {
            const isOpen = item.is('.mfw-nav-open-sm');

            closeAllMenus();

            if (!isOpen) {
                item.addClass('mfw-nav-active-sm mfw-nav-open-sm');
                item.children('.mfw-nav-submenu:first').show();
                positionCollapsedSubmenu(item);
            }

            return;
        }

        if (item.is('.mfw-nav-active')) {
            item.removeClass('mfw-nav-active mfw-nav-active-sm');
            item.children('.mfw-nav-submenu:first').stop(true, true).slideUp();
        } else {
            if (!item.parent().is('.mfw-nav-submenu')) {
                closeAllMenus();
            }

            item.addClass('mfw-nav-active');
            item.children('.mfw-nav-submenu:first').stop(true, true).slideDown();
        }
    });

    menuToggle.on('click', function () {
        if (body.hasClass('nav-md')) {
            sidebarMenu.find('.mfw-nav-item.mfw-nav-active .mfw-nav-submenu').stop(true, true).hide();
            sidebarMenu.find('.mfw-nav-item.mfw-nav-active').addClass('mfw-nav-active-sm').removeClass('mfw-nav-active mfw-nav-open-sm');
        } else {
            sidebarMenu.find('.mfw-nav-item.mfw-nav-active-sm').addClass('mfw-nav-active').removeClass('mfw-nav-active-sm mfw-nav-open-sm');
            sidebarMenu.find('.mfw-nav-item.mfw-nav-active .mfw-nav-submenu').show();
        }

        body.toggleClass('nav-md nav-sm');
        updateToggleAccessibility();

        $('.dataTable').each(function () {
            $(this).dataTable().fnDraw();
        });
    });

    mobileClose.on('click', closeMobileNavigation);

    sidebarMenu.find('.mfw-nav-link, .mfw-nav-sublink').filter(function () {
        return this.href === currentUrl || this.href + '/archived' === currentUrl;
    }).each(function () {
        const currentLink = $(this);
        const currentSubmenu = currentLink.parents('.mfw-nav-submenu').first();
        const currentParent = currentSubmenu.parent('.mfw-nav-item');

        currentLink.closest('.mfw-nav-item, .mfw-nav-subitem').addClass('mfw-nav-current-page');

        if (currentSubmenu.length) {
            currentSubmenu.show();

            if (body.hasClass('nav-sm')) {
                currentParent.addClass('mfw-nav-active-sm');
            } else {
                currentParent.addClass('mfw-nav-active');
            }
        }
    });

    $(document).on('click.mfwNavSidebar', function (event) {
        if (!body.hasClass('nav-sm') || $(event.target).closest('#mfw-nav-sidebar').length > 0) {
            return;
        }

        if (mobileNavigation.matches && $(event.target).closest('#mfw-nav-menu-toggle').length === 0) {
            closeMobileNavigation();

            return;
        }

        closeAllMenus();
    });

    $(document).on('keydown.mfwNavSidebar', function (event) {
        if (event.key === 'Escape') {
            closeMobileNavigation();
        }
    });

    $(window).on('resize.mfwNavSidebar', updateToggleAccessibility);
    $(window).on('resize.mfwNavSidebar scroll.mfwNavSidebar', function () {
        if (!body.hasClass('nav-sm')) {
            return;
        }

        sidebarMenu.find('.mfw-nav-item.mfw-nav-open-sm').each(function () {
            positionCollapsedSubmenu($(this));
        });
    });

    updateToggleAccessibility();
}(jQuery));
