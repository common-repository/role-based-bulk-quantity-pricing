function rbbqp_addCommas(nStr) {
    nStr += '';
    const x = nStr.split('.');
    let x1 = x[0];
    const x2 = x.length > 1 ? '.' + x[1] : '';
    const rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function rbbqp_resetVariations() {
    jQuery('.reset_variations').click(function() {
        if ( $('#product_total_price').length ) {
            jQuery('#product_total_price .price').html('--');
        }
    });
}

function rbbqp_getPriceForQuantity( item_id, quantity ) {
    let pricingTables = jQuery("#customPricingTable").data('json');
    let pricingTable = pricingTables[item_id];
    let selectedPricing = pricingTable.filter(el => quantity >= ( +el.threshold_min_qty - +el.initial_quantity ) );

    let selectedPricing1;
    let lowestPrice = 999999999999;
    pricingTable.forEach((element, index) => {
        if (+element.base_unit_price < lowestPrice) {
            selectedPricing1 = element;
        }
        if (quantity >= +element.threshold_min_qty && +element.threshold_unit_price < lowestPrice) {
            selectedPricing1 = element;
        }
    });

    if (selectedPricing.length === 0) {
        price = +pricingTable[0].base_unit_price;
    } else {
        selectedPricing = selectedPricing.sort((a,b) => (+a.threshold_unit_price > +b.threshold_unit_price) ? 1 : -1);
        price = +selectedPricing[0].threshold_unit_price                      
    }

    return price;
}

function rbbqp_updateCurrentPrice( productType, price_suffix = '', product_id = -1 ) {

    if ( productType === 'variable' ) {

        jQuery(function($) {

            // Fired when the user selects all the required dropdowns / attributes
            // and a final variation is selected / shown
            $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {

                let selectedVariationId = $( 'input.variation_id' ).val();
                if (selectedVariationId > 0) {
                        rbbqp_updatePriceDisplay(1, selectedVariationId, 'woocommerce-variation-price', price_suffix);
                    }
                } );
    
            $('[name=quantity]').change(function(){
   
                let selectedVariationId = $( 'input.variation_id' ).val();

                rbbqp_updatePriceDisplay(this.value, selectedVariationId, 'woocommerce-variation-price', price_suffix);
            });
        });

    } else if ( productType === 'simple' ) {

        jQuery(function($) {

            rbbqp_updatePriceDisplay(1, product_id, 'entry-summary', price_suffix);

            $('[name=quantity]').change(function() {
                rbbqp_updatePriceDisplay(this.value, product_id, 'entry-summary', price_suffix);
            });
        });


    } 
}

function rbbqp_updatePriceDisplay(quantity, product_id, entry_class, price_suffix) {
    jQuery(function($) {
        const currency = rbbqp_localized_strings.currency_symbol;
        if (!(quantity < 1) && product_id > 0) {

            let price = rbbqp_getPriceForQuantity(product_id, quantity);

            const product_total = parseFloat(price * quantity);
            const product_total_html = currency + rbbqp_addCommas(product_total.toFixed(2));
            const price_html = currency + rbbqp_addCommas(price.toFixed(2)); 
            let price_html_span = "<span class='woocommerce-Price-amount amount'>" + price_html + "</span>";

            price_html_span += ' per unit.';

            if ( price_suffix != '' ) {
                price_html_span += ' <span class="rbbqp_price_suffix">' + unEscape(price_suffix) + '</span>';
            }

            $('.' + entry_class + ' > .price').html( price_html_span );

            if ( $('#product_total_price').length ) {
                $('#product_total_price .price').html( product_total_html );
            }
        }

    });

    function unEscape(htmlStr) {
        htmlStr = htmlStr.replace(/&lt;/g , "<");	 
        htmlStr = htmlStr.replace(/&gt;/g , ">");     
        htmlStr = htmlStr.replace(/&quot;/g , "\"");  
        htmlStr = htmlStr.replace(/&#39;/g , "\'");   
        htmlStr = htmlStr.replace(/&amp;/g , "&");
        return htmlStr;
    }
}