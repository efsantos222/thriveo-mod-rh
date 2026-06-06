/**
 * toast.js — Engajex Toast Notification System
 * API: Toast.show(message, type, options)
 *      Toast.success(message, options)
 *      Toast.error(message, options)
 *      Toast.warning(message, options)
 *      Toast.info(message, options)
 */
(function (global) {
    'use strict';

    var ICONS = {
        success: '<i class="fa-solid fa-circle-check"></i>',
        error:   '<i class="fa-solid fa-circle-xmark"></i>',
        warning: '<i class="fa-solid fa-triangle-exclamation"></i>',
        info:    '<i class="fa-solid fa-circle-info"></i>',
    };

    var DEFAULT_DURATION = 4500; // ms

    function getContainer() {
        var el = document.getElementById('toast-container');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast-container';
            document.body.appendChild(el);
        }
        return el;
    }

    function show(message, type, options) {
        type    = type    || 'info';
        options = options || {};

        var duration = options.duration !== undefined ? options.duration : DEFAULT_DURATION;
        var title    = options.title    || null;

        var container = getContainer();

        // Build toast element
        var toast = document.createElement('div');
        toast.className = 'toast toast--' + type;
        if (!title) toast.classList.add('toast--simple');

        var bodyHtml = title
            ? '<p class="toast__title">' + _escape(title) + '</p><p class="toast__message">' + _escape(message) + '</p>'
            : '<p class="toast__message">' + _escape(message) + '</p>';

        toast.innerHTML =
            '<div class="toast__icon">' + (ICONS[type] || ICONS.info) + '</div>' +
            '<div class="toast__body">' + bodyHtml + '</div>' +
            '<button class="toast__close" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>' +
            (duration > 0 ? '<div class="toast__progress" style="animation-duration:' + duration + 'ms"></div>' : '');

        container.appendChild(toast);

        // Close button
        toast.querySelector('.toast__close').addEventListener('click', function () {
            dismiss(toast);
        });

        // Click anywhere on toast also closes it
        toast.addEventListener('click', function (e) {
            if (!e.target.closest('.toast__close')) dismiss(toast);
        });

        // Auto-dismiss
        var timer = null;
        if (duration > 0) {
            timer = setTimeout(function () { dismiss(toast); }, duration);
        }

        // Pause progress on hover
        toast.addEventListener('mouseenter', function () {
            if (timer) clearTimeout(timer);
            var bar = toast.querySelector('.toast__progress');
            if (bar) bar.style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', function () {
            var bar = toast.querySelector('.toast__progress');
            if (bar) {
                // Restart remaining animation is complex; just use remaining = 1.5s
                bar.style.animationPlayState = 'running';
            }
            if (duration > 0) {
                timer = setTimeout(function () { dismiss(toast); }, 1500);
            }
        });

        return toast;
    }

    function dismiss(toast) {
        if (toast.classList.contains('toast--dismissing')) return;
        toast.classList.add('toast--dismissing');
        toast.addEventListener('animationend', function handler() {
            toast.removeEventListener('animationend', handler);
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        });
    }

    function _escape(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    var Toast = {
        show:    show,
        success: function (msg, opts) { return show(msg, 'success', opts); },
        error:   function (msg, opts) { return show(msg, 'error',   opts); },
        warning: function (msg, opts) { return show(msg, 'warning', opts); },
        info:    function (msg, opts) { return show(msg, 'info',    opts); },
    };

    global.Toast = Toast;

})(window);
