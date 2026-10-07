<?php
/**
 * Template part: single post tags.
 *
 * @package tso-blog
 */

$tsothm_tags = get_the_tags();
if ( ! $tsothm_tags ) {
	return;
}
?>
<div class="single-tags" aria-label="<?php esc_attr_e( 'Etiquetas', 'tso-blog' ); ?>">
	<span class="tags-label"><?php esc_html_e( 'Etiquetas:', 'tso-blog' ); ?></span>
	<?php foreach ( $tsothm_tags as $tsothm_tag ) : ?>
		<a href="<?php echo esc_url( get_tag_link( $tsothm_tag->term_id ) ); ?>" rel="tag">
			<?php echo esc_html( $tsothm_tag->name ); ?>
		</a>
	<?php endforeach; ?>
</div>
