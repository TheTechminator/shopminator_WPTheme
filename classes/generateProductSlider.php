<?php
/**
 * A fájl tartalmazza a főoldalon megjelenő slider-eket (Ezekben vannak a termékek)
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory()."/classes/productCardForSlider.php";


/**
 * A megadott paraméterek alapján létrehoz egy swiper slider es slidert,
 * melyet feltölt azokkal az termékekkel amiket meg adunk neki paraméterbe.
 * 
 * @param title - az adott sor címe (p.: Akciós termékek) a slider fölött ez jelenik meg
 * @param products - a megjeleníteni kívánt termékek (típusa: WC_Product_Simple)
 * @param before - a slider és a title elött jelenik meg, ez lesz az ami majd összefogja az egészet (pl.: <div>)
 * @param after - a slider után jelenik meg, értelem szerűen ez a befor bezárója (pl.: </div>)
 * @return - nem ad semmit vissza, hanem a függvény meghívásakor kiírja a html códot
 */

function generateProductSlider($title = "", $products = NULL, $before = "", $after = "") {
   
    if($products != NULL) {
        echo $before;
?>

        <h3 class="mb-3"><?php echo $title; ?></h3>

        <div class="swiper swiperSale">
            <div class="swiper-wrapper">
                <?php
                    for($i = 0; $i<count($products); $i++) { 
                        echo '<div class="swiper-slide">';
                            showProduct($products[$i]);
                        echo '</div>';
                    }
                ?>
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    
<?php
        echo $after;
    }
}

?>