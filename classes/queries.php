<?php
defined( 'ABSPATH' ) || exit;

/**
 * Ez az osztály segítségével lehetőségünk van az adatbázisból kigyűjteni megadott termékek ID-jét.
 * Ezzel az ID-vel wc_get_products al le tudjuk kérni a megadott termékeket.
 * Tehát az osztály metódusai visszaadnak egy megfelelően szerkeztett array-t az ID-k ből,
 * amelyet oda tudunk adni a wc_get_products() metódusnak közvetlenül és az visszaadja a kívánt termékek minden adatát.
 */

class FrontPageProductSliderQueries {

    private $postsAndPostmetaJoin;
    private $pref;
    private $tablePosts = "posts";
    private $tablePostmeta = "postmeta";
    private $tableTermRelationships = "term_relationships";
    private $tableTermTaxonomy = "term_taxonomy";
    private $tableTerms = "terms";
    private $tableWCProductMetaLookup = "wc_product_meta_lookup";
    private $tableWCCategoryLookup = "wc_category_lookup";
    private $wpdb;

    /**
     * Meg kell adnunk a $wpdb változót és a többi majd megy magától :).
     */
    public function __construct($wpdb) {
        $this->wpdb = $wpdb;
        $this->pref = $wpdb->base_prefix;

        $this->tablePosts = $this->pref.$this->tablePosts;
        $this->tablePostmeta = $this->pref.$this->tablePostmeta;
        $this->tableTermRelationships = $this->pref.$this->tableTermRelationships;
        $this->tableTermTaxonomy = $this->pref.$this->tableTermTaxonomy;
        $this->tableTerms = $this->pref.$this->tableTerms;
        $this->tableWCProductMetaLookup = $this->pref.$this->tableWCProductMetaLookup;
        $this->tableWCCategoryLookup = $this->pref.$this->tableWCCategoryLookup;

        $this->postsAndPostmetaJoin = $this->tablePosts.' LEFT JOIN '.$this->tablePostmeta.' ON ID = post_id';
    }

    /**
     * Visszaadja azoknak a termékeknek az ID-ját egy array-ban amelyeknél az akció
     * csak egy limitált ideig tart.
     * 
     * @param limit - Maximum ennyi termék ID-jét adja vissza.
     * @return array amely tartalmazza az ID-kat.
     */
    public function getLimitedTimeSaleProducts ($limit) {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare('
                SELECT ID
                FROM '.$this->postsAndPostmetaJoin.'
                WHERE post_type = %s AND post_status = %s AND meta_key = %s
                LIMIT %d
            ', array("product", "publish", "_sale_price_dates_to", $limit))
        , OBJECT);
        
        return $this->generateWCIDsFromSQLResult($results);
    }

    /**
     * Visszaadja azoknak a termékeknek az ID-ját egy array-ben amelyek akciósak,
     * de NEM limitált ideig.
     * 
     * @param limit - Maximum ennyi termék ID-jét adja vissza.
     * @return array amely tartalmazza az ID-kat.
     */
    public function getSaleProducts ($limit) {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare('
                SELECT ID
                FROM '.$this->postsAndPostmetaJoin.' LEFT JOIN (
                    SELECT ID AS "limitedSaleID"
                    FROM '.$this->postsAndPostmetaJoin.'
                    WHERE post_type = %s AND post_status = %s AND meta_key = %s
                ) AS limitedSale ON ID = limitedSaleID
                WHERE post_type = %s AND post_status = %s AND meta_key = %s AND limitedSaleID is NULL
                LIMIT %d           
            ', array("product", "publish", "_sale_price_dates_to", "product", "publish", "_sale_price", $limit))
        , OBJECT);
        
        return $this->generateWCIDsFromSQLResult($results);
    }

    /**
     * Visszaadja a legtöbbet eladott termékeket. A legtöbbet eladott kerül a legelejére.
     * 
     * @param limit - Maximum ennyi termék ID-jét adja vissza.
     * @return array amely tartalmazza az ID-kat.
     */
    public function getBestSellerProducts ($limit) {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare('
                SELECT ID
                FROM '.$this->postsAndPostmetaJoin.'
                WHERE post_type = %s AND post_status = %s AND meta_key = %s
                ORDER BY meta_value DESC
                LIMIT %d
            ', array("product", "publish", "total_sales", $limit))
        , OBJECT);

        return $this->generateWCIDsFromSQLResult($results);
    }

