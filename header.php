<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package shopminator
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--link rel="profile" href="https://gmpg.org/xfn/11"-->

	<link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css"/>
	<link rel="stylesheet" href="<?php echo get_theme_file_uri( 'assets/nouislider/nouislider.css' ); ?>">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'shopminator' ); ?></a>

	<header class="bg-light text-black">
		<div class="container-md px-2 px-md-3" style="position: relative;">
			<div class="row justify-content-center align-items-center mx-0">
				<div id="mySwiper" class="d-md-none">
					<div id="mySidenav" class="sidenav">
						<a href="javascript:void(0)" class="//closebtn">
							<img src="<?php echo get_theme_file_uri( 'assets/images/icons/cancel.svg' ); ?>" class="menuIcons" style="
									    position: absolute;
										right: 20px;
										top: 30px;
								">
						</a>

						<div class="dropDownHolder">
							<a class="nav-link"><img src="<?php echo get_theme_file_uri( 'assets/images/icons/product.svg' ); ?>" class="menuIcons">Kategóriák
								<img src="<?php echo get_theme_file_uri( 'assets/images/icons/next.svg' ); ?>" class="menuIcons" style="
									position: absolute;
									right: 20px;
								">
							</a>
							<div class ="dropDownMenu">
								<span class="dropDownMenuClose">
									<i class="bi bi-chevron-left"></i>Vissza
								</span>
								<?php
									require_once get_template_directory() . '/classes/getProductCategories.php';
									$getProductCategories = new GetProductCategories();
									echo $getProductCategories->forMobile();
								?>
							</div>
						</div>

						<a class="nav-link" ><img src="<?php echo get_theme_file_uri( 'assets/images/icons/shopping-cart.svg' ); ?>" class="menuIcons">Kiárusítás</a>
						<a class="nav-link"><img src="<?php echo get_theme_file_uri( 'assets/images/icons/new-product.svg' ); ?>" class="menuIcons">Újdonságok</a>
						<a class="nav-link"><img src="<?php echo get_theme_file_uri( 'assets/images/icons/home-repair.svg' ); ?>" class="menuIcons">Lakásfelújítás</a>
						<a class="nav-link"><img src="<?php echo get_theme_file_uri( 'assets/images/icons/contact.svg' ); ?>" class="menuIcons">Kapcsolat</a>
					</div>
					<div id="swipablePad"></div>
					<!--div class="leftNavOpenBtn swiperMenuNavOpenBtns"><span class="navbar-toggler-icon"><i class="bi bi-list"></i></span></!--div-->
				</div>

				<div class="col-5 col-md-3 col-lg-2 col-xl-3 my-2 text-start p-0 p-md-2">
					<span class="navbar-toggler-icon swiperMenuNavOpenBtns d-inline d-md-none" style="font-size: 22px;"><i class="bi bi-list"></i></span>
					<?php 
						the_custom_logo(); 
					?>

					<span class="d-none d-xl-inline" style="font-size: 16px;">
						<?php 
							echo get_option( 'blogname' );
						?>
					</span>
				</div>

				<div class="d-none d-md-block col-md-6 col-lg-8 col-xl-5 my-2 text-start p-1 p-md-2">
					<?php aws_get_search_form( true ); ?>
				</div>
				
				<div class="col-7 col-md-3 col-lg-2 col-xl-4 my-2 text-end p-1 p-md-2">
					<div class="TopNavigationHeaderMenu">
						<a class="px-2 d-inline d-md-none" href="javascript:void(0)" id="mobilleSearchBarHolderOpener">
							<i class="bi bi-search"></i>
						</a>
						<a class="px-2" href="/uzlet/">
							<i class="bi bi-shop"></i> 
							<span class="d-none d-xl-inline">Üzlet</span>
						</a>
						<a class="px-2" href="/penztar/">
							<i class="bi bi-wallet2"></i> 
							<span class="d-none d-xl-inline">Pénztár</span>
						</a>
						<a class="px-2 pe-0" href="/kosar/">
							<i class="bi bi-cart4"></i>
							<span class="d-none d-xl-inline">Kosár</span>
						</a>
					</div>
				</div>
			</div>

			<div class="d-block d-md-none" id="mobileSearchBarHolder">
				<div class="aws-container" data-url="/?wc-ajax=aws_action" data-siteurl="<?php echo get_site_url(); ?>" data-lang="" data-show-loader="true" data-show-more="true" data-show-page="true" data-ajax-search="true" data-show-clear="true" data-mobile-screen="false" data-use-analytics="false" data-min-chars="1" data-buttons-order="1" data-timeout="300" data-is-mobile="true" data-page-id="2" data-tax="">
					<form class="aws-search-form aws-show-clear" action="<?php echo get_site_url(); ?>" method="get" role="search">
						<div class="aws-wrapper">
							<label class="aws-search-label" for="611ec3040529c">Termékkereső</label>
							<input type="search" name="s" id="611ec3040529c" value="" class="aws-search-field" placeholder="Termékkereső" autocomplete="off">
							<input type="hidden" name="post_type" value="product">
							<input type="hidden" name="type_aws" value="true">
							<div class="aws-loader"></div>
							<div class="aws-search-clear">
								<span id="mobilleSearchBarHolderCloser"><i class="bi bi-x"></i></span>
							</div>
							<div class="aws-search-btn aws-form-btn">
								<span class="aws-search-btn_icon">
									<i class="bi bi-search"></i>
								</span>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</header>
	
	<div class="underHeaderList bg-primary d-none d-md-block">
		<div class="container">
			<a href="#" class="uHLCategory swiperMenuNavOpenBtns"><i class="bi bi-list"></i>Kategóriák</a>
			<a href="#" class="uHLOther">Kiárusítás</a>
			<a href="#" class="uHLOther">Újdonságok</a>
			<a href="#" class="uHLOther">Lakásfelújítás</a>
		</div>
	</div>