/**
 * JS Scripts for creation of dialogs ( floating windows )
 */
var processes = {};

function redmond_window( objid , title , content , filecommands , canResize , draggable , icon ) {
	if( typeof( filecommands ) !== 'object' ) {
		filecommands = {
			'close': {
				title: redmond_terms.close,
				onclick: 'redmond_close_this(this)',
			}
		};
	}
	if( typeof( draggable ) == 'undefined' ) {
		draggable = true;
	}
	if( typeof( canResize ) == 'undefined' ) {
		canResize = true;
	}
	if( typeof( content ) == 'undefined' ) {
		content = '';
	}
	if( typeof( icon ) == 'undefined' ) {
		icon = redmond_terms.windowIcon;
	}
        var workspaceTarget = jQuery('#desktop-window-area');
        if ( ! workspaceTarget.length ) {
                workspaceTarget = jQuery('body');
        }
        if( typeof( processes[objid] ) == 'undefined' ) {
                jQuery('#modal-holder').append('<div id="' + objid + '"><div class="file-bar"><ul></ul></div>' + content + '</div>');
                processes[objid] = jQuery("#" + objid);
                processes[objid].dialog({
                        autoOpen: true,
                        closeOnEscape: false,
                        dialogClass: "redmond-dialog-window",
                        appendTo: workspaceTarget,
                        draggable: draggable,
                        resizable: canResize,
                        position: {
                                my: 'center top',
                                at: 'center top+40',
                                of: workspaceTarget,
                                collision: 'fit'
                        },
                        close: function( event, ui ) {
                                processes[objid].parent().addClass('redmond-dialog-hidden');
                                jQuery(window).trigger('checkOpenWindows');
                        },
                        open: function( event, ui ) {
                                processes[objid].find('div.file-bar>ul').append(redmond_filecommands_to_html(filecommands));
                                processes[objid].parent().find('.ui-dialog-titlebar>span').css({
                                        'background-image': 'url(' + icon + ')',
                                        'background-repeat': 'no-repeat',
                                        'background-size': 'contain',
                                });
                                processes[objid].dialog('option', 'closeText', '');
                                processes[objid].parent().removeClass('redmond-dialog-hidden');

                                var closeButton = processes[objid].parent().find('button.ui-dialog-titlebar-close');
                                redmond_style_close_button(closeButton);
                                closeButton
                                        .off('click.redmondClose')
                                        .on('click.redmondClose', function(){
                                                redmond_close_this(this);
                                        });
                                processes[objid].find('div.file-bar').zIndex(processes[objid].zIndex());
                                jQuery(window).trigger('checkOpenWindows');
                                processes[objid].parent()
                                        .off('click.redmondFocus')
                                        .on('click.redmondFocus', function() {
                                                find_window_on_top();
                                        });
                                processes[objid].find('iframe')
                                        .off('click.redmondIframe')
                                        .on('click.redmondIframe', function() {
                                                processes[objid].dialog('moveToTop');
                                                find_window_on_top();
                                        });
                                processes[objid].find('span.dialog-close-button').css({
                                        left: function() {
                                                var fullwidth = parseInt( processes[objid].find('span.dialog-close-button').parent().css('width') , 10 );
                                                return ( fullwidth / 2 ) - ( parseInt( processes[objid].find('span.dialog-close-button').css('width') , 10 ) / 2 ) - 10;
                                        }
                                });

                                redmond_adjust_dialog_sizes();
                        },
                        focus: function( event, ui ) {
                                processes[objid].find('div.file-bar').zIndex(processes[objid].zIndex());
                                find_window_on_top();
                        },
			title: title,
			closeText: '',
			width: 'auto',
                        height: 'auto',
                });
                processes[objid].parent().attr('data-redmond-limit-height', canResize ? 'true' : 'false');
        }
        else {
                processes[objid].html('<div class="file-bar"><ul></ul></div>' + content + '</div>');
                processes[objid].find('div.file-bar>ul').append(redmond_filecommands_to_html(filecommands));
                processes[objid].dialog('option', {
                        appendTo: workspaceTarget,
                        closeOnEscape: false,
                        draggable: draggable,
                        resizable: canResize,
                        position: {
                                my: 'center top',
                                at: 'center top+40',
                                of: workspaceTarget,
                                collision: 'fit'
                        },
                        closeText: ''
                });

                processes[objid].parent().attr('data-redmond-limit-height', canResize ? 'true' : 'false');

                processes[objid].parent().removeClass('redmond-dialog-hidden');
                processes[objid].parent().find('.ui-dialog-titlebar>span').css({
                        'background-image': 'url(' + icon + ')',
                        'background-repeat': 'no-repeat',
                        'background-size': 'contain',
                });
                var closeButton = processes[objid].parent().find('button.ui-dialog-titlebar-close');
                redmond_style_close_button(closeButton);
                closeButton
                        .off('click.redmondClose')
                        .on('click.redmondClose', function(){
                                redmond_close_this(this);
                        });
                processes[objid].find('div.file-bar').zIndex(processes[objid].zIndex());
                jQuery(window).trigger('checkOpenWindows');
                processes[objid].parent()
                        .off('click.redmondFocus')
                        .on('click.redmondFocus', function() {
                                find_window_on_top();
                        });
                processes[objid].find('span.dialog-close-button').css({
                        left: function() {
                                var fullwidth = parseInt( processes[objid].find('span.dialog-close-button').parent().css('width') , 10 );
                                return ( fullwidth / 2 ) - ( parseInt( processes[objid].find('span.dialog-close-button').css('width') , 10 ) / 2 ) - 10;
                        }
                });
                processes[objid].dialog('open');
                processes[objid].dialog('moveToTop');
                find_window_on_top();
        }
        redmond_adjust_dialog_sizes();
}

