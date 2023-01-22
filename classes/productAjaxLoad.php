<?php
defined( 'ABSPATH' ) || exit;

/**
 * Az url paramétereknek megfelelően szűri és adja vissza a termékekekt.
 * Meg lehet ez a függvény közvetlenül is hívni, 
 * de meghívhatjuk a wordpressen keresztül is ajax hívással.
 */
function getProduct( $direct ) {

    global $wpdb;
    $productQueries = new AjaxProductLoaderQueries($wpdb);
    $productOrdering = new ProductOrdering();

    $term_taxonomy_id = null;
    $queriedObject = get_queried_object();

    if( isset( $queriedObject ) && is_a( $queriedObject, 'WP_Term' ) ) {
        $term_taxonomy_id = $queriedObject->term_taxonomy_id;
    } else {
        $term_taxonomy_id = isset($_REQUEST['term_taxonomy_id']) ? $_REQUEST['term_taxonomy_id'] : null;
    }

    $order = $productOrdering->getOrder();
    $searchKeyword = isset($_REQUEST['s']) ? $_REQUEST['s'] : null;
    $attributeFilters = isset($_REQUEST['filters']) ? $_REQUEST['filters'] : null;
    $priceFilter = null;

    if(isset($_REQUEST['minPrice']) && isset($_REQUEST['maxPrice'])) {
        $priceFilter['minPrice'] = $_REQUEST['minPrice'];
        $priceFilter['maxPrice'] = $_REQUEST['maxPrice'];

    }

    $jsonFilterObject = stripslashes($attributeFilters == '{}' ? null : $attributeFilters);
    $filterObject = json_decode($jsonFilterObject);


    $productQueries->prepareTempTables($term_taxonomy_id, $order, $searchKeyword, $filterObject, $priceFilter);


    $productIDs = $productQueries->getProductIds();


    $ajaxReturnData = array ();
    $ajaxReturnData['resultCount'] = $productQueries->getCountAndMinMaxPrice()->count;
    $ajaxReturnData['minPrice'] = $productQueries->getCountAndMinMaxPrice()->min;
    $ajaxReturnData['maxPrice'] = $productQueries->getCountAndMinMaxPrice()->max;

    $ajaxReturnData['attributes'] = $productQueries->getAttributes();
    $ajaxReturnData['attributeValues'] = $productQueries->getAttributeValues();
    $ajaxReturnData['productCountOfEachAttributeValue'] = $productQueries->getProductCountOfEachAttributeValue();

    $ajaxReturnData['products'] = '';


    ob_start();
    if($productIDs != null) {
        for($i = 0; $i<count($productIDs); $i++) {
            echo '<div class="col-6 col-sm-4 col-md-6 col-lg-4 col-xl-3 productCol">';
            showProduct(wc_get_product($productIDs[$i]));
            echo '</div>';
        }
    } else {
        echo ('
            <div class="row mt-3 productRow" id="rowForProducts">
                <div class="col-12">
                    <p class="woocommerce-info noProductFound">
                        Egy termék se felelt meg a keresésnek.
                    </p>
                </div>
            </div>
        ');
    }

    $ajaxReturnData['products'] = ob_get_contents();
    ob_end_clean();

    echo json_encode($ajaxReturnData);
    
    if( !( isset( $direct ) && $direct == 'direct' ) ) {
        die();
    }
}

// This bit is a special action hook that works with the WordPress AJAX functionality.
add_action( 'wp_ajax_getProduct', 'getProduct' );
add_action( 'wp_ajax_nopriv_getProduct', 'getProduct' ); 

?>