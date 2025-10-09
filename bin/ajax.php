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
                        'icon'       => get_theme_mod( 'redmond_default_documents_icon', REDMONDURI . '/resources/docs.png' ),
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
                        'icon'       => get_theme_mod( 'redmond_default_documents_icon', REDMONDURI . '/resources/docs.png' ),
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
?>
