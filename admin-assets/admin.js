jQuery(function ($) {

    const $code    = $('#base47-he-code');
    const $file    = $('#base47-he-current-file');
    const $set     = $('#base47-he-current-set');
    const $preview = $('#base47-he-preview');

    /* ==========================
       COPY SHORTCODE
    ========================== */
    $(document).on('click', '.base47-he-copy', function () {
        const sc = $(this).data('shortcode');
        if (!sc) return;

        const btn      = $(this);
        const original = btn.html();

        navigator.clipboard.writeText(sc).then(() => {
            // Check if we're on Soft UI page
            if ($('.base47-sc-soft-ui').length) {
                // Show Soft UI toast
                showSoftUIToast('Shortcode Copied!', '✓');
            } else {
                // Old style feedback
                btn.text('Copied').css('background', '#2ecc71');
                setTimeout(() => {
                    btn.html(original).css('background', '');
                }, 1200);
            }
        });
    });
    
    /* ==========================
       SOFT UI TOAST NOTIFICATION
    ========================== */
    function showSoftUIToast(message, icon = '✓') {
        // Remove existing toast
        $('.base47-sc-toast').remove();
        
        // Create toast
        const $toast = $('<div class="base47-sc-toast">' +
            '<span class="base47-sc-toast-icon">' + icon + '</span>' +
            '<span class="base47-sc-toast-message">' + message + '</span>' +
            '</div>');
        
        $('body').append($toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            $toast.css('animation', 'slideOutRight 0.3s ease');
            setTimeout(() => $toast.remove(), 300);
        }, 3000);
    }
    
    /* ==========================
       SHORTCODES PAGE SEARCH
    ========================== */
    $('#base47-sc-search').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        
        $('.base47-sc-card').each(function() {
            const templateName = $(this).data('template-name') || '';
            const matches = templateName.includes(query);
            
            $(this).toggle(matches);
        });
        
        // Hide/show theme sections if all cards are hidden
        $('.base47-sc-theme-section').each(function() {
            const $section = $(this);
            const visibleCards = $section.find('.base47-sc-card:visible').length;
            $section.toggle(visibleCards > 0);
        });
    });

    /* ==========================
       ACTIVE SET HELPER
    ========================== */
