<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Review Notice class
 *
 * Displays a dismissible admin notice after 14 days of usage requesting a review.
 */
class wpMandrill_ReviewNotice {

	private $plugin_name;
	private $plugin_slug;
	private $activation_option;
	private $dismissed_meta_key;
	private $review_url;
	private $text_domain;
	private $icon_url;
	private $icon_dashicon;

	public function __construct( $plugin_name, $plugin_slug, $activation_option, $text_domain, $icon_url = '', $icon_dashicon = '' ) {
		$this->plugin_name = $plugin_name;
		$this->plugin_slug = $plugin_slug;
		$this->activation_option = $activation_option;
		$this->dismissed_meta_key = $plugin_slug . '_review_dismissed';
		$this->review_url = 'https://wordpress.org/support/plugin/' . $plugin_slug . '/reviews/#new-post';
		$this->text_domain = $text_domain;
		$this->icon_url = $icon_url;
		$this->icon_dashicon = $icon_dashicon;

		add_action( 'admin_notices', array( $this, 'show_review_notice' ) );
		add_action( 'admin_init', array( $this, 'handle_dismiss' ) );
	}

	public function show_review_notice() {
		// Only show to admins
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if already dismissed
		if ( get_user_meta( get_current_user_id(), $this->dismissed_meta_key, true ) ) {
			return;
		}

		// Check activation time
		$activated = get_option( $this->activation_option );
		if ( ! $activated ) {
			return;
		}

		// Show after 14 days
		$days_active = ( time() - $activated ) / DAY_IN_SECONDS;
		if ( $days_active < 14 ) {
			return;
		}

		// Output the notice
		?>
		<div class="notice notice-info is-dismissible" id="<?php echo esc_attr( $this->plugin_slug ); ?>-review-notice" style="display: flex; align-items: center; padding: 12px;">
			<?php if ( $this->icon_url ) : ?>
				<img src="<?php echo esc_url( $this->icon_url ); ?>" alt="" style="width: 48px; height: 48px; margin-right: 16px; flex-shrink: 0;">
			<?php elseif ( $this->icon_dashicon ) : ?>
				<span class="dashicons <?php echo esc_attr( $this->icon_dashicon ); ?>" style="font-size: 48px; width: 48px; height: 48px; margin-right: 16px; flex-shrink: 0; color: #2271b1;"></span>
			<?php endif; ?>
			<div style="flex: 1;">
			<p style="margin: 0.5em 0;">
				<?php
				printf(
					/* translators: 1: plugin name, 2: opening link tag, 3: closing link tag */
					esc_html__( 'Hey! You\'ve been using %1$s for a while now. If you\'re enjoying it, would you mind %2$sleaving a 5-star review%3$s? It helps us keep improving! 🙏', 'send-emails-with-mandrill' ),
					'<strong>' . esc_html( $this->plugin_name ) . '</strong>',
					'<a href="' . esc_url( $this->review_url ) . '" target="_blank">',
					'</a>'
				);
				?>
			</p>
			<p style="margin: 0.5em 0;">
				<a href="<?php echo esc_url( $this->review_url ); ?>" class="button button-primary" target="_blank">
					<?php esc_html_e( 'Leave a Review', 'send-emails-with-mandrill' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( $this->plugin_slug . '_dismiss_review', 'true' ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Maybe Later', 'send-emails-with-mandrill' ); ?>
				</a>
			</p>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('#<?php echo esc_js( $this->plugin_slug ); ?>-review-notice').on('click', '.notice-dismiss, .button', function() {
					$.post(ajaxurl, {
						action: '<?php echo esc_js( $this->plugin_slug ); ?>_dismiss_review_notice'
					});
				});
			});
		</script>
		<?php
	}

	public function handle_dismiss() {
		// Handle URL parameter dismiss
		if ( isset( $_GET[ $this->plugin_slug . '_dismiss_review' ] ) ) {
			update_user_meta( get_current_user_id(), $this->dismissed_meta_key, true );
			wp_safe_redirect( remove_query_arg( $this->plugin_slug . '_dismiss_review' ) );
			exit;
		}

		// Handle AJAX dismiss
		add_action( 'wp_ajax_' . $this->plugin_slug . '_dismiss_review_notice', array( $this, 'ajax_dismiss' ) );
	}

	public function ajax_dismiss() {
		update_user_meta( get_current_user_id(), $this->dismissed_meta_key, true );
		wp_die();
	}
}
