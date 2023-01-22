<?php
defined( 'ABSPATH' ) || exit;

/**
 * A termékek rendezését és szűrését teszi lehetővé.
 * Az archive-product oldalon lehet használni.
 * A megjelenített termékekről egyéb infókat és szűrési feltételeket lehet megkapni.
 * 
 * error_log( print_r( $attributeFilterCase, true ) ); //debug nagyon fajin :)
 * 
 */
class AjaxProductLoaderQueries {

    private $pref;
    private $tablePosts = "posts";
    private $tablePostmeta = "postmeta";
    private $tableTermRelationships = "term_relationships";
    private $tableTermTaxonomy = "term_taxonomy";
    private $tableTerms = "terms";
    private $tableWCProductMetaLookup = "wc_product_meta_lookup";
    private $tableWCCategoryLookup = "wc_category_lookup";
    private $tableWoocommerceAttributeTaxonomies = "woocommerce_attribute_taxonomies";
    private $wpdb;

    private $orderby;

    /**
     * Meg kell adnunk a $wpdb változót és a többi majd megy magától :).
     */
    public function __construct( $wpdb ) {
        $this->wpdb = $wpdb;
        $this->pref = $wpdb->base_prefix;

        $this->tablePosts = $this->pref.$this->tablePosts;
        $this->tablePostmeta = $this->pref.$this->tablePostmeta;
        $this->tableTermRelationships = $this->pref.$this->tableTermRelationships;
        $this->tableTermTaxonomy = $this->pref.$this->tableTermTaxonomy;
        $this->tableTerms = $this->pref.$this->tableTerms;
        $this->tableWCProductMetaLookup = $this->pref.$this->tableWCProductMetaLookup;
        $this->tableWCCategoryLookup = $this->pref.$this->tableWCCategoryLookup;
        $this->tableWoocommerceAttributeTaxonomies = $this->pref.$this->tableWoocommerceAttributeTaxonomies;
    }

    /**
     * Létrehozza és feltölti adatokkal az ideiglenes táblákat. 
     * AZ adatok egyszerűbb kezelhetősége miatt van erre szükség, így nem kell újra és újra lefuttatni az adott sql kódokat.
     */
    public function prepareTempTables ( $term_taxonomy_id, $orderby, $keyword, $attributeFilterObject, $priceFilter ) {
        $this->orderby = $orderby;

        $this->createTempTableForProducts();
        $this->storeProductsInTempTable( $term_taxonomy_id, $keyword, $priceFilter );

        $this->createTempTableForProductsWithAttributes();
        $this->storeProductsWithAttributesInTempTable();

        $this->createTempTableForFilteredProducts();
        $this->storeFilteredProductsInTempTable( $attributeFilterObject );

        $this->createTempTableForAttributes();
        $this->storeAttributesInTempTable();

        $this->createTempTableForAttributeValues();
        $this->storeAttributeValuesInTempTable();
    }

