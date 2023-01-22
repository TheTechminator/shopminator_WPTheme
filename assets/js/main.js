/*--------------------INIT SWIPERJS--------------------*/

/**
 * A képeket tartalmazó slider inicializálása
 */
const swiperImages = new Swiper( '.swiperImages', {
    direction: 'horizontal',
    loop: true,
    grabCursor: true,
    pagination: {
        el: '.swiper-pagination',
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    }
} );

/**
 * A termékeket tartalmazó slider inicializálása.
 * (A főoldalon található több is ebböl a sliderből és különböző termékek vannak bennük 
 * (pl.: Akciós, Limitált ideig akciós stb.))
 */

const swiperSale = new Swiper( '.swiperSale', {
    //slidesPerView: "auto",
    slidesPerView: 2,
    spaceBetween: 0,
    direction: 'horizontal',
    cssMode: true,
    breakpoints: {
        1400: {
            slidesPerView: 6,
            cssMode: false,
        },
        992: {
            slidesPerView: 5,
            cssMode: false,
        },
        768: {
            slidesPerView: 4,
            cssMode: false,
        },
        576: {
            slidesPerView: 3,
        },
    },
    pagination: {
        el: '.swiper-pagination',
        clickable: false,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    }
} );

/*--------------------SHOW PRODUCT SALE REMAINING TIME--------------------*/

var limitedSaleProduct = document.getElementsByClassName( "productSaleTimeLeft" );

function productSaleRemainingTime ( data ) {

    var time = Date.parse( data )*1;
    var now = Date.now();

    var remaining = Math.floor( ( time-now ) / 1000 );

    var d = Math.floor( remaining / 86400 );
    remaining -= d * 86400; 

    var h = Math.floor( remaining / 3600 );
    remaining -= h * 3600;

    var m = Math.floor( remaining / 60 );
    remaining -= m * 60;

    var s = remaining;

    //return "Nap: " + d + ", óra: " + h + ", perc: " + m + ", másodperc: " + s;
    return d + " NAP " + h+ ":" + m + ":" + s;
}

setInterval ( () => {
    for( i = 0; i<limitedSaleProduct.length; i++ ) {
        if( limitedSaleProduct[i].dataset.saleEnd != "" ) {
            limitedSaleProduct[i].innerHTML = productSaleRemainingTime( limitedSaleProduct[i].dataset.saleEnd );
        }
    }
}, 1000 );

/*--------------------SHOW/HIDE CATEGORY CHILDREN--------------------*/
var prodCatParents = document.getElementsByClassName( "prodCatsParent" );
var prodCatChildren = document.getElementsByClassName( "productCategoriesSecondLevelHolder" );

for( i = 0; i<prodCatParents.length; i++ ) {
    prodCatParents[i].addEventListener( "mouseover", productCategoriesSecondLevelHolderView, false );
    prodCatParents[i].addEventListener( "mouseout", productCategoriesSecondLevelHolderHide, false );

    prodCatChildren[i].addEventListener( "mouseover", productCategoriesSecondLevelHolderView, false );
    prodCatChildren[i].addEventListener( "mouseout", productCategoriesSecondLevelHolderHide, false );
}

function productCategoriesSecondLevelHolderView () {
    var holder = document.getElementById( this.dataset.id );
    holder.style.display = "block";
}

function productCategoriesSecondLevelHolderHide () {
    var holder = document.getElementById( this.dataset.id );
    holder.style.display = "none";
}

/*--------------------INIT SWIPERMENU--------------------*/
var swiperem = new SwiperMenu( "mySwiper" );

/*--------------------MOBILE SEARCH BAR HOLDER--------------------*/
var mobilleSearchBarHolderOpener = document.getElementById( "mobilleSearchBarHolderOpener" );
var mobilleSearchBarHolderCloser = document.getElementById( "mobilleSearchBarHolderCloser" );
mobilleSearchBarHolderOpener.addEventListener( "click", openMobileSearchBarHolder, false );
mobilleSearchBarHolderCloser.addEventListener( "click", closeMobileSearchBarHolder, false )

function openMobileSearchBarHolder () {
    document.getElementById( "mobileSearchBarHolder" ).style.transform = "translateY( 0px )";
}
function closeMobileSearchBarHolder () {
    document.getElementById( "mobileSearchBarHolder" ).style.transform = "translateY( -100% )";
}

