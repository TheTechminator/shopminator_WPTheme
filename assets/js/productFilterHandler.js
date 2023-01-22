/**
 * Az osztály amely a filterek kinyitás és becsukását intézi ( mást azért nem intéz el, csak akit kell :) )
 */
class ProductFilterOpenCloseHandler {
	filterHolderBaseHeights = [];
	chevronUp;
	chevronDown;
    filterBlocks;
    filterTitles;
    filterOptionsHolders;

    /**
     * Igazság szerint ez csak úgy itt van (de minek? kitudja... XD)
     */
    constructor () {
        this.chevronUp = '<i class="bi bi-chevron-up" style="float: right;"></i>';
        this.chevronDown = '<i class="bi bi-chevron-down" style="float: right;"></i>';
    }

    /**
     * Inicializálja a dolgokat. Megkeresi az összes filter és elvégzi a szükséges műveleteket amely ahhoz kell, hogy műkődjön a nyitás csukás funkció.
     */
    init() {
        this.filterHolderBaseHeights = [];
        this.filterBlocks = document.getElementsByClassName('filterBlock');
        this.filterTitles = this.findAllElementsIn( this.filterBlocks, 'filterTitleHolder' );
        this.filterOptionsHolders = this.findAllElementsIn( this.filterBlocks, 'filterOptionsHolder' );
        this.initFilterTitleHolders( this.filterTitles, this.filterOptionsHolders, this.filterHolderBaseHeights );
        this.mobileSetup();
    }

    /**
     * Egy megadott html elem kollekcióból visszaadja az elemekhez tartozó első gyereket a megadott className alapján.
     * 
     * @param filterBlocks - az elemeket tartalmazó tömb
     * @param name - a keresett elemek class name-je
     * @returns array, amely tartalmazza a keresetett elemeket
     */
    findAllElementsIn( filterBlocks, name ) {
        let elements = [];
        for( let i = 1; i<filterBlocks.length; i++ ) {
            elements.push(filterBlocks[i].getElementsByClassName( name )[0]);
        }
    
        return elements;
    }

    /**
     * Összegyűjti egy tömben az összes filterOptionsHolders-nek az alapértelmezett magasságát és beállítja az a magasságot inline style-ként,
     * hogy majd lehessen 0-ra állítani és szépen animállta csúkódjon be.
     * 
     * Ezekután az összes title-hoz hozzáadja a kics fel mutató nyilat, majd esemény is rendel minden title-hoz, hogyha rákkattintunk, 
     * akkor módosítani lehessen az alatta levő tárolót.
     * 
     * @param {*} filterTitles - html collection amely tartalmazza a title elemeket
     * @param {*} filterOptionsHolders - html collection amely tartalmazza a title (címek) alatt levő input tárolókat.
     * @param {*} filterHolderBaseHeights - array amelybe beletöltjük a tárolók alapértelmezett magasságát
     */
    initFilterTitleHolders ( filterTitles, filterOptionsHolders, filterHolderBaseHeights ) {
		for( let i = 0; i<filterTitles.length; i++ ) {
			filterHolderBaseHeights.push( filterOptionsHolders[i].offsetHeight );
			filterOptionsHolders[i].style.height = filterHolderBaseHeights[i] + "px";
			this.addChevronUpForFilterTitle(filterTitles[i]);
			this.addEventListenerForFilterTitleHolder( filterTitles[i], filterOptionsHolders[i], filterHolderBaseHeights[i] );
		}
    }

    /**
     * Minden megadott title elemhez hozzárendel egy click esemény figyelést. Ha a title-re kattintunk akkor vagy becsukódik vagy kinyílik az
     * alatta elhelyezett tároló.
     * 
     * @param {*} filterTitle - egy megadott title elem
     * @param {*} filterOptionsHolder - egy megadott inputokat tartalmazó tároló
     * @param {*} filterHolderBaseHeight - egy megadott tárolóhoz kapcsolódó alapértelmezett magasság
     */
    addEventListenerForFilterTitleHolder ( filterTitle, filterOptionsHolder, filterHolderBaseHeight ) {
        filterTitle.addEventListener( 'click', () => {

            if(filterOptionsHolder.style.height == "0px") {
                this.mobileSetup();
                this.openFilterBlock (filterOptionsHolder, filterTitle, filterHolderBaseHeight );
            } else
                this.closeFilterBlock( filterOptionsHolder, filterTitle );

        });
    }

