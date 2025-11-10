var sounds = {
	error: new Audio( redmond_terms.errorSound ),
	open: new Audio( redmond_terms.openSound ),
	login: new Audio( redmond_terms.loginSound ),
};

jQuery(function() {
        startSystemClock();
        handle_start_menu();
        jQuery('a')
                .off('click.redmondGlobal')
                .on('click.redmondGlobal', function( e ) {
                        var $a = jQuery(this);

                        if ( $a.hasClass('redmond-close-window') ) {
                                return;
                        }

                        var id = $a.attr('id');

                        if (
                                typeof id !== 'undefined' &&
                                (
                                        id === 'home-start-menu-link' ||
                                        id === 'new-post-start-menu-link' ||
                                        id === 'control-panel-start-menu-link' ||
                                        id === 'start-menu-bottom-bar-logout' ||
                                        id === 'start-menu-bottom-bar-register' ||
                                        id === 'start-menu-bottom-bar-login' ||
                                        id === 'system-info-start-menu-link' ||
                                        id === 'my-documents-start-menu-link' ||
                                        id === 'my-tags-start-menu-link' ||
                                        id === 'authors-start-menu-link' ||
                                        id === 'system-search-start-menu-link'
                                )
                        ) {
                                return;
                        }

                        e.preventDefault();
                        open_this_as_redmond_dialog(this);
                });
	jQuery(window).on('checkOpenWindows',function() {
		checkOpenWindows();
	});
	jQuery('body').on('onContextMenu',function(e){
		e.preventDefault();
		return false;
	});
	if( redmond_terms.playLoginSounds ) {
		sounds.login.play();
	}
        for( var process in processes ) {
                if ( ! Object.prototype.hasOwnProperty.call(processes, process) ) {
                        continue;
                }

                if ( typeof processes[process].dialog === 'function' && processes[process].dialog('isOpen') ) {
                        processes[process].dialog('moveToTop');
                        find_window_on_top();
                        break;
                }
        }

        if ( typeof redmond_adjust_dialog_sizes === 'function' ) {
                redmond_adjust_dialog_sizes();
        }
});

jQuery(document).on('click', '.redmond-close-window', function ( e ) {
        e.preventDefault();
        e.stopPropagation();

        var $dialog = jQuery(this).closest('.ui-dialog');

        if ( $dialog.length ) {
                var $content = $dialog.find('.ui-dialog-content');

                if ( $content.length && typeof $content.dialog === 'function' ) {
                        $content.dialog('close');
                }
                else {
                        $dialog.hide();
                }
        }
});

jQuery(window)
        .off('resize.redmond')
        .on('resize.redmond', function () {
                var maxHeight = jQuery(window).height() * 0.9;

                jQuery('div.redmond-dialog-window').each(function () {
                        var $window = jQuery(this);

                        if ( $window.height() > maxHeight ) {
                                $window.css({
                                        height: maxHeight,
                                        'padding-bottom': 20
                                });
                        }
                        else {
                                $window.css({
                                        height: 'auto',
                                        'padding-bottom': 0
                                });
                        }

                        $window.find('.ui-dialog-content').css({
                                'overflow-y': 'auto',
                                'overflow-x': 'auto'
                        });
                });

                if ( typeof redmond_adjust_dialog_sizes === 'function' ) {
                        redmond_adjust_dialog_sizes();
                }
        })
        .trigger('resize.redmond');

