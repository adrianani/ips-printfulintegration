;( function($, _, undefined) {
    "use strict";

	ips.controller.register('store.merch.list', {
        initialize: function() {
            this.on(document, 'click', "#elCurrency_menu > li:not(.ipsMenu_itemChecked) > a", this.currencyChangeWarn);
        },

        currencyChangeWarn: function(e) {
            e.stopImmediatePropagation();
            e.preventDefault();

            ips.ui.alert.show({
                type: 'confirm',
                icon:  'exclamation-triangle',
                message: ips.getString('store_currency_change_warning'),
                callbacks: {
                    ok: () => {
                        window.location = $(e.target).attr('href');
                    }
                }
            });
        }
    });
}(jQuery, _));