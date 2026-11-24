<?php
/**
 * Customizer repeaters (team, pricing, landing highlights) and their front-end output.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   33. REPETIDORES DEL CUSTOMIZER — EQUIPO, PRECIOS, LANDING
   ============================================================
   Esquemas de campos + saneado + getters + render para las tres
   listas configurables desde Apariencia → Personalizar.
   ============================================================ */

/**
 * Field schema for the "Equipo" repeater.
 *
 * @return array
 */
function tsothm_get_team_fields() {
	return array(
		array(
			'key'   => 'photo',
			'type'  => 'image',
			'label' => __( 'Photo', 'tso-blog' ),
		),
		array(
			'key'   => 'name',
			'type'  => 'text',
			'label' => __( 'Name', 'tso-blog' ),
		),
		array(
			'key'   => 'role',
			'type'  => 'text',
			'label' => __( 'Position / role', 'tso-blog' ),
		),
		array(
			'key'   => 'link',
			'type'  => 'text',
			'label' => __( 'Link (website, LinkedIn…) — optional', 'tso-blog' ),
		),
	);
}

/**
 * Field schema for the "Precios" repeater.
 *
 * @return array
 */
function tsothm_get_pricing_fields() {
	return array(
		array(
			'key'   => 'title',
			'type'  => 'text',
			'label' => __( 'Plan name', 'tso-blog' ),
		),
		array(
			'key'   => 'price',
			'type'  => 'text',
			'label' => __( 'Price (e.g. €19.99)', 'tso-blog' ),
		),
		array(
			'key'   => 'period',
			'type'  => 'text',
			'label' => __( 'Period (e.g. /month) — optional', 'tso-blog' ),
		),
		array(
			'key'   => 'features',
			'type'  => 'text',
			'label' => __( 'Features, separated by |', 'tso-blog' ),
		),
		array(
			'key'   => 'button_text',
			'type'  => 'text',
			'label' => __( 'Button text', 'tso-blog' ),
		),
		array(
			'key'   => 'button_url',
			'type'  => 'text',
			'label' => __( 'Button URL', 'tso-blog' ),
		),
		array(
			'key'   => 'featured',
			'type'  => 'checkbox',
			'label' => __( 'Highlight this plan', 'tso-blog' ),
		),
	);
}

/**
 * Field schema for the "Landing" highlights repeater.
 *
 * @return array
 */
function tsothm_get_landing_highlight_fields() {
	return array(
		array(
			'key'   => 'icon',
			'type'  => 'text',
			'label' => __( 'Icon (an emoji, e.g. 🚀)', 'tso-blog' ),
		),
		array(
			'key'   => 'title',
			'type'  => 'text',
			'label' => __( 'Short title', 'tso-blog' ),
		),
		array(
			'key'   => 'text',
			'type'  => 'text',
			'label' => __( 'Short text', 'tso-blog' ),
		),
	);
}

/**
 * Sanitize a single field value according to its schema type.
 *
 * @param mixed $value Raw value.
 * @param array $field Field schema (key/type).
 * @return mixed
 */
function tsothm_sanitize_repeater_field_value( $value, $field ) {
	switch ( $field['type'] ) {
		case 'textarea':
			return sanitize_textarea_field( is_string( $value ) ? $value : '' );
		case 'checkbox':
			return (bool) $value;
		case 'image':
			return absint( $value );
		case 'text':
		default:
			$text = is_string( $value ) ? $value : '';
			// Older pricing rows stored one feature per line. Keep them as separate items.
			if ( isset( $field['key'] ) && 'features' === $field['key'] ) {
				$text = str_replace( array( "\r\n", "\r", "\n" ), ' | ', $text );
			}
			// Los campos de texto que parecen URL (link, button_url) se sanean como URL.
			if ( isset( $field['key'] ) && false !== strpos( $field['key'], 'url' ) ) {
				return esc_url_raw( $text );
			}
			if ( isset( $field['key'] ) && 'link' === $field['key'] ) {
				return esc_url_raw( $text );
			}
			return sanitize_text_field( $text );
	}
}

/**
 * Generic repeater sanitizer: decodes JSON, keeps only known fields,
 * sanitizes each value by type, re-encodes to JSON.
 *
 * @param string $value  Raw JSON value from the Customizer control.
 * @param array  $fields Field schema (tsothm_get_*_fields()).
 * @param int    $max    Maximum rows. Pricing uses 5; other repeaters default to 50.
 * @return string Sanitized JSON.
 */
