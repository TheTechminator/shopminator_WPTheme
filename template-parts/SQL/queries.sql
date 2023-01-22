/**
 * Összes akciós termék (limitált és nem limitált egyaránt beletartozik)
 */

SELECT ID
FROM wp_posts LEFT JOIN wp_postmeta ON ID = post_id
WHERE post_type = "product" AND post_status = "publish" AND meta_key = "_sale_price"
LIMIT 10

/** 
 * Limitált ideig akciós termékek
 */

SELECT ID
FROM wp_posts LEFT JOIN wp_postmeta ON ID = post_id
WHERE post_type = "product" AND post_status = "publish" AND meta_key = "_sale_price_dates_to"
LIMIT 10


/**
 * Azok az akciós termékek amik NEM limitált ideig akciósak
 */

SELECT ID
FROM wp_posts LEFT JOIN wp_postmeta ON ID = post_id LEFT JOIN (
	SELECT ID AS "limitedSaleID"
	FROM wp_posts LEFT JOIN wp_postmeta ON ID = post_id
	WHERE post_type = "product" AND post_status = "publish" AND meta_key = "_sale_price_dates_to"
) AS limitedSale ON ID = limitedSaleID
WHERE post_type = "product" AND post_status = "publish" AND meta_key = "_sale_price" AND limitedSaleID is NULL
LIMIT 10

/**
 * Minden termék megjelenítése
 */

SELECT *
FROM wp_posts
WHERE post_type = "product" AND post_status = "publish";


/**
 * Termékek megjelenítése eladások alapján (A legtöbbet eladott kerül a legelejére)
 */

SELECT ID
FROM wp_posts LEFT JOIN wp_postmeta ON ID = post_id
WHERE post_type = "product" AND post_status = "publish" AND meta_key = "total_sales"
ORDER BY meta_value DESC
LIMIT 10


/**
 * Visszaadja egy megadott kategória és az összes ahhoz tartozó alkategória termékének ID-jét.
 * Kategória ID-t kell megadni.
 */

SELECT object_id
FROM wp_wc_category_lookup INNER JOIN wp_term_relationships ON category_id = term_taxonomy_id
WHERE category_tree_id IN (16)

/**
 * Visszaadja egy megadott kategória és az összes ahhoz tartozó alkategória termékének ID-jét.
 * Kategória nevet kell megadni.
 */

SELECT e.ID
FROM 
    wp_term_taxonomy AS a INNER JOIN wp_terms AS b ON a.term_id = b.term_id
    INNER JOIN wp_wc_category_lookup AS c ON a.term_taxonomy_id = c.category_tree_id
    INNER JOIN wp_term_relationships AS d ON c.category_id = d.term_taxonomy_id
	INNER JOIN wp_posts AS e ON d.object_id = e.ID
WHERE 
	a.taxonomy = "product_cat" 
	AND post_type = "product" 
	AND post_status = "publish"
	AND b.name = "Laptop | Notebook"
LIMIT 10


/**
 * Valami ideiglenes baromság
 */

SELECT post_id as ID,
	MAX(CASE WHEN meta_key = 'total_sales' THEN meta_value END) AS 'total_sales'
FROM wp_postmeta
WHERE post_id = 36
GROUP BY ID

/**
 * Visszaadja az összes terméket a megfelelő oszlopokkal
 */

SELECT ID, post_date, post_content, name, post_excerpt, onsale, average_rating, total_sales, sku, price
FROM (

	SELECT ID, post_date, post_content, post_title AS 'name', post_excerpt, onsale, average_rating, total_sales, sku, min_price AS 'price'
	FROM wp_posts INNER JOIN wp_wc_product_meta_lookup ON ID = product_id
	WHERE 
		( post_type = "product" AND post_status = "publish" )
		AND (
			post_title LIKE "%pró%"
			OR post_content LIKE "%pró%"
			OR post_excerpt LIKE "%pró%"
			OR sku LIKE "%pró%"
		)

) AS a INNER JOIN (

	SELECT object_id
	FROM wp_wc_category_lookup INNER JOIN wp_term_relationships ON category_id = term_taxonomy_id
	WHERE category_tree_id IN (16)

) AS b ON  ID = object_id
ORDER BY total_sales DESC

