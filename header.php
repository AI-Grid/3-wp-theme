<?php
defined( 'ABSPATH' ) || die( 'Sorry, but you cannot access this page directly.' );
global $wpdb;
$quick_launch      = redmond_get_menu_as_array( 'quick_launch' );
$start_menu_items  = redmond_get_menu_as_array( 'start' );
$current_user      = wp_get_current_user();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta name="format-detection" content="telephone=no">
		<?php
			wp_head();
		?>
	</head>
<body <?php body_class( 'custom-background dark-mode' ); ?>>
		<?php
		if ( function_exists( 'wp_body_open' ) ) {
			wp_body_open();
		}
		?>
		<div id="taskbar-outer">
			<div id="taskbar-inner">
				<span class="button-outer">
					<button class="system-button" id="start-button">
						<img src="<?php print esc_url( get_theme_mod( 'redmond_start_icon' , REDMONDURI . '/resources/search.ico' ) ); ?>" />
						<span class="button-text"><?php esc_html_e( 'Start', RTEXTDOMAIN ); ?></span>
					</button>
				</span>
				<div class="start-menu-seperator-outer">
					<div class="start-menu-seperator-inner"></div>
				</div>
				<ul id="quick-launch-links">
				<?php
				foreach ( $quick_launch as $quick ) {
				?>
					<li>
						<a href="<?php print esc_url( $quick->url ); ?>" <?php if ( $quick->object !== 'custom' ) { ?> class="post-link" data-post-id="<?php print intval( $quick->object_id ); ?>" <?php } ?> title="<?php print esc_html( get_the_title( $quick->object_id ) ); ?>">
							<img src="<?php print esc_url( redmond_get_post_icon( $quick->object_id ) ); ?>" />
						</a>
					</li>
				<?php
				}
				?>
				</ul>
				<div class="start-menu-seperator-outer">
					<div class="start-menu-seperator-inner"></div>
				</div>
				<ul id="open-processes"></ul>
				<div id="taskbar-clock" class="hidden-xs hidden-sm">Loading Clock</div>
			</div>
		</div>
		<div id="start-menu-wrapper">
			<div id="start-menu-inner">
				<div id="start-menu-user-bar">
					<p class="current-user">
						<?php print get_avatar( $current_user->ID , 32 ); ?>
						<?php print esc_html( ( is_object( $current_user->data ) && property_exists( $current_user->data, 'display_name' ) ) ? $current_user->data->display_name : __( 'Guest', RTEXTDOMAIN ) ); ?>
					</p>
				</div>
				<div id="start-menu-internal">
					<div class="row">
						<div class="col-xs-6">
						<div id="start-menu-posts"<?php if ( ! empty( $start_menu_items ) ) { ?> class="has-nav"<?php } ?>>
					<?php
					if ( ! empty( $start_menu_items ) ) {
						foreach ( $start_menu_items as $menu_item ) {
							$item_title = wp_strip_all_tags( $menu_item->title );
							$item_label = wp_html_excerpt( $item_title, 24, '...' );
							$item_icon  = ( 'custom' === $menu_item->object ) ? get_theme_mod( 'redmond_external_page_icon', REDMONDURI . '/resources/external.ico' ) : redmond_get_post_icon( $menu_item->object_id );
							$has_children = ! empty( $menu_item->subs );
							$link_classes = array( 'start-menu-link' );

							if ( 'custom' !== $menu_item->object ) {
								$link_classes[] = 'post-link';
							}

							?>
							<div class="start-menu-item<?php echo $has_children ? ' has-children' : ''; ?>">
								<a href="<?php print esc_url( $menu_item->url ); ?>" class="<?php print esc_attr( implode( ' ', $link_classes ) ); ?>" <?php if ( 'custom' !== $menu_item->object ) { ?>data-post-id="<?php print intval( $menu_item->object_id ); ?>"<?php } ?> title="<?php print esc_attr( $item_title ); ?>">
									<img src="<?php print esc_url( $item_icon ); ?>" alt="" />
									<span class="start-menu-label"><?php print esc_html( $item_label ); ?></span>
									<?php if ( $has_children ) : ?>
										<span class="submenu-caret" aria-hidden="true">&#9656;</span>
									<?php endif; ?>
								</a>
								<?php if ( $has_children ) : ?>
								<ul class="start-menu-submenu">
								<?php foreach ( $menu_item->subs as $sub_item ) :
									$sub_title = wp_strip_all_tags( $sub_item->title );
									$sub_label = wp_html_excerpt( $sub_title, 24, '...' );
									$sub_icon  = ( 'custom' === $sub_item->object ) ? get_theme_mod( 'redmond_external_page_icon', REDMONDURI . '/resources/external.ico' ) : redmond_get_post_icon( $sub_item->object_id );
									$sub_classes = array( 'start-menu-link' );
									if ( 'custom' !== $sub_item->object ) {
										$sub_classes[] = 'post-link';
									}
								?>
									<li>
										<a href="<?php print esc_url( $sub_item->url ); ?>" class="<?php print esc_attr( implode( ' ', $sub_classes ) ); ?>" <?php if ( 'custom' !== $sub_item->object ) { ?>data-post-id="<?php print intval( $sub_item->object_id ); ?>"<?php } ?> title="<?php print esc_attr( $sub_title ); ?>">
											<img src="<?php print esc_url( $sub_icon ); ?>" alt="" />
											<span class="start-menu-label"><?php print esc_html( $sub_label ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
								</ul>
								<?php endif; ?>
							</div>
							<?php
						}
					} else {
						$recent_items = get_posts( array(
							'post_type'        => array( 'post', 'page' ),
							'post_status'      => 'publish',
							'posts_per_page'   => 8,
							'orderby'          => 'date',
							'order'            => 'DESC',
							'suppress_filters' => false,
						) );

						if ( empty( $recent_items ) ) {
							?>
							<span class="start-menu-empty">
								<?php esc_html_e( 'No recent content available yet.', RTEXTDOMAIN ); ?>
							</span>
							<?php
						}
						else {
							foreach ( $recent_items as $recent_post ) {
								$recent_title = get_the_title( $recent_post );
								$recent_label = wp_html_excerpt( $recent_title, 20, '...' );
								?>
								<a href="<?php print esc_url( get_permalink( $recent_post ) ); ?>" class="post-link" data-post-id="<?php print intval( $recent_post->ID ); ?>" title="<?php print esc_attr( $recent_title ); ?>">
									<img src="<?php print esc_url( redmond_get_post_icon( $recent_post->ID ) ); ?>" alt="" />
									<?php print esc_html( $recent_label ); ?>
								</a>
								<?php
							}
							wp_reset_postdata();
						}
					}
					?>
							</div>
						</div>
						<div class="col-xs-6">
							<div id="start-menu-content-links">
								<a id="my-documents-start-menu-link">
									<img src="<?php print esc_url( get_theme_mod( 'redmond_default_documents_icon' , REDMONDURI . '/resources/docs.ico' ) ); ?>" />
									<?php esc_html_e( 'Categories', RTEXTDOMAIN ); ?>
								</a>
								<?php if ( count( get_tags() ) > 0 ) { ?>
								<a id="my-tags-start-menu-link">
									<img src="<?php print esc_url( get_theme_mod( 'redmond_default_documents_icon' , REDMONDURI . '/resources/docs.ico' ) ); ?>" />
									<?php esc_html_e( 'Tags', RTEXTDOMAIN ); ?>
								</a>
								<a id="authors-start-menu-link">
									<img src="<?php print esc_url( get_theme_mod( 'redmond_default_documents_icon' , REDMONDURI . '/resources/docs.ico' ) ); ?>" />
									<?php esc_html_e( 'Authors', RTEXTDOMAIN ); ?>
								</a>
								<?php
}
if ( current_user_can( 'publish_posts' ) ) {
								?>
								<a class="seperator"></a>
								<a href="<?php print esc_url( admin_url( 'customize.php?return=%2F' ) ); ?>" id="system-info-start-menu-link">
									<img src="<?php print esc_url( get_theme_mod( 'redmond_default_info_icon' , REDMONDURI . '/resources/sysinfo.ico' ) ); ?>" />
									<?php esc_html_e( 'Site Customization', RTEXTDOMAIN ); ?>
								</a>
								<a href="<?php print esc_url( admin_url() ); ?>" id="control-panel-start-menu-link">
									<img src="<?php print esc_url( get_theme_mod( 'redmond_default_control_panel_icon' , REDMONDURI . '/resources/register.ico' ) ); ?>" />
									<?php esc_html_e( 'Control Panel' ,RTEXTDOMAIN ); ?>
								</a>
								<?php
}
								?>
								<a class="seperator"></a>
								<a href="/" title="<?php esc_html_e( 'Home', RTEXTDOMAIN );?>" alt="<?php esc_html_e( 'Home', RTEXTDOMAIN );?>" id="home-start-menu-link">
									<img src="<?php print esc_url( get_theme_mod( 'redmond_default_home_icon' , REDMONDURI . '/resources/home.ico' ) ); ?>" />
									<?php esc_html_e( 'Home', RTEXTDOMAIN ); ?>
								</a>
								<a id="system-search-start-menu-link">
									<img src="<?php print esc_url( get_theme_mod( 'redmond_default_search_icon' , REDMONDURI . '/resources/sitesearch.ico' ) ); ?>" />
									<?php esc_html_e( 'Search', RTEXTDOMAIN ); ?>
								</a>
							</div>
						</div>
					</div>
				</div>
				<ul id="start-menu-archive-link-wrapper" class="hidden-xs hidden-sm">
					<li class="seperator"></li>
					<li id="start-menu-archives-link">
						<?php esc_html_e( 'More', RTEXTDOMAIN ); ?><span style="margin-left: 20px;" class="glyphicon glyphicon-play pull-right"></span>
						<?php
							$table = $wpdb->prefix . 'posts';
							$query = 'SELECT
								MONTH( ' . $table . '.post_date_gmt ) as `month`,
								YEAR( ' . $table . '.post_date_gmt ) as `year`,
								CONCAT( YEAR( ' . $table . '.post_date_gmt ) , \'-\', MONTH( ' . $table . '.post_date_gmt ) , \'-01 00:00:00\' ) as `timestamp`
							FROM
								' . $table . '
							WHERE
								' . $table . '.post_type = \'post\'
							AND
								' . $table . '.post_status = \'publish\'
							GROUP BY `timestamp`';
							$archives_month = wp_cache_get( 'redmond_archives_month' );
						if ( false == $archives_month ) {
							$archives_month = $wpdb->get_results( $query );
							wp_cache_set( 'redmond_archives_month', $archives_month );
						}
						?>
						<div id="archives-top-level-outer-wrapper" class="archives-outer-wrapper">
							<ul id="archives-top-level-inner" class="archives-inner-wrapper">
							<?php
								if ( current_user_can( 'publish_posts' ) ) {
								?>
								<li>
									<a href="<?php print esc_url( admin_url( 'post-new.php' ) ); ?>" id="new-post-start-menu-link">
										<img src="<?php print esc_url( get_theme_mod( 'redmond_default_new_icon' , REDMONDURI . '/resources/new.ico' ) ); ?>" />
										<?php esc_html_e( 'New Post', RTEXTDOMAIN ); ?>
									</a>
								</li>
								<li class="seperator"></li>
								<?php
								}
								foreach ( $archives_month as $month ) {
									print '<li><img src="' . esc_url( get_theme_mod( 'redmond_archives_icon' , REDMONDURI . '/resources/archives.ico' ) ) . '" />' . esc_html( date_i18n( 'F Y', strtotime( $month->timestamp ) ) ) . '<span style="margin-left: 20px;" class="glyphicon glyphicon-play pull-right"></span>';
									$ar_query = 'SELECT
										' . $table . '.ID,
										' . $table . '.post_title
									FROM
										' . $table . '
									WHERE
										' . $table . '.post_type = \'post\'
									AND
										MONTH( ' . $table . '.post_date_gmt ) = %s
									AND
										YEAR( ' . $table . '.post_date_gmt ) = %s
									AND
										' . $table . '.post_status = \'publish\'';
									$ar_posts = $wpdb->get_results( $wpdb->prepare( $ar_query, array( $month->month, $month->year ) ) );
									?>
									<div class="archives-outer-wrapper">
										<ul class="archives-inner-wrapper">
										<?php
										foreach ( $ar_posts as $p ) {
										?>
											<li>
												<a href="<?php print esc_url( get_permalink( $p->ID ) ); ?>" class="post-link" data-post-id="<?php print intval( $p->ID ); ?>" title="<?php print esc_html( $p->post_title ); ?>">
													<img src="<?php print esc_url( redmond_get_post_icon( $p->ID ) ); ?>" />
													<?php print esc_html( substr( esc_html( $p->post_title ) , 0 , 20 ) ); ?>
												</a>
											</li>
										<?php
										}
										?>
										</ul>
									</div>
									<?php
										print '</li>' . "\r\n";
									}
									?>
								</ul>
							</div>
						</li>
				</ul>
				<div id="start-menu-login-bar-outer">
					<div id="start-menu-login-bar-inner">
					<?php
						if ( is_user_logged_in() ) {
					?>
					<a class="start-menu-bottom-bar-command logout" id="start-menu-bottom-bar-logout" href="<?php print esc_url( wp_logout_url( home_url() ) ); ?>">
						<img src="<?php print esc_url( get_theme_mod( 'redmond_logout_icon' , REDMONDURI . '/resources/logout.ico' ) ); ?>" />
						<?php esc_html_e( 'Log Off', RTEXTDOMAIN ); ?>
					</a>
					<?php
						}
						else {
						if ( get_option( 'users_can_register' ) ) {
					?>
					<a class="start-menu-bottom-bar-command register" id="start-menu-bottom-bar-register" href="<?php print esc_url( wp_registration_url() ); ?>">
						<img src="<?php print esc_url( get_theme_mod( 'redmond_register_icon' , REDMONDURI . '/resources/register.ico' ) ); ?>" />
						<?php esc_html_e( 'Register', RTEXTDOMAIN ); ?>
					</a>
					<?php
						}
					?>
					<a class="start-menu-bottom-bar-command login" id="start-menu-bottom-bar-login" href="<?php print esc_url( wp_login_url( home_url() ) ); ?>">
						<img src="<?php print esc_url( get_theme_mod( 'redmond_logout_icon' , REDMONDURI . '/resources/logout.ico' ) ); ?>" />
						<?php esc_html_e( 'Log In', RTEXTDOMAIN ); ?>
					</a>
					<?php
						}
					?>
					</div>
				</div>
			</div>
		</div>
	       <div id="desktop-area">
		       <div id="desktop-icons">
			       <?php
			       redmond_generate_folder_shortcuts_from_menu( 'desktop' );
			       ?>
		       </div>
		       <div id="desktop-window-area">
			       <div id="modal-holder"></div>
		       </div>
	       </div>
	       <?php
	       //print('<pre>');
	       //print_r( wp_kses_allowed_html( 'post' ) );
	       //print('</pre>');
	       ?>