    /**
     * Eltávolítja az összes fölösleges elemet a title-ból,
     * csak a legelső címet hagyja meg és ahhoz ad hozzá még egy kis nyilacskát
     * @param filterTitle - az adott filter block címet tartalmazó tárolója.
     */
    addChevronUpForFilterTitle ( filterTitle ) {
        let title = filterTitle.childNodes[0].data;
        filterTitle.innerHTML = title;
        filterTitle.innerHTML += this.chevronUp;
    }

    /**
     * Formálisan eltávolítja a működést a filterekről ( Valójában csak azért van, hogy menő programozónak tűnjek :) Na jó, azért van egy kis értelme is... )
     */
    destroy () {
        if(this.filterTitles != undefined)
            for(let i = 0; i<this.filterTitles.length; i++) {
                let newNode =  this.filterTitles[i].cloneNode(true);
                this.filterTitles[i].parentNode.replaceChild(newNode, this.filterTitles[i]);
            }
    }

    /**
     * Ha az adott eszköz mobil akkor alapértelmezetten becsukja az összes filter blockot.
     */
    mobileSetup () {
        if( window.innerWidth < 768 ) {
            this.closeAllFilterBlock( this.filterTitles, this.filterOptionsHolders );
        }
    }

    /**
     * Becsukja az összes filter blockot.
     * 
     * @param {*} filterTitles - a filterek címei
     * @param {*} filterOptionsHolders - a filter blockokat tartalmazó tömb.
     */
    closeAllFilterBlock ( filterTitles, filterOptionsHolders ) {
        for( let i = 0; i<filterTitles.length; i++ ) {
            this.closeFilterBlock( filterOptionsHolders[i], filterTitles[i] );
		}
    }

    /**
     * A megadott filter block-ot becsukja. A filter block az ami tárolja egy adott szűrő opcióit.
     * 
     * @param {*} filterBlock - a megadott filter block amit be akarunk csukni
     * @param {*} filterTitle - a megadott filter blockhoz tartozó cím ( ami fölötte van )
     */
    closeFilterBlock ( filterBlock, filterTitle ) {
        filterBlock.style.height = "0px";   
        filterTitle.removeChild(filterTitle.lastChild);
        filterTitle.innerHTML += this.chevronDown;
    }

    /**
     * A megadott filter block-ot kinyitja
     * 
     * @param {*} filterBlock - a megadott filter block amit ki akarunk nyitni
     * @param {*} filterTitle - a megadott filter blockhoz tartozó cím ( ami fölötte van )
     * @param {*} filterTitle - a megadott filter block alapértelmezett magassága
     */
    openFilterBlock (filterBlock, filterTitle, baseHeight ) {
        filterBlock.style.height = baseHeight + "px";
        filterTitle.removeChild(filterTitle.lastChild);
        filterTitle.innerHTML += this.chevronUp;
    }
}

class ProductFilterHandler {
    productFilterForm;
    minPrice;
    maxPrice;
    buttonFilterPrice;
    priceSlider;
    filterOptionInputs;
    filterArray;
    dataForAjaxLoad;
    clearAllFilters;
    loadingSpinner;

    constructor( filterContainer, dataForAjaxLoad ) { //productFilterForm
        this.filterArray = {};
        this.dataForAjaxLoad = dataForAjaxLoad;
        this.productFilterForm = document.getElementById( filterContainer );
    }

    initFields() {
        this.minPrice = document.getElementById( 'minPrice' );
        this.maxPrice = document.getElementById( 'maxPrice' );
        this.buttonFilterPrice = document.getElementById( 'buttonFilterPrice' );
        this.priceSlider = document.getElementById( 'priceSlider');
        this.filterOptionInputs = document.getElementsByClassName( 'filterOptionInput' );
        this.clearAllFilters = document.getElementById( 'clearAllFilters' );
        this.loadingSpinner = document.getElementById( 'loadingSpinner' );
    }