function tsothm_sanitize_repeater_json( $value, $fields, $max = 50 ) {
	$rows = json_decode( is_string( $value ) ? $value : '', true );
	if ( ! is_array( $rows ) ) {
		return wp_json_encode( array() );
	}

	$max = absint( $max );
	if ( $max < 1 ) {
		$max = 50;
	}

	$clean = array();
	foreach ( array_slice( $rows, 0, $max ) as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$clean_row = array();
		foreach ( $fields as $field ) {
			$key                = $field['key'];
			$clean_row[ $key ] = tsothm_sanitize_repeater_field_value(
				isset( $row[ $key ] ) ? $row[ $key ] : '',
				$field
			);
		}
		$clean[] = $clean_row;
	}

	return wp_json_encode( $clean );
}

/**
 * Sanitize callback: Equipo.
 *
 * @param string $value Raw value.
 * @return string
 */
function tsothm_sanitize_team_members( $value ) {
	return tsothm_sanitize_repeater_json( $value, tsothm_get_team_fields() );
}

/**
 * Sanitize callback: Precios.
 *
 * @param string $value Raw value.
 * @return string
 */
function tsothm_sanitize_pricing_plans( $value ) {
	return tsothm_sanitize_repeater_json( $value, tsothm_get_pricing_fields(), 5 );
}

/**
 * Sanitize callback: Landing (destacados).
 *
 * @param string $value Raw value.
 * @return string
 */
function tsothm_sanitize_landing_highlights( $value ) {
	return tsothm_sanitize_repeater_json( $value, tsothm_get_landing_highlight_fields() );
}

/**
 * Decode + defensively re-sanitize a repeater theme_mod.
 *
 * @param string $mod_name Theme mod name.
 * @param array  $fields   Field schema.
 * @param int    $max      Maximum rows to read (pricing uses 5).
 * @return array
 */
function tsothm_get_repeater_rows( $mod_name, $fields, $max = 50 ) {
	$raw  = get_theme_mod( $mod_name, '[]' );
	$json = tsothm_sanitize_repeater_json( $raw, $fields, $max );
	$rows = json_decode( $json, true );
	return is_array( $rows ) ? $rows : array();
}

/**
 * Team members configured in the Customizer.
 *
 * @return array
 */
function tsothm_get_team_members() {
	$rows = tsothm_get_repeater_rows( 'tsothm_team_members', tsothm_get_team_fields() );
	return array_values(
		array_filter(
			$rows,
			function( $row ) {
				return ! empty( $row['name'] );
			}
		)
	);
}

/**
 * Pricing plans configured in the Customizer.
 *
 * @return array
 */
function tsothm_get_pricing_plans() {
	$rows = tsothm_get_repeater_rows( 'tsothm_pricing_plans', tsothm_get_pricing_fields(), 5 );
	return array_values(
		array_filter(
			$rows,
			function( $row ) {
				return ! empty( $row['title'] );
			}
		)
	);
}

/**
 * Landing highlights configured in the Customizer.
 *
 * @return array
 */
function tsothm_get_landing_highlights() {
	$rows = tsothm_get_repeater_rows( 'tsothm_landing_highlights', tsothm_get_landing_highlight_fields() );
	return array_values(
		array_filter(
			$rows,
			function( $row ) {
				return ! empty( $row['title'] ) || ! empty( $row['text'] );
			}
		)
	);
}

/**
 * Render the Team grid from Customizer data. Falls back to false so the
 * template can show the block content (pattern) instead.
 *
 * @param int $post_id Page ID (para el aviso de edición).
 * @return bool True si se ha pintado algo.
 */
function tsothm_render_team_grid( $post_id ) {
	$members = tsothm_get_team_members();

	if ( empty( $members ) ) {
		return false;
	}

	echo '<div class="tso-team-grid tso-team-grid--dynamic">';
	foreach ( $members as $member ) {
		echo '<div class="tso-team-member">';
		if ( ! empty( $member['photo'] ) ) {
			echo wp_get_attachment_image(
				(int) $member['photo'],
				'medium',
				false,
				array( 'class' => 'tso-team-photo' )
			);
		} else {
			echo '<div class="tso-team-photo tso-team-photo--placeholder" aria-hidden="true">' . esc_html( mb_substr( (string) $member['name'], 0, 1 ) ) . '</div>';
		}
		echo '<h3 class="tso-team-name">';
		if ( ! empty( $member['link'] ) ) {
			echo '<a href="' . esc_url( $member['link'] ) . '">' . esc_html( $member['name'] ) . '</a>';
		} else {
			echo esc_html( $member['name'] );
		}
		echo '</h3>';
		if ( ! empty( $member['role'] ) ) {
			echo '<p class="tso-team-role">' . esc_html( $member['role'] ) . '</p>';
		}
		echo '</div>';
	}
	echo '</div>';

	return true;
}