/**
 * Szűrés attribútumok alapján
 */

SELECT *
FROM 
	wp_term_taxonomy AS a 
	INNER JOIN wp_terms AS b ON a.term_id = b.term_id
	INNER JOIN wp_term_relationships AS c ON a.term_taxonomy_id = c.term_taxonomy_id
WHERE 
	taxonomy IN ('pa_processzor-gyarto', 'pa_processzor-tipusa', 'pa_processzor-tipusa')
	AND name IN ('Intel®', 'i7', 'i5')





SELECT object_id, COUNT(object_id) AS dbEgyezes
FROM 
	wp_term_taxonomy AS a 
	INNER JOIN wp_terms AS b ON a.term_id = b.term_id
	INNER JOIN wp_term_relationships AS c ON a.term_taxonomy_id = c.term_taxonomy_id
WHERE 
	taxonomy IN ('pa_processzor-gyarto', 'pa_processzor-tipusa', 'pa_processzor-tipusa')
	AND name IN ('Intel®', 'i7', 'i5')
GROUP BY object_id
HAVING dbEgyezes = (
	SELECT MAX(dbEgyezes) AS maxEgyezes
	FROM (
		SELECT object_id, COUNT(object_id) AS dbEgyezes
		FROM 
			wp_term_taxonomy AS a 
			INNER JOIN wp_terms AS b ON a.term_id = b.term_id
			INNER JOIN wp_term_relationships AS c ON a.term_taxonomy_id = c.term_taxonomy_id
		WHERE 
			taxonomy IN ('pa_processzor-gyarto', 'pa_processzor-tipusa', 'pa_processzor-tipusa')
			AND name IN ('Intel®', 'i7', 'i5')
		GROUP BY object_id
	) AS ga
)





SELECT object_id
FROM (
	SELECT
		MAX(CASE WHEN taxonomy = 'pa_processzor-gyarto' THEN name ELSE NULL END) AS processzorGyarto,
		MAX(CASE WHEN taxonomy = 'pa_processzor-tipusa' THEN name ELSE NULL END) AS processzorTipusa,
		object_id
	FROM (
		SELECT object_id, taxonomy, name
		FROM 
			wp_term_taxonomy AS a 
			INNER JOIN wp_terms AS b ON a.term_id = b.term_id
			INNER JOIN wp_term_relationships AS c ON a.term_taxonomy_id = c.term_taxonomy_id
		WHERE taxonomy LIKE 'pa_%'
	) AS tableA
	GROUP BY object_id
	HAVING 
		processzorGyarto IS NOT NULL 
		AND processzorTipusa IS NOT NULL 
		AND ( processzorGyarto IN  ('Intel®') )
		AND ( processzorTipusa IN  ('i7', 'i5') )
) AS tableFilter

/**
 * Ideiglenes tábla létrehozása és használata
 */

CREATE TEMPORARY TABLE temp_teszt1
SELECT ID FROM wp_posts
LIMIT 0;

INSERT INTO temp_teszt1
SELECT ID
FROM wp_posts;

SELECT *
FROM temp_teszt1;

TRUNCATE TABLE temp_teszt1;


/**
 * Product Filters for ajax loading
 */

CREATE TEMPORARY TABLE products (
	ID	bigint(20) unsigned,
	price decimal(19,4),
	total_sales bigint(20),
	average_rating decimal(3,2),
	name text
);

INSERT INTO products
SELECT ID, price, total_sales, average_rating, name
FROM (

    SELECT ID, post_date, post_content, post_title AS 'name', post_excerpt, onsale, average_rating, total_sales, sku, min_price AS 'price'
    FROM wp_posts INNER JOIN wp_wc_product_meta_lookup ON ID = product_id
    WHERE 
        ( post_type = "product" AND post_status = "publish" )
        AND (
            post_title LIKE "%pró%"
            OR post_content LIKE "%pró%"
            OR post_excerpt LIKE "%pró%"
            OR sku LIKE "%pró%"
        )

) AS a;

