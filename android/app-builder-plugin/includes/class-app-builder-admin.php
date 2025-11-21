<?php
class App_Builder_Admin {
    private $plugin_path;
    private $scripts_root;
    private $option_key = 'android_app_builder_settings';

    public function __construct($plugin_path) {
        $this->plugin_path = rtrim($plugin_path, DIRECTORY_SEPARATOR);
        $this->scripts_root = realpath(dirname($this->plugin_path));
    }

    public function hooks() {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_app_builder_generate_keystore', [$this, 'handle_keystore_generation']);
        add_action('admin_post_app_builder_trigger_build', [$this, 'handle_build_request']);
        add_action('admin_post_app_builder_refresh_sdk', [$this, 'handle_sdk_refresh']);
    }

    public function register_menu() {
        add_menu_page(
            __('Android App Builder', 'android-app-builder'),
            __('Android Builder', 'android-app-builder'),
            'manage_options',
            'android-app-builder',
            [$this, 'render_admin_page'],
            'dashicons-smartphone',
            61
        );
    }

    public function register_settings() {
        register_setting($this->option_key, $this->option_key, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_settings'],
            'default' => [
                'app_name' => '',
                'package_name' => '',
                'version_name' => '1.0.0',
                'version_code' => '1',
                'webview_url' => '',
                'keystore_path' => '',
            ],
        ]);

        add_settings_section(
            'android_app_builder_main',
            __('Publishing profile', 'android-app-builder'),
            function () {
                echo '<p>' . esc_html__('Configure the WebView shell and signing profile used for builds.', 'android-app-builder') . '</p>';
            },
            'android-app-builder'
        );

        $fields = [
            'app_name' => __('App name', 'android-app-builder'),
            'package_name' => __('Application ID (package)', 'android-app-builder'),
            'version_name' => __('Version name', 'android-app-builder'),
            'version_code' => __('Version code', 'android-app-builder'),
            'webview_url' => __('WebView entry URL', 'android-app-builder'),
        ];