/**
 * Render the Pricing table from Customizer data.
 *
 * @param int $post_id Page ID.
 * @return bool True si se ha pintado algo.
 */
function tsothm_render_pricing_table( $post_id ) {
	$plans = tsothm_get_pricing_plans();

	if ( empty( $plans ) ) {
		return false;
	}

	echo '<div class="tso-pricing-grid tso-pricing-grid--dynamic">';
	foreach ( $plans as $plan ) {
		$featured = ! empty( $plan['featured'] );
		echo '<div class="tso-pricing-card' . ( $featured ? ' tso-pricing-card--featured' : '' ) . '">';
		if ( $featured ) {
			echo '<span class="tso-pricing-badge">' . esc_html__( 'Recommended', 'tso-blog' ) . '</span>';
		}
		echo '<h3 class="tso-pricing-title">' . esc_html( $plan['title'] ) . '</h3>';
		if ( ! empty( $plan['price'] ) ) {
			echo '<p class="tso-pricing-price">' . esc_html( $plan['price'] );
			if ( ! empty( $plan['period'] ) ) {
				echo '<span>' . esc_html( $plan['period'] ) . '</span>';
			}
			echo '</p>';
		}
		if ( ! empty( $plan['features'] ) ) {
			echo '<ul class="tso-pricing-features">';
			foreach ( preg_split( '/\s*\|\s*|\r\n|\r|\n/', (string) $plan['features'] ) as $line ) {
				$line = trim( $line );
				if ( '' === $line ) {
					continue;
				}
				echo '<li>' . esc_html( $line ) . '</li>';
			}
			echo '</ul>';
		}
		if ( ! empty( $plan['button_text'] ) && ! empty( $plan['button_url'] ) ) {
			echo '<a class="tso-pricing-button" href="' . esc_url( $plan['button_url'] ) . '">' . esc_html( $plan['button_text'] ) . '</a>';
		}
		echo '</div>';
	}
	echo '</div>';

	return true;
}

/**
 * Render the Landing highlights row from Customizer data.
 *
 * @return bool True si se ha pintado algo.
 */
function tsothm_render_landing_highlights() {
	$items = tsothm_get_landing_highlights();

	if ( empty( $items ) ) {
		return false;
	}

	echo '<div class="tso-landing-highlights">';
	foreach ( $items as $item ) {
		echo '<div class="tso-landing-highlight">';
		if ( ! empty( $item['icon'] ) ) {
			echo '<span class="tso-landing-highlight-icon" aria-hidden="true">' . esc_html( $item['icon'] ) . '</span>';
		}
		if ( ! empty( $item['title'] ) ) {
			echo '<h3 class="tso-landing-highlight-title">' . esc_html( $item['title'] ) . '</h3>';
		}
		if ( ! empty( $item['text'] ) ) {
			echo '<p class="tso-landing-highlight-text">' . esc_html( $item['text'] ) . '</p>';
		}
		echo '</div>';
	}
	echo '</div>';

	return true;
}

/**
 * Landing hero settings (eyebrow + botones), Customizer.
 *
 * @return array<string, string>
 */
function tsothm_get_landing_hero_settings() {
	return array(
		'eyebrow'     => sanitize_text_field( get_theme_mod( 'tsothm_landing_eyebrow', '' ) ),
		'cta_text'    => sanitize_text_field( get_theme_mod( 'tsothm_landing_cta_text', '' ) ),
		'cta_url'     => esc_url_raw( get_theme_mod( 'tsothm_landing_cta_url', '' ) ),
		'cta2_text'   => sanitize_text_field( get_theme_mod( 'tsothm_landing_cta2_text', '' ) ),
		'cta2_url'    => esc_url_raw( get_theme_mod( 'tsothm_landing_cta2_url', '' ) ),
	);
}
