<?php
/**
 * Page template helpers: body classes, FAQ accordion, resources list, legal table of contents and About Me card.
 *
 * @package tso-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   27. PLANTILLES NOVES — CLASSE DE BODY GENÈRICA
   ============================================================
   Afegeix automàticament body.tso-tpl-{slug} per a qualsevol
   plantilla de /page-templates/, sense haver de crear un filtre
   dedicat per cada una.
   ============================================================ */
add_filter( 'body_class', function( $classes ) {
	if ( ! is_page() ) {
		return $classes;
	}
	$tpl = get_page_template_slug();
	if ( $tpl && 0 === strpos( $tpl, 'page-templates/' ) ) {
		$slug       = str_replace( array( 'page-templates/', '.php' ), '', $tpl );
		$classes[]  = 'tso-tpl-' . sanitize_html_class( $slug );
	}
	return $classes;
} );

/* ============================================================
   28. PLANTILLA "PREGUNTAS FRECUENTES" — ACORDIÓN ACCESIBLE
   ============================================================
   Cada bloque Encabezado (H3) del contenido inicia una pregunta;
   los bloques siguientes, hasta el próximo H3, son la respuesta.
   Se renderiza con <details>/<summary> nativo (sin JS).
   ============================================================ */

/**
 * Render page content as an accessible FAQ accordion.
 *
 * @param int $post_id Page ID.
 */
function tsothm_render_faq_accordion( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || ! has_blocks( $post->post_content ) ) {
		the_content();
		return;
	}

	$blocks  = parse_blocks( $post->post_content );
	$items   = array();
	$current = null;

	foreach ( $blocks as $block ) {
		$is_empty = empty( $block['blockName'] ) && '' === trim( wp_strip_all_tags( isset( $block['innerHTML'] ) ? $block['innerHTML'] : '' ) );
		if ( $is_empty ) {
			continue;
		}

		if ( 'core/heading' === $block['blockName'] ) {
			if ( null !== $current ) {
				$items[] = $current;
			}
			$current = array(
				'question' => trim( wp_strip_all_tags( render_block( $block ) ) ),
				'answer'   => array(),
			);
			continue;
		}

		if ( null === $current ) {
			echo render_block( $block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block() ya sanea vía core.
			continue;
		}

		$current['answer'][] = $block;
	}
	if ( null !== $current ) {
		$items[] = $current;
	}

	if ( empty( $items ) ) {
		if ( current_user_can( 'edit_post', $post_id ) ) {
			echo '<p class="tso-faq-empty">' . esc_html__( 'Add a Heading (H3) block for each question and the answer text below it. Publish the page to see the accordion.', 'tso-blog' ) . '</p>';
		}
		return;
	}

	echo '<div class="tso-faq-list">';
	foreach ( $items as $item ) {
		$answer_html = trim( implode( '', array_map( 'render_block', $item['answer'] ) ) );
		echo '<details class="tso-faq-item">';
		echo '<summary class="tso-faq-question">' . esc_html( $item['question'] ) . '</summary>';
		echo '<div class="tso-faq-answer">' . $answer_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block() ya sanea vía core.
		echo '</details>';
	}
	echo '</div>';
}

/* ============================================================
   29. PLANTILLA "RECURSOS / DESCARGAS"
   ============================================================
   Lee los bloques Archivo (core/file) del contenido y los
   muestra como una lista de descargas con icono, nombre y peso.
   ============================================================ */

/**
 * Recursively collect core/file block data from parsed blocks.
 *
 * @param array $blocks Parsed blocks.
 * @return array<int, array{id:int, href:string, name:string}>
 */
