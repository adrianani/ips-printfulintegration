;( function($, _, undefined){
	"use strict";

	ips.controller.mixin( 'printful.purchases', 'core.front.core.app', true, function () {

        this.after('initialize', function() {
                this.setPurchasesImage();
        });

        this.setPurchasesImage = function() {

            ips.getAjax()(
                '?app=printfulintegration&module=store&controller=images&do=purchases'
            ).done(res => {
                if( res.length != 0 ) {

                    this.scope.find('.cNexusPurchaseList .ipsNoThumb').each( ( index, item ) => {

                            if( res[ index ] ) {

                                $(item).removeClass('ipsNoThumb ipsNoThumb_product')
                                    .addClass('cNexusPurchaseList_image')
                                    .css('background-image', "url(" + res[ index ] + ")");

                            } else {

                                let itemTitle = $(item).parent().find('.ipsType_sectionHead > a');

                                if( itemTitle.length > 0 && itemTitle.text().indexOf('Shipping: ') === 0 ) {

                                    $(item).removeClass('ipsNoThumb ipsNoThumb_product')
                                        .addClass('ipsType_center cNexusPurchaseList_image')
                                        .html( $('<i class="fa fa-truck ipsType_light ipsType_veryLarge" />') )
                                        .css('padding-top', '36px');
                                }
                            }
                    });
                }
            });
        }
	});
}(jQuery, _));