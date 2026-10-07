<?php
/**
 * Customizer control: drag-and-drop social icon order.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drag-and-drop social order control.
 */
class Tsotheme_Customize_Social_Order_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'tsotheme_social_order';

	/**
	 * Enqueue control scripts/styles.
	 */
	public function enqueue() {
		$theme_uri = get_template_directory_uri();
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style(
			'tsothm-customize-social',
			$theme_uri . '/tso-customize-social.css',
			array(),
			tsothm_get_asset_version( 'tso-customize-social.css' )
		);
		wp_enqueue_script(
			'tsothm-customize-social',
			$theme_uri . '/tso-customize-social.js',
			array( 'jquery', 'jquery-ui-sortable', 'customize-controls' ),
			tsothm_get_asset_version( 'tso-customize-social.js' ),
			true
		);
	}

	/**
	 * Render the control.
	 */
	public function render_content() {
		$networks = tsothm_get_social_networks();
		$order    = tsothm_get_social_order_keys();
		?>
		<?php if ( ! empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>
		<ul class="tso-social-order-list" id="tso-social-order-list">
			<?php foreach ( $order as $key ) : ?>
				<?php if ( ! isset( $networks[ $key ] ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<li class="tso-social-order-item" data-key="<?php echo esc_attr( $key ); ?>">
					<span class="tso-social-order-handle dashicons dashicons-menu" aria-hidden="true"></span>
					<span class="tso-social-order-label"><?php echo esc_html( $networks[ $key ]['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<input
			type="hidden"
			class="tso-social-order-input"
			value="<?php echo esc_attr( implode( ',', $order ) ); ?>"
			<?php $this->link(); ?>
		/>
		<?php
	}
}