    initProductFilter( minimumFilterPrice, maximumFilterPrice ) {
        if( minimumFilterPrice != null ) {
            this.initFields();
            this.createNoUiSlider( parseInt( minimumFilterPrice ), parseInt( maximumFilterPrice ) );
            this.addEventListenerForButtonFilterPrice( this.buttonFilterPrice, this.minPrice, this.maxPrice, this.filterArray, this.priceSlider );
            this.addEventListenerForPriceSlider( this.minPrice, this.maxPrice, this.filterArray, this.priceSlider );
            this.loadClearAllFiltersButton( this.clearAllFilters );
            this.filterFormDisableSubmit( this.productFilterForm );
            this.addEventListenerForOptionInputs( this.filterOptionInputs, this.filterArray );
            this.loadPreviousInputStates( this.filterOptionInputs, this.filterArray );
        }
    }

    createNoUiSlider( min, max ) {
        if( this.priceSlider != null ) {
            noUiSlider.create(this.priceSlider, {
                start: [min, max],
                connect: true,
                range: {
                    'min': min,
                    'max': max
                }
            });
        }
    }

    addEventListenerForButtonFilterPrice( buttonFilterPrice, minPriceInput, maxPriceInput, filterArray, priceSlider ) {
        if( buttonFilterPrice != null ) {
            buttonFilterPrice.addEventListener( 'click', () => {
                let min = parseInt( minPriceInput.value );
                let max = parseInt( maxPriceInput.value );
                if( priceSlider.noUiSlider.options.range.min != min || priceSlider.noUiSlider.options.range.max != max ) {
                    priceSlider.noUiSlider.set( [min, max] );
                    this.dataForAjaxLoad['minPrice'] = min;
                    this.dataForAjaxLoad['maxPrice'] = max;
                    this.filterChanged( this.dataForAjaxLoad, filterArray );
                }
            });
        }
    }

    addEventListenerForPriceSlider( minPriceInput, maxPriceInput, filterArray, priceSlider ) {
        if( priceSlider != null ) {
            priceSlider.noUiSlider.on( 'change', () => {
                let min = parseInt( priceSlider.noUiSlider.get()[0] );
                let max = parseInt( priceSlider.noUiSlider.get()[1] );
                if( priceSlider.noUiSlider.options.range.min != min || priceSlider.noUiSlider.options.range.max != max ) {
                    this.dataForAjaxLoad['minPrice'] = min;
                    this.dataForAjaxLoad['maxPrice'] = max;
                    this.filterChanged( this.dataForAjaxLoad, filterArray );
                }
            });
            
            priceSlider.noUiSlider.on( 'update', () => {
                minPriceInput.value = parseInt( priceSlider.noUiSlider.get()[0] );
                maxPriceInput.value = parseInt( priceSlider.noUiSlider.get()[1] );
            });
        }
    }

    parseInt( string ) {
        return string*1<<0;
    }

    filterFormDisableSubmit( productFilterForm ) {
        productFilterForm.addEventListener( 'submit', function ( e ) {
            e.preventDefault();
        });
    }

    addEventListenerForOptionInputs( inputs, filterArray ) {
        for( let i = 0; i<inputs.length; i++ ) {
            inputs[i].addEventListener( "change", () => {
                let index = inputs[i].dataset.attribute;

                if( inputs[i].checked ) {
                    if( filterArray[index] == undefined )
                        filterArray[index] = [];

                    filterArray[index].push( inputs[i].name );
                } else {
                    filterArray[index].splice( filterArray[index].indexOf( inputs[i].name ), 1 );
                    if( filterArray[index].length == 0 ) 
                        delete filterArray[index];
                }
                
                this.filterChanged( this.dataForAjaxLoad, filterArray );
            });
        }
    }

    loadClearAllFiltersButton( clearAllFilters ) {
        this.addEventListenerForClearAllFilters( clearAllFilters );
        if( ( this.filterArray == null || this.filterArray == undefined || Object.keys( this.filterArray ).length === 0 ) && this.dataForAjaxLoad['minPrice'] == undefined ) {
            clearAllFilters.parentNode.style = "display: none;";
        } else {
            clearAllFilters.parentNode.style = "display: block;";
        }
    }

    addEventListenerForClearAllFilters( clearAllFilters ) {
        clearAllFilters.addEventListener( "click", () => {
            this.filterArray = {};

            this.dataForAjaxLoad['minPrice'] = null;
            delete this.dataForAjaxLoad['minPrice'];
            this.dataForAjaxLoad['maxPrice'] = null;
            delete this.dataForAjaxLoad['maxPrice'];

            console.log(this.dataForAjaxLoad);
            this.filterChanged( this.dataForAjaxLoad, this.filterArray );
        } );
    }

