    </div><!-- #content -->

    <footer class="site-footer" role="contentinfo">
        <?php
        $tsothm_footer_ids = array( 'footer-1', 'footer-2', 'footer-3' );
        $tsothm_footer_active = array();
        foreach ( $tsothm_footer_ids as $tsothm_footer_id ) {
            if ( is_active_sidebar( $tsothm_footer_id ) ) {
                $tsothm_footer_active[] = $tsothm_footer_id;
            }
        }
        $tsothm_footer_count = count( $tsothm_footer_active );
        if ( $tsothm_footer_count > 0 ) :
            $tsothm_footer_mod = 'footer-layout--cols-' . $tsothm_footer_count;
            if ( 1 === $tsothm_footer_count ) {
                $tsothm_footer_mod .= ' footer-layout--horizontal';
            }
            ?>
        <div class="footer-layout <?php echo esc_attr( $tsothm_footer_mod ); ?>">
            <?php foreach ( $tsothm_footer_active as $tsothm_footer_id ) : ?>
            <div class="footer-layout-cell">
                <div class="footer-column">
                    <?php dynamic_sidebar( $tsothm_footer_id ); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="footer-bottom">
            <p><?php tsothm_footer_copyright_text(); ?></p>
            <?php tsothm_footer_legal_text(); ?>
        </div>
    </footer>

</div><!-- #page-wrapper -->
<?php wp_footer(); ?>
</body>
</html>