function redmond_filecommands_to_html( filecommands ) {
	html = '';
	html += '<li>' + "\r\n";
	html += '	' + redmond_terms.file;
	html += '	<ul class="file-sub-menu">' + "\r\n";
	for( var cmd in filecommands ) {
	html += '		<li data-file-command="' + cmd + '" onclick="' + filecommands[cmd].onclick + '">' + "\r\n";
	html += '			' + filecommands[cmd].title + "\r\n";
	html += '		</li>' + "\r\n";
	}
	html += '	</ul>' + "\r\n";
	html += '</li>' + "\r\n";
	return html;
}

function redmond_style_close_button( closeButton ) {
        if ( ! closeButton || ! closeButton.length ) {
                return;
        }

        var closeLabel = ( window.redmond_terms && redmond_terms.close ) ? redmond_terms.close : 'Close';

        closeButton
                .removeClass('ui-button-icon-only')
                .addClass('redmond-close-button')
                .attr('type', 'button')
                .attr('title', closeLabel)
                .attr('aria-label', closeLabel);

        closeButton.find('span.ui-icon').remove();
        closeButton.find('span.ui-button-icon-space').remove();
        closeButton
                .contents()
                .filter(function(){
                        return this.nodeType === 3;
                })
                .remove();
        closeButton.find('span.redmond-close-text').remove();
        closeButton.append('<span class="redmond-close-text" aria-hidden="true">&times;</span>');
}

function redmond_adjust_dialog_sizes() {
        var workspace = jQuery('#desktop-window-area');
        var viewportHeight = workspace.length ? workspace.innerHeight() : jQuery(window).height();
        if ( ! viewportHeight || viewportHeight <= 0 ) {
                return;
        }

        var targetWindowHeight = Math.max(Math.floor(viewportHeight * 0.5), 1);

        var positionTarget = workspace.length ? workspace : jQuery(window);

        jQuery('div.redmond-dialog-window').each(function() {
                var dialogWrapper = jQuery(this);
                var shouldCapHeight = dialogWrapper.attr('data-redmond-limit-height') !== 'false';
                var titleBar = dialogWrapper.children('.ui-dialog-titlebar');
                var contentArea = dialogWrapper.children('.ui-dialog-content');
                var titleBarHeight = titleBar.outerHeight(true) || 0;
                var contentHeight = Math.max(targetWindowHeight - titleBarHeight, 0);

                dialogWrapper.css({
                        'height': '',
                        'min-height': '',
                        'max-height': shouldCapHeight && targetWindowHeight > 0 ? targetWindowHeight : '',
                        'overflow-y': shouldCapHeight ? 'hidden' : 'visible',
                        'overflow-x': 'visible',
                        'padding-bottom': ''
                });

                var contentStyles = {
                        'overflow-y': 'scroll',
                        'overflow-x': 'auto',
                        'min-height': ''
                };

                if ( contentHeight > 0 ) {
                        contentStyles.height = '';
                        contentStyles['max-height'] = shouldCapHeight ? contentHeight : '';
                } else {
                        contentStyles.height = '';
                        contentStyles['max-height'] = '';
                }

                contentArea.css(contentStyles);

                var dialogInstance = contentArea.data('ui-dialog') || contentArea.data('dialog');

                if ( dialogInstance ) {
                        var dialogOptions = {
                                position: {
                                        my: 'center top',
                                        at: 'center top+40',
                                        of: positionTarget,
                                        collision: 'fit'
                                },
                                height: 'auto'
                        };

                        if ( shouldCapHeight && targetWindowHeight > 0 ) {
                                dialogOptions.maxHeight = targetWindowHeight;
                        } else {
                                dialogOptions.maxHeight = false;
                        }

                        contentArea.dialog('option', dialogOptions);
                }
        });
}

function redmond_close_this( obj ) {
        var $obj = jQuery(obj);
        var dialogContent = $obj.closest('.ui-dialog-content');

        if ( ! dialogContent.length ) {
                var dialogWrapper = $obj.closest('.ui-dialog');

                if ( dialogWrapper.length ) {
                        dialogContent = dialogWrapper.find('.ui-dialog-content').first();
                }
        }

        if ( dialogContent.length && typeof dialogContent.dialog === 'function' ) {
                dialogContent.dialog('close');
        }
}
