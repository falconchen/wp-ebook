<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package NewsAnchor
 */
?>

			</div>
		</div>		
	</div><!-- .page-content -->

    <a class="go-top">
        <i class="fa fa-angle-up"></i>
    </a>

	<footer id="colophon" class="site-info" role="contentinfo">
		<div class="go-top2"></div>

		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			<?php get_sidebar('footer'); ?>
		<?php endif; ?>

		<div class="container">
			<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'newsanchor' ) ); ?>"><?php printf( esc_html__( 'Proudly powered by %s', 'newsanchor' ), 'WordPress' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( esc_html__( 'Theme: %2$s by %1$s.', 'newsanchor' ), 'aThemes', '<a href="http://athemes.com/theme/newsanchor" rel="designer">NewsAnchor</a>' ); ?>
		</div><!-- /.container -->
	</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