    /**
     * Létrehozza a megfelelő termékek tárolására alkalmas ideiglenes táblát.
     */
    private function createTempTableForProducts () {
        $this->wpdb->get_results('
            CREATE TEMPORARY TABLE products (
                ID	bigint(20) unsigned,
                price decimal(19,4),
                total_sales bigint(20),
                average_rating decimal(3,2),
                name text
            );
        ');
    }

    /**
     * A megfelelő termékeket etárolja az előzőleg létrehozott táblában.
     * Már csak azok a termékek lesznek eltárolva amire kerestünk, amely kategóriában vagyunk, 
     * amelynek az ára a felhasználó álta beállított értékek között van
     * és rendezve is vannak a megfelelő feltétel szerint.
     */
    private function storeProductsInTempTable ( $term_taxonomy_id, $keyword, $priceFilter ) {
        $this->wpdb->get_results('
            INSERT INTO products
            SELECT ID, price, total_sales, average_rating, name
            FROM (
                SELECT ID, post_date, post_content, post_title AS "name", post_excerpt, onsale, average_rating, total_sales, sku, min_price AS "price"
                FROM '.$this->tablePosts.' INNER JOIN '.$this->tableWCProductMetaLookup.' ON ID = product_id
                WHERE 
                    ( post_type = "product" AND post_status = "publish" ) 
                    '.$this->getSearching( $keyword ).' 
                    '.$this->getPriceFiltering( $priceFilter ).' 
            ) AS a 
            '.$this->getCategoryFiltering( $term_taxonomy_id ).';
        ');
    }
    
    /**
     * Létrehoz egy táblát, amelyben benne vannak a megfelelő termékek és az azokhoz tartozó attribútumok és attribútum értékek
     */
    private function createTempTableForProductsWithAttributes () {
        $this->wpdb->get_results('
            CREATE TEMPORARY TABLE productsWithAttributes (
                ID	bigint(20) unsigned,
                price decimal(19,4),
                taxonomy varchar(200),
                name varchar(200)
            );
        ');
    }

    /**
     * AZ előzőleg létrehozott táblát tölti fel termékekkel és attribútumokkal meg attribútum értékekkel
     */
    private function storeProductsWithAttributesInTempTable () {
        $this->wpdb->get_results('
            INSERT INTO productsWithAttributes
            SELECT ID, price, taxonomy, filterTable.name
            FROM products INNER JOIN (
                SELECT object_id, taxonomy, name
                FROM 
                '.$this->tableTermTaxonomy.' AS a 
                INNER JOIN '.$this->tableTerms.' AS b ON a.term_id = b.term_id
                INNER JOIN '.$this->tableTermRelationships.' AS c ON a.term_taxonomy_id = c.term_taxonomy_id
            ) AS filterTable ON ID = object_id;
        ');
    }

    /**
     * Létrehozza a megfelelő már attribútomok alapján szűrt termékek tárolására alkalmas ideiglenes táblát.
     */
    private function createTempTableForFilteredProducts () {
        $this->wpdb->get_results('
            CREATE TEMPORARY TABLE filteredProducts (
                ID	bigint(20) unsigned,
                price decimal(19,4)	
            );
        ');
    }

    /**
     * AZ előzőleg létrehozott táblát tölti fel attribútumok alapján szűrt termékekkel.
     */
    private function storeFilteredProductsInTempTable ( $attributeFilterObject ) {
        $this->wpdb->get_results('
            INSERT INTO filteredProducts
            SELECT productsWithAttributes.ID, productsWithAttributes.price
            FROM productsWithAttributes 
            '.$this->getAttributeFiltering( $attributeFilterObject ).' 
            GROUP BY productsWithAttributes.ID;
        ');
    }

    /**
     * Visszaadja az attribútumok és egyéb feltételek alapján szűrt termékek darabszámát, minimum és maximum árát.
     * 
     * @return object - count, min, max
     */
    public function getCountAndMinMaxPrice () {
        $results = $this->wpdb->get_results('
            SELECT COUNT(ID) AS "count", MIN(price) AS "min", MAX(price) AS "max"
            FROM filteredProducts;
        ', OBJECT);

        return $results[0];
    }

    /**
     * Visszaadja az attribútumok és egyéb feltételek alapján szűrt termékek id-ját.
     * 
     * @return array - termék id-kat tartalmaz.
     */
    public function getProductIds () {
        $results = $this->wpdb->get_results('
            SELECT filteredProducts.ID
            FROM filteredProducts INNER JOIN products ON filteredProducts.ID = products.ID
            '.$this->getOrdering( $this->orderby ).';
        ', OBJECT);

        $ids = null;
        for($i = 0; $i<count($results); $i++) {
            $ids[$i] = $results[$i]->ID;
        }

        return $ids == null ? null : $ids;
    }

    /**
     * Létrehozza a megfelelő attribútumok tárolására alkalmas ideiglenes táblát.
     */
    private function createTempTableForAttributes () {
        $this->wpdb->get_results('
            CREATE TEMPORARY TABLE attributes (
                attribute_label	varchar(200),
                attribute_name varchar(200)
            );
        ');
    }

    /**
     * A már szűrt termékekhez tartozó attribútumokat gyűjti össze és tárolja el az előzőleg létrehozott ideiglenes táblában.
     */
    private function storeAttributesInTempTable () {
        $this->wpdb->get_results('
            INSERT INTO attributes
            SELECT attribute_label, attribute_name
            FROM filteredProducts INNER JOIN (
                SELECT object_id, attribute_label, attribute_name
                FROM 
                    '.$this->tableTermTaxonomy.' AS a 
                    INNER JOIN '.$this->tableTerms.' AS b ON a.term_id = b.term_id
                    INNER JOIN '.$this->tableTermRelationships.' AS c ON a.term_taxonomy_id = c.term_taxonomy_id
                    INNER JOIN '.$this->tableWoocommerceAttributeTaxonomies.' ON taxonomy = CONCAT("pa_", attribute_name)
                WHERE taxonomy LIKE "pa_%"
            ) AS filters ON ID = object_id
            GROUP BY attribute_label;
        ');
    }

    /**
     * Az előzőleg eltárolt attribútumokat adja vissza.
     * Ezeket kapja majd meg a javasript és jeleníti meg a szűrőket.
     * 
     * @return object - attribute_label, attribute_name
     */
    public function getAttributes () {
        $results = $this->wpdb->get_results('
            SELECT attribute_label, attribute_name
            FROM attributes;
        ', OBJECT);

        return $results;
    }

    /**
     * Létrehozza a megfelelő attribútumokhoz tartalmazó valuek tárolására alkalmas ideiglenes táblát.
     */
    private function createTempTableForAttributeValues () {
        $this->wpdb->get_results('
            CREATE TEMPORARY TABLE attributeValues (
                name varchar(200),
                slug varchar(200),
                attribute_name varchar(200)
            );
        ');
    }

    /**
     * AZ előzőleg létrehozott táblában eltárolja a megfelelő attribútumokhoz kapcsolódo valuek-at.
     */
    private function storeAttributeValuesInTempTable () {
        $this->wpdb->get_results('
            INSERT INTO attributeValues
            SELECT filters.name, slug, filters.attribute_name
            FROM products INNER JOIN (
                SELECT taxonomy, name, slug, object_id, attribute_name
                FROM 
                    '.$this->tableTermTaxonomy.' AS a 
                    INNER JOIN '.$this->tableTerms.' AS b ON a.term_id = b.term_id
                    INNER JOIN '.$this->tableTermRelationships.' AS c ON a.term_taxonomy_id = c.term_taxonomy_id
                    INNER JOIN '.$this->tableWoocommerceAttributeTaxonomies.' ON taxonomy = CONCAT("pa_", attribute_name)
                WHERE taxonomy LIKE "pa_%"
            ) AS filters ON ID = object_id INNER JOIN attributes ON filters.attribute_name = attributes.attribute_name;
        ');
    }

    /**
     * Az előzőleg eltárolt attribútum valuek-at adja vissza.
     * Ezeket fogja megkapni a javascript és jeleníti meg a szűrőket.
     * 
     * @return object - name, slug, attribute_name
     */
    public function getAttributeValues () {
        $results = $this->wpdb->get_results('
            SELECT *
            FROM attributeValues
            GROUP BY slug;
        ', OBJECT);

        return $results;
    }

    /**
     * Minden egyes attribútum értékhez (value) megadja hány termék tartozik hozzá.
     * A javascript ezt is meg fogja kapni és ez is a szűrők megjelenítésénél játszik nagy szerepet.
     * 
     * @return object - count, name
     */
    public function getProductCountOfEachAttributeValue () {
        $results = $this->wpdb->get_results('
            SELECT COUNT(name) AS "count", name
            FROM attributeValues
            GROUP BY slug;
        ', OBJECT);

        return $results;
    }

    /**
     * A megadott orderby paraméter alapján létrehozz egy SQL kódot,
     * amivel majd rendezni lehet az adatbázisból kiválasztott termékeket. 
     * 
     * @param orderby - a paraméter ami alapján majd rendezzünk a termékeket (pl.: total_sales DESC)
     * @return sqlCode
     */
    private function getOrdering ( $orderby ) {
        if($orderby == null) {
            return '';
        } else {
            return 'ORDER BY products.'.$this->wpdb->_escape($orderby);
        }
    }

    /**
     * A megadott kulcsszó alapján előkészíti az SQL kódot,
     * amivel majd tudunk keresni az adatbázisban a termékek között.
     * 
     * @param keyword - a kulcsszó ami alapján majd keresünk a termékek között.
     * @return searchQuery - egyszerű szöveg SQL
     */
    private function getSearching ( $keyword ) {
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
     * A megadott kategória ID alapján előkészíti az SQL kódot,
     * amivel majd lehet a termék kategóriák alapján szűkíteni a találatokat.
     * 
     * @param categoryId - a megadott kategória ID (term_taxonomy_id)
     * @return categoryQuery - egyszerű szöveg SQL
     */
    private function getCategoryFiltering ( $categoryId ) {
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

    /**
     * A megadott attribútumos szűrési feltételeket tartalmazó objektum alapján elkészíti az SQL kódot
     * amivel majd lehet a termék attribútumok alapján szűkíteni a találatokat.
     * 
     * @param attributeFilterObject - a megadott attribútumokat tartalmazó objectum
     * @return attributeFilterQuery - egyszerű szöveg SQL
     */
    public function getAttributeFiltering ( $attributeFilterObject ) {
        if($attributeFilterObject != null) {
            $attributeFilterQuery = '';
            $index = 0;

            foreach ( $attributeFilterObject as $key => $value ) {
                $this->generateTempTableForFilterCase( 'tempiTable'.$index, $this->wpdb->_escape( $key ), $value );

                $attributeFilterQuery .= '
                    INNER JOIN tempiTable'.$index.' ON productsWithAttributes.ID = tempiTable'.$index.'.ID  
                ';

                $index++;
            }

            return $attributeFilterQuery;
        }

        return '';
    }

    /**
     * A megadott attribútum név és ahhoz tratozó értékek alapján elkészít egy SQL kódot,
     * amivel majd szűkíteni lehet a találatokat
     * 
     * @param attributeName - a megadott aattribútum neve
     * @param values - a megadott megadott attribútumhoz tartozó értékek
     * @return attributeFilterCase - egyszerű szöveg SQL ( amely egy táblát ír le )
     */
    private function generateTempTableForFilterCase ( $tableName, $attributeName, $values ) {
        $this->wpdb->get_results('
            CREATE TEMPORARY TABLE '.$tableName.' (
                ID	bigint(20) unsigned
            );
        ');

        $this->wpdb->get_results('
            INSERT INTO '.$tableName.'
            SELECT ID
            FROM productsWithAttributes
            WHERE taxonomy = CONCAT( "pa_", "'.$attributeName.'" ) AND name IN ( '.$this->getAttributeFilterValues( $values ).' );
        ');
    }

    /**
     * Egy adott attribútumhoz tartozó értékek alapján készít egy SQL kódot amely majd az ( IN () be kerül )
     * 
     * @param values - Egy adott attribútumhoz tartozó értékek gyűjteménye ( tömb )
     * @return SQL - a szűrő lekérdezés WHERE részébe kerül majd
     */
    private function getAttributeFilterValues ( $values ) {
        $attributeFilterValues = '"'.$this->wpdb->_escape( $values[0] ).'"';

        for( $i = 1; $i < count( $values ); $i++ ) {
            $attributeFilterValues .= ', "'.$this->wpdb->_escape( $values[$i] ).'"';
        }

        return $attributeFilterValues;
    }

    /**
     * Elkészíti az ár szűrését lehetővé tevvő SQL kódot.
     * 
     * @param priceFilter - benne van a minimum, maximum ár vagy null ha nem akarunk szűrni
     * @return priceFilterQuery -  SQL kód, ár szúrést tesz lehetővé
     */
    private function getPriceFiltering ( $priceFilter ) {
        if($priceFilter != null) {
            $priceFilterQuery = $this->wpdb->prepare('
                AND ( CAST(min_price AS DECIMAL) >= %d AND CAST(min_price AS DECIMAL) <= %d )
            ', array($priceFilter['minPrice'], $priceFilter['maxPrice']));

            return $priceFilterQuery;
        }

        return '';
    }
}