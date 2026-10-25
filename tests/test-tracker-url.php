<?php
/**
 * PLUGINS_SMALL-3: no tracker URL, or the old placeholder, means no tracking output and a notice.
 */

class Test_Tracker_Url extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		delete_option( 'umami_tracking_url' );
		update_option( 'umami_tracking_website_id', 'abc-123' );
		wp_dequeue_script( 'umami-tracking-loader' );
		wp_deregister_script( 'umami-tracking-loader' );
		wp_set_current_user( 0 );
	}

	/**
	 * Inline script attached to the loader, or '' when the loader was not enqueued.
	 */
	private function loader_output() {
		Umami_Tracking::instance()->enqueue_tracking_script();
		if ( ! wp_script_is( 'umami-tracking-loader', 'enqueued' ) ) {
			return '';
		}
		return implode( "\n", (array) wp_scripts()->get_data( 'umami-tracking-loader', 'after' ) );
	}

	private function notice_output() {
		ob_start();
		Umami_Tracking::instance()->render_not_configured_notice();
		return ob_get_clean();
	}

	public function test_no_url_saved_outputs_nothing() {
		$this->assertSame( '', Umami_Tracking::tracker_url() );
		$this->assertFalse( Umami_Tracking::is_configured() );
		$this->assertSame( '', $this->loader_output() );
	}

	public function test_no_placeholder_default_on_the_option() {
		Umami_Tracking::instance()->register_settings();
		$this->assertSame( '', get_option( 'umami_tracking_url' ) );
	}

	/**
	 * @dataProvider placeholder_urls
	 */
	public function test_saved_placeholder_counts_as_unset( $url ) {
		update_option( 'umami_tracking_url', $url );
		$this->assertSame( '', Umami_Tracking::tracker_url() );
		$this->assertSame( '', $this->loader_output() );
		$this->assertStringNotContainsString( 'example.', $this->loader_output() );
	}

	public function placeholder_urls() {
		return array(
			'old default'     => array( 'https://analytics.example.com/script.js' ),
			'bare example'    => array( 'https://example.com/script.js' ),
			'example.org'     => array( 'https://stats.example.org/script.js' ),
			'upper case host' => array( 'https://Analytics.Example.COM/script.js' ),
			'no host'         => array( 'script.js' ),
		);
	}

	public function test_real_url_is_output() {
		update_option( 'umami_tracking_url', 'https://stats.fullworks.net/script.js' );
		$this->assertTrue( Umami_Tracking::is_configured() );
		$out = $this->loader_output();
		$this->assertStringContainsString( 'https://stats.fullworks.net/script.js', $out );
		$this->assertStringContainsString( 'abc-123', $out );
	}

	public function test_lookalike_domain_is_not_a_placeholder() {
		update_option( 'umami_tracking_url', 'https://myexample.com/script.js' );
		$this->assertSame( 'https://myexample.com/script.js', Umami_Tracking::tracker_url() );
	}

	public function test_no_website_id_outputs_nothing() {
		update_option( 'umami_tracking_url', 'https://stats.fullworks.net/script.js' );
		update_option( 'umami_tracking_website_id', '' );
		$this->assertSame( '', $this->loader_output() );
	}

	public function test_custom_events_script_not_output_when_unconfigured() {
		update_option( 'umami_tracking_custom_events_script', 'window.marker = 1;' );
		$this->assertStringNotContainsString( 'window.marker', $this->loader_output() );
	}

	public function test_notice_shown_to_admin_when_unconfigured() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'umami_tracking_url', 'https://analytics.example.com/script.js' );
		$out = $this->notice_output();
		$this->assertStringContainsString( 'not recording any visits', $out );
		$this->assertStringContainsString( 'options-general.php?page=umami-tracking-settings', $out );
	}

	public function test_notice_hidden_when_configured() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'umami_tracking_url', 'https://stats.fullworks.net/script.js' );
		$this->assertSame( '', $this->notice_output() );
	}

	public function test_notice_hidden_from_non_admins() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );
		$this->assertSame( '', $this->notice_output() );
	}

	public function test_settings_field_does_not_prefill_placeholder() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		update_option( 'umami_tracking_url', 'https://analytics.example.com/script.js' );
		ob_start();
		Umami_Tracking::instance()->render_settings_page();
		$html = ob_get_clean();
		$this->assertMatchesRegularExpression( '/name="umami_tracking_url"\s+value=""/', $html );
		$this->assertStringContainsString( 'placeholder="https://analytics.example.com/script.js"', $html );
	}
}
