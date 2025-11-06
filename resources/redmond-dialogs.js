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
	if( typeof( processes[objid] ) == 'undefined' ) {
		jQuery('#modal-holder').append('<div id="' + objid + '"><div class="file-bar"><ul></ul></div>' + content + '</div>');
                processes[objid] = jQuery("#" + objid);
                processes[objid].dialog({
                        autoOpen: true,
                        closeOnEscape: false,
                        dialogClass: "redmond-dialog-window",
                        draggable: draggable,
                        resizable: canResize,
                        dragStop: function() {
                                redmond_enforce_window_bounds( objid );
                        },
                        resizeStop: function() {
                                redmond_enforce_window_bounds( objid );
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
                                processes[objid].parent().find('button.ui-dialog-titlebar-close').off('click.redmond').on('click.redmond',function(e){
                                        e.preventDefault();
                                        processes[objid].dialog('close');
                                });
				redmond_enforce_window_bounds( objid );
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
		redmond_enforce_window_bounds( objid );
		find_window_on_top();
	}
	jQuery("div.redmond-dialog-window").each(function() {
		var obj = this;
		jQuery(this).css({
			'padding-bottom': function() {
				if ( jQuery(obj).height() > ( jQuery(window).height() * 0.9 ) ) {
					return 20;
				}
				else {
					return 0;
				}
			},
			height: function() {
				if ( jQuery(obj).height() > ( jQuery(window).height() * 0.9 ) ) {
					return ( jQuery(window).height() * 0.9 );
				}
				else {
					return 'auto';
				}
			},
			'overflow': 'hidden',
		});
		var processId = jQuery(this).attr('id');
		if ( processId ) {
			redmond_enforce_window_bounds( processId );
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

function redmond_close_this( obj ) {
        var dialogContent = jQuery(obj).closest('.ui-dialog');
        if ( dialogContent.length ) {
                dialogContent = dialogContent.find('.ui-dialog-content');
        }
        if ( ! dialogContent || ! dialogContent.length ) {
                dialogContent = jQuery(obj).closest('.ui-dialog-content');
        }
        if ( dialogContent && dialogContent.length ) {
                dialogContent.dialog('close');
        }
}

function redmond_enforce_window_bounds( processId ) {
        if ( typeof processes[processId] === 'undefined' ) {
                return;
        }
        var dialog = processes[processId];
        var container = dialog.parent('.ui-dialog');
        if ( container.length === 0 ) {
                return;
        }
        var viewportWidth = jQuery(window).width();
        var viewportHeight = jQuery(window).height();
        var taskbar = jQuery('#taskbar-outer');
        var taskbarHeight = taskbar.length ? taskbar.outerHeight(true) : 0;
        var usableHeight = Math.max( viewportHeight - taskbarHeight, 0 );
        var maxWidth = Math.floor( viewportWidth * 0.9 );
        var maxHeight = Math.floor( usableHeight * 0.9 );
        if ( maxHeight <= 0 ) {
                maxHeight = Math.floor( viewportHeight * 0.9 );
        }
        dialog.dialog('option', 'maxWidth', maxWidth );
        dialog.dialog('option', 'maxHeight', maxHeight );
        if ( container.outerWidth() > maxWidth ) {
                dialog.dialog('option', 'width', maxWidth );
        }
        if ( container.outerHeight() > maxHeight ) {
                dialog.dialog('option', 'height', maxHeight );
        }
        var containerWidth = container.outerWidth();
        var containerHeight = container.outerHeight();
        var maxLeft = Math.max( viewportWidth - containerWidth, 0 );
        var maxTop = Math.max( usableHeight - containerHeight, 0 );
        var currentTop = parseInt( container.css('top'), 10 );
        if ( isNaN( currentTop ) ) {
                currentTop = container.position().top || 0;
        }
        var currentLeft = parseInt( container.css('left'), 10 );
        if ( isNaN( currentLeft ) ) {
                currentLeft = container.position().left || 0;
        }
        var boundedTop = Math.min( Math.max( currentTop, 0 ), maxTop );
        var boundedLeft = Math.min( Math.max( currentLeft, 0 ), maxLeft );
        container.css({
                'max-width': maxWidth + 'px',
                'max-height': maxHeight + 'px',
                'overflow': 'visible',
                'top': boundedTop + 'px',
                'left': boundedLeft + 'px'
        });
        var titlebar = container.find('.ui-dialog-titlebar');
        var titleHeight = ( titlebar.length > 0 ) ? titlebar.outerHeight(true) : 0;
        var contentMaxHeight = maxHeight;
        if ( usableHeight > 0 && maxHeight === usableHeight ) {
                contentMaxHeight = usableHeight;
        }
        if ( contentMaxHeight > titleHeight ) {
                dialog.css('max-height', ( contentMaxHeight - titleHeight ) + 'px' );
        }
}