    /**
     * Egy adott kategóriához tartozó termékek ID-jét adja vissza.
     * 
     * @param categoryName - A kívánt kategória neve
     * @param limit - Maximum ennyi termék ID-jét adja vissza.
     * @return array amely tartalmazza az ID-kat.
     */
    public function getProductsForCategory ($categoryName, $limit) {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare('
                SELECT e.ID
                FROM 
                    '.$this->tableTermTaxonomy.' AS a INNER JOIN '.$this->tableTerms.' AS b ON a.term_id = b.term_id
                    INNER JOIN '.$this->tableWCCategoryLookup.' AS c ON a.term_taxonomy_id = c.category_tree_id
                    INNER JOIN '.$this->tableTermRelationships.' AS d ON c.category_id = d.term_taxonomy_id
                    INNER JOIN '.$this->tablePosts.' AS e ON d.object_id = e.ID
                WHERE 
                    a.taxonomy = "product_cat" 
                    AND post_type = "product" 
                    AND post_status = "publish"
                    AND b.name = %s
                LIMIT %d
            ', array($categoryName, $limit))
        , OBJECT);
        
        return $this->generateWCIDsFromSQLResult($results);
    }

    /**
     * Egy adott SQL lekérdezésből amely csak a termékek ID-ját tartalmazza létrehoz egy tömböt amely szintén az ID-kat tartalmazza
     * csak olyan formában mit a woocommerce elfogad.
     * 
     * @param results - SQL lekérdezés eredménye amelyben termék ID-k vannak
     * @return array
     */
    private function generateWCIDsFromSQLResult ($results) {
        $ids = NULL;
        for($i = 0; $i<count($results); $i++)
            $ids[$i] = $results[$i]->ID;
        
        if($ids == NULL)
            $ids[0] = -1;

        return array('include' => $ids);
    }

    /**
     * Visszaadja a paraméterek alapján beállított termékek ID-ját.
     * 
     * @param
     * @return array amely tartalmazza az ID-kat.
     */
    public function getSpecificProducts ($term_taxonomy_id, $orderby, $keyword, $attributeFilterObject, $priceFilter) {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare('
                SELECT ID
                FROM (
                    SELECT ID, post_date, post_content, post_title AS "name", post_excerpt, onsale, average_rating, total_sales, sku, min_price AS "price"
                    FROM '.$this->tablePosts.' INNER JOIN '.$this->tableWCProductMetaLookup.' ON ID = product_id
                    WHERE 
                        ( post_type = "product" AND post_status = "publish" ) 
                        '.$this->getSearching($keyword).' 
                        '.$this->getPriceFiltering($priceFilter).' 
                ) AS a 
                '.$this->getCategoryFiltering($term_taxonomy_id).' 
                '.$this->getAttributeFiltering($attributeFilterObject).' 
                '.$this->getOrdering($orderby).' 
            ', array(NULL))
        , OBJECT);
        
        $ids = null;
        for($i = 0; $i<count($results); $i++) {
            $ids[$i] = $results[$i]->ID;
        }

        if($ids == null) {
            return null;
        } else {
            return $ids;
        }
    }

    /**
     * A megadott orderby paraméter alapján létrehozz egy SQL kódot,
     * amivel majd rendezni lehet az adatbázisból kiválasztott termékeket. 
     * 
     * @param orderby - a paraméter ami alapján majd rendezzünk a termékeket (pl.: total_sales DESC)
     * @return sqlCode
     */
    private function getOrdering ($orderby) {
        if($orderby == null) {
            return '';
        } else {
            return 'ORDER BY '.$this->wpdb->_escape($orderby);
        }
    }

