<?php
/**
 * searchform.php — Search form with live autocomplete.
 *
 * Unique IDs per instance so header + 404 / empty-search can coexist.
 *
 * @package tso-blog
 */

static $tsothm_search_form_instance = 0;
$tsothm_search_form_instance++;

$tsothm_search_uid      = 'tso-search-' . (int) $tsothm_search_form_instance;
$tsothm_search_input_id = $tsothm_search_uid . '-input';
$tsothm_search_list_id  = $tsothm_search_uid . '-results';
$tsothm_search_form_id  = $tsothm_search_uid . '-form';
?>
<div class="tso-search-wrapper">
	<form
		role="search"
		method="get"
		id="<?php echo esc_attr( $tsothm_search_form_id ); ?>"
		class="searchform"
		action="<?php echo esc_url( home_url( '/' ) ); ?>"
	>
		<div>
			<label class="screen-reader-text" for="<?php echo esc_attr( $tsothm_search_input_id ); ?>">
				<?php esc_html_e( 'Buscar:', 'tso-blog' ); ?>
			</label>
			<input
				type="search"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				name="s"
				id="<?php echo esc_attr( $tsothm_search_input_id ); ?>"
				class="tso-search-input"
				placeholder="<?php esc_attr_e( 'Buscar en el sitio', 'tso-blog' ); ?>"
				autocomplete="off"
				role="combobox"
				aria-autocomplete="list"
				aria-controls="<?php echo esc_attr( $tsothm_search_list_id ); ?>"
				aria-expanded="false"
				aria-haspopup="listbox"
			/>
			<button type="submit" class="tso-search-submit" aria-label="<?php esc_attr_e( 'Buscar', 'tso-blog' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<circle cx="11" cy="11" r="8"></circle>
					<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
				</svg>
			</button>
		</div>
	</form>
	<ul
		id="<?php echo esc_attr( $tsothm_search_list_id ); ?>"
		class="tso-search-results"
		role="listbox"
		aria-label="<?php esc_attr_e( 'Sugerencias de búsqueda', 'tso-blog' ); ?>"
		hidden
	></ul>
</div>