function tsothm_extract_file_blocks( $blocks ) {
	$files = array();

	foreach ( (array) $blocks as $block ) {
		if ( empty( $block['blockName'] ) ) {
			if ( ! empty( $block['innerBlocks'] ) ) {
				$files = array_merge( $files, tsothm_extract_file_blocks( $block['innerBlocks'] ) );
			}
			continue;
		}

		if ( 'core/file' === $block['blockName'] ) {
			$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
			$href  = isset( $attrs['href'] ) ? esc_url_raw( $attrs['href'] ) : '';
			if ( $href ) {
				$files[] = array(
					'id'   => isset( $attrs['id'] ) ? absint( $attrs['id'] ) : 0,
					'href' => $href,
					'name' => isset( $attrs['fileName'] ) ? sanitize_text_field( $attrs['fileName'] ) : '',
				);
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$files = array_merge( $files, tsothm_extract_file_blocks( $block['innerBlocks'] ) );
		}
	}

	return $files;
}

/**
 * File entries (core/file blocks) for a Resources page.
 *
 * @param int $post_id Page ID.
 * @return array
 */
function tsothm_get_resource_files( $post_id ) {
	$post_id = absint( $post_id );
	$post    = get_post( $post_id );
	if ( ! $post || ! has_blocks( $post->post_content ) ) {
		return array();
	}
	$files = tsothm_extract_file_blocks( parse_blocks( $post->post_content ) );

	/**
	 * Filter resource files list.
	 *
	 * @param array $files   File entries.
	 * @param int   $post_id Page ID.
	 */
	return apply_filters( 'tsothm_resource_files', $files, $post_id );
}

/**
 * File extension label for a resource entry.
 *
 * @param array $file File entry with 'href'.
 * @return string
 */
function tsothm_get_resource_file_ext( $file ) {
	$path = wp_parse_url( isset( $file['href'] ) ? $file['href'] : '', PHP_URL_PATH );
	$ext  = $path ? strtoupper( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';
	return $ext ? $ext : __( 'File', 'tso-blog' );
}

/**
 * Human-readable file size for a resource entry (solo adjuntos locales).
 *
 * @param array $file File entry with 'id'.
 * @return string
 */
function tsothm_get_resource_file_size( $file ) {
	if ( empty( $file['id'] ) ) {
		return '';
	}
	$path = get_attached_file( (int) $file['id'] );
	if ( ! $path || ! file_exists( $path ) ) {
		return '';
	}
	$bytes = filesize( $path );
	return $bytes ? size_format( $bytes ) : '';
}

/**
 * Render the resources/downloads list.
 *
 * @param int $post_id Page ID.
 */
function tsothm_render_resource_list( $post_id ) {
	$files = tsothm_get_resource_files( $post_id );

	if ( empty( $files ) ) {
		if ( current_user_can( 'edit_post', $post_id ) ) {
			echo '<p class="tso-resources-empty">' . esc_html__( 'Add one or more File blocks in the editor and publish the page so they show up here.', 'tso-blog' ) . '</p>';
		}
		return;
	}

	echo '<ul class="tso-resources-list">';
	foreach ( $files as $file ) {
		$ext   = tsothm_get_resource_file_ext( $file );
		$size  = tsothm_get_resource_file_size( $file );
		$label = $file['name'] ? $file['name'] : ( $file['id'] ? get_the_title( $file['id'] ) : '' );
		if ( '' === trim( (string) $label ) ) {
			$label = $ext;
		}

		echo '<li class="tso-resource-item">';
		echo '<a class="tso-resource-link" href="' . esc_url( $file['href'] ) . '" download>';
		echo '<span class="tso-resource-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" focusable="false"><path fill="currentColor" d="M6 2c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/></svg></span>';
		echo '<span class="tso-resource-info">';
		echo '<span class="tso-resource-name">' . esc_html( $label ) . '</span>';
		echo '<span class="tso-resource-meta">' . esc_html( $size ? $ext . ' · ' . $size : $ext ) . '</span>';
		echo '</span>';
		echo '</a>';
		echo '</li>';
	}
	echo '</ul>';
}

/* ============================================================
   30. PLANTILLA "LEGAL / DOCUMENTO LARGO" — ÍNDICE (TOC)
   ============================================================
   Añade id="" a cada H2/H3 del contenido ya renderizado y
   construye el índice lateral a partir de esos mismos títulos.
   ============================================================ */

/**
 * Add id attributes to H2/H3 headings in rendered HTML and build a matching TOC.
 *
 * @param string $html Rendered content HTML (tras aplicar los filtros de 'the_content').
 * @return array{content: string, toc: array}
 */
function tsothm_add_heading_ids_and_toc( $html ) {
	$toc        = array();
	$used_slugs = array();

	$html = preg_replace_callback(
		'/<h([23])\b([^>]*)>(.*?)<\/h\1>/is',
		function( $m ) use ( &$toc, &$used_slugs ) {
			$level = (int) $m[1];
			$attrs = (string) $m[2];
			$inner = (string) $m[3];
			$text  = trim( wp_strip_all_tags( $inner ) );

			if ( preg_match( '/\bid=["\']([^"\']+)["\']/', $attrs, $id_m ) ) {
				$slug = $id_m[1];
			} else {
				$base = $text ? sanitize_title( $text ) : 'section';
				$slug = $base;
				$i    = 2;
				while ( isset( $used_slugs[ $slug ] ) ) {
					$slug = $base . '-' . $i;
					++$i;
				}
				$attrs .= ' id="' . esc_attr( $slug ) . '"';
			}
			$used_slugs[ $slug ] = true;

			if ( $text ) {
				$toc[] = array(
					'level' => $level,
					'slug'  => $slug,
					'text'  => $text,
				);
			}

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		$html
	);

	return array(
		'content' => (string) $html,
		'toc'     => $toc,
	);
}

/**
 * Render the TOC nav for the Legal template.
 *
 * @param array $toc Entries from tsothm_add_heading_ids_and_toc().
 */
function tsothm_render_legal_toc( $toc ) {
	if ( empty( $toc ) ) {
		return;
	}
	echo '<nav class="tso-legal-toc" aria-label="' . esc_attr__( 'Document contents', 'tso-blog' ) . '">';
	echo '<strong class="tso-legal-toc-title">' . esc_html__( 'Contents', 'tso-blog' ) . '</strong>';
	echo '<ul>';
	foreach ( $toc as $item ) {
		echo '<li class="tso-legal-toc-item tso-legal-toc-level-' . (int) $item['level'] . '">';
		echo '<a href="#' . esc_attr( $item['slug'] ) . '">' . esc_html( $item['text'] ) . '</a>';
		echo '</li>';
	}
	echo '</ul>';
	echo '</nav>';
}

/* ============================================================
   31. PLANTILLA "SOBRE MÍ" — TARJETA DE CONTACTO OPCIONAL
   ============================================================
   Reutiliza los mismos ajustes del Customizer que la plantilla
   Contacto (email, teléfono, shortcode de formulario, redes).
   ============================================================ */

/**
 * Render an optional "get in touch" card for the About Me template.
 *
 * @param int $post_id Page ID (para el aviso de edición).
 */
function tsothm_render_about_me_contact_card( $post_id ) {
	$contact    = tsothm_get_contact_settings();
	$has_direct = ( $contact['email'] || $contact['phone'] );

	if ( ! $contact['form_shortcode'] && ! $has_direct ) {
		if ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'edit_theme_options' ) ) {
			echo '<p class="tso-about-contact-hint">' . esc_html__( 'Set an email, phone or a form shortcode under Customize → Templates → Contact to show a contact method here.', 'tso-blog' ) . '</p>';
		}
		return;
	}

	echo '<div class="tso-about-contact-card">';
	echo '<h2 class="tso-about-contact-title">' . esc_html__( 'Get in touch', 'tso-blog' ) . '</h2>';

	if ( $has_direct ) {
		echo '<p class="tso-about-contact-direct">';
		if ( $contact['email'] ) {
			echo '<a href="' . esc_url( 'mailto:' . $contact['email'] ) . '">' . esc_html( $contact['email'] ) . '</a>';
		}
		if ( $contact['email'] && $contact['phone'] ) {
			echo ' &middot; ';
		}
		if ( $contact['phone'] ) {
			echo '<a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $contact['phone'] ) ) . '">' . esc_html( $contact['phone'] ) . '</a>';
		}
		echo '</p>';
	}

	if ( $contact['form_shortcode'] ) {
		echo '<div class="tso-about-contact-form">' . do_shortcode( $contact['form_shortcode'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_shortcode() output, shortcode ya validado en tsothm_sanitize_contact_form_shortcode().
	}

	if ( ! empty( $contact['show_social'] ) ) {
		tsothm_social_icons();
	}

	echo '</div>';
}