    /**
     * A megadott kulcsszó alapján előkészíti az SQL kódot,
     * amivel majd tudunk keresni az adatbázisban a termékek között.
     * 
     * @param keyword - a kulcsszó ami alapján majd keresünk a termékek között.
     * @return searchQuery - egyszerű szöveg SQL
     */
    private function getSearching ($keyword) {
        if($keyword != null) {
            $searchQuery = $this->wpdb->prepare('
                AND (
                    post_title LIKE %s
                    OR post_content LIKE %s
                    OR post_excerpt LIKE %s
                    OR sku LIKE %s
                )
            ', array("%".$keyword."%", "%".$keyword."%", "%".$keyword."%", "%".$keyword."%"));

            return $searchQuery;
        }

        return '';
    }

    /**
     * A megadott categória ID alapján előkészíti az SQL kódot,
     * amivel majd lehet a termék kategóriák alapján szűkíteni a találatokat.
     * 
     * @param categoryId - a megadott kategória ID (term_taxonomy_id)
     * @return categoryQuery - egyszerű szöveg SQL
     */
    private function getCategoryFiltering ($categoryId) {
        if($categoryId != null) {
            $categoryQuery = $this->wpdb->prepare('
            INNER JOIN (
                
                SELECT object_id
                FROM '.$this->tableWCCategoryLookup.' INNER JOIN '.$this->tableTermRelationships.' ON category_id = term_taxonomy_id
                WHERE category_tree_id IN (%d)
            
            ) AS b ON ID = object_id
            ', array($categoryId));

            return $categoryQuery;
        }

        return '';
    }

    public function getAttributeFiltering ($attributeFilterObject) {
        if($attributeFilterObject != null) {

            $attributeFilterQuery = '
            INNER JOIN (
                
                SELECT
                    '.$this->getAttributeFilterCases($attributeFilterObject).'
                    object_id
                FROM (
                    SELECT object_id, taxonomy, name
                    FROM 
                        wp_term_taxonomy AS a 
                        INNER JOIN wp_terms AS b ON a.term_id = b.term_id
                        INNER JOIN wp_term_relationships AS c ON a.term_taxonomy_id = c.term_taxonomy_id
                    WHERE taxonomy LIKE "pa_%"
                ) AS tableA
                GROUP BY object_id
                HAVING 
                    '.$this->getAttributeFilterValues($attributeFilterObject).'
            
            ) AS tableFilter ON ID = object_id
            ';

            return $attributeFilterQuery;
        }

        return '';
    }

    private function getAttributeFilterCases ($attributeFilterObject) {
        $attributeFilterCases = "";

        foreach ($attributeFilterObject as $key => $value) {
            
            $rowName = $this->wpdb->_escape(implode("", explode("-", $key)));
            $attributeFilterCases .= 'MAX(CASE WHEN taxonomy = "pa_'.$this->wpdb->_escape($key).'" THEN name ELSE NULL END) AS '.$rowName.', '.PHP_EOL;
        }

        return $attributeFilterCases;
    }

    private function getAttributeFilterValues ($attributeFilterObject) {
        $attributeFilterValues = "";

        foreach ($attributeFilterObject as $key => $value) {

            $rowName = $this->wpdb->_escape(implode("", explode("-", $key)));

            $values = "";

            foreach ($value as $key => $value) {
                if($values == "") {
                    $values .= '"'.$this->wpdb->_escape($value).'"';
                } else {
                    $values .= ', "'.$this->wpdb->_escape($value).'"';
                }
            }

            if($attributeFilterValues == "") {
                $attributeFilterValues .= '( '.$rowName.' IN  ( '.$values.' ) ) '.PHP_EOL;
            } else {
                $attributeFilterValues .= 'AND ( '.$rowName.' IN  ( '.$values.' ) ) '.PHP_EOL;
            }
    
        }

        return $attributeFilterValues;
    }

    private function getPriceFiltering ($priceFilter) {
        if($priceFilter != null) {
            $priceFilterQuery = $this->wpdb->prepare('
                AND ( CAST(min_price AS DECIMAL) >= %d AND CAST(min_price AS DECIMAL) <= %d )
            ', array($priceFilter['minPrice'], $priceFilter['maxPrice']));

            return $priceFilterQuery;
        }

        return '';
    }
}
?> 