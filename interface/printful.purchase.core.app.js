;( function($, _, undefined){
	"use strict";

	ips.controller.mixin( 'printful.purchase', 'core.front.core.app', true, function () {

        this.after('initialize', function() {
                this.setPurchaseImage();
        });

        this.setPurchaseImage = function() {

            ips.getAjax()(
                '?app=printfulintegration&module=store&controller=images&do=purchase&id=' + this.scope.data('pageid')
            ).done(res => {
                if( res.length != 0 ) {

                    if( res['url'] ) {
                        this.scope.find('.cNexusPurchase .ipsNoThumb').removeClass('ipsNoThumb ipsNoThumb_product')
                            .addClass('cNexusPurchase_image')
                            .css('background-image', "url(" + res['url'] + ")");
                    }
                    
                    if( res['shipping'] ) {
                        this.scope.find('.cNexusPurchase .ipsNoThumb').removeClass('ipsNoThumb ipsNoThumb_product')
                            .addClass('ipsType_center cNexusPurchase_image')
                            .html( $('<i class="fa fa-truck ipsType_light ipsType_huge" />') )
                            .css('padding-top', '62px');
                    }

                }
            });
        }
	});
}(jQuery, _));