<?php get_header(); ?>

<div class="main-container">
    <main id="primary" role="main">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h1 class="page-title"><?php the_title(); ?></h1>
                <div class="entry-content">
                    <?php
                    the_content();
                    wp_link_pages( array(
                        'before'      => '<div class="page-links">' . esc_html__( 'Páginas:', 'tso-theme' ),
                        'after'       => '</div>',
                        'link_before' => '<span class="page-number">',
                        'link_after'  => '</span>',
                    ) );
                    ?>
                </div>
            </article>
        <?php endwhile; endif; ?>
    </main>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
