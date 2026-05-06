/**
 * Tooltips & Help System
 * 
 * Native tooltip implementation — no external dependencies.
 * Help sidebar toggle, keyboard shortcut, contextual help.
 * 
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */

(function($) {
    'use strict';

    /* =========================================================
       NAMESPACE
    ========================================================= */
    window.Base47Help = {
        sidebarActive: false,

        toggle: function() {
            var sidebar = document.getElementById('base47-help-sidebar');
            var overlay = document.getElementById('help-sidebar-overlay');
            if (!sidebar) return;

            this.sidebarActive = !this.sidebarActive;

            if (this.sidebarActive) {
                sidebar.classList.add('active');
                if (overlay) overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        },

        show: function(topic) {
            if (!this.sidebarActive) {
                this.toggle();
            }
            setTimeout(function() {
                var el = document.querySelector('[data-help-topic="' + topic + '"]');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            }, 300);
        }
    };

    // Keep backward-compatible global functions
    window.base47ToggleHelpSidebar = function() {
        Base47Help.toggle();
    };
    window.base47ShowHelp = function(topic) {
        Base47Help.show(topic);
    };

    /* =========================================================
       SAFE localStorage WRAPPER
    ========================================================= */
    function storageGet(key) {
        try {
            return localStorage.getItem(key);
        } catch(e) {
            return null;
        }
    }

    function storageSet(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch(e) {
            // Storage unavailable — silently ignore
        }
    }

    /* =========================================================
       INIT
    ========================================================= */
    $(document).ready(function() {
        initNativeTooltips();
        initHelpSidebar();
        initKeyboardShortcut();
        initHelpSearch();
    });

    /* =========================================================
       NATIVE TOOLTIPS
       Uses title attribute + CSS-powered tooltips via
       data-tippy-content. No external library needed.
    ========================================================= */
    function initNativeTooltips() {
        // Convert data-tippy-content to native title for accessibility
        $('[data-tippy-content]').each(function() {
            var $el = $(this);
            if (!$el.attr('title')) {
                $el.attr('title', $el.attr('data-tippy-content'));
            }
        });

        // Pro feature badges
        $('.pro-feature').each(function() {
            var $el = $(this);
            if (!$el.attr('title')) {
            $el.attr('title', 'Available in Base47 HTML Editor Pro');
            }
        });

        // Coming soon badges
        $('.coming-soon').each(function() {
            var $el = $(this);
            if (!$el.attr('title')) {
                $el.attr('title', 'Coming soon in a future update');
            }
        });
    }


    /* =========================================================
       HELP SIDEBAR
    ========================================================= */
    function initHelpSidebar() {
        if (!$('#base47-help-sidebar').length) {
            return;
        }

        // Close button
        $(document).on('click', '.help-sidebar-close', function(e) {
            e.preventDefault();
            Base47Help.toggle();
        });

        // Overlay click
        $(document).on('click', '.help-sidebar-overlay', function(e) {
            e.preventDefault();
            Base47Help.toggle();
        });

        // ESC key
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && Base47Help.sidebarActive) {
                Base47Help.toggle();
            }
        });
    }

    /* =========================================================
       KEYBOARD SHORTCUT (Ctrl+Shift+/)
       Only on Base47 admin pages
    ========================================================= */
    function initKeyboardShortcut() {
        // Only enable on Base47 pages
        if (!$('body').hasClass('base47-admin-page')) {
            return;
        }

        $(document).on('keydown', function(e) {
            // Ctrl+Shift+/ (question mark)
            if (e.ctrlKey && e.shiftKey && e.keyCode === 191) {
                e.preventDefault();
                Base47Help.toggle();
            }
        });
    }

    /* =========================================================
       HELP SEARCH
    ========================================================= */
    function initHelpSearch() {
        var $helpContent = $('.help-sidebar-content');
        if (!$helpContent.length) return;

        // Add search box
        var searchBox = '<div class="help-search-box" style="margin-bottom: 1rem;">' +
            '<input type="text" id="help-search" placeholder="Search help..." ' +
            'style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 0.5rem;">' +
            '</div>';
        $helpContent.prepend(searchBox);

        // Handle search
        $('#help-search').on('input', function() {
            var query = $(this).val().toLowerCase().trim();
            filterHelpContent(query);
        });
    }

    function filterHelpContent(query) {
        var $sections = $('.help-sidebar-content').find('h3, h4, p, li');

        if (!query) {
            $sections.show();
            return;
        }

        $sections.each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(query) !== -1);
        });
    }

    /* =========================================================
       REFRESH TOOLTIPS (when content updates)
    ========================================================= */
    $(document).on('base47ContentUpdated', function() {
        initNativeTooltips();
    });

})(jQuery);