function getActiveSet() {
    let setVal = $set.val();

    if (!setVal || setVal === 'undefined') {
        setVal = (window.BASE47_HE && BASE47_HE.default_set)
            ? BASE47_HE.default_set
            : 'base47-templates';
    }

    return setVal;
}
    /* ==========================
       LIVE PREVIEW (EDITOR)
    ========================== */
    let timer;
    if ($code.length) {
        $code.on('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                $.post(BASE47_HE.ajax_url, {
	action: 'base47_he_live_preview',
                    nonce:  BASE47_HE.nonce,
                    file:   $file.val(),
                    set:    getActiveSet(),
                    content: $code.val()
                }, function (resp) {
                    if (resp.success && resp.data && resp.data.html) {
                        const iframe = $preview.get(0);
                        iframe.contentWindow.document.open();
                        iframe.contentWindow.document.write(resp.data.html);
                        iframe.contentWindow.document.close();
                    }
                });
            }, 700);
        });
    }

    /* ==========================
       DUPLICATE TEMPLATE (EDITOR)
    ========================== */
    $('#base47-he-duplicate').on('click', function (e) {
        e.preventDefault();
        
        // Open duplicate modal
        $('#base47-he-duplicate-modal').fadeIn(200);
        
        // Reset form
        $('#base47-he-duplicate-name').val('');
        $('#base47-he-duplicate-error').hide();
        $('#base47-he-duplicate-confirm').prop('disabled', false);
        
        // Focus on input
        setTimeout(() => {
            $('#base47-he-duplicate-name').focus();
        }, 250);
    });

    // Duplicate confirmation
    $('#base47-he-duplicate-confirm').on('click', function (e) {
        e.preventDefault();
        
        const newName = $('#base47-he-duplicate-name').val().trim();
        const $error = $('#base47-he-duplicate-error');
        const $btn = $(this);
        
        // Reset error state
        $error.hide();
        
        // Validate input
        if (!newName) {
            $error.find('p').text('Please enter a template name.');
            $error.show();
            return;
        }
        
        // Validate filename format
        if (!/^[a-zA-Z0-9_-]+\.html?$/i.test(newName)) {
            $error.find('p').text('Invalid filename. Use only letters, numbers, hyphens, underscores, and .html extension.');
            $error.show();
            return;
        }
        
        // Disable button and show loading
        $btn.prop('disabled', true).text('Creating...');
        
        // Send duplication request
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_duplicate_template',
            nonce: BASE47_HE.nonce,
            file: $file.val(),
            set: getActiveSet(),
            new_name: newName,
            content: $code.val() // Include current editor content
        }, function (resp) {
            if (resp.success) {
                // Close modal
                $('#base47-he-duplicate-modal').fadeOut(200);
                
                // Show success message
                if ($('.base47-sc-soft-ui').length) {
                    showSoftUIToast('Template duplicated successfully!', '✓');
                } else {
                    showSoftUIToast('Template duplicated successfully!', '✓');
                }
                
                // Redirect to the new template
                if (resp.data && resp.data.redirect_url) {
                    window.location.href = resp.data.redirect_url;
                }
            } else {
                // Show error
                $error.find('p').text(resp.data || 'Failed to duplicate template.');
                $error.show();
                
                // Re-enable button
                $btn.prop('disabled', false).text('Create Duplicate');
            }
        }).fail(function() {
            $error.find('p').text('Network error. Please try again.');
            $error.show();
            $btn.prop('disabled', false).text('Create Duplicate');
        });
    });

    // Handle Enter key in duplicate name input
    $('#base47-he-duplicate-name').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            $('#base47-he-duplicate-confirm').click();
        }
    });

    /* ==========================
       RESTORE BACKUP MODAL (EDITOR)
    ========================== */
    let selectedBackup = null;

    $('#base47-he-restore').on('click', function (e) {
        e.preventDefault();
        
        // Open modal
        $('#base47-he-restore-modal').fadeIn(200);
        
        // Load backup list
        $('#base47-he-refresh-backups').click();
    });

    // Close modal
    $(document).on('click', '.base47-he-modal-close', function() {
        $('#base47-he-restore-modal').fadeOut(200);
    });

    // Select backup
    $(document).on('click', '.base47-he-backup-item', function() {
        $('.base47-he-backup-item').removeClass('selected');
        $(this).addClass('selected');
        
        selectedBackup = $(this).data('backup');
        $('#base47-he-restore-selected, #base47-he-download-selected').prop('disabled', false);
        
        // Load preview
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_ajax_restore_backup',
            nonce:  BASE47_HE.nonce,
            file:   $file.val(),
            set:    getActiveSet(),
            backup_filename: selectedBackup
        }, function (resp) {
            if (resp.success && resp.data && resp.data.content) {
                $('#base47-he-backup-preview-content').val(resp.data.content);
                $('#base47-he-backup-preview').slideDown(200);
            }
        });
    });

    // Restore selected backup
    $('#base47-he-restore-selected').on('click', function() {
        if (!selectedBackup) return;
        
        const btn = $(this);
        const originalText = btn.text();
        btn.prop('disabled', true).text('Restoring...');
        
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_ajax_restore_backup',
            nonce:  BASE47_HE.nonce,
            file:   $file.val(),
            set:    getActiveSet(),
            backup_filename: selectedBackup
        }, function (resp) {
            if (resp.success && resp.data && resp.data.content) {
                // Load content into editor
                $code.val(resp.data.content);
                
                // Update preview
                $.post(BASE47_HE.ajax_url, {
                    action: 'base47_he_live_preview',
                    nonce:  BASE47_HE.nonce,
                    file:   $file.val(),
                    set:    getActiveSet(),
                    content: resp.data.content
                }, function (previewResp) {
                    if (previewResp.success && previewResp.data && previewResp.data.html) {
                        const iframe = $preview.get(0);
                        iframe.contentWindow.document.open();
                        iframe.contentWindow.document.write(previewResp.data.html);
                        iframe.contentWindow.document.close();
                    }
                });
                
                // Close modal
                $('#base47-he-restore-modal').fadeOut(200);
                
                // Show success message
                if ($('.base47-sc-soft-ui').length) {
                    showSoftUIToast('Backup restored! Remember to save if you want to keep these changes.', '✓');
                } else {
                    showSoftUIToast('Backup restored! Remember to save if you want to keep these changes.', '✓');
                }
            } else {
                showSoftUIToast('Failed to restore backup.', '✗');
            }
            btn.prop('disabled', false).text(originalText);
        });
    });

    // Download selected backup
    $('#base47-he-download-selected').on('click', function() {
        if (!selectedBackup) return;
        
        const url = BASE47_HE.ajax_url + 
            '?action=base47_he_download_backup' +
            '&file=' + encodeURIComponent($file.val()) +
            '&set=' + encodeURIComponent(getActiveSet()) +
            '&backup_filename=' + encodeURIComponent(selectedBackup) +
            '&nonce=' + BASE47_HE.nonce;
        
        window.location.href = url;
    });

    // Create manual backup
    $('#base47-he-create-backup').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="font-size:14px;width:14px;height:14px;animation:spin 1s linear infinite;"></span> Creating...');
        
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_save_template',
            nonce: BASE47_HE.nonce,
            file: $file.val(),
            set: getActiveSet(),
            content: getCurrentEditorContent()
        }, function(resp) {
            btn.prop('disabled', false).html('<span class="dashicons dashicons-plus-alt" style="font-size:14px;width:14px;height:14px;"></span> Create Backup');
            if (resp && resp.success) {
                showSoftUIToast('Backup created!', '✓');
                // Refresh backup list
                $('#base47-he-refresh-backups').click();
            } else {
                showSoftUIToast('Failed to create backup.', '✗');
            }
        }).fail(function() {
            btn.prop('disabled', false).html('<span class="dashicons dashicons-plus-alt" style="font-size:14px;width:14px;height:14px;"></span> Create Backup');
            showSoftUIToast('Network error.', '✗');
        });
    });

    // Refresh backup list
    $('#base47-he-refresh-backups').on('click', function() {
        selectedBackup = null;
        $('#base47-he-restore-selected, #base47-he-download-selected').prop('disabled', true);
        $('#base47-he-backup-preview').hide();
        $('#base47-he-backup-list').html('<p style="text-align:center;color:var(--b47-text-secondary);padding:20px;margin:0;">Loading backups...</p>');
        
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_list_backups',
            nonce: BASE47_HE.nonce,
            file: $file.val(),
            set: getActiveSet()
        }, function(resp) {
            if (resp.success && resp.data && resp.data.length > 0) {
                var html = '';
                resp.data.forEach(function(backup, index) {
                    var badge = index === 0 ? '<span style="background:var(--b47-primary);color:white;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:600;">Latest</span>' : '';
                    var size = (backup.size / 1024).toFixed(1) + ' KB';
                    html += '<div class="base47-he-backup-item" data-backup="' + backup.filename + '" style="padding:12px 16px;border:2px solid var(--b47-border,#E5E7EB);border-radius:8px;margin-bottom:8px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;transition:all 0.15s;">' +
                        '<div><div style="font-weight:600;font-size:13px;color:var(--b47-text);">' + backup.display_date + '</div>' +
                        '<div style="font-size:12px;color:var(--b47-text-secondary);">' + size + '</div></div>' +
                        badge + '</div>';
                });
                $('#base47-he-backup-list').html(html);
            } else {
                $('#base47-he-backup-list').html('<p style="text-align:center;color:var(--b47-text-secondary);padding:30px 20px;margin:0;font-style:italic;">No backups yet. Save a file to create the first backup automatically.</p>');
            }
        }).fail(function() {
            $('#base47-he-backup-list').html('<p style="text-align:center;color:#d63638;padding:20px;margin:0;">Failed to load backups.</p>');
        });
    });

    /* ==========================
       PREVIEW SIZE SWITCHER
    ========================== */
    $(document).on('click', '.preview-size-btn', function () {
        $('.preview-size-btn').removeClass('active');
        $(this).addClass('active');

        const size = $(this).data('size');
        
        const $previewWrap = $('.base47-he-preview-wrap');
        
        if (!$previewWrap.length) {
            // Fallback: try to resize iframe directly
            const $iframe = $('#base47-he-preview');
            if ($iframe.length) {
                if (size === '100%') {
                    $iframe.css({
                        width: '100%',
                        maxWidth: '100%',
                        margin: '0'
                    });
                } else {
                    $iframe.css({
                        width: size + 'px',
                        maxWidth: size + 'px',
                        margin: '0 auto'
                    });
                }
            }
            return;
        }
        
        if (size === '100%') {
            $previewWrap.css({
                width: '100%',
                maxWidth: '100%',
                margin: '0'
            });
        } else {
            $previewWrap.css({
                width: size + 'px',
                maxWidth: size + 'px',
                margin: '0 auto'
            });
        }
    });

    /* ==========================
       OPEN PREVIEW IN NEW TAB
    ========================== */
    $('#base47-he-open-preview').on('click', function (e) {
        e.preventDefault();
        
        const $iframe = $('#base47-he-preview');
        const src = $iframe.attr('src');
        
        if (src && src !== '' && src !== 'about:blank') {
            window.open(src, '_blank');
        } else {
            // Fallback: construct preview URL from current file/set
            const file = $('#base47-he-current-file').val();
            const set = $('#base47-he-current-set').val();
            
            if (file && set) {
                const previewUrl = BASE47_HE.ajax_url.replace('admin-ajax.php', '') + 
                    'admin-ajax.php?action=base47_he_preview&file=' + 
                    encodeURIComponent(file) + '&set=' + encodeURIComponent(set) +
                    '&_wpnonce=' + BASE47_HE.nonce;
                window.open(previewUrl, '_blank');
            } else {
                showSoftUIToast('Please select a file first', '⚠');
            }
        }
    });

    /* ==========================
       DRAG RESIZER (Grid-based)
    ========================== */
    const $resizer = $('#base47-he-resizer');
    const $layout  = $('.b47-editor-layout');

    if ($resizer.length && $layout.length) {
        let dragging = false;

        $resizer.on('mousedown', function (e) {
            e.preventDefault();
            dragging = true;
            $('body').addClass('base47-he-dragging');
            $resizer.addClass('dragging');
        });

        $(document).on('mousemove', function (e) {
            if (!dragging) return;

            const layoutRect = $layout[0].getBoundingClientRect();
            const totalW     = layoutRect.width;
            const mouseX     = e.clientX - layoutRect.left;

            // Clamp: min 300px editor, min 250px preview
            const editorW  = Math.max(300, Math.min(mouseX, totalW - 256));
            const previewW = totalW - editorW - 6;

            $layout.css('grid-template-columns', editorW + 'px 6px ' + previewW + 'px');
        });

        $(document).on('mouseup', function () {
            if (dragging) {
                dragging = false;
                $('body').removeClass('base47-he-dragging');
                $resizer.removeClass('dragging');
            }
        });
    }

    /* =======================================================
       LAZY PREVIEW � Shortcodes Page
       (button: .base47-load-preview-btn)
    ======================================================= */
    $(document).on('click', '.base47-load-preview-btn', function (e) {
        e.preventDefault();

        const btn    = $(this);
        const file   = btn.data('file');
        const set    = btn.data('set');
        
        // Support both old and new card structures
        const card   = btn.closest('.base47-he-template-box, .base47-sc-card');
        const iframe = card.find('.base47-he-template-iframe').get(0);

        if (!file || !set || !iframe) {
            return;
        }

        const originalText = btn.html();
        btn.html('<span class="dashicons dashicons-update"></span> Loading...').prop('disabled', true);
        
        // Add loading state to card (Soft UI)
        card.addClass('is-loading');
        
        // Hide empty state (Soft UI)
        card.find('.base47-sc-preview-empty').hide();

        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_lazy_preview',
            nonce:  BASE47_HE.nonce,
            file:   file,
            set:    set
        }, function (res) {

            btn.html(originalText).prop('disabled', false);
            card.removeClass('is-loading');

            if (res && res.success && res.data && res.data.html) {
                iframe.srcdoc = res.data.html;
                $(iframe).show(); // Show iframe (Soft UI)
            } else {
                iframe.srcdoc = '<div style="padding:20px;color:#c00;">Preview error.</div>';
                $(iframe).show();
            }
        }).fail(function () {
            btn.html(originalText).prop('disabled', false);
            card.removeClass('is-loading');
            if (iframe) {
                iframe.srcdoc = '<div style="padding:20px;color:#c00;">Network error.</div>';
                $(iframe).show();
            }
        });
    });

    /* ==========================
       UNINSTALL THEME (Theme Manager)
    ========================== */
    $(document).on('click', '.base47-tm-uninstall-btn', function (e) {
        e.preventDefault();

        const $btn  = $(this);
        const slug  = $btn.data('theme');
        const $card = $btn.closest('.base47-tm-card');

        if (!slug) {
            return;
        }

        if (!confirm('Are you sure you want to uninstall this theme? This will delete its folder from the server.')) {
            return;
        }

        $btn.prop('disabled', true).text('Uninstalling�');

        $.post(
            BASE47_HE.ajax_url,
            {
                action: 'base47_he_uninstall_theme',
                theme:  slug,
                nonce: BASE47_HE.nonce
            }
        )
        .done(function (resp) {
            if (resp && resp.success) {
                $card.slideUp(200, function () {
                    $(this).remove();
                });
            } else {
                showSoftUIToast((resp && resp.data && resp.data.message) ? resp.data.message : 'Error uninstalling theme.', '✗');
                $btn.prop('disabled', false).text('Uninstall');
            }
        })
        .fail(function () {
            showSoftUIToast('Ajax error while uninstalling theme.', '✗');
            $btn.prop('disabled', false).text('Uninstall');
        });
    });

    /* ==========================
       DEFAULT THEME SELECTOR
    ========================== */
    $('#base47_default_theme').on('change', function () {

        const selected = $(this).val();

        $.ajax({
            url: BASE47_HE.ajax_url,
            method:   'POST',
            data: {
                action:   'base47_set_default_theme',
                theme:    selected,
                nonce: BASE47_HE.nonce
            },
            success: function (response) {
                if (response && response.success) {
                    // Theme updated successfully
                } else {
                    showSoftUIToast('Could not save default theme.', '✗');
                }
            }
        });
    });

    /* ==========================
       MONACO EDITOR INTEGRATION
    ========================== */
    
    let monacoEditor = null;
    let currentEditorMode = BASE47_HE.editor_mode || 'classic';  // Default: classic
    let lastSavedContent = '';
    let hasUnsavedChanges = false;
    
    // Normalize has_pro to boolean (handles "1", 1, true, "true")
    const hasPro = (BASE47_HE.has_pro === true || BASE47_HE.has_pro === 1 || BASE47_HE.has_pro === '1' || BASE47_HE.has_pro === 'true');
    
    // Dynamic Monaco Loader Function
    function loadMonacoLoader(callback) {
        if (window.require && window.require.config) {
            callback();
            return;
        }
        
        if (!BASE47_HE.monaco_path) {
            showSoftUIToast('Monaco Editor not available. PRO plugin required.', '🔒');
            return;
        }
        
        var existing = document.querySelector('script[data-base47-monaco-loader="1"]');
        if (existing) {
            existing.addEventListener('load', callback);
            return;
        }
        
        var loaderUrl = BASE47_HE.monaco_path.replace(/\/$/, '') + '/loader.js';
        
        fetch(loaderUrl, { credentials: 'same-origin' })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Monaco loader HTTP ' + response.status);
                }
                
                var script = document.createElement('script');
                script.src = loaderUrl;
                script.async = false;
                script.dataset.base47MonacoLoader = '1';
                
                script.onload = function() {
                    setTimeout(function() {
                        if (window.require && window.require.config) {
                            callback();
                        } else {
                            if (window.console && window.console.error) {
                                console.error('Monaco loader executed but window.require.config is missing.');
                            }
                            showSoftUIToast('Monaco loader failed to initialize.', '✗');
                        }
                    }, 50);
                };
                
                script.onerror = function(e) {
                    if (window.console && window.console.error) {
                        console.error('Failed to load Monaco loader:', loaderUrl, e);
                    }
                    showSoftUIToast('Failed to load Monaco loader.', '✗');
                };
                
                document.head.appendChild(script);
            })
            .catch(function(error) {
                if (window.console && window.console.error) {
                    console.error('Monaco loader fetch failed:', error);
                }
                showSoftUIToast('Monaco loader is not accessible: ' + error.message, '✗');
            });
    }
    
    // Initialize Monaco Editor if on editor page AND Pro is active
    if ($('#base47-monaco-editor').length > 0 && hasPro) {
        // Pro is active - set initial button states FIRST
        $('#base47-he-mode-classic').addClass('active');
        $('#base47-he-mode-advanced').removeClass('active');
        
        // Hide Monaco initially (Classic is default)
        $('#base47-monaco-editor').hide();
        $('#base47-he-code').show();
        
        // DO NOT load Monaco on page load - it will be loaded when user clicks Monaco button
        // This prevents AMD conflicts with WordPress's module system
        // Monaco loader.js defines global require/define which breaks React, wp.data, etc.
    } else if ($('#base47-monaco-editor').length > 0 && !hasPro) {
        // Free version - hide Monaco, show Classic
        $('#base47-monaco-editor').hide();
        $('#base47-he-code').show();
        $('#base47-he-mode-classic-free').addClass('active');
    }
    
    function initializeMonacoEditor() {
        loadMonacoLoader(function() {
            window.require.config({
                paths: {
                    vs: BASE47_HE.monaco_path.replace(/\/$/, '')
                }
            });
            
            window.require(['vs/editor/editor.main'], function() {
                // Get initial content from textarea
                var initialContent = $('#base47-he-code').val() || '';
                lastSavedContent = initialContent;
                
                // Get theme from settings
                var theme = (BASE47_HE.editor_theme === 'dark') ? 'vs-dark' : 'vs';
                
                // Show Monaco container for initialization
                $('#base47-monaco-editor').show();
                
                // Create Monaco Editor
                monacoEditor = monaco.editor.create(document.getElementById('base47-monaco-editor'), {
                    value: initialContent,
                    language: 'html',
                    theme: theme,
                    automaticLayout: true,
                    lineNumbers: 'on',
                    wordWrap: 'on',
                    minimap: { enabled: true },
                    scrollBeyondLastLine: false,
                    fontSize: 14,
                    tabSize: 2,
                    insertSpaces: true,
                    renderWhitespace: 'selection',
                    bracketPairColorization: { enabled: true },
                    suggest: {
                        showKeywords: true,
                        showSnippets: true
                    }
                });
                
                // Track changes for unsaved detection
                monacoEditor.onDidChangeModelContent(function() {
                    var currentContent = monacoEditor.getValue();
                    hasUnsavedChanges = (currentContent !== lastSavedContent);
                    updateSaveButtonState();
                });
                
                // Add keyboard shortcuts
                monacoEditor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function() {
                    $('#base47-he-save').click();
                });
                
                // Switch to Monaco view
                $('#base47-he-code').hide();
                $('#base47-monaco-editor').show();
                $('#base47-he-mode-advanced').addClass('active');
                $('#base47-he-mode-classic').removeClass('active');
                
                currentEditorMode = 'advanced';
                localStorage.setItem('base47_editor_mode', 'advanced');
                
                // Re-enable buttons
                $('#base47-he-mode-classic, #base47-he-mode-advanced').prop('disabled', false);
                
                showSoftUIToast('Monaco Editor loaded!', '✓');
                
            }, function(err) {
                if (window.console && window.console.error) {
                    console.error('Failed to load Monaco editor.main:', err);
                }
                showSoftUIToast('Failed to load Monaco editor.', '✗');
                $('#base47-monaco-editor').hide();
                $('#base47-he-code').show();
                $('#base47-he-mode-classic').addClass('active');
                $('#base47-he-mode-advanced').removeClass('active');
                $('#base47-he-mode-classic, #base47-he-mode-advanced').prop('disabled', false);
            });
        });
    }
    
    // Editor Mode Switching (use event delegation for dynamically created buttons)
    $(document).on('click', '#base47-he-mode-classic, #base47-he-mode-advanced', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        const mode = $btn.attr('id') === 'base47-he-mode-advanced' ? 'advanced' : 'classic';
        
        // Ensure Pro is active before allowing Monaco switch
        if (mode === 'advanced' && !hasPro) {
            showSoftUIToast('Monaco Editor is a Pro feature. Please upgrade to unlock advanced editing.', '🔒');
            return;
        }
        
        // Prevent rapid clicking
        if ($btn.prop('disabled')) {
            return;
        }
        
        switchEditorMode(mode);
    });
    
    function switchEditorMode(mode) {
        if (mode === currentEditorMode) return;
        
        // Disable buttons during switch to prevent rapid clicking
        $('#base47-he-mode-classic, #base47-he-mode-advanced').prop('disabled', true);
        
        var currentContent = getCurrentEditorContent();
        
        if (mode === 'advanced') {
            // If Monaco editor already initialized, just switch to it
            if (monacoEditor) {
                $('#base47-he-code').hide();
                $('#base47-monaco-editor').show();
                
                monacoEditor.setValue(currentContent);
                monacoEditor.layout();
                
                $('#base47-he-mode-advanced').addClass('active');
                $('#base47-he-mode-classic').removeClass('active');
                
                currentEditorMode = mode;
                localStorage.setItem('base47_editor_mode', mode);
                setTimeout(function() {
                    $('#base47-he-mode-classic, #base47-he-mode-advanced').prop('disabled', false);
                }, 100);
                return;
            }
            
            // Monaco not yet loaded - initialize it (handles loading + switching)
            showSoftUIToast('Loading Monaco Editor...', '⏳');
            initializeMonacoEditor();
            return;
            
        } else {
            // Switch to Classic
            $('#base47-monaco-editor').hide();
            $('#base47-he-code').show().val(currentContent);
            
            // Apply dark theme to classic editor if needed
            if (BASE47_HE.editor_theme === 'dark') {
                $('#base47-he-code').css({
                    'background': '#1e1e1e',
                    'color': '#d4d4d4',
                    'border-color': '#3e3e3e'
                });
            }
            
            $('#base47-he-mode-classic').addClass('active');
            $('#base47-he-mode-advanced').removeClass('active');
        }
        
        currentEditorMode = mode;
        
        // Save mode preference
        localStorage.setItem('base47_editor_mode', mode);
        
        // Re-enable buttons after switch
        setTimeout(function() {
            $('#base47-he-mode-classic, #base47-he-mode-advanced').prop('disabled', false);
        }, 100);
    }
    
    // Get content from current active editor
    function getCurrentEditorContent() {
        if (currentEditorMode === 'advanced' && monacoEditor) {
            return monacoEditor.getValue();
        } else {
            return $('#base47-he-code').val();
        }
    }
    
    // Update save button state based on changes
    function updateSaveButtonState() {
        const saveBtn = $('#base47-he-save');
        if (hasUnsavedChanges) {
            saveBtn.text('Save *').addClass('base47-he-unsaved');
        } else {
            saveBtn.text('Save').removeClass('base47-he-unsaved');
        }
    }

    /**
     * Show a transient or persistent safety indicator in the editor UI.
     * type: 'backup' | 'warnings' | 'restore' | 'protected'
     */
    function base47ShowSafetyIndicator(type, message) {
        const containerId = '#base47-he-safety-indicators';
        if (!$(containerId).length) return;

        const id = 'base47-indicator-' + type;
        $('#' + id).remove();

        const isTransient = (type === 'backup');
        const cls = isTransient ? 'base47-safety-indicator base47-indicator-transient' : 'base47-safety-indicator';
        const icon = type === 'warnings' ? '⚠️' : type === 'backup' ? '✅' : type === 'restore' ? '↩️' : '🔒';

        const $el = $('<div>')
            .attr('id', id)
            .addClass(cls)
            .html(icon + ' <span>' + message + '</span>');

        $(containerId).append($el);

        if (isTransient) {
            setTimeout(function() { $el.fadeOut(400, function() { $(this).remove(); }); }, 3000);
        }
    }
    
    // Load saved editor mode preference (override settings default)
    const savedMode = localStorage.getItem('base47_editor_mode');
    if (savedMode && monacoEditor) {
        switchEditorMode(savedMode);
    }
    
    // Warn about unsaved changes
    $(window).on('beforeunload', function() {
        if (hasUnsavedChanges) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    // Override existing save handler to work with both editors
    $('#base47-he-save').off('click').on('click', function(e) {
        e.preventDefault();
        
        const content = getCurrentEditorContent();
        const btn = $(this);
        const originalText = btn.text();
        
        console.log('Save request sent'); // Temporary debug
        
        btn.prop('disabled', true).text('Saving...');
        
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_save_template',
            nonce: BASE47_HE.nonce,
            file: $('#base47-he-current-file').val(),
            set: $('#base47-he-current-set').val(),
            content: content
        })
        .done(function(resp) {
            console.log('Save response:', resp); // Temporary debug
            
            // wp_send_json_error() comes here as resp.success = false
            if (resp && resp.success) {
                // SUCCESS
                lastSavedContent = content;
                hasUnsavedChanges = false;
                updateSaveButtonState();

                // Show "Auto backup created" indicator
                base47ShowSafetyIndicator('backup', 'Auto backup created');

                // Show validation warnings if any
                const data = resp.data || {};
                if (data.warning_count && data.warning_count > 0) {
                    base47ShowSafetyIndicator('warnings', 'Validation warnings found (' + data.warning_count + ')');
                }

                // Update preview with live preview (not reload)
                $.post(BASE47_HE.ajax_url, {
                    action: 'base47_he_live_preview',
                    nonce: BASE47_HE.nonce,
                    file: $('#base47-he-current-file').val(),
                    set: $('#base47-he-current-set').val(),
                    content: content
                }, function(previewResp) {
                    if (previewResp.success && previewResp.data && previewResp.data.html) {
                        const iframe = $('#base47-he-preview')[0];
                        if (iframe && iframe.contentWindow) {
                            iframe.contentWindow.document.open();
                            iframe.contentWindow.document.write(previewResp.data.html);
                            iframe.contentWindow.document.close();
                        }
                    }
                });

                // Show success feedback
                btn.text('Saved!').css('background', '#46b450');
                setTimeout(function() {
                    btn.css('background', '').text('Save');
                }, 1500);
                
            } else {
                // ERROR (wp_send_json_error comes here)
                const errorMsg = (resp && resp.data && resp.data.message) 
                    ? resp.data.message 
                    : 'Unknown server error';
                
                // Log details if available
                if (resp && resp.data && resp.data.details) {
                    console.error('Save error details:', resp.data.details);
                }
                
                showSoftUIToast('Save failed: ' + errorMsg, '✗');
            }
        })
        .fail(function(xhr, status, error) {
            // Network/parsing errors only
            console.error('AJAX network error:', xhr.responseText, status, error);
            
            let errorMsg = 'Network error';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response && response.data && response.data.message) {
                    errorMsg = response.data.message;
                }
            } catch (e) {
                errorMsg = 'Server response error (Status: ' + (xhr.status || 'Unknown') + ')';
            }
            
            showSoftUIToast('Save failed: ' + errorMsg, '✗');
        })
        .always(function() {
            console.log('Save always executed'); // Temporary debug
            
            // ALWAYS re-enable button - this is critical
            btn.prop('disabled', false);
            
            // Reset text if still showing "Saving..."
            if (btn.text() === 'Saving...') {
                btn.text(originalText);
            }
        });
    });
    
    /* ==========================
       DISMISS PRO UPGRADE NOTICE (Dashboard)
    ========================== */
    $(document).on('click', '#pro-upgrade-notice .notice-dismiss', function() {
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_dismiss_pro_notice',
            nonce: BASE47_HE.nonce
        });
    });
    
    /* ==========================
       DISMISS MONACO NOTICE (Editor)
    ========================== */
    $(document).on('click', '#base47-dismiss-monaco-notice, #base47-close-monaco-notice', function() {
        $('#base47-monaco-notice').fadeOut(300);
        
        // Save dismissal to user meta
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_dismiss_monaco_notice',
            nonce: BASE47_HE.nonce
        });
    });
    
    /* ==========================
       DISMISS THEME MANAGER NOTICE
    ========================== */
    $(document).on('click', '#theme-manager-free-notice .notice-dismiss, #theme-notice-dismiss-btn', function() {
        $('#theme-manager-free-notice').fadeOut(300);
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_dismiss_theme_notice',
            nonce: BASE47_HE.nonce
        });
    });

    /* ==========================
       DISMISS WIDGETS PRO NOTICE
    ========================== */
    $(document).on('click', '#dismiss-widgets-notice', function() {
        $('#widgets-pro-notice').fadeOut(300);
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_dismiss_widgets_notice',
            nonce: BASE47_HE.nonce
        });
    });

    /* ==========================
       LOG FILTERING (Logs Page)
    ========================== */
    $(document).on('click', '.b47-log-filter-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        
        // Check if button is disabled (Free version)
        if ($btn.prop('disabled')) {
            showSoftUIToast('Log filtering is a Pro feature. Upgrade to unlock advanced log management.', '🔒');
            return;
        }
        
        const level = $btn.data('level');
        
        // Update button states
        $('.b47-log-filter-btn').removeClass('active').css({
            'background': 'white',
            'color': 'var(--b47-text)'
        });
        $btn.addClass('active').css({
            'background': 'var(--b47-primary)',
            'color': 'white'
        });
        
        // Filter log rows
        if (level === 'all') {
            $('.b47-log-row[data-level]').show();
        } else {
            $('.b47-log-row[data-level]').hide();
            $('.b47-log-row[data-level="' + level + '"]').show();
        }
        
        // Update visible count
        const visibleCount = $('.b47-log-row[data-level]:visible').length;
        const totalCount = $('.b47-log-row[data-level]').length;
        
        // Show filter status (optional)
        if (level !== 'all') {
            if (!$('#b47-log-filter-status').length) {
                $('.b47-log-row').first().before('<div id="b47-log-filter-status" style="padding:12px 20px;background:#F0F9FF;border-bottom:1px solid var(--b47-border);font-size:13px;color:var(--b47-text-secondary);"><span class="dashicons dashicons-filter" style="font-size:14px;width:14px;height:14px;"></span> Showing ' + visibleCount + ' of ' + totalCount + ' entries (filtered by ' + level.toUpperCase() + ')</div>');
            } else {
                $('#b47-log-filter-status').html('<span class="dashicons dashicons-filter" style="font-size:14px;width:14px;height:14px;"></span> Showing ' + visibleCount + ' of ' + totalCount + ' entries (filtered by ' + level.toUpperCase() + ')');
            }
        } else {
            $('#b47-log-filter-status').remove();
        }
    });

});