function checkOpenWindows() {
        var processList = '';
        for( var process in processes ) {
                if ( ! Object.prototype.hasOwnProperty.call(processes, process) ) {
                        continue;
                }

                if ( typeof processes[process].dialog !== 'function' || ! processes[process].dialog('isOpen') ) {
                        continue;
                }

                if( parseInt(processes[process].parent().css('top'),10) >= 0 ) {
                        processes[process].currentTop = parseInt(processes[process].parent().css('top'),10);
                }
                else {
                        processes[process].parent().css('top',processes[process].currentTop);
                }
                processList += '<li onclick="processes[\'' +process+ '\'].dialog(\'moveToTop\')" id="' +process+ '_taskbar">';
                processList += '<span class="task-icon"';
                processList += ' style="' + processes[process].parent().find('.ui-dialog-title').attr('style') + '"';
                processList += '></span>';
                processList += processes[process].parent().find('.ui-dialog-title').html().substring(0,20) + '</li>' + "\r\n";
        }
        jQuery("#open-processes").html( processList );
	jQuery("#open-processes>li").on('click',function() {
		find_window_on_top();
	});
	find_window_on_top();
}

function find_window_on_top() {
	var top;
        var hightestZ = -Infinity;
        for( var process in processes ) {
                if ( ! Object.prototype.hasOwnProperty.call(processes, process) ) {
                        continue;
                }

                if ( typeof processes[process].dialog !== 'function' || ! processes[process].dialog('isOpen') ) {
                        continue;
                }

                if( processes[process].zIndex() > hightestZ ) {
                        top = process;
                        hightestZ = processes[process].zIndex();
                }
                var currTop = processes[process].parent().offset().top;
                if( currTop < 0 ) {
                        processes[process].parent().offset({top: 100, left: processes[process].parent().offset().left});
                }
        }

        jQuery("#open-processes>li").removeClass('active');

        if ( typeof top === 'undefined' ) {
                return;
        }

        var taskbar = jQuery("#" + top + '_taskbar');
        taskbar.addClass('active');
}

function startSystemClock() {
	jQuery("#taskbar-clock").html( currentTime() );
	setInterval( function() {
		jQuery("#taskbar-clock").html( currentTime() );
	},1000);
}

function currentTime() {
	var d = new Date();
	return d.toLocaleTimeString();
}

function handle_start_menu() {
	jQuery("#start-button").on('click',function() {
		toggle_activated(jQuery(this).parent());
		jQuery('#start-menu-wrapper').toggle();
	});
	jQuery("#start-menu-wrapper a").on('click',function() {
		if( jQuery(this).is(':visible') ) {
			jQuery('#start-menu-wrapper').toggle();
			toggle_activated( jQuery("#start-button").parent() );
		}
	});
	jQuery("#start-menu-archives-link").on('mouseover',function() {
		jQuery("#start-menu-archives-link>div.archives-outer-wrapper").show();
	});
	jQuery("#start-menu-archives-link").on('mouseout',function() {
		jQuery("#start-menu-archives-link>div.archives-outer-wrapper").hide();
	});
	jQuery("ul.archives-inner-wrapper>li").on('mouseover',function() {
		var obj = this;
		jQuery(this).find('div.archives-outer-wrapper').show();
		var newtop = 0;
		jQuery(this).prevAll().each(function() {
			if( jQuery(this).hasClass('seperator') ) {
				newtop = newtop + 2;
			}
			else {
				newtop = newtop + 22;	
			}
		});
		jQuery(this).find('div.archives-outer-wrapper').each(function() {
			jQuery(this).css({
				bottom: 'auto',
				height: function() {
					return parseInt( 22 * jQuery(obj).find('li').length ,10);
				},
				top: newtop,
			})
		});
	});
	jQuery("ul.archives-inner-wrapper>li").on('mouseout',function() {
		jQuery(this).find('div.archives-outer-wrapper').hide();
	});
	jQuery("#my-documents-start-menu-link").on('click',function(e){
		e.preventDefault();
		open_archive_as_dialog();
	});
	jQuery("#my-tags-start-menu-link").on('click',function(e){
		e.preventDefault();
		open_archive_as_dialog('all','tags');
	});
	jQuery("#system-search-start-menu-link").on('click',function(e){
		e.preventDefault();
		open_redmond_search_window();
	});
	jQuery("#authors-start-menu-link").on('click',function(e){
		e.preventDefault();
		open_redmond_authors_window();
	});
}

