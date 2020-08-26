;( function($, _, undefined){
	"use strict";

	ips.controller.mixin( 'printful.invoice', 'core.front.core.app', true, function () {

        this.after('initialize', function() {
                this.setInvoiceProductImages();
        });

        this.setInvoiceProductImages = function() {

            ips.getAjax()(
                '?app=printfulintegration&module=store&controller=images&do=invoice&id=' + this.scope.data('pageid')
            ).done(res => {

                if( res.length != 0 ) {

                    this.scope.find('.ipsDataItem.cNexusCheckout_subtotal').parent().children().each( ( index, item ) => {

                        let thumb = $(item).find('.ipsNoThumb');

                        if(thumb.length === 1 ) {

                            if( res[ index ] ) {
                                thumb.replaceWith( $("<img class='ipsImage ipsThumb_small' src='" + ips.getSetting('baseURL') + 'uploads/' + res[ index ] + "' />") );
                            }

                            if( index === res.length - 1 ) {
                                thumb.removeClass('ipsNoThumb ipsNoThumb_product').addClass('ipsType_center').html( $('<i class="fa fa-truck ipsType_light ipsType_veryLarge" />') );
                                $(item).find('.ipsDataItem_main > .ipsDataItem_title > .ipsType_light').remove()
                                return false;
                            }
                        }
                    });
                }
            });
        }
	});
}(jQuery, _));