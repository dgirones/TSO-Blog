<?php
/**
 * Customizer control: generic repeater (add/remove/reorder rows with fields).
 * Usado por Equipo, Precios y Landing (destacados).
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generic repeater control. El valor se guarda como JSON en la setting.
 *
 * Uso:
 *   new Tsotheme_Customize_Repeater_Control( $wp_customize, 'mi_setting', array(
 *       'label'      => __( 'Mis filas', 'tso-blog' ),
 *       'section'    => 'mi_seccion',
 *       'row_label'  => __( 'Fila', 'tso-blog' ),
 *       'add_label'  => __( 'Añadir fila', 'tso-blog' ),
 *       'fields'     => array(
 *           array( 'key' => 'name', 'type' => 'text', 'label' => __( 'Nombre', 'tso-blog' ) ),
 *           array( 'key' => 'photo', 'type' => 'image', 'label' => __( 'Foto', 'tso-blog' ) ),
 *           array( 'key' => 'active', 'type' => 'checkbox', 'label' => __( 'Destacado', 'tso-blog' ) ),
 *       ),
 *   ) );
 */
class Tsotheme_Customize_Repeater_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'tsotheme_repeater';

	/**
	 * Field definitions.
	 *
	 * @var array
	 */
	public $fields = array();

	/**
	 * Label for a single row (used in the "add" button).
	 *
	 * @var string
	 */
	public $row_label = '';

	/**
	 * Label for the "add row" button.
	 *
	 * @var string
	 */
	public $add_label = '';

	/**
	 * Enqueue control scripts/styles.
	 */
	public function enqueue() {
		$theme_uri = get_template_directory_uri();
		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style(
			'tsothm-customize-repeater',
			$theme_uri . '/tso-customize-repeater.css',
			array(),
			tsothm_get_asset_version( 'tso-customize-repeater.css' )
		);
		wp_enqueue_script(
			'tsothm-customize-repeater',
			$theme_uri . '/tso-customize-repeater.js',
			array( 'jquery', 'jquery-ui-sortable', 'customize-controls', 'wp-util', 'media-editor' ),
			tsothm_get_asset_version( 'tso-customize-repeater.js' ),
			true
		);
		wp_localize_script(
			'tsothm-customize-repeater',
			'tsoRepeaterL10n',
			array(
				'selectImage' => __( 'Seleccionar imagen', 'tso-blog' ),
				'removeImage' => __( 'Quitar', 'tso-blog' ),
				'chooseImage' => __( 'Elegir imagen', 'tso-blog' ),
			)
		);
	}

	/**
	 * Current rows (decoded), always as a plain array of assoc arrays.
	 *
	 * @return array
	 */
	protected function get_rows() {
		$raw = $this->value();
		$rows = json_decode( is_string( $raw ) ? $raw : '', true );
		return is_array( $rows ) ? array_values( $rows ) : array();
	}

	/**
	 * Render the control.
	 */
	public function render_content() {
		$rows       = $this->get_rows();
		$control_id = 'tso-repeater-' . $this->id;
		?>
		<?php if ( ! empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
		<?php endif; ?>

		<div
			class="tso-repeater"
			id="<?php echo esc_attr( $control_id ); ?>"
			data-fields="<?php echo esc_attr( wp_json_encode( $this->fields ) ); ?>"
			data-add-label="<?php echo esc_attr( $this->add_label ? $this->add_label : __( 'Añadir fila', 'tso-blog' ) ); ?>"
		>
			<ul class="tso-repeater-list"></ul>
			<button type="button" class="button tso-repeater-add">
				<?php echo esc_html( $this->add_label ? $this->add_label : __( 'Añadir fila', 'tso-blog' ) ); ?>
			</button>
			<input
				type="hidden"
				class="tso-repeater-input"
				value="<?php echo esc_attr( wp_json_encode( $rows ) ); ?>"
				<?php $this->link(); ?>
			/>
		</div>
		<?php
	}
}