function toggle_activated( obj ) {
	if( jQuery(obj).hasClass('activated') ) {
		jQuery(obj).removeClass('activated');
	}
	else {
		jQuery(obj).addClass('activated');
	}
}

function open_this_as_redmond_dialog( obj ) {
	var type = ( typeof( jQuery(obj).attr('data-post-id') ) !== 'undefined' ) ? 'inline' : 'iframe';
	if( type == 'inline' ) {
		open_post_as_dialog( parseInt( jQuery(obj).attr('data-post-id') , 10 ) );
	}
	else {
                var html = '<iframe src="' + jQuery(obj).attr('href') + '"></iframe>';
                var process = new Date().getTime();
                redmond_window( process , jQuery(obj).attr('title') , html , false , true , true , redmond_terms.externalPageIcon );
                processes[process].css({
                        'overflow-y': 'auto',
                        'overflow-x': 'auto'
                });
        }
}

function open_category_as_redmond_dialog( obj ) {
	var o = jQuery(obj);
	open_archive_as_dialog( o.attr('data-category-id') , o.attr('data-type') );
}

function open_post_as_dialog( postId ) {
	jQuery.ajax({
		url: redmond_terms.ajaxurl,
		data: {
			action: 'getpost',
			post: postId,
			nonce: redmond_terms.nonce,
		},
		async: true,
		beforeSend: function() {},
		cache: false,
		crossDomain: false,
		dataType: 'json',
		error: function( jqXHR ) {
			redmondHandleAjaxError( jqXHR );
		},
		method: 'POST',
		success: function( returned ) {
			var data = redmondParseAjaxResponse( returned );
			if ( false === data ) {
				redmondHandleAjaxError();
				return;
			}
                        redmond_window( data.task_name , data.post_title , data.post_content , data.fileMenu , true, true , data.post_icon );
                        processes[data.task_name].find('article').css({
                                padding: 10,
                        });
                        redmondEnsureDialogScroll( processes[data.task_name] );
                        sounds.open.play();
                }
        });
}


function open_archive_as_dialog( archive , taxonomy , targetId ) {
	if( typeof( taxonomy ) == 'undefined' ) {
		taxonomy = 'category';
	}
	if( typeof( archive ) == 'undefined' ) {
		archive = 'all';
	}
	jQuery.ajax({
		url: redmond_terms.ajaxurl,
		data: {
			action: 'getarchive',
			taxonomy: taxonomy,
			archive: archive,
			nonce: redmond_terms.nonce,
		},
		async: true,
		beforeSend: function() {},
		cache: false,
		crossDomain: false,
		dataType: 'json',
		error: function( jqXHR ) {
			redmondHandleAjaxError( jqXHR );
		},
		method: 'POST',
		success: function( returned ) {
			var data = redmondParseAjaxResponse( returned );
			if ( false === data ) {
				redmondHandleAjaxError();
				return;
			}
			if( typeof( targetId ) !== 'undefined' && jQuery("#" + targetId ).length > 0 ) {
				jQuery("#"+targetId).dialog('close');
			}
                        redmond_window( data.taskname , data.title , data.html , data.menu , true, true , data.icon );
                        if ( processes[data.taskname] ) {
                                redmondEnsureDialogScroll( processes[data.taskname] );
                        }
			jQuery("#" + data.taskname).find('a').on('click',function(e) {
				if( typeof( jQuery(this).attr('id') ) === 'undefined' && typeof( jQuery(this).attr('data-type') ) !== 'undefined' ) {
					e.preventDefault();
					switch( jQuery(this).attr('data-type') ) {
					case 'regular':
						open_this_as_redmond_dialog(this);
						break;

					default:
						open_category_as_redmond_dialog(this);
						break;
					}
				}
			});
			sounds.open.play();
		}
	});
}

