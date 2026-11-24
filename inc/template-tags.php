<?php
/**
 * Template tags: footer texts, Load more button, related posts and GTranslate header slot.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   15. HELPERS
   ============================================================ */
/**
 * Replace footer text placeholders with live site values.
 *
 * Supported: {year}, {site_name}, {home_url}, {privacy_url}.
 * Legacy home-page token (site + _url) is still replaced for older configs.
 *
 * @param string $text Raw theme mod text.
 * @return string
 */
function tsothm_footer_replace_placeholders( $text ) {
    $text = (string) $text;
    if ( '' === $text ) {
        return '';
    }
    $privacy = '';
    if ( function_exists( 'get_privacy_policy_url' ) ) {
        $privacy = (string) get_privacy_policy_url();
    }
    $home = home_url( '/' );
    $map  = array(
        '{year}'        => (string) current_time( 'Y' ),
        '{site_name}'   => get_bloginfo( 'name' ),
        '{home_url}'    => $home,
        '{privacy_url}' => $privacy,
    );
    // Legacy Customizer token (same value as {home_url}).
    $map[ '{' . 'site' . '_url}' ] = $home;
    return str_replace( array_keys( $map ), array_values( $map ), $text );
}

function tsothm_footer_copyright_text() {
    $custom = trim( (string) get_theme_mod( 'tsothm_footer_copyright', '' ) );
    if ( '' === $custom ) {
        echo '&copy; ' . esc_html( current_time( 'Y' ) ) . ' ' . esc_html( get_bloginfo( 'name' ) );
        return;
    }
    echo wp_kses_post( tsothm_footer_replace_placeholders( $custom ) );
}

function tsothm_footer_legal_text() {
    $legal = trim( (string) get_theme_mod( 'tsothm_footer_legal', '' ) );
    if ( '' === $legal ) {
        return;
    }
    echo '<div class="footer-legal">' . wp_kses_post( tsothm_footer_replace_placeholders( $legal ) ) . '</div>';
}

function tsothm_load_more_text() {
    return sanitize_text_field( (string) get_theme_mod( 'tsothm_text_load_more', 'Load more' ) );
}

/**
 * Output the home “Load more” button when there are more pages.
 * Used by home.php and the Customizer selective-refresh partial.
 */
function tsothm_render_load_more_button() {
    global $wp_query;
    if ( ! $wp_query instanceof WP_Query || (int) $wp_query->max_num_pages < 2 ) {
        return;
    }
    ?>
	<button
		id="load-more-btn"
		class="btn-load-more"
		type="button"
		data-next-page="<?php echo esc_url( next_posts( $wp_query->max_num_pages, false ) ); ?>"
		data-loading-text="<?php esc_attr_e( 'Loading...', 'tso-blog' ); ?>"
		data-load-text="<?php echo esc_attr( tsothm_load_more_text() ); ?>"
	>
		<?php echo esc_html( tsothm_load_more_text() ); ?>
	</button>
	<?php
}

function tsothm_related_count() {
    return max( 1, min( 6, (int) get_theme_mod( 'tsothm_related_count', 3 ) ) );
}

function tsothm_related_title() {
    return sanitize_text_field( (string) get_theme_mod( 'tsothm_related_title', 'Related posts' ) );
}

/**
 * Whether the optional GTranslate header slot should render.
 * Off by default; requires Customizer opt-in and a registered shortcode.
 *
 * @return bool
 */
function tsothm_is_gtranslate_header_enabled() {
    if ( ! (int) get_theme_mod( 'tsothm_enable_gtranslate', 0 ) ) {
        return false;
    }
    return shortcode_exists( 'gtranslate' );
}

/**
 * Print the GTranslate header block when enabled and available.
 */
function tsothm_header_gtranslate() {
    if ( ! tsothm_is_gtranslate_header_enabled() ) {
        return;
    }
    echo '<div class="header-gtranslate">';
    echo do_shortcode( '[gtranslate]' );
    echo '</div>';
}
