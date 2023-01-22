<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package shopminator
 */

?>

	<footer id="colophon" class="site-footer bg-light">
		<div class="container-md pt-5">
			<div class="row">
				<div class="col-lg-3 col-sm-6">
					<?php dynamic_sidebar( 'footer-widget-col-one' ); ?>
				</div>
				<div class="col-lg-3 col-sm-6 mt-4 mt-sm-0">
					<?php dynamic_sidebar( 'footer-widget-col-two' ); ?>
				</div>
				<div class="col-lg-6 mt-4 mt-lg-0">
					<form class="feliratkozasUrlap">
					<h5>Iratkozzon fel hírlevelünkre!</h5>
					<div class="row">
						<div class="col-md-6" >
							<input type="text" class="form-control" name="feliratkozasVezNev" id="feliratkozasVezNev" value="Vezetéknév">
						</div>
						<div class="col-md-6">
							<input type="text" class="form-control" name="feliratkozasKerNev" id="feliratkozasKerNev" value="Keresztnév">
						</div>
					</div>
					<div class="row">
						<div class="col-12">
							<input type="email" class="form-control" name="feliratkozasEmail" id="feliratkozasEmail" value="email@domain.hu">
						</div>
					</div>
					<div class="row justify-content-end">
						<div class="col-12">
							<button class="btn btn-primary" type="button">Feliratkozok</button>
						</div>
					</div>
					</form>
				</div>
			</div>

			<div class="d-flex justify-content-between py-4 mt-4 border-top">
				<p>Minden jog fenntartva 2020-2021 ©</p>
			</div>
		</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="<?php echo get_theme_file_uri( 'assets/SwiperMenu/js/swiperMenuScript.js?v=' . _S_VERSION ); ?>"></script>
<script src="<?php echo get_theme_file_uri( 'assets/js/productFilterHandler.js?v=' . _S_VERSION ); ?>"></script>
<script src="<?php echo get_theme_file_uri( 'assets/js/main.js?v=' . _S_VERSION ); ?>"></script>

</body>
</html>
