<?php
/**
 * Block styles and the block pattern category (patterns live in /patterns).
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   25. BLOCK STYLES — estils addicionals per a blocs de Gutenberg
   ============================================================
   Afegeix variants visuals als blocs principals del tema.
   L'usuari pot triar l'estil des del panell lateral de l'editor.
   ============================================================ */
add_action( 'init', function() {

    // Botó — variant destacat amb color d'acent del tema
    register_block_style( 'core/button', array(
        'name'  => 'tso-accent',
        'label' => __( 'TSO Accent', 'tso-blog' ),
    ) );

    // Cita — variant de barra lateral (blockquote amb línia esquerra)
    register_block_style( 'core/quote', array(
        'name'  => 'tso-sidebar-quote',
        'label' => __( 'Sidebar', 'tso-blog' ),
    ) );

    // Separador — variant d'ombra curta
    register_block_style( 'core/separator', array(
        'name'  => 'tso-short',
        'label' => __( 'Centered short', 'tso-blog' ),
    ) );

    // Imatge — variant amb ombra suau
    register_block_style( 'core/image', array(
        'name'  => 'tso-shadow',
        'label' => __( 'With shadow', 'tso-blog' ),
    ) );

} );

/* ============================================================
   26. BLOCK PATTERNS — category
   ============================================================
   The patterns themselves live in /patterns/*.php (one file per
   pattern) and WordPress registers them automatically.
   ============================================================ */
add_action( 'init', function() {
    register_block_pattern_category( 'tso-blog', array(
        'label' => __( 'Tu Soporte Online', 'tso-blog' ),
    ) );
} );
