;( function($, _, undefined){
	"use strict";

	ips.controller.mixin( 'printful.invoices', 'core.front.core.app', true, function () {

        this.after('initialize', function() {

            $('.ipsDataList.cNexusOrderList_items > .ipsDataItem:last-of-type .ipsNoThumb')
                .removeClass('ipsNoThumb ipsNoThumb_product').addClass('ipsType_center')
                .html( $('<i class="fa fa-truck ipsType_light ipsType_veryLarge" />') );
        });
	});
}(jQuery, _));