    loadPreviousInputStates( filterOptionInputs, filterArray ) {
        for( let i = 0; i<filterOptionInputs.length; i++ ) {
            let array = filterArray[filterOptionInputs[i].dataset.attribute];
            filterOptionInputs[i].checked = ( array != undefined && array.includes( filterOptionInputs[i].name ) );
        }
    }

    destroy() {
        if( this.priceSlider != null && this.priceSlider != undefined )
            this.priceSlider.noUiSlider.destroy();
    }

    filterChanged( data, filterArray ) {
        this.showHideLoadingSpinner( this.loadingSpinner );

        let jsonText = JSON.stringify( filterArray );
        data['filters'] = ( jsonText == "{}" ? null : jsonText );
    
        ajaxProductLoader ( data, ajaxurl, () => {
            this.refreshSiteData( response );
            this.showHideLoadingSpinner( this.loadingSpinner );
        });
    }

    refreshSiteData( response ) {
        if(response.resultCount != 0) {
            if( response['minPrice'] != null && response['maxPrice'] != null ) {
                minimumFilterPrice = parseInt( response['minPrice'] );
                maximumFilterPrice = parseInt( response['maxPrice'] );
            }

            this.priceSlider.noUiSlider.updateOptions({start: [minimumFilterPrice, maximumFilterPrice],range: {'min': minimumFilterPrice,'max': maximumFilterPrice}});
        }

        document.getElementById('resultCount').innerHTML = `(${response['resultCount']} db)`;
        document.getElementById('rowForProducts').innerHTML = response['products'];
    }

    showHideLoadingSpinner( loadingSpinner ) {
        if( loadingSpinner.style.display == 'none' ) {
            loadingSpinner.style.display = 'flex';
        } else {
            loadingSpinner.style.display = 'none';
        }
    }
}

/**
 * A php-tól visszakapott eredmények alapján generálja le újra a filtereket.
 */
class ProductFilterGeneratorByResults {
    jsonObject;
    productFilterForm;

    constructor ( jsonObject, filterContainer ) {
        if(jsonObject.resultCount != 0) {
            this.jsonObject = jsonObject;
            this.productFilterForm = document.getElementById( filterContainer );
            this.prepareFilterForm( this.productFilterForm );
            this.generateFilterBlocksFromJSONObject( this.productFilterForm, this.jsonObject );
        }
    }

    /**
     * Előkészíti a filter form-ot az új filter blockok érkezésére.
     * Tehát kidobja a munkanélküli gyerekeit XD. (Az elsőt meghagyja mert annak még lesz dolga)
     * 
     * @param productFilterForm - tartalmazza a form-ot
     */
    prepareFilterForm ( productFilterForm ) {
        let childNodes = productFilterForm.getElementsByClassName( 'filterBlock' );
        
        while( childNodes.length > 1 ) {
            productFilterForm.removeChild( childNodes[1] );
        }
    }

    /**
     * A megkapott adatok alapján legenerálja a filter block-okat 
     * (Még szerencse, hogy nem a Magyar tanárnak kell elolvasnia a kódomat, mert szerintem sírva fakadna a helyesírásomtól XD)
     * 
     * @param productFilterForm - tartalmazza a html form objektumot
     * @param jsonObject - JSON objektum amiben benne vannak a megfelelő adatok
     */
    generateFilterBlocksFromJSONObject ( productFilterForm, jsonObject ) {
        let attributes = jsonObject['attributes'];
        let values = jsonObject['attributeValues'];
        let counts = jsonObject['productCountOfEachAttributeValue'];

        let filters = this.createPriceFilterBlock();
        attributes.forEach(element => {
            filters += this.createFilterBlock(element, values, counts);
        });

        productFilterForm.innerHTML += filters;
    }

