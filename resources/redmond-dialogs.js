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
                                my: 'center',
                                at: 'center',
                                of: workspaceTarget,
                                collision: 'fit'
                        },
                        close: function( event, ui ) {
                                processes[objid].remove();
                                delete processes[objid];
				jQuery(window).trigger('checkOpenWindows');
			},
			open: function( event, ui ) {
				processes[objid].find('div.file-bar>ul').append(redmond_filecommands_to_html(filecommands));
                                processes[objid].parent().find('.ui-dialog-titlebar>span').css({
                                        'background-image': 'url(' + icon + ')',
                                        'background-repeat': 'no-repeat',
                                        'background-size': 'contain',
                                });
                                var closeButton = processes[objid].parent().find('button.ui-dialog-titlebar-close');
                                redmond_style_close_button(closeButton);
                                closeButton
                                        .off('click.redmondClose')
                                        .on('click.redmondClose', function(e){
                                                e.preventDefault();
                                                e.stopPropagation();
                                                redmond_close_this(this);
                                        });
                                processes[objid].find('div.file-bar').zIndex(processes[objid].zIndex());
                                jQuery(window).trigger('checkOpenWindows');
                                processes[objid].parent().on('click',function() {
                                        find_window_on_top();
                                });
				processes[objid].find('iframe').on('click',function() {
					processes[objid].dialog('moveToTop');
					find_window_on_top();
				});
				processes[objid].find('span.dialog-close-button').css({
					left: function() {
						var fullwidth = parseInt( processes[objid].find('span.dialog-close-button').parent().css('width') , 10 );
						return ( fullwidth / 2 ) - ( parseInt( processes[objid].find('span.dialog-close-button').css('width') , 10 ) / 2 ) - 10;
					}
				});
			},
			focus: function( event, ui ) {
				processes[objid].find('div.file-bar').zIndex(processes[objid].zIndex());
				find_window_on_top();
			},
			title: title,
			closeText: "\u00d7",
			width: 'auto',
			height: 'auto',
		});
	}
	else {
		processes[objid].html('<div class="file-bar"><ul></ul></div>' + content + '</div>');
                processes[objid].find('div.file-bar>ul').append(redmond_filecommands_to_html(filecommands));
                processes[objid].parent().find('.ui-dialog-titlebar>span').css({
                        'background-image': 'url(' + icon + ')',
                        'background-repeat': 'no-repeat',
                        'background-size': 'contain',
                });
                var closeButton = processes[objid].parent().find('button.ui-dialog-titlebar-close');
                redmond_style_close_button(closeButton);
                closeButton
                        .off('click.redmondClose')
                        .on('click.redmondClose', function(e){
                                e.preventDefault();
                                e.stopPropagation();
                                redmond_close_this(this);
                        });
                processes[objid].find('div.file-bar').zIndex(processes[objid].zIndex());
		jQuery(window).trigger('checkOpenWindows');
		processes[objid].parent().on('click',function() {
			find_window_on_top();
		});
		processes[objid].find('span.dialog-close-button').css({
			left: function() {
				var fullwidth = parseInt( processes[objid].find('span.dialog-close-button').parent().css('width') , 10 );
				return ( fullwidth / 2 ) - ( parseInt( processes[objid].find('span.dialog-close-button').css('width') , 10 ) / 2 ) - 10;
			}
		});
                processes[objid].dialog('moveToTop');
                processes[objid].dialog('option', 'appendTo', workspaceTarget);
                processes[objid].dialog('option', 'position', {
                        my: 'center',
                        at: 'center',
                        of: workspaceTarget,
                        collision: 'fit'
                });
                find_window_on_top();
        }
        jQuery("div.redmond-dialog-window").each(function() {
                var dialogWrapper = jQuery(this);
                var workspace = jQuery('#desktop-window-area');
                var viewportHeight = workspace.length ? workspace.innerHeight() : jQuery(window).height();
                var maxWindowHeight = Math.floor(viewportHeight * 0.9);
                var titleBarHeight = dialogWrapper.children('.ui-dialog-titlebar').outerHeight(true) || 0;
                var contentArea = dialogWrapper.children('.ui-dialog-content');
                var maxContentHeight = maxWindowHeight - titleBarHeight;

                if ( maxContentHeight < 120 ) {
                        maxContentHeight = Math.max(viewportHeight - titleBarHeight - 20, 120);
                }

                dialogWrapper.css({
                        'height': '',
                        'padding-bottom': '',
                        'max-height': maxWindowHeight,
                        'overflow-x': 'visible'
                });

                var wrapperNeedsScroll = dialogWrapper.outerHeight() > maxWindowHeight;

                dialogWrapper.css({
                        'padding-bottom': wrapperNeedsScroll ? 20 : '',
                        'overflow-y': wrapperNeedsScroll ? 'auto' : 'visible',
                        'height': wrapperNeedsScroll ? maxWindowHeight : ''
                });

                contentArea.css({
                        'height': '',
                        'max-height': maxContentHeight,
                        'overflow-y': 'auto',
                        'overflow-x': 'auto'
                });

                if ( contentArea.outerHeight() > maxContentHeight ) {
                        contentArea.css('height', maxContentHeight);
                }
        });
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
                .attr('title', closeLabel)
                .attr('aria-label', closeLabel);

        closeButton.find('span.ui-icon').remove();
        closeButton.find('span.redmond-close-text').remove();
        closeButton.append('<span class="redmond-close-text" aria-hidden="true">&times;</span>');
}

function redmond_close_this( obj ) {
        var $obj = jQuery(obj);
        var dialogContent = $obj.closest('.ui-dialog-content');

        if ( ! dialogContent.length ) {
                var dialogWrapper = $obj.closest('.ui-dialog');

                if ( dialogWrapper.length ) {
                        var dialogInstance = dialogWrapper.data('ui-dialog') || dialogWrapper.data('dialog');

                        if ( dialogInstance && typeof dialogInstance.close === 'function' ) {
                                dialogInstance.close();
                                return;
                        }

                        dialogContent = dialogWrapper.find('.ui-dialog-content').first();
                }
        }

        if ( dialogContent.length ) {
                dialogContent.dialog('close');
        }
}