function open_redmond_authors_window( author ) {
	if( typeof( author ) == 'undefined' ) {
		author = 'all';
	}
	jQuery.ajax({
		url: redmond_terms.ajaxurl,
		data: {
			action: 'getauthor',
			author: author,
			nonce: redmond_terms.nonce,
		},
		async: true,
		beforeSend: function() {},
		cache: false,
		crossDomain: false,
		dataType: 'json',
		error: function( jqXHR ) {
			redmondHandleAjaxError( jqXHR );
		},
		method: 'POST',
		success: function( returned ) {
			var data = redmondParseAjaxResponse( returned );
			if ( false === data ) {
				redmondHandleAjaxError();
				return;
			}
			if( typeof( targetId ) !== 'undefined' && jQuery("#" + targetId ).length > 0 ) {
				jQuery("#"+targetId).dialog('close');
			}
                        redmond_window( data.taskname , data.title , data.html , data.menu , true, true , data.icon );
                        if ( processes[data.taskname] ) {
                                redmondEnsureDialogScroll( processes[data.taskname] );
                        }
			jQuery("#" + data.taskname).find('a').on('click',function(e) {
				if( typeof( jQuery(this).attr('id') ) === 'undefined' && typeof( jQuery(this).attr('data-type') ) !== 'undefined' ) {
					e.preventDefault();
					switch( jQuery(this).attr('data-type') ) {
					case 'regular':
						open_this_as_redmond_dialog(this);
						break;

					default:
						open_redmond_authors_window( jQuery(this).attr('data-author-id') );
						break;
					}
				}
			});
			sounds.open.play();
		}
	});
}

function open_redmond_search_window( search ) {
	if( typeof(search) == 'undefined' || search.length == 0 ) {
		var windowTitle = redmond_terms.search;
	}
	else {
		var windowTitle = redmond_terms.search_for + search;
	}
	jQuery.ajax({
		url: redmond_terms.ajaxurl,
		data: {
			action: 'getsearch',
			search:search,
			nonce: redmond_terms.nonce,
		},
		async: true,
		beforeSend: function() {},
		cache: false,
		crossDomain: false,
		dataType: 'json',
		error: function( jqXHR ) {
			redmondHandleAjaxError( jqXHR );
		},
		method: 'POST',
		success: function( returned ) {
			var data = redmondParseAjaxResponse( returned );
			if ( false === data ) {
				redmondHandleAjaxError();
				return;
			}
                        redmond_window( 'searchwindow' , windowTitle , data.html , redmond_terms.default_file_menu , false , true , redmond_terms.searchIcon );
                        if ( processes.searchwindow ) {
                                redmondEnsureDialogScroll( processes.searchwindow );
                        }
			jQuery("#searchwindow").find('button').on('click',function(e) {
				e.preventDefault();
				open_redmond_search_window( jQuery('#searchwindow').find('input').val() );
			});
			jQuery("#searchwindow").find('input').on('keyup',function(e){
				if( e.keyCode == 13 ) {
					open_redmond_search_window( jQuery('#searchwindow').find('input').val() );
				}
			});
			jQuery("#searchwindow").find('a').on('click',function(e) {
				if( typeof( jQuery(this).attr('id') ) === 'undefined' && typeof( jQuery(this).attr('data-type') ) !== 'undefined' ) {
					e.preventDefault();
					switch( jQuery(this).attr('data-type') ) {
					case 'regular':
						open_this_as_redmond_dialog(this);
						break;

					default:
						open_category_as_redmond_dialog(this);
						break;
					}
				}
			});
			sounds.open.play();
		}
	});
}

function redmondHandleAjaxError( jqXHR ) {
	var message = redmond_terms.ajaxerror;
	if ( jqXHR && jqXHR.responseJSON && jqXHR.responseJSON.data ) {
		if ( typeof jqXHR.responseJSON.data === 'string' && jqXHR.responseJSON.data.length > 0 ) {
			message = jqXHR.responseJSON.data;
		} else if ( jqXHR.responseJSON.data.message ) {
			message = jqXHR.responseJSON.data.message;
		}
	}
	do_redmond_error_window( message );
	console.log( 'AJAX Error', jqXHR );
}