/*--------------------DROPDOWNMENU--------------------*/
var dropDowns = document.getElementsByClassName( "dropDownHolder" );
for( i = 0; i<dropDowns.length; i++ ) {
    dropDowns[i].addEventListener( "click", openDropDownMenu, false );
    dropDowns[i].getElementsByClassName( "dropDownMenuClose" )[0].addEventListener( "click", closeDropDownMenu, false );
}

function openDropDownMenu () {
    var dropMenu = this.getElementsByClassName( "dropDownMenu" );
    dropMenu[0].style.transform = "translateX( 0px )";
}

function closeDropDownMenu () {
    var dropMenu = this.parentElement;

    setTimeout( function(){ 
        dropMenu.style.transform = "translateX( 100% )";
    }, 1 );
}

/*--------------------PRODUCT FILTER--------------------*/

/**
 * A json objektumból csinál egy szolidabb szöveget amelyet el lehet küldeni akár az url-en keresztül is paraméterekként
 */
const encodeJSONIntoURL = function ( data ) {
    let url = Object.keys(data).map(function(k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
    }).join('&');

    return url;
}

/**
 * Súrolópor (ajax) segítségével lekérjük a megfelelő termékeket
 */
 function ajaxProductLoader ( data, wordpressAjaxUrl, callBack ) {
    xhttp = new XMLHttpRequest();

    xhttp.onload = function() {
        response = JSON.parse( this.responseText );
        updateProductFilters( response );
        callBack();
    }

    xhttp.open( "POST", wordpressAjaxUrl );
    xhttp.setRequestHeader( "Content-type", "application/x-www-form-urlencoded" );
    xhttp.send(encodeJSONIntoURL( data ));
}


function removeAllChildNodeExceptFirst ( elem ) {
    let nodes = elem.childNodes;

    while( nodes.length > 1 ) {
        elem.removeChild( nodes[1] );
    }
}

function generateFilterBlock ( attribute, values, productCountOfEachAttributeValue ) {
    let filterBlock = 
            `<div class="filterBlock attributeFilterBlock">` +
            `<div class="filterTitleHolder">${attribute['attribute_label']}</div> ` +
            `<div class="filterOptionsHolder">` +
                `<ul>` +
                    `${generateFilterOptions( attribute['attribute_name'], values, productCountOfEachAttributeValue )}` +
                `</ul>` +
            `</div>` +
        `</div>`;

    return filterBlock;
}

function generateFilterOptions ( attributeName, values, productCountOfEachAttributeValue ) {
    let filterOptions = "";

    values.forEach( item => {
        if( item['attribute_name'] == attributeName ) {
            filterOptions +=
                `<li>` +
                    `<input class="filterOptionInput" type="checkbox" name="${item['name']}" id="${item['name']}" data-attribute="${attributeName}"> ` +
                    `<span class="filterOptionText">${item['name']}</span> ` +
                    `<span class="filterResultCount">(${getFilterResultCount(item['name'], productCountOfEachAttributeValue)})</span>` +
                `</li>`;
        }
    } );

    return filterOptions;
}

function getFilterResultCount ( name, productCountOfEachAttributeValue ) {
    if( productCountOfEachAttributeValue != undefined ) 
        for( let i = 0; i<productCountOfEachAttributeValue.length; i++ ) {
            if( productCountOfEachAttributeValue[i]['name'] == name ) {
                return productCountOfEachAttributeValue[i]['count'];
            }
        }
}

/**
 * Mobilos nézetben a termékszűrő tárolójának megjelenítése és elrejtése
 */
let sidebarHolderWithFilters = document.getElementById( "sidebarHolderWithFilters" );
let openSideBarHolder = document.getElementById( "openSideBarHolder" );
let closeSideBarHolder = document.getElementById( "closeSideBarHolder" );

if( sidebarHolderWithFilters != null && openSideBarHolder != null && closeSideBarHolder != null ) {
    openSideBarHolder.addEventListener( "click", () => {
        sidebarHolderWithFilters.style = "transform: translateX(0%);";
    });

    closeSideBarHolder.addEventListener( "click", () => {
        sidebarHolderWithFilters.style = "transform: translateX(-100%);";
        productFilterOpenCloseHandler.mobileSetup();
    });
}

let orderbySelect = document.getElementById( "orderbySelect" );

if( orderbySelect != null ) {
    orderbySelect.addEventListener( 'change', ( event ) => {
        let select = event.target;
        data['orderby'] = select.options[select.selectedIndex].value;
        productFilterHandler.filterChanged( data, productFilterHandler.filterArray );
    });
}

//767.98px