    /**
     * Az árak szűrését lehetővé tevő block elkészítése.
     * 
     * @returns - elkészített block
     */
    createPriceFilterBlock () {
        let filterBlock = 
            '<div class="filterBlock">' + 
                '<div class="filterTitleHolder">Ár</div>' + 
                '<div class="filterOptionsHolder">' + 
                    '<ul>' + 
                        '<li>' + 
                            '<div id="priceSlider"></div>' + 
                            '<input type="text" name="minPrice" id="minPrice" value="0"> - ' + 
                            '<input type="text" name="maxPrice" id="maxPrice" value="100000">' + 
                            '<button class="btn-primary" name="buttonFilterPrice" id="buttonFilterPrice"><i class="bi bi-chevron-right"></i></button>' + 
                        '</li>' + 
                    '</ul>' + 
                '</div>' + 
            '</div>';

        return filterBlock;
    }

    /**
     * Létrehoz egy ( filterBlock ) blokkot ezek vanna benne a form-ban és ezekben vannak a szűrők stb.
     * 
     * @param attribute - egy megadott attributum amihez block-ot hoz létre
     * @param values - az összes block hoz tartozó value, majd a rendszer kiválasztja ami neki tetszik
     * @param counts - az összes value (érték) hez kapcsolódó darabszám (Ez a szám mondja meg, hogy egy megadott szűrési feltételhez hány termék tartozik)
     * @returns - egy elkészített filter block
     */
    createFilterBlock (attribute, values, counts) {
        let filterBlock = 
                `<div class="filterBlock attributeFilterBlock">` +
                `<div class="filterTitleHolder">${attribute['attribute_label']}</div> ` +
                `<div class="filterOptionsHolder">` +
                    `<ul>` +
                        `${this.generateFilterOptions(attribute['attribute_name'], values, counts)}` +
                    `</ul>` +
                `</div>` +
            `</div>`;
    
        return filterBlock;
    }

    /**
     * Egy megadott filter block-hoz szedi össze az összes szűrési feltételt ( értéket (value) )
     * 
     * @param attributeName - A megadott block attribútumának a neve ez alapján válogat a valuek közül
     * @param values - az összes block hoz tartozó value, majd a rendszer kiválasztja ami neki tetszik
     * @param counts - az összes value (érték) hez kapcsolódó darabszám
     * @returns - az elkészített filter optionokból álló szöveg
     */
    generateFilterOptions (attributeName, values, counts) {
        let filterOptions = "";
    
        values.forEach(item => {
            if(item['attribute_name'] == attributeName) {
                filterOptions +=
                    `<li>` +
                        `<input class="filterOptionInput" type="checkbox" name="${item['name']}" id="${item['name']}" data-attribute="${attributeName}"> ` +
                        `<span class="filterOptionText">${item['name']}</span> ` +
                        `<span class="filterResultCount">(${this.getFilterResultCount(item['name'], counts)})</span>` +
                    `</li>`;
            }
        });
    
        return filterOptions;
    }
    
    /**
     * Egy adott szűrési paraméterhez (filter option) keresi ki a darabszámot
     * 
     * @param name - a megadott filter option neve
     * @param counts - az összes value (érték) hez kapcsolódó darabszám
     * @returns - egy számot ad vissza ha talál (ha nem talál még 0 sem, akkor lófaszt sem ad vissza XD)
     */
    getFilterResultCount (name, counts) {
        if(counts != undefined) 
            for(let i = 0; i<counts.length; i++)
                if(counts[i]['name'] == name)
                    return counts[i]['count'];
    }
}

function updateProductFilters ( jsonObject ) {
    productFilterHandler.destroy();
    productFilterOpenCloseHandler.destroy();
    productFilterGeneratorByResults = new ProductFilterGeneratorByResults( jsonObject, 'productFilterForm' );
    productFilterHandler.initProductFilter( jsonObject['minPrice'], jsonObject['maxPrice'] );
    productFilterOpenCloseHandler.init();
}

if (typeof defaultPageData !== 'undefined') {
    productFilterGeneratorByResults = new ProductFilterGeneratorByResults( defaultPageData, 'productFilterForm' );

    productFilterHandler = new ProductFilterHandler( 'productFilterForm', data );
    productFilterHandler.initProductFilter( 0, 500000 );

    productFilterOpenCloseHandler = new ProductFilterOpenCloseHandler();
    productFilterOpenCloseHandler.init();

    updateProductFilters( defaultPageData );
    productFilterHandler.refreshSiteData ( defaultPageData );
}
