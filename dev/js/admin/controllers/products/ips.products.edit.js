;( function($, _, undefined){
	"use strict";

	ips.controller.register('printfulintegration.admin.products.edit', {
        initialize: function() {

            if ( $('input[name="printful_product_images_primary_image"]').length ) {
				$('input[name="printful_product_images_primary_image"]:first').attr('checked', true);
			}
        }
    });

}(jQuery, _));