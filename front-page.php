<?php
/**
 * Ez a sablon a főoldal megjelenítésére van.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shopminator
 */

get_header();

//echo wp_hash_password("Valami");
//echo "<script> console.log(" . json_encode($results) . "); </script>";
//$product = wc_get_product($productId);

/*$products = wc_get_products(array(
    'include' => array(118),
));*/

/*echo "<pre>";
    //print_r($products[0]);
    //print_r(get_class_methods($products[0]));  TRUNCATE TABLE Categories;
    //print_r(get_class($products[0]));
    //print_r(count($products));
    //print_r($results[0]->ID);
echo "</pre><br><br><br>";*/

//echo "<script> console.log(" . $product->__toString() . "); </script>";

require_once get_template_directory() . '/classes/getProductCategories.php';
require_once get_template_directory()."/classes/generateProductSlider.php";
require_once get_template_directory()."/classes/queries.php";


$getProductCategories = new GetProductCategories();

global $wpdb;
$productQueries = new FrontPageProductSliderQueries($wpdb);
?>
	<main id="primary" class="site-main container-md">
        <!----------Kategóriák és Képek---------->
        <div class="flex-container categoriesAndImagesHolder">
            <div class="productCategories d-none d-md-block">
                <h3>Kategóriák</h3>
                <?php
                    echo $getProductCategories->parents();
                ?>
            </div>

            <div class="sliderHolder" id="slideHolderID">
                <div class="swiper-container swiperImages">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="<?php echo get_theme_file_uri( 'assets/images/slider-imgs/img12.jpg' ); ?>" alt="">
                        </div>
                        <div class="swiper-slide">
                            <img src="<?php echo get_theme_file_uri( 'assets/images/slider-imgs/img12.jpg' ); ?>" alt="">
                        </div>
                        <div class="swiper-slide">
                            <img src="<?php echo get_theme_file_uri( 'assets/images/slider-imgs/img12.jpg' ); ?>" alt="">
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>

                <?php
                    echo $getProductCategories->childHolders();
                ?>
            </div>
        </div>

        <?php
            $products = wc_get_products( $productQueries->getSaleProducts( 10 ) );
            generateProductSlider( 'Akciós termékek', $products, '<div class="row mt-5">', '</div>' );

            $products = wc_get_products( $productQueries->getLimitedTimeSaleProducts( 10 ) );
            generateProductSlider( 'Limitál ideig akciós termékek', $products, '<div class="row mt-5">', '</div>' );

            $products = wc_products_array_orderby(
                wc_get_products( $productQueries->getBestSellerProducts( 10 ) ), "total_sales", "DESC"
            );
            generateProductSlider( 'Legtöbbet eladott termékek', $products, '<div class="row mt-5">', '</div>' );

            $products = wc_get_products( $productQueries->getProductsForCategory( 'Laptop | Notebook', 10 ) );
            generateProductSlider( 'Laptop | Notebook', $products, '<div class="row mt-5">', '</div>' );

            $products = wc_get_products( $productQueries->getProductsForCategory( 'Clothing', 10 ) );
            generateProductSlider( 'Clothing', $products, '<div class="row mt-5 mb-5">', '</div>' );
        ?>
	</main><!-- #main -->
<?php
get_footer();