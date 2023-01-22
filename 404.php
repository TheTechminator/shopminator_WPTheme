<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package shopminator
 */

get_header();
?>

	<main id="primary" class="site-main container-md mb-5">

		<section class="error-404 not-found">
			<h1 class="page-title"><?php esc_html_e( 'Hoppá! A keresett oldal nem található.', 'shopminator' ); ?></h1>
			<img src="<?php echo get_theme_file_uri( 'assets/images/slider-imgs/404.jpg'); ?>">
			<a href="http://www.freepik.com">Designed by Freepik</a>
		</section><!-- .error-404 -->

	</main><!-- #main -->

<?php
get_footer();
