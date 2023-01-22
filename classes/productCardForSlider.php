<?php
/**
 * A fájl tartalmazza a főoldalon megjeleő sliderekben megjelenő termékek vázát.
 * Kap egy termék ID-t majd azt megjeleníti egy kártya ként.
 */

defined( 'ABSPATH' ) || exit;


/**
 * Megjeleníti a megadott termék adatait egy kártya ként.
 * 
 * @param product - WC_Product_Simple-t vár (nem is fogad el mást, mert nagyon válogatós :) )
 */
function showProduct ( $product ) {
    
    if( !( isset($product) && ( is_a( $product, 'WC_Product_Simple' ) || is_a($product, 'WC_Product_Variable') ) ) ) {
        echo "Nincs megjeleníthető termék sajnos :( <br> (Ha te vagy a rendszergazda szedd össze magad különben ki leszel rúgva!!!)";
    } else {

    $ratingsVisibility = "";
    $oldPriceVisibility = "";

    if ( $product->get_average_rating() == 0 ) {
        $ratingsVisibility = "style='visibility: hidden;'";
    }

    if( !$product->is_on_sale() ) {
        $oldPriceVisibility = "style='visibility: hidden;'";
    }
?>

<div class="cardProductHolder">

    <?php if( $product->get_date_on_sale_to() == NULL && $product->is_on_sale() ) { ?>
        <div class="cardSpecialOffer">
            AKCIÓ
        </div>
    <?php } ?>

    <?php if( $product->get_date_on_sale_to() != NULL ) { ?>
        <div class="productSaleTimeLeft" data-sale-end="<?php echo $product->get_date_on_sale_to(); ?>"></div>
    <?php } ?>

    <div class="cardToolbox"><button></button></div>

    <a href="<?php echo $product->get_permalink(); ?>" class="cardProductContainer">
        <div class="cardHeading">
            <?php echo $product->get_image(); ?>
        </div>

        <div class="cardTitle">
            <?php echo $product->get_name(); ?>
        </div>

        <div class="cardRatings" <?php echo $ratingsVisibility; ?>>
            <div class="starsContainer">
                <i class="bi bi-star"></i>
                <i class="bi bi-star"></i>
                <i class="bi bi-star"></i>
                <i class="bi bi-star"></i>
                <i class="bi bi-star"></i>
                <div class="innerStarsContainer" style="width: <?php echo ( $product->get_average_rating() / 5 ) * 100 + 2; ?>%;">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                </div>
            </div>
        </div>

        <?php
            $currency = get_woocommerce_currency_symbol();

            if(is_a($product, 'WC_Product_Variable')) {
                $minPrice = $product->get_variation_regular_price( 'min' );
                $maxPrice = $product->get_variation_regular_price( 'max' ); 
                
                $salePercent =  $product->get_variation_price( 'min' ) / $minPrice * 100;
                $salePercent = ( $salePercent + ( $product->get_variation_price( 'max' ) / $maxPrice * 100 ) ) / 2;
        ?>
                <div class="cardPrice">
                    <p class="oldPrice" <?php echo $oldPriceVisibility; ?>>
                        <span class="oldPriceNum"><?php echo $minPrice . " " . $currency . " - " . $maxPrice . " " . $currency; ?></span>
                        <span class="priceSavingPercent">(<?php echo round( $salePercent , 0 ) - 100;  ?>%)</span>
                    </p>
                    <p class="newPrice"><?php echo $product->get_price_html(); ?></p>
                </div>
        <?php
            } else {
        ?>
            <div class="cardPrice">
                <p class="oldPrice" <?php echo $oldPriceVisibility; ?>>
                    <span class="oldPriceNum"><?php echo $product->get_regular_price() . " " . $currency; ?></span>
                    <span class="priceSavingPercent">(<?php echo round( wc_get_price_to_display( $product ) / $product->get_regular_price() * 100, 0 ) - 100;  ?>%)</span>
                </p>
                <p class="newPrice"><?php echo wc_get_price_to_display( $product ) . " " . $currency; ?></p>
            </div>
        <?php
            }
        ?>
    </a>
</div>

<?php
    } //149
}
?>