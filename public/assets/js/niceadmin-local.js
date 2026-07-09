$(function () {
    const savedTheme = localStorage.getItem('niceadmin-theme');
    if (savedTheme === 'dark') {
        $('body').addClass('dark-theme');
        $('#theme-toggle i').removeClass('bi-moon-stars').addClass('bi-sun');
    }

    $('.sidebar-toggle').on('click', function () {
        const isMobile = window.matchMedia('(max-width: 900px)').matches;

        if (isMobile) {
            $('body').removeClass('sidebar-hidden');
            $('.nice-sidebar').toggleClass('open');
            $(this).attr('aria-expanded', $('.nice-sidebar').hasClass('open'));
            return;
        }

        $('body').toggleClass('sidebar-hidden');
        $('.nice-sidebar').removeClass('open');
        $(this).attr('aria-expanded', !$('body').hasClass('sidebar-hidden'));
    });

    $('.password-toggle').on('click', function () {
        const input = $('#password');
        const isPassword = input.attr('type') === 'password';
        input.attr('type', isPassword ? 'text' : 'password');
        $(this).find('i').toggleClass('bi-eye bi-eye-slash');
    });

    $('.js-submenu-toggle').on('click', function (event) {
        const $nextMenu = $(this).next('.submenu');

        if (!$nextMenu.length) {
            return;
        }

        event.preventDefault();

        const isNested = $nextMenu.hasClass('nested');
        const isOpen = $nextMenu.hasClass('open');
        const $siblings = isNested
            ? $nextMenu.siblings('.submenu.nested.open')
            : $(this).closest('.menu-section').children('.submenu.open').not($nextMenu);

        $siblings.each(function () {
            const $menu = $(this);
            $menu.stop(true, true).slideUp(180, function () {
                $menu.removeClass('open').removeAttr('style');
            });
            $menu.prev('.js-submenu-toggle').removeClass('active')
                .find('.chevron').first().removeClass('bi-chevron-down').addClass('bi-chevron-right');
        });

        if (isOpen) {
            $nextMenu.stop(true, true).slideUp(180, function () {
                $nextMenu.removeClass('open').removeAttr('style');
            });
            $(this).removeClass('active');
            $(this).find('.chevron').first().removeClass('bi-chevron-down').addClass('bi-chevron-right');
            return;
        }

        $nextMenu.stop(true, true).slideDown(180, function () {
            $nextMenu.addClass('open').removeAttr('style');
        });
        $(this).addClass('active');
        $(this).find('.chevron').first().removeClass('bi-chevron-right').addClass('bi-chevron-down');
    });

    $('.js-top-panel').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        const panelId = $(this).data('panel');
        const $panel = $('#' + panelId);
        const isOpen = $panel.hasClass('open');

        $('.top-panel').removeClass('open');
        $('.js-top-panel').removeClass('is-open');

        if (!isOpen) {
            $panel.addClass('open');
            $(this).addClass('is-open');
        }
    });

    $('.top-panel').on('click', function (event) {
        event.stopPropagation();
    });

    $(document).on('click', function () {
        $('.top-panel').removeClass('open');
        $('.js-top-panel').removeClass('is-open');
    });

    $('#theme-toggle').on('click', function (event) {
        event.preventDefault();
        $('body').toggleClass('dark-theme');

        const isDark = $('body').hasClass('dark-theme');
        localStorage.setItem('niceadmin-theme', isDark ? 'dark' : 'light');
        $(this).find('i').toggleClass('bi-moon-stars bi-sun');
    });
});