        foreach ($fields as $key => $label) {
            add_settings_field(
                $key,
                $label,
                function () use ($key) {
                    $value = $this->get_setting($key);
                    printf('<input type="text" name="%1$s[%2$s]" value="%3$s" class="regular-text" />', esc_attr($this->option_key), esc_attr($key), esc_attr($value));
                },
                'android-app-builder',
                'android_app_builder_main'
            );
        }
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        settings_errors();
        $keystore_path = $this->get_setting('keystore_path');
        $keystore_exists = $keystore_path && file_exists($keystore_path);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Android WebView Builder', 'android-app-builder'); ?></h1>
            <p><?php echo esc_html__('Generate signed WebView APKs and App Bundles directly from WordPress.', 'android-app-builder'); ?></p>

            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_key);
                do_settings_sections('android-app-builder');
                submit_button(__('Save publishing profile', 'android-app-builder'));
                ?>
            </form>

            <hr />
            <h2><?php echo esc_html__('Keystore', 'android-app-builder'); ?></h2>
            <p><?php echo esc_html__('Create a signing key for release builds. The key material stays on this server.', 'android-app-builder'); ?></p>
            <p><strong><?php echo esc_html__('Status:', 'android-app-builder'); ?></strong> <?php echo $keystore_exists ? esc_html__('Keystore ready', 'android-app-builder') : esc_html__('No keystore detected', 'android-app-builder'); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('app_builder_generate_keystore'); ?>
                <input type="hidden" name="action" value="app_builder_generate_keystore" />
                <?php submit_button(__('Generate keystore', 'android-app-builder'), 'secondary', 'submit', false); ?>
            </form>

            <hr />
            <h2><?php echo esc_html__('Build outputs', 'android-app-builder'); ?></h2>
            <p><?php echo esc_html__('Kick off a WebView build. Outputs are written to the android/output directory.', 'android-app-builder'); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex;gap:12px;align-items:center;">
                <?php wp_nonce_field('app_builder_trigger_build'); ?>
                <input type="hidden" name="action" value="app_builder_trigger_build" />
                <label for="build_target" class="screen-reader-text"><?php echo esc_html__('Build type', 'android-app-builder'); ?></label>
                <select name="build_target" id="build_target">
                    <option value="apk"><?php echo esc_html__('Release APK', 'android-app-builder'); ?></option>
                    <option value="aab"><?php echo esc_html__('Play Store AAB', 'android-app-builder'); ?></option>
                </select>
                <?php submit_button(__('Start build', 'android-app-builder'), 'primary', 'submit', false); ?>
            </form>

            <hr />
            <h2><?php echo esc_html__('SDK maintenance', 'android-app-builder'); ?></h2>
            <p><?php echo esc_html__('Refresh cached Android SDK metadata before a new cycle. This is safe to run anytime.', 'android-app-builder'); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('app_builder_refresh_sdk'); ?>
                <input type="hidden" name="action" value="app_builder_refresh_sdk" />
                <?php submit_button(__('Refresh SDK packages', 'android-app-builder'), 'secondary', 'submit', false); ?>
            </form>
        </div>
        <?php
    }

    public function handle_keystore_generation() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not allowed to perform this action.', 'android-app-builder'));
        }
        check_admin_referer('app_builder_generate_keystore');

        $keystore_dir = $this->scripts_root . '/output';
        if (!is_dir($keystore_dir)) {
            wp_mkdir_p($keystore_dir);
        }
        $keystore_path = $keystore_dir . '/app-release-keystore.pem';

        $dn = [
            'countryName' => 'US',
            'stateOrProvinceName' => 'Remote',
            'localityName' => 'Automation',
            'organizationName' => 'Android Builder',
            'organizationalUnitName' => 'WordPress',
            'commonName' => $this->get_setting('app_name') ?: 'WebView App',
            'emailAddress' => get_bloginfo('admin_email'),
        ];

        $privkey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $csr = openssl_csr_new($dn, $privkey, ['digest_alg' => 'sha256']);
        $sscert = openssl_csr_sign($csr, null, $privkey, 3650, ['digest_alg' => 'sha256']);

        openssl_x509_export($sscert, $certout);
        openssl_pkey_export($privkey, $pkeyout);

        file_put_contents($keystore_path, $pkeyout . "\n" . $certout);

        $settings = get_option($this->option_key, []);
        $settings['keystore_path'] = $keystore_path;
        update_option($this->option_key, $settings);

        add_settings_error('app_builder', 'keystore_created', __('Keystore generated and stored in android/output.', 'android-app-builder'), 'updated');
        wp_safe_redirect(admin_url('admin.php?page=android-app-builder'));
        exit;
    }

    public function handle_build_request() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not allowed to perform this action.', 'android-app-builder'));
        }
        check_admin_referer('app_builder_trigger_build');

        $target = isset($_POST['build_target']) ? sanitize_text_field(wp_unslash($_POST['build_target'])) : 'apk';
        $target = in_array($target, ['apk', 'aab'], true) ? $target : 'apk';

        $script = $this->scripts_root . '/scripts/build-webview.sh';
        $output = [];
        $return_var = 0;
        $settings = get_option($this->option_key, []);

        $env = [
            'APP_NAME' => $this->get_setting('app_name'),
            'PACKAGE_NAME' => $this->get_setting('package_name'),
            'VERSION_NAME' => $this->get_setting('version_name'),
            'VERSION_CODE' => $this->get_setting('version_code'),
            'WEBVIEW_URL' => $this->get_setting('webview_url'),
            'KEYSTORE_PATH' => $this->get_setting('keystore_path'),
        ];

        $env_string = '';
        foreach ($env as $key => $value) {
            $env_string .= sprintf('%s=%s ', escapeshellcmd($key), escapeshellarg($value));
        }

        $command = $env_string . escapeshellarg($script) . ' ' . escapeshellarg($target) . ' 2>&1';
        exec($command, $output, $return_var);

        if (0 === $return_var) {
            add_settings_error('app_builder', 'build_success', __('Build started. Check android/output for artifacts.', 'android-app-builder'), 'updated');
        } else {
            add_settings_error('app_builder', 'build_failed', sprintf(__('Build failed: %s', 'android-app-builder'), implode("\n", $output)));
        }

        wp_safe_redirect(admin_url('admin.php?page=android-app-builder'));
        exit;
    }

    public function handle_sdk_refresh() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not allowed to perform this action.', 'android-app-builder'));
        }
        check_admin_referer('app_builder_refresh_sdk');

        $script = $this->scripts_root . '/scripts/refresh-sdk.sh';
        $output = [];
        $return_var = 0;
        exec(escapeshellarg($script) . ' 2>&1', $output, $return_var);

        if (0 === $return_var) {
            add_settings_error('app_builder', 'sdk_refreshed', __('SDK metadata refreshed.', 'android-app-builder'), 'updated');
        } else {
            add_settings_error('app_builder', 'sdk_failed', __('SDK refresh failed.', 'android-app-builder'));
        }

        wp_safe_redirect(admin_url('admin.php?page=android-app-builder'));
        exit;
    }

    private function get_setting($key) {
        $settings = get_option($this->option_key, []);
        return isset($settings[$key]) ? $settings[$key] : '';
    }

    public function sanitize_settings($settings) {
        $sanitized = [];
        $fields = ['app_name', 'package_name', 'version_name', 'version_code', 'webview_url', 'keystore_path'];

        foreach ($fields as $field) {
            $sanitized[$field] = isset($settings[$field]) ? sanitize_text_field($settings[$field]) : '';
        }

        return $sanitized;
    }
}
