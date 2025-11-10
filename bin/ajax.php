<?php
        defined( 'ABSPATH' ) || die( 'Sorry, but you cannot access this page directly.' );

        function redmond_getpost_callback() {
                check_ajax_referer( 'redmond_ajax_nonce', 'nonce' );

                if ( empty( $_POST['post'] ) ) {
                        wp_send_json_error( array( 'message' => __( 'Invalid post request.', RTEXTDOMAIN ) ), 400 );
                }

                $post_id = absint( wp_unslash( $_POST['post'] ) );
                if ( ! $post_id ) {
                        wp_send_json_error( array( 'message' => __( 'Invalid post request.', RTEXTDOMAIN ) ), 400 );
                }

                $post = get_post( $post_id );
                if ( empty( $post ) || 'publish' !== get_post_status( $post ) ) {
                        wp_send_json_error( array( 'message' => __( 'The requested post could not be found.', RTEXTDOMAIN ) ), 404 );
                }

                $content = apply_filters( 'the_content', $post->post_content );
                $content = redmond_add_comments_to_content( $content, $post->ID );

                $data = array(
                        'ID'          => $post->ID,
                        'post_title'  => get_the_title( $post ),
                        'post_content'=> $content,
                        'post_icon'   => redmond_get_post_icon( $post->ID ),
                        'task_name'   => 'post_' . $post->ID,
                        'fileMenu'    => get_file_menu_for_post( $post->ID ),
                );

                wp_send_json_success( $data );
        }

        function redmond_add_comments_to_content( $content, $post_id ) {
                $content = '<article>' . $content . "\r\n";
                if ( comments_open( $post_id ) && get_comments_number( $post_id ) > 0 ) {
                        $comments = get_comments( array( 'post_id' => $post_id, 'status' => 'approve' ) );
                        $content .= '<hr />' . "\r\n";
                        $content .= '<h4>' . __( 'Comments:', RTEXTDOMAIN ) . '</h4>' . "\r\n";
                        $content .= '<ul class="list-group">' . "\r\n";
                        foreach ( $comments as $comment ) {
                                $content .= '<li class="list-group-item">' . "\r\n";
                                $content .= '<a href="' . esc_url( $comment->comment_author_url ) . '" target="_blank">' . get_avatar( $comment->comment_author_email , 64 ) . '</a>' . "\r\n";
                                $content .= '<h5 class="list-group-item-heading">' . esc_html( $comment->comment_author ) . '</h5>' . "\r\n";
                                $content .= wpautop( esc_html( $comment->comment_content ) );
                                $content .= '</li>' . "\r\n";
                        }
                        $content .= '</ul>' . "\r\n";
                }
                $content .= '</article>' . "\r\n";
                return $content;
        }

        function redmond_getarchive_callback() {
                check_ajax_referer( 'redmond_ajax_nonce', 'nonce' );

                $taxonomy = ! empty( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : 'category';
                $archive  = isset( $_POST['archive'] ) ? wp_unslash( $_POST['archive'] ) : 'all';

                if ( 'all' !== $archive ) {
                        $archive = absint( $archive );
                }

                $data = array(
                        'html'       => '',
                        'breadcrumbs'=> '',
                        'menu'       => array(),
                        'title'      => __( 'Archive', RTEXTDOMAIN ),
                        'icon'       => get_theme_mod( 'redmond_default_documents_icon', REDMONDURI . '/resources/docs.ico' ),
                        'taskname'   => null,
                );

                switch ( $taxonomy ) {
                        case 'tags':
                                $data['html'] = redmond_generate_folder_shortcuts_from_tag( ( 'all' === $archive ) ? 'all' : $archive );
                                $data['menu']['close'] = array(
                                        'title'   => __( 'Close', RTEXTDOMAIN ),
                                        'onclick' => 'redmond_close_this(this)',
                                );
                                $data['taskname'] = 'tag_archive_' . esc_attr( ( 'all' === $archive ) ? 'all' : $archive );
                                if ( 'all' !== $archive && $archive ) {
                                        $tag = get_tag( $archive );
                                        $data['title'] = ( $tag instanceof WP_Term ) ? $tag->name : __( 'Tag', RTEXTDOMAIN );
                                } else {
                                        $data['title'] = __( 'All Tags', RTEXTDOMAIN );
                                }
                                break;

                        default:
                                $data['html'] = redmond_generate_folder_shortcuts_from_category( ( 'all' === $archive ) ? 'all' : $archive );
                                $data['menu']['close'] = array(
                                        'title'   => __( 'Close', RTEXTDOMAIN ),
                                        'onclick' => 'redmond_close_this(this)',
                                );
                                if ( 'all' !== $archive && $archive ) {
                                        $category_title = get_the_category_by_ID( $archive );
                                        $data['title']   = $category_title ? $category_title : __( 'Category', RTEXTDOMAIN );
                                } else {
                                        $data['title'] = __( 'All Categories', RTEXTDOMAIN );
                                }
                                $data['taskname'] = 'cat_archive_' . esc_attr( ( 'all' === $archive ) ? 'all' : $archive );
                                break;
                }

                wp_send_json_success( $data );
        }

        function redmond_getsearch_callback() {
                check_ajax_referer( 'redmond_ajax_nonce', 'nonce' );

                $search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

                wp_send_json_success( array( 'html' => redmond_generate_search_results( $search ) ) );
        }

        function redmond_getauthor_callback() {
                check_ajax_referer( 'redmond_ajax_nonce', 'nonce' );

                $author = isset( $_POST['author'] ) ? wp_unslash( $_POST['author'] ) : 'all';
                if ( 'all' !== $author ) {
                        $author = absint( $author );
                }

                $data = array(
                        'html'       => '',
                        'breadcrumbs'=> '',
                        'menu'       => array(),
                        'title'      => __( 'Author', RTEXTDOMAIN ),
                        'icon'       => get_theme_mod( 'redmond_default_documents_icon', REDMONDURI . '/resources/docs.ico' ),
                        'taskname'   => null,
                );
                $data['menu']['close'] = array(
                        'title'   => __( 'Close', RTEXTDOMAIN ),
                        'onclick' => 'redmond_close_this(this)',
                );

                if ( 'all' !== $author && $author ) {
                        $user          = get_userdata( $author );
                        $data['title'] = ( $user instanceof WP_User ) ? $user->display_name : __( 'Author', RTEXTDOMAIN );
                } else {
                        $data['title'] = __( 'All Authors', RTEXTDOMAIN );
                }

                $data['taskname'] = 'author_archive_' . esc_attr( ( 'all' === $author ) ? 'all' : $author );
                $data['html']     = redmond_generate_folder_shortcuts_from_author( ( 'all' === $author ) ? 'all' : $author );

                wp_send_json_success( $data );
        }

        function redmond_get_comment_form_callback() {
                check_ajax_referer( 'redmond_ajax_nonce', 'nonce' );

                if ( empty( $_POST['postId'] ) ) {
                        wp_send_json_error( array( 'message' => __( 'Invalid post request.', RTEXTDOMAIN ) ), 400 );
                }

                $post_id = absint( wp_unslash( $_POST['postId'] ) );
                if ( ! $post_id ) {
                        wp_send_json_error( array( 'message' => __( 'Invalid post request.', RTEXTDOMAIN ) ), 400 );
                }

                $post = get_post( $post_id );
                if ( empty( $post ) || 'publish' !== get_post_status( $post ) ) {
                        wp_send_json_error( array( 'message' => __( 'The requested post could not be found.', RTEXTDOMAIN ) ), 404 );
                }

                if ( ! comments_open( $post_id ) ) {
                        wp_send_json_error( array( 'message' => __( 'Comments are closed for this post.', RTEXTDOMAIN ) ), 403 );
                }

                $commenter          = wp_get_current_commenter();
                $require_name_email = (bool) get_option( 'require_name_email' );
                $aria_required      = $require_name_email ? " aria-required='true' required" : '';
                $safe_title         = wp_strip_all_tags( get_the_title( $post_id ) );

                $fields = array(
                        'author' => '<p class="comment-form-author">'
                                . '<label for="author">' . esc_html__( 'Name', RTEXTDOMAIN ) . ( $require_name_email ? ' <span class="required">*</span>' : '' ) . '</label>'
                                . '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" class="system-field" size="30"' . $aria_required . ' />'
                                . '</p>',
                        'email'  => '<p class="comment-form-email">'
                                . '<label for="email">' . esc_html__( 'Email', RTEXTDOMAIN ) . ( $require_name_email ? ' <span class="required">*</span>' : '' ) . '</label>'
                                . '<input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" class="system-field" size="30"' . $aria_required . ' />'
                                . '</p>',
                        'url'    => '<p class="comment-form-url">'
                                . '<label for="url">' . esc_html__( 'Website', RTEXTDOMAIN ) . '</label>'
                                . '<input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" class="system-field" size="30" />'
                                . '</p>',
                );

                ob_start();
                comment_form(
                        array(
                                'title_reply'          => esc_html__( 'Leave a comment', RTEXTDOMAIN ),
                                'title_reply_before'   => '<h3 class="comment-form-title">',
                                'title_reply_after'    => '</h3>',
                                'fields'               => $fields,
                                'comment_field'        => '<p class="comment-form-comment">'
                                        . '<label for="comment">' . esc_html__( 'Comment', RTEXTDOMAIN ) . '</label>'
                                        . '<textarea id="comment" name="comment" class="system-textarea" cols="45" rows="8" required aria-required="true"></textarea>'
                                        . '</p>',
                                'comment_notes_before' => '',
                                'comment_notes_after'  => '',
                                'logged_in_as'         => '',
                                'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
                                'submit_field'         => '<p class="form-submit"><span class="button-outer">%1$s</span> %2$s</p>',
                                'class_submit'         => 'system-button',
                        ),
                        $post_id
                );
                $form = ob_get_clean();

                $menu = array(
                        'close' => array(
                                'title'   => __( 'Close', RTEXTDOMAIN ),
                                'onclick' => 'redmond_close_this(this)',
                        ),
                );

                $data = array(
                        'html'       => '<div class="redmond-comment-dialog-content">' . $form . '</div>',
                        'title'      => sprintf( __( 'Leave a Comment on %s', RTEXTDOMAIN ), $safe_title ),
                        'menu'       => $menu,
                        'icon'       => redmond_get_post_icon( $post_id ),
                        'resizable'  => false,
                        'draggable'  => true,
                        'limitHeight'=> true,
                );

                wp_send_json_success( $data );
        }

        function redmond_share_post_callback() {
                check_ajax_referer( 'redmond_ajax_nonce', 'nonce' );

                if ( empty( $_POST['postId'] ) ) {
                        wp_send_json_error( array( 'message' => __( 'Invalid post request.', RTEXTDOMAIN ) ), 400 );
                }

                $post_id = absint( wp_unslash( $_POST['postId'] ) );
                if ( ! $post_id ) {
                        wp_send_json_error( array( 'message' => __( 'Invalid post request.', RTEXTDOMAIN ) ), 400 );
                }

                $post = get_post( $post_id );
                if ( empty( $post ) || 'publish' !== get_post_status( $post ) ) {
                        wp_send_json_error( array( 'message' => __( 'The requested post could not be found.', RTEXTDOMAIN ) ), 404 );
                }

                $permalink     = get_permalink( $post_id );
                $post_title    = wp_strip_all_tags( get_the_title( $post_id ) );
                $charset       = get_bloginfo( 'charset' ) ?: 'UTF-8';
                $encoded_title = rawurlencode( html_entity_decode( $post_title, ENT_QUOTES, $charset ) );
                $encoded_url   = rawurlencode( $permalink );

                $share_links = array(
                        'facebook' => array(
                                'label' => __( 'Facebook', RTEXTDOMAIN ),
                                'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_url,
                        ),
                        'twitter'  => array(
                                'label' => __( 'Twitter / X', RTEXTDOMAIN ),
                                'url'   => 'https://twitter.com/intent/tweet?url=' . $encoded_url . '&text=' . $encoded_title,
                        ),
                        'linkedin' => array(
                                'label' => __( 'LinkedIn', RTEXTDOMAIN ),
                                'url'   => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $encoded_url . '&title=' . $encoded_title,
                        ),
                        'reddit'   => array(
                                'label' => __( 'Reddit', RTEXTDOMAIN ),
                                'url'   => 'https://www.reddit.com/submit?url=' . $encoded_url . '&title=' . $encoded_title,
                        ),
                        'email'    => array(
                                'label' => __( 'Email', RTEXTDOMAIN ),
                                'url'   => 'mailto:?subject=' . rawurlencode( sprintf( __( 'Check out "%s"', RTEXTDOMAIN ), $post_title ) ) . '&body=' . rawurlencode( sprintf( __( 'I thought you might enjoy this post: %s', RTEXTDOMAIN ), $permalink ) ),
                        ),
                );

                $input_id = 'redmond-share-link-' . $post_id;

                $html  = '<div class="redmond-share-dialog">';
                $html .= '<p>' . esc_html__( 'Share this post using the options below.', RTEXTDOMAIN ) . '</p>';
                $html .= '<ul class="redmond-share-list">';
                foreach ( $share_links as $network => $link ) {
                        $html .= '<li class="redmond-share-item redmond-share-' . esc_attr( $network ) . '">';
                        $html .= '<a class="redmond-share-link" href="' . esc_url( $link['url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $link['label'] ) . '</a>';
                        $html .= '</li>';
                }
                $html .= '</ul>';
                $html .= '<div class="redmond-share-copy">';
                $html .= '<label for="' . esc_attr( $input_id ) . '">' . esc_html__( 'Direct link', RTEXTDOMAIN ) . '</label>';
                $html .= '<div class="redmond-share-copy-controls">';
                $html .= '<input type="text" readonly class="system-field redmond-share-url" id="' . esc_attr( $input_id ) . '" value="' . esc_url( $permalink ) . '" />';
                $html .= '<span class="button-outer"><button type="button" class="system-button redmond-copy-link" data-copy-target="' . esc_attr( $input_id ) . '" data-success-message="' . esc_attr__( 'Link copied to clipboard.', RTEXTDOMAIN ) . '" data-error-message="' . esc_attr__( 'Unable to copy the link. Please copy it manually.', RTEXTDOMAIN ) . '">' . esc_html__( 'Copy link', RTEXTDOMAIN ) . '</button></span>';
                $html .= '</div>';
                $html .= '<p class="redmond-share-feedback" role="status" aria-live="polite"></p>';
                $html .= '</div>';
                $html .= '</div>';

                $menu = array(
                        'close' => array(
                                'title'   => __( 'Close', RTEXTDOMAIN ),
                                'onclick' => 'redmond_close_this(this)',
                        ),
                );

                $data = array(
                        'html'      => $html,
                        'title'     => sprintf( __( 'Share "%s"', RTEXTDOMAIN ), $post_title ),
                        'menu'      => $menu,
                        'icon'      => redmond_get_post_icon( $post_id ),
                        'resizable' => false,
                        'draggable' => true,
                        'limitHeight'=> true,
                );

                wp_send_json_success( $data );
        }
?>
