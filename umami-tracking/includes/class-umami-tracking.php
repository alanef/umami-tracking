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
    }

    public function enqueue_tracking_script() {
        $website_id = get_option( 'umami_tracking_website_id' );
        if ( ! empty( $website_id ) ) {
            wp_enqueue_script(
                'umami-tracking',
                'https://analytics.fw9.uk/script.js',
                array(),
                UMAMI_TRACKING_VERSION,
                false // Load in head, not footer
            );
        }
    }

    public function add_script_attributes( $tag, $handle ) {
        if ( 'umami-tracking' === $handle ) {
            $website_id = get_option( 'umami_tracking_website_id' );
            if ( ! empty( $website_id ) ) {
                $tag = str_replace( ' src=', ' defer data-website-id="' . esc_attr( $website_id ) . '" src=', $tag );
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
    }

    public function render_settings_page() {
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
                        <th scope="row">Website ID</th>
                        <td><input type="text" name="umami_tracking_website_id" value="<?php echo esc_attr( get_option( 'umami_tracking_website_id' ) ); ?>"/></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
