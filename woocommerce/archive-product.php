<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

//echo 'Hello ' . htmlspecialchars($_GET["name"]) . '!';

$productOrdering = new ProductOrdering(); 

?>

<!--select id="sort-by-select" name="sort">
	<option value="brand">Gyártó A-Z</option>
	<option value="desc_brand">Gyártó Z-A</option>
</select-->

<div class="container-md pt-4">
	<div class="row">
		<div class="col-12 mb-3">
			<?php woocommerce_breadcrumb(); ?>
		</div>
	</div>
</div>

<main class="container-md productContainer pb-4" style="display: flex; flex-direction: row; flex-wrap: nowrap;">

	<div class="d-none d-md-block sidebarHolder" id="sidebarHolderWithFilters">
		<div class="d-md-none closeButtonTHolderForSideBarHolder">
			<button class="btn btn-primary" type="button"  id="closeSideBarHolder">
				Vissza
			</button>
		</div>

		<?php
			get_sidebar();
		?>
	</div>

	<div class="rightColForProducts ms-0 ms-md-3">

		<div class="row w-100 m-0">
			<div class="col-12 px-0">
				<h1 class="pageTitle mb-3">
					<?php echo woocommerce_page_title(); ?>
				</h1>
			</div>
		</div>

		<?php

			$queriedObject = get_queried_object();

			$searchKeyword = isset($_GET['s']) ? $_GET['s'] : null;
			$term_taxonomy_id = null;

			if( isset( $queriedObject ) && is_a( $queriedObject, 'WP_Term' ) ) {
				$term_taxonomy_id = $queriedObject->term_taxonomy_id;
			}
		?>

		<div class="row w-100 m-0 justify-content-between">
			<div class="mb-3 mb-md-0 col-md-6 px-0">
				<p class="productResultCount">
					Találatok
					<span id="resultCount">
						(0 db)
					</span>
				</p>
			</div>

			<div class="col-auto d-md-none px-0">
				<button class="btn btn-primary" type="button" id="openSideBarHolder">
					Szűrők
				</button>
			</div>

			<div class="col-auto col-md-6 px-0">
				<?php
					$productOrdering->getOrderByForm();
				?>
			</div>
		</div>

		<div class="row mt-3 productRow" id="rowForProducts"></div>
	</div>
</main>

<div class="loading" id="loadingSpinner" style="display: none;">
	<img src="<?php echo get_theme_file_uri( 'assets/images/icons/loading.svg'); ?>">
</div>

<script src="<?php echo get_theme_file_uri( 'assets/nouislider/nouislider.js'); ?>"></script>
<script>

	let defaultPageData = <?php getProduct("direct"); ?>;

	/**
	 * Globális változó
	 */
	let ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";


	/**
	 * Globális változó
	 */
	let data = {
		<?php
			if($term_taxonomy_id != null)
				echo "'term_taxonomy_id': '".$term_taxonomy_id."', ";

			if($productOrdering->getOrder() != null)
				echo "'orderby': '".$productOrdering->getOrder()."', ";

			if($searchKeyword != null)
				echo "'s': '".$searchKeyword."', ";
		?>

		'action':'getProduct'
	}

	/**
	 * Globális változó
	 */
	minimumFilterPrice = 0;
	maximumFilterPrice = 1500000;
</script>

<?php

get_footer( 'shop' );