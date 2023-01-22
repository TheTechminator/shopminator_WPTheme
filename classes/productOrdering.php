<?php
defined( 'ABSPATH' ) || exit;

/**
 * Az adott osztály a termék rendezését teszi lehetővé, 
 * de nem rendez csak előkészíti a rendező űrlapot és megadja a rendezési feltételeket.
 */
class ProductOrdering {
    /**
     * Ez a tömb tartalmazza a rendezési feltételeket,
     * tehát ezek alapján lehet rendezni a termékeket.
     */
    private $orderby = array (
        array (
            'value' => 'popularity',
            'text' => 'Népszerűség szerint',
            'order' => 'total_sales DESC'
        ),
        array (
            'value' => 'rating',
            'text' => 'Vásárlói értékelés szerint',
            'order' => 'average_rating DESC'
        ),
        array (
            'value' => 'name',
            'text' => 'Terméknév A-Z',
            'order' => 'name ASC'
        ),
        array (
            'value' => 'name-desc',
            'text' => 'Terméknév Z-A',
            'order' => 'name DESC'
        ),
        array (
            'value' => 'price',
            'text' => 'Legolcsóbb elöl',
            'order' => 'price ASC'
        ),
        array (
            'value' => 'price-desc',
            'text' => 'Legdrágább elöl',
            'order' => 'price DESC'
        )
    );
    private $defaultOrder = 'popularity';
    private $ordering;

    /**
     * Mikor létrehozunk egy új példányt automatikusan beállítja magát és már használhatjuk is.
     */
    public function __construct() {
        $this->setUpOrdering();
    }

    /**
     * Előkészíti a dolgokat.
     * Tehát elkéri mi alapján akarunk rendezni ha ilyen nincs akkor alapértelmezett.
     */
    private function setUpOrdering () {
        if( isset( $_REQUEST['orderby'] ) ) {
            $this->ordering = $this->getOrdering( $_REQUEST['orderby'] );
        } else {
            $this->ordering = $this->getOrdering( $this->defaultOrder );
        }
    }

    /**
     * A megadott érték alapján amit az url-be írunk visszakeresi a megfeleő sql rendezési feltételt.
     * 
     * @param value - orderby tömb value-ja, eredetileg ez van/volna az url-ben
     * @return - az orderby tömb megfelelő eleme
     */
    private function getOrdering ( $value ) {
        return $this->orderby[array_search($value, array_column($this->orderby, 'value'))];
    }

    /**
     * Megjeleníti a rendező űrlapot, 
     * csak meg kell hívni és egy varázsló odavarázsol egy űrlapot amiben benne vannak a rendezési paraméterek.
     */
    public function getOrderByForm () {
        echo '
            <form class="woocommerce-ordering" id="productOrdering" method="get" onsubmit="event.preventDefault();">
                <div class="d-none d-md-block mb-2"> Rendezés </div>
                '.$this->getOrderByList().'
                '.$this->getHiddenInputs().'
            </form>
        ';
    }

    /**
     * A rendező űrlap lenyíló listáját teszi össze és adja vissza.
     * 
     * @return htmlCode
     */
    private function getOrderByList () {
        return '
            <select name="orderby" id="orderbySelect" class="orderby btn btn-primary" aria-label="Sorrend">
                '.$this->getOrderingOptions().'
            </select>
        ';
    }

    /**
     * A rendező űrlap lenyíló listájának a lehetőségeit (option) teszi össze.
     * 
     * @return htmlCode - options
     */
    private function getOrderingOptions () {
        $options = "";
        foreach ( $this->orderby as $item ) {
            if( $this->getOrder() == $item['order'] ) {
                $options .= '<option value="'.$item['value'].'" selected="selected">'.$item['text'].'</option>';
            } else {
                $options .= '<option value="'.$item['value'].'">'.$item['text'].'</option>';
            }
        }
        return $options;
    }

    /**
     * A rendező űrlaphoz hozzáad rejtett inputokat, 
     * eredetileg azt a célt szolgálta, hogy amikor kiválasztottunk egy új rendezési feltételt és újra töltött az oldal
     * ne vesszenek el az url-ben levő egyéb feltételek.
     * 
     * Viszont már szügségtelen mivel nem tölt újra az oldal mivel be sem tölt XD
     * Najó betölt csak ajax-on keresztül kéri el a termékeket.
     * 
     * @return htmlCode - inputokat tartalmaz
     */
    private function getHiddenInputs () {
        $inputs = "";
        foreach ($_GET as $key => $value) {
            if($key != "orderby")
                $inputs .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
        }
        return $inputs;
    }

    /**
     * Visszaadja az sql rendezési feltételt
     * 
     * @return sql - csak egy ORDER BY kell elé és működik is
     */
    public function getOrder () {
        return $this->ordering['order'];
    }
}

?>