<?php
/**
 * Template part: single post tags.
 *
 * @package tso-theme
 */

$tso_tags = get_the_tags();
if ( ! $tso_tags ) {
	return;
}
?>
<div class="single-tags" aria-label="<?php esc_attr_e( 'Etiquetas', 'tso-theme' ); ?>">
	<span class="tags-label"><?php esc_html_e( 'Etiquetas:', 'tso-theme' ); ?></span>
	<?php foreach ( $tso_tags as $tso_tag ) : ?>
		<a href="<?php echo esc_url( get_tag_link( $tso_tag->term_id ) ); ?>" rel="tag">
			<?php echo esc_html( $tso_tag->name ); ?>
		</a>
	<?php endforeach; ?>
</div>
