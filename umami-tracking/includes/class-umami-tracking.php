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
        add_action( 'wp_head', array( $this, 'add_tracking_script' ), 1 );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_tracking_script() {
        $website_id = get_option( 'umami_tracking_website_id' );
        if ( ! empty( $website_id ) ) {
            ?>
            <script defer src="https://analytics.fw9.uk/script.js" data-website-id="<?php echo esc_attr( $website_id ); ?>"></script>
            <?php
        }
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
