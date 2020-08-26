;( function($, _, undefined) {
    "use strict";

	ips.controller.register('printful.cart.review', {
        
        initialize: function() {
            this.on('change', 'form[data-role="quantityForm"] > select', this.updateQuantity);
        },

        updateQuantity(e) {
            let selected = $(e.currentTarget).children('option:selected').html(),
                url = $(e.currentTarget).parent().attr('action') + `&quantity=${selected}`;

            ips.getAjax()(url).done(function(response) {
                $('div[data-role="merchCartContent"]').html(response.cart);
            });
        }
    });
}(jQuery, _));