CREATE TEMPORARY TABLE productsWithAttributes (
	ID	bigint(20) unsigned,
	price decimal(19,4),
    taxonomy varchar(200),
	name varchar(200)
);

INSERT INTO productsWithAttributes
SELECT ID, price, taxonomy, filterTable.name
FROM products INNER JOIN (
    SELECT object_id, taxonomy, name
    FROM 
    wp_term_taxonomy AS a 
    INNER JOIN wp_terms AS b ON a.term_id = b.term_id
    INNER JOIN wp_term_relationships AS c ON a.term_taxonomy_id = c.term_taxonomy_id
) AS filterTable ON ID = object_id;

CREATE TEMPORARY TABLE filteredProducts (
	ID	bigint(20) unsigned,
	price decimal(19,4)	
);

INSERT INTO filteredProducts
SELECT productsWithAttributes.ID, productsWithAttributes.price
FROM productsWithAttributes INNER JOIN (
	SELECT *
	FROM productsWithAttributes
	WHERE taxonomy = CONCAT( 'pa_', 'processzor-gyarto' ) AND name IN ( 'Intel®' )
) AS f0 ON productsWithAttributes.ID = f0.ID INNER JOIN (
	SELECT *
	FROM productsWithAttributes
	WHERE taxonomy = CONCAT( 'pa_', 'processzor-tipusa' ) AND name IN ( 'i7', 'i5' )
) AS f1 ON productsWithAttributes.ID = f1.ID
GROUP BY productsWithAttributes.ID;

SELECT COUNT(ID) AS 'count', MIN(price) AS 'min', MAX(price) AS 'max'
FROM filteredProducts;

SELECT filteredProducts.ID
FROM filteredProducts INNER JOIN products ON filteredProducts.ID = products.ID
ORDER BY products.name ASC;

CREATE TEMPORARY TABLE attributes (
	attribute_label	varchar(200),
	attribute_name varchar(200)
);

INSERT INTO attributes
SELECT attribute_label, attribute_name
FROM filteredProducts INNER JOIN (
	SELECT object_id, attribute_label, attribute_name
	FROM 
		wp_term_taxonomy AS a 
		INNER JOIN wp_terms AS b ON a.term_id = b.term_id
		INNER JOIN wp_term_relationships AS c ON a.term_taxonomy_id = c.term_taxonomy_id
        INNER JOIN wp_woocommerce_attribute_taxonomies ON taxonomy = CONCAT('pa_', attribute_name)
	WHERE taxonomy LIKE 'pa_%'
) AS filters ON ID = object_id
GROUP BY attribute_label;

SELECT attribute_label, attribute_name
FROM attributes;

CREATE TEMPORARY TABLE attributeValues (
	name varchar(200),
	slug varchar(200),
	attribute_name varchar(200)
);

INSERT INTO attributeValues
SELECT filters.name, slug, filters.attribute_name
FROM products INNER JOIN (
	SELECT taxonomy, name, slug, object_id, attribute_name
	FROM 
		wp_term_taxonomy AS a 
		INNER JOIN wp_terms AS b ON a.term_id = b.term_id
		INNER JOIN wp_term_relationships AS c ON a.term_taxonomy_id = c.term_taxonomy_id
        INNER JOIN wp_woocommerce_attribute_taxonomies ON taxonomy = CONCAT('pa_', attribute_name)
	WHERE taxonomy LIKE 'pa_%'
) AS filters ON ID = object_id INNER JOIN attributes ON filters.attribute_name = attributes.attribute_name;

SELECT *
FROM attributeValues
GROUP BY slug;

SELECT COUNT(name) AS 'count', name
FROM attributeValues
GROUP BY slug;