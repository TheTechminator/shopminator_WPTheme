<?php
/**
 * A termék kategoriák megjelenítését segítő osztály.
 * Elkéri a woocommerce-től a termék kategóriákat a tömbként,
 * majd megjeleníti egy megfelelő html-es linkes stílusban, 
 * amit visszaad.
 * 
 * 
 * @package Shopminator
 */
defined( 'ABSPATH' ) || exit;

class GetProductCategories {

    private $args;
    private $product_categories;

    public function __construct() {
        $this->args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => 'name',
            'show_count'   => 1,    // 1 for yes, 0 for no
            'pad_counts'   => 1,    // 1 for yes, 0 for no
            'hierarchical' => 1,    // 1 for yes, 0 for no
            'title_li'     => '',
            'hide_empty'   => 0
        );

        $this->product_categories = get_categories($this->args);
    }

    /**
     * Egy adott fő szölű kategória kiválasztott gyerekéhez visszaadja az összes gyereket (unokát).
     * Csak mobilon.
     * 
     * @param productCategories - tartalmazza az összes kategóriát (szülők és gyerekek)
     * @param parentCatId - Egy adott fő szülő gyerekének az ID-je, tehét ennek a szülője
     * @return htmlCode
     */
    private function grandChildsForMobile ($productCategories, $parentCatId) {
        $prodCats = '';

        foreach($productCategories as $prods) {
            if($prods -> parent == $parentCatId) {
                $prodCats .= '<a class="nav-link mobileProductCategoryGrandChild" href="' . get_term_link($prods -> slug, 'product_cat') . '">' . $prods -> cat_name . '</a>';
            }
        }

        return $prodCats;
    }

    /**
     * A megadott szülő kategóriához visszaadja az összes gyereket.
     * Csak mobilon.
     * 
     * @param productCategories - tartalmazza az összes kategóriát (szülők és gyerekek)
     * @param parentCategory - Egy kiválasztott szülő amelyhez visszaadja a gyerekeket.
     * @return htmlCode - Vagy semmi ha nincs gyerek vagy pedig a gyerekek
     */
    private function childsForMobile ($productCategories, $parentCategory) {
        $prodCats = '
        <div class ="dropDownMenu">
            <span class="dropDownMenuClose">
                <i class="bi bi-chevron-left"></i>Vissza
            </span>

            <a class="nav-link goToParentCategory" href="' . get_term_link($parentCategory -> slug, 'product_cat') . '">
                Tovább a(z) ' . $parentCategory -> cat_name . ' kategóriára
            </a>
        ';

        foreach($productCategories as $prods) {
            if($prods -> parent == $parentCategory -> cat_ID) {
                $prodCats .= '<a class="nav-link mobileProductCategoryChild" href="' . get_term_link($prods -> slug, 'product_cat') . 
                '">' . $prods -> cat_name . '</a>' .
                $this->grandChildsForMobile($productCategories, $prods -> cat_ID);
            }
        }

        $prodCats .= "</div>";

        return $prodCats;
    }

    /**
     * Egy az egyben létrehozza és visszaadja az összes kategóriát hierarhikus formában.
     * Tehét visszaadja a szülőket és a gyerekeket (+unokákat). Ha mobilon rákattintunk egy szülő kategóriára akkor
     * lenyílik egy lista amelyben megtaláljuk a gyerekeit. (Így nem ömlesztve jelenik meg a sok kategóriá).
     * Csak mobilon.
     * 
     * @return htmlCode
     */
    public function forMobile () {
        $prodCats = "";
        foreach($this->product_categories as $prods) {
            if($prods->parent == 0) {
                $prodCats .= '<div class="dropDownHolder">' . 
                '<a class="nav-link mobileProductCategoryParent">' . $prods -> cat_name . 
                '<i class="bi bi-chevron-right"></i></a>' . 
                $this->childsForMobile($this->product_categories, $prods) .
                '</div>';
            }
        }
        return $prodCats;
    }

    /**
     * Webes nézetben visszaadja egy megadott szülőhöz tartozó gyerek gyerekkategóriáit,
     * tehát a fő szülő unokáit.
     * Csak a webes nézetben van használva.
     * 
     * @param parent_cat_id - a fő szülő adott gyerekének az ID-je, tehát ennek a szülője
     * @param product_categories - tartalmazza az összes kategóriát (szülők és gyerekek)
     * @return htmlCode
     */
    private function grandChildren ($parent_cat_id, $product_categories) {
        $data = "";
        foreach($product_categories as $prods) {
            if($prods -> parent == $parent_cat_id) {
                $data .= "<a href='" . get_term_link($prods -> slug, 'product_cat') . "' style='color: gray !important;'>" .
                $prods -> cat_name . "</a>";
            }
        }
        return $data;
    }

    /**
     * A webes nézetben a megadott szülőhöz visszaadja az összes gyerek kategóriát (beleértve az unokákat is).
     * Csak a webes nézetben van használva.
     * 
     * @param parent_cat_id - egy adott szülő ID-je
     * @param product_categories - tartalmazza az összes kategóriát (szülők és gyerekek)
     * @return htmlCode
     */
    private function children ($parent_cat_id, $product_categories) {
        $data = "";
        foreach($product_categories as $prods) {
            if($prods -> parent == $parent_cat_id) {
                $data .= "<div class='prodBox'>" .
                "<a href='" . get_term_link($prods -> slug, 'product_cat') . "'>" .
                $prods -> cat_name . "</a>" .
                $this->grandChildren($prods -> cat_ID, $product_categories) .
                "</div>";
            }
        }
        return $data;
    } 

    /**
     * Létrehoz egy megadott szülőhőz egy gyerektartót, amelyen minden gyerekkategória fel van sorolva
     * a megadott szülőhöz. 
     * Csak a webes nézetben van használva.
     * 
     * @param parent_cat_id - a kiválasztott szülő kategória ID-je
     * @param product_categories - tartalmazza az összes kategóriát (szülők és gyerekek)
     * @param index - a megadott szülő sorszáma (a szülő kategóriák között az adott szülő helye 0, 1, 2, ...)
     * @return htmlCode
     */
    private function createChildHolder ($parent_cat_id, $product_categories, $index) {
        $holder = "<div class='productCategoriesSecondLevelHolder' data-id='prodCatParent" . $index . "' id='prodCatParent" . $index . "'>" .
        "<div class='prodContainer'>";

        $holder .= $this->children($parent_cat_id, $product_categories);

        $holder .= "</div></div>";
        return $holder;
    }

    /**
     * Összerakja és vissza adja a szülö kategóriákhoz tartozó gyerek kategóriák tárolóit.
     * Minden szülőhöz tartozik egy ilyen holder amely tartalmazza a gyerekeit,
     * akkor jelenik meg ha a szülő fölé visszük az egeret.
     * Csak a webes nézetben van használva.
     * 
     * @return htmlCode - tartalmazza a szülőkhöz tartozó gyerek kategóriákat.
     */

    public function childHolders () {
        $holders = "";
        $index = 0;
        foreach($this->product_categories as $prods) {
            if($prods -> parent == 0) {
                $holders .= $this->createChildHolder($prods -> cat_ID, $this->product_categories, $index);
                $index++;
            }
        }
        return $holders;
    }

    /**
     * A webes nézetben megjelenő módon rakja össze a szülő kategóriákat.
     * Szülő kategória az aminek a szülője 0 tehát nincs.
     * Csak a webes nézetben van használva.
     * 
     * A függvény meghívásakor visszakapjuk a szülő kategóriákat.
     * 
     * @return htmlCode - <ul><li></li></ul> formátumban adja vissza a kategóriákat.
     */

    public function parents () {
        

        $prodCats = "";
        $index = 0;
        foreach($this->product_categories as $prods) {
            if($prods -> parent == 0) {
                $prodCats .= "
                    <a href='" . get_term_link($prods -> slug, 'product_cat') . "' class='prodCatsParent' data-id='prodCatParent" . $index . "'>" .
                        $prods -> cat_name . 
                    "</a>";
                $index++;
            }
        }
        return $prodCats;
    }
}

?>