    </div><!-- #content -->

    <footer class="site-footer" role="contentinfo">
        <table class="footer-layout-table" cellspacing="0" cellpadding="0">
            <tr class="footer-layout-row">
                <td class="footer-layout-cell">
                    <?php dynamic_sidebar( 'footer-1' ); ?>
                </td>
                <td class="footer-layout-cell">
                    <?php dynamic_sidebar( 'footer-2' ); ?>
                </td>
                <td class="footer-layout-cell">
                    <?php dynamic_sidebar( 'footer-3' ); ?>
                </td>
            </tr>
        </table>
        <div class="footer-bottom">
            <p><?php tso_footer_copyright_text(); ?></p>
            <?php tso_footer_legal_text(); ?>
        </div>
    </footer>

</div><!-- #page-wrapper -->
<?php wp_footer(); ?>
</body>
</html>
