<?php

class Umami_Tracking {

    private static $instance;

    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_tracking_script' ) );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_filter( 'script_loader_tag', array( $this, 'add_script_attributes' ), 10, 2 );
        add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_toggle' ), 999 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_self_exclusion_script' ) );
    }

    public function enqueue_tracking_script() {
        // Check if user should be tracked based on role
        if ( ! $this->should_track_user() ) {
            return;
        }

        $website_id = get_option( 'umami_tracking_website_id' );
        $tracker_url = get_option( 'umami_tracking_url', 'https://analytics.example.com/script.js' );
        
        if ( ! empty( $website_id ) && ! empty( $tracker_url ) ) {
            // Add inline script to check localStorage before loading Umami
            $inline_script = "
            (function() {
                // Check if user has self-excluded via localStorage
                if (localStorage.getItem('umami.disabled') === '1') {
                    return; // Don't load Umami tracking
                }
                
                // Load Umami tracking script dynamically
                var script = document.createElement('script');
                script.defer = true;
                script.src = '" . esc_url( $tracker_url ) . "';
                script.setAttribute('data-website-id', '" . esc_attr( $website_id ) . "');";
                
            // Add optional attributes
            $host_url = get_option( 'umami_tracking_host_url' );
            if ( ! empty( $host_url ) ) {
                $inline_script .= "\n                script.setAttribute('data-host-url', '" . esc_url( $host_url ) . "');";
            }
            
            if ( get_option( 'umami_tracking_do_not_track', false ) ) {
                $inline_script .= "\n                script.setAttribute('data-do-not-track', 'true');";
            }
            
            $domains = get_option( 'umami_tracking_domains' );
            if ( ! empty( $domains ) ) {
                $inline_script .= "\n                script.setAttribute('data-domains', '" . esc_attr( $domains ) . "');";
            }
            
            $inline_script .= "
                document.head.appendChild(script);
            })();
            ";
            
            // Add the inline script
            wp_add_inline_script( 'wp-hooks', $inline_script, 'before' );
            
            // Ensure wp-hooks is enqueued
            wp_enqueue_script( 'wp-hooks' );

            // Enqueue external link tracking if enabled
            if ( get_option( 'umami_tracking_track_external_links', false ) ) {
                $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
                
                // Also load external link tracking conditionally
                $external_link_script = "
                (function() {
                    // Only load if not self-excluded
                    if (localStorage.getItem('umami.disabled') !== '1') {
                        var checkUmami = setInterval(function() {
                            if (typeof umami !== 'undefined') {
                                clearInterval(checkUmami);
                                var script = document.createElement('script');
                                script.src = '" . esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/external-link-tracking' . $suffix . '.js' ) . "';
                                document.body.appendChild(script);
                            }
                        }, 100);
                        
                        // Stop checking after 5 seconds
                        setTimeout(function() {
                            clearInterval(checkUmami);
                        }, 5000);
                    }
                })();
                ";
                
                wp_add_inline_script( 'wp-hooks', $external_link_script, 'after' );
            }
        }
    }

    private function should_track_user() {
        // Always track non-logged-in users
        if ( ! is_user_logged_in() ) {
            return true;
        }

        // Get excluded roles
        $excluded_roles = get_option( 'umami_tracking_excluded_roles', array( 'administrator', 'editor' ) );
        
        // Get current user
        $user = wp_get_current_user();
        
        // Check if any of the user's roles are excluded
        foreach ( $user->roles as $role ) {
            if ( in_array( $role, $excluded_roles, true ) ) {
                return false;
            }
        }

        return true;
    }

    public function add_script_attributes( $tag, $handle ) {
        if ( 'umami-tracking' === $handle ) {
            $website_id = get_option( 'umami_tracking_website_id' );
            $host_url = get_option( 'umami_tracking_host_url' );
            $do_not_track = get_option( 'umami_tracking_do_not_track', false );
            $domains = get_option( 'umami_tracking_domains' );
            
            if ( ! empty( $website_id ) ) {
                // Start building attributes
                $attributes = ' defer data-website-id="' . esc_attr( $website_id ) . '"';
                
                // Add optional attributes
                if ( ! empty( $host_url ) ) {
                    $attributes .= ' data-host-url="' . esc_url( $host_url ) . '"';
                }
                
                if ( $do_not_track ) {
                    $attributes .= ' data-do-not-track="true"';
                }
                
                if ( ! empty( $domains ) ) {
                    $attributes .= ' data-domains="' . esc_attr( $domains ) . '"';
                }
                
                $tag = str_replace( ' src=', $attributes . ' src=', $tag );
            }
        }
        return $tag;
    }

    public function add_settings_page() {
        add_options_page(
            'Umami Tracking Settings',
            'Umami Tracking',
            'manage_options',
            'umami-tracking-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting(
            'umami_tracking_settings',
            'umami_tracking_website_id',
            array(
                'sanitize_callback' => 'sanitize_text_field',
            )
        );

        register_setting(
            'umami_tracking_settings',
            'umami_tracking_excluded_roles',
            array(
                'sanitize_callback' => array( $this, 'sanitize_excluded_roles' ),
                'default' => array( 'administrator', 'editor' ),
            )
        );

        register_setting(
            'umami_tracking_settings',
            'umami_tracking_url',
            array(
                'sanitize_callback' => 'esc_url_raw',
                'default' => 'https://analytics.example.com/script.js',
            )
        );

        register_setting(
            'umami_tracking_settings',
            'umami_tracking_host_url',
            array(
                'sanitize_callback' => 'esc_url_raw',
            )
        );

        register_setting(
            'umami_tracking_settings',
            'umami_tracking_do_not_track',
            array(
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => false,
            )
        );

        register_setting(
            'umami_tracking_settings',
            'umami_tracking_domains',
            array(
                'sanitize_callback' => 'sanitize_text_field',
            )
        );

        register_setting(
            'umami_tracking_settings',
            'umami_tracking_track_external_links',
            array(
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => false,
            )
        );

        register_setting(
            'umami_tracking_settings',
            'umami_tracking_enable_self_exclusion',
            array(
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => true,
            )
        );

        register_setting(
            'umami_tracking_settings',
            'umami_tracking_show_exclusion_button',
            array(
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => false,
            )
        );
    }

    public function sanitize_excluded_roles( $input ) {
        if ( ! is_array( $input ) ) {
            return array();
        }

        $valid_roles = array_keys( wp_roles()->roles );
        $sanitized = array();

        foreach ( $input as $role ) {
            if ( in_array( $role, $valid_roles, true ) ) {
                $sanitized[] = $role;
            }
        }

        return $sanitized;
    }

    public function render_settings_page() {
        $excluded_roles = get_option( 'umami_tracking_excluded_roles', array( 'administrator', 'editor' ) );
        ?>
        <div class="wrap">
            <h1>Umami Tracking Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'umami_tracking_settings' );
                do_settings_sections( 'umami_tracking_settings' );
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Website ID', 'umami-tracking' ); ?></th>
                        <td>
                            <input type="text" 
                                   name="umami_tracking_website_id" 
                                   value="<?php echo esc_attr( get_option( 'umami_tracking_website_id' ) ); ?>"
                                   class="regular-text"
                                   required />
                            <p class="description"><?php esc_html_e( 'Your Umami website ID (required)', 'umami-tracking' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Tracker Script URL', 'umami-tracking' ); ?></th>
                        <td>
                            <input type="url" 
                                   name="umami_tracking_url" 
                                   value="<?php echo esc_url( get_option( 'umami_tracking_url', 'https://analytics.example.com/script.js' ) ); ?>"
                                   class="regular-text code" />
                            <p class="description"><?php esc_html_e( 'URL to your Umami tracking script (e.g., https://analytics.example.com/script.js)', 'umami-tracking' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Host URL', 'umami-tracking' ); ?></th>
                        <td>
                            <input type="url" 
                                   name="umami_tracking_host_url" 
                                   value="<?php echo esc_url( get_option( 'umami_tracking_host_url' ) ); ?>"
                                   class="regular-text code" />
                            <p class="description"><?php esc_html_e( 'Override the data collection endpoint (optional). Leave empty to use the same server as the script.', 'umami-tracking' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Domain Restrictions', 'umami-tracking' ); ?></th>
                        <td>
                            <input type="text" 
                                   name="umami_tracking_domains" 
                                   value="<?php echo esc_attr( get_option( 'umami_tracking_domains' ) ); ?>"
                                   class="regular-text" />
                            <p class="description"><?php esc_html_e( 'Comma-separated list of domains where tracking should be active (e.g., example.com,www.example.com). Leave empty to track on all domains.', 'umami-tracking' ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Tracking Features', 'umami-tracking' ); ?></th>
                        <td>
                            <fieldset>
                                <label for="umami_tracking_track_external_links">
                                    <input type="checkbox" 
                                           id="umami_tracking_track_external_links"
                                           name="umami_tracking_track_external_links" 
                                           value="1" 
                                           <?php checked( get_option( 'umami_tracking_track_external_links', false ) ); ?> />
                                    <?php esc_html_e( 'Track external link clicks', 'umami-tracking' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Automatically track when visitors click on links to external websites.', 'umami-tracking' ); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Privacy Settings', 'umami-tracking' ); ?></th>
                        <td>
                            <fieldset>
                                <label for="umami_tracking_do_not_track">
                                    <input type="checkbox" 
                                           id="umami_tracking_do_not_track"
                                           name="umami_tracking_do_not_track" 
                                           value="1" 
                                           <?php checked( get_option( 'umami_tracking_do_not_track', false ) ); ?> />
                                    <?php esc_html_e( 'Respect visitor\'s Do Not Track browser setting', 'umami-tracking' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'When enabled, visitors who have Do Not Track enabled in their browser will not be tracked.', 'umami-tracking' ); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Exclude Roles from Tracking', 'umami-tracking' ); ?></th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span><?php esc_html_e( 'Exclude Roles from Tracking', 'umami-tracking' ); ?></span>
                                </legend>
                                <?php
                                $roles = wp_roles()->roles;
                                foreach ( $roles as $role_key => $role ) {
                                    ?>
                                    <label for="umami_tracking_excluded_roles_<?php echo esc_attr( $role_key ); ?>">
                                        <input type="checkbox" 
                                               id="umami_tracking_excluded_roles_<?php echo esc_attr( $role_key ); ?>"
                                               name="umami_tracking_excluded_roles[]" 
                                               value="<?php echo esc_attr( $role_key ); ?>" 
                                               <?php checked( in_array( $role_key, $excluded_roles, true ) ); ?> />
                                        <?php echo esc_html( translate_user_role( $role['name'] ) ); ?>
                                    </label><br />
                                    <?php
                                }
                                ?>
                                <p class="description"><?php esc_html_e( 'Users with selected roles will not be tracked when logged in.', 'umami-tracking' ); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e( 'Self-Exclusion Settings', 'umami-tracking' ); ?></th>
                        <td>
                            <fieldset>
                                <label for="umami_tracking_enable_self_exclusion">
                                    <input type="checkbox" 
                                           id="umami_tracking_enable_self_exclusion"
                                           name="umami_tracking_enable_self_exclusion" 
                                           value="1" 
                                           <?php checked( get_option( 'umami_tracking_enable_self_exclusion', true ) ); ?> />
                                    <?php esc_html_e( 'Enable self-exclusion feature', 'umami-tracking' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Allow users to exclude themselves from tracking using localStorage. When enabled, the tracking script checks localStorage before loading. Logged-in users with appropriate permissions can toggle their tracking status via the admin bar.', 'umami-tracking' ); ?></p>
                                
                                <br />
                                
                                <label for="umami_tracking_show_exclusion_button">
                                    <input type="checkbox" 
                                           id="umami_tracking_show_exclusion_button"
                                           name="umami_tracking_show_exclusion_button" 
                                           value="1" 
                                           <?php checked( get_option( 'umami_tracking_show_exclusion_button', false ) ); ?> />
                                    <?php esc_html_e( 'Show floating toggle button to all visitors', 'umami-tracking' ); ?>
                                </label>
                                <p class="description"><?php esc_html_e( 'Display a floating button on frontend pages that shows the current tracking status. All visitors can see their tracking status. Logged-out visitors will be redirected to login when clicking the button. Logged-in users with appropriate permissions can toggle tracking on/off directly.', 'umami-tracking' ); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_self_exclusion_script() {
        // Check if self-exclusion is enabled
        if ( ! get_option( 'umami_tracking_enable_self_exclusion', true ) ) {
            return;
        }

        $show_button = get_option( 'umami_tracking_show_exclusion_button', false );
        $can_exclude = false;
        $show_admin_bar = false;

        if ( is_user_logged_in() ) {
            // Check if user has a role that would normally be tracked
            $excluded_roles = get_option( 'umami_tracking_excluded_roles', array( 'administrator', 'editor' ) );
            $user = wp_get_current_user();
            $is_excluded_role = false;
            
            foreach ( $user->roles as $role ) {
                if ( in_array( $role, $excluded_roles, true ) ) {
                    $is_excluded_role = true;
                    break;
                }
            }

            // Allow capability check or if user is not in excluded roles
            if ( current_user_can( 'manage_options' ) || ! $is_excluded_role ) {
                $can_exclude = true;
                $show_admin_bar = true;
            }
        }

        // Enqueue script if either the button should be shown to all users or user can exclude
        if ( $show_button || $can_exclude ) {
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            wp_enqueue_script(
                'umami-self-exclusion',
                plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/self-exclusion' . $suffix . '.js',
                array(),
                UMAMI_TRACKING_VERSION,
                true
            );

            // Localize script
            wp_localize_script( 'umami-self-exclusion', 'umamiSelfExclusion', array(
                'canExclude' => $can_exclude,
                'showButton' => $show_button,
                'showAdminBar' => $show_admin_bar,
                'isLoggedIn' => is_user_logged_in(),
                'loginUrl' => wp_login_url( get_permalink() ),
                'messages' => array(
                    'excluded' => __( 'You are now excluded from Umami tracking', 'umami-tracking' ),
                    'included' => __( 'You are now included in Umami tracking', 'umami-tracking' ),
                    'excludedButton' => __( '🚫 Tracking OFF', 'umami-tracking' ),
                    'includedButton' => __( '📊 Tracking ON', 'umami-tracking' ),
                    'adminBarExcluded' => __( '🚫 Umami: OFF', 'umami-tracking' ),
                    'adminBarIncluded' => __( '📊 Umami: ON', 'umami-tracking' ),
                    'loginRequired' => __( 'Please log in to manage your tracking preferences', 'umami-tracking' ),
                ),
            ) );

            // Add inline CSS
            wp_add_inline_style( 'admin-bar', '
                #wp-admin-bar-umami-tracking-toggle .umami-excluded {
                    background: rgba(255, 0, 0, 0.1) !important;
                }
                #wp-admin-bar-umami-tracking-toggle .umami-included {
                    background: rgba(0, 150, 0, 0.1) !important;
                }
                .umami-exclusion-toggle-button.excluded {
                    background: #666 !important;
                }
            ' );
        }
    }

    public function add_admin_bar_toggle( $wp_admin_bar ) {
        // Only show for logged-in users with appropriate capabilities
        if ( ! is_user_logged_in() || ! get_option( 'umami_tracking_enable_self_exclusion', true ) ) {
            return;
        }

        // Check if user can manage options or is not in excluded roles
        $excluded_roles = get_option( 'umami_tracking_excluded_roles', array( 'administrator', 'editor' ) );
        $user = wp_get_current_user();
        $is_excluded_role = false;
        
        foreach ( $user->roles as $role ) {
            if ( in_array( $role, $excluded_roles, true ) ) {
                $is_excluded_role = true;
                break;
            }
        }

        if ( current_user_can( 'manage_options' ) || ! $is_excluded_role ) {
            $wp_admin_bar->add_node( array(
                'id'    => 'umami-tracking-toggle',
                'title' => '<span class="ab-icon"></span><span class="ab-label">' . __( 'Umami', 'umami-tracking' ) . '</span>',
                'href'  => '#',
                'meta'  => array(
                    'title' => __( 'Toggle Umami tracking for your visits', 'umami-tracking' ),
                ),
            ) );
        }
    }
}