function redmondParseAjaxResponse( returned ) {
        if ( ! returned || typeof returned.success === 'undefined' ) {
                return false;
        }
        if ( true !== returned.success || typeof returned.data === 'undefined' ) {
                return false;
        }
        return returned.data;
}

function redmondOpenAjaxDialog( processId, data, fallbackTitle ) {
        var dialogTitle = ( data && data.title ) ? data.title : fallbackTitle;
        var dialogHtml = ( data && data.html ) ? data.html : '';
        var dialogMenu = ( data && data.menu ) ? data.menu : null;
        var dialogIcon = ( data && data.icon ) ? data.icon : redmond_terms.windowIcon;
        var canResize = ( data && typeof data.resizable === 'boolean' ) ? data.resizable : false;
        var draggable = ( data && typeof data.draggable === 'boolean' ) ? data.draggable : true;
        var limitHeight = ( data && typeof data.limitHeight === 'boolean' ) ? data.limitHeight : true;

        redmond_window( processId, dialogTitle, dialogHtml, dialogMenu, canResize, draggable, dialogIcon, limitHeight );

        if ( ! processes[processId] || ! processes[processId].length ) {
                return null;
        }

        return processes[processId];
}

function redmondEnsureDialogScroll( dialogContent ) {
        if ( ! dialogContent || ! dialogContent.length ) {
                return;
        }

        dialogContent.css({
                'overflow-y': 'auto',
                'overflow-x': 'auto',
        });

        var wrapper = dialogContent.closest('.ui-dialog');
        if ( wrapper.length ) {
                wrapper.find('.ui-dialog-content').css({
                        'overflow-y': 'auto',
                        'overflow-x': 'auto',
                });
        }

        redmond_adjust_dialog_sizes();
}

function redmondCopyToClipboard( text ) {
        if ( navigator.clipboard && typeof navigator.clipboard.writeText === 'function' ) {
                return navigator.clipboard.writeText( text );
        }

        return new Promise(function( resolve, reject ) {
                var $buffer = jQuery('<textarea class="redmond-copy-buffer" aria-hidden="true"></textarea>');
                $buffer.val( text ).css({
                        position: 'fixed',
                        top: '-1000px',
                        left: '-1000px',
                        opacity: 0,
                        width: '1px',
                        height: '1px',
                });
                jQuery('body').append( $buffer );

                var node = $buffer.get(0);
                try {
                        node.focus();
                        node.select();
                        if ( typeof node.setSelectionRange === 'function' ) {
                                node.setSelectionRange( 0, node.value.length );
                        }
                        var successful = document.execCommand( 'copy' );
                        $buffer.remove();
                        if ( successful ) {
                                resolve();
                        }
                        else {
                                reject();
                        }
                }
                catch ( err ) {
                        $buffer.remove();
                        reject( err );
                }
        });
}

function redmondShareFeedback( dialogContent, message, isError ) {
        if ( ! dialogContent || ! dialogContent.length || ! message ) {
                return;
        }

        var feedback = dialogContent.find('.redmond-share-feedback');
        if ( ! feedback.length ) {
                        return;
        }

        feedback.stop( true, true );
        feedback.text( message );
        feedback.removeClass( 'is-error is-success is-visible' );
        feedback.addClass( isError ? 'is-error' : 'is-success' );
        feedback.addClass( 'is-visible' );

        var previousTimer = feedback.data( 'redmondFeedbackTimer' );
        if ( previousTimer ) {
                clearTimeout( previousTimer );
        }

        var timer = setTimeout( function() {
                feedback.removeClass( 'is-visible is-error is-success' );
                feedback.text( '' );
        }, 2400 );

        feedback.data( 'redmondFeedbackTimer', timer );
}

