;( function($, _, undefined) {
    "use strict";

	ips.controller.register('printful.cart.checkout', {
        
        initialize: function() {
            this.on('submit', this.submit);
        },

        submit: function(e) {
            e.preventDefault();
            e.stopPropagation();

            let form = this.scope.find('form'),
                newUrl = form.data('shippingurl'),
                newTitle = form.data('shippingtitle'),
                currentDialog = ips.ui.dialog.getObj( $('#cartCheckout') ),
                formData = form.serialize();

            currentDialog.updateContent('');
            currentDialog.setLoading(true);

            ips.getAjax()(form.attr('action'), {
                data: formData,
                type: 'post',
            }).done(function(response) {
                let newDialog =  ips.ui.dialog.create({
                        title: newTitle,
                        url: newUrl,
                    });
                
                currentDialog.hide();
                newDialog.show();
            });
        }
    });
}(jQuery, _));