function do_redmond_error_window( message ) {
        var contents = '';
        contents += '<p>' + message +'</p>' + "\r\n";
        contents += '<span class="button-outer dialog-close-button"><button class="system-button" type="button" onclick="redmond_close_this(this); return false;">' + redmond_terms.closetext + '</button></span>' + "\r\n";
        redmond_window('error',redmond_terms.errTitle,contents,null,false,true,redmond_terms.errorIcon);
        processes.error.css({
                'overflow-y': 'hidden',
                background: '#1f2937',
                'background-color': '#1f2937',
                padding: 10,
                'max-width': 700,
        });
        processes.error.find('div.file-bar').css({
                display:'none',
        });
        redmondEnsureDialogScroll( processes.error );
        sounds.error.play();
}

function redmond_comment_field( postid ) {
        jQuery.ajax({
                url: redmond_terms.ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                        action: 'redmond_get_comment_form',
                        nonce: redmond_terms.nonce,
                        postId: postid,
                },
                success: function( returned ) {
                        var data = redmondParseAjaxResponse( returned );
                        if ( false === data ) {
                                redmondHandleAjaxError();
                                return;
                        }

                        var processId = 'comment_for_' + postid;
                        var dialogContent = redmondOpenAjaxDialog( processId, data, 'Comment' );
                        if ( ! dialogContent ) {
                                return;
                        }

                        var wrapper = dialogContent.closest('.ui-dialog');
                        if ( wrapper.length ) {
                                wrapper.addClass('redmond-comment-window');
                        }

                        redmondEnsureDialogScroll( dialogContent );

                        var commentField = dialogContent.find('#comment');
                        if ( commentField.length ) {
                                commentField.trigger('focus');
                        }

                        sounds.open.play();
                },
                error: function( jqXHR ) {
                        redmondHandleAjaxError( jqXHR );
                }
        });
}

function redmond_share_post( postid ) {
        jQuery.ajax({
                url: redmond_terms.ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                        action: 'redmond_get_share_data',
                        nonce: redmond_terms.nonce,
                        postId: postid,
                },
                success: function( returned ) {
                        var data = redmondParseAjaxResponse( returned );
                        if ( false === data ) {
                                redmondHandleAjaxError();
                                return;
                        }

                        var processId = 'share_post_' + postid;
                        var dialogContent = redmondOpenAjaxDialog( processId, data, 'Share' );
                        if ( ! dialogContent ) {
                                return;
                        }

                        var wrapper = dialogContent.closest('.ui-dialog');
                        if ( wrapper.length ) {
                                wrapper.addClass('redmond-share-window');
                        }

                        redmondEnsureDialogScroll( dialogContent );

                        dialogContent
                                .off('click.redmondShareCopy')
                                .on('click.redmondShareCopy', '.redmond-copy-link', function( event ) {
                                        event.preventDefault();
                                        var button = jQuery( this );
                                        var targetId = button.data( 'copyTarget' );
                                        if ( ! targetId ) {
                                                return;
                                        }

                                        var target = dialogContent.find( '#' + targetId );
                                        if ( ! target.length ) {
                                                return;
                                        }

                                        redmondCopyToClipboard( target.val() )
                                                .then( function() {
                                                        redmondShareFeedback( dialogContent, button.data( 'successMessage' ), false );
                                                } )
                                                .catch( function() {
                                                        if ( target.length && target[0] && typeof target[0].select === 'function' ) {
                                                                target.trigger('focus');
                                                                target[0].select();
                                                                if ( typeof target[0].setSelectionRange === 'function' ) {
                                                                        target[0].setSelectionRange( 0, target.val().length );
                                                                }
                                                        }
                                                        redmondShareFeedback( dialogContent, button.data( 'errorMessage' ), true );
                                                } );
                                });

                        sounds.open.play();
                },
                error: function( jqXHR ) {
                        redmondHandleAjaxError( jqXHR );
                }
        });
}
