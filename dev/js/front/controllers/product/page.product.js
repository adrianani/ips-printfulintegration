;( function($, _, undefined) {
    "use strict";

	ips.controller.register('printful.product.page', {
        _prices: null,

        initialize: function() {
            $('.productImageCarousel').slick({
                infinite: true,
                slidesToShow: 1,
                arrows: false,
                fade: true,
                asNavFor: '.productImageCarousel_nav',
            });

            $('.productImageCarousel_nav').slick({
                infinite: true,
                slidesToShow: 3,
                slidesToScroll: 1,
                centerMode: true,
                arrows: false,
                asNavFor: '.productImageCarousel',
                centerPadding: '50px',
                focusOnSelect: true,
                variableWidth: true,
                responsive: [
                    {
                        breakpoint: 470,
                        settings: {
                            slidesToShow: 2,
                        }
                    }
                ]
            });

            this._prices = this.scope.find('.cPrintful_itemPrice').data('pricing');

            this.on('change', 'select[name="printful_item_size"]', this.changePrice);
            this.on('submit', '.ipsForm', this.addToCart);

            $('.slick-slide.ipsHide').removeClass('ipsHide');
        },

        changePrice: function(e) {
            let size = $(e.currentTarget).children('option:selected').html(),
                color = this.scope.find('select[name="printful_item_color"]').children('option:selected').html(),
                itemPriceContainer = this.scope.find('.cPrintful_itemPrice');
            
            ips.getAjax()(
                itemPriceContainer.data('url') + "&size=" + size + "&color=" + color,
            ).done((response) => {
                itemPriceContainer.html( response.price );
            })
        },

        addToCart: function(e) {
            e.preventDefault();
            let form = $(e.currentTarget),
                data = form.serialize(),
                formDims = ips.utils.position.getElemDims( form ),
                formPos = ips.utils.position.getElemPosition( form ),
                loadingElem = $('<div class="ipsLoading ipsAreaBackground_light ipsAreaBackground_rounded" />');

            form.after( loadingElem );
    
            loadingElem.css({
                position: 'absolute',
                top: formPos.offsetPos.top + 'px',
                left: formPos.offsetPos.left + 'px',
                width: formDims.outerWidth + 'px',
                height: formDims.outerHeight + 'px'
            });

            ips.getAjax()(
                form.attr('action'),
                {
                    data,
                    type: 'post'
                }
            ).done((response) => {
                let content = $('<div />').html(response.content);

                ips.getContainer().append(content);

                let dialog = ips.ui.dialog.create({
                    title: this.scope.find('.ipsType_pageTitle.ipsType_largeTitle').text(),
                    content,
                    size: 'narrow',
                    forceReload: true,
                    modal: true,
                });

                dialog.show();

                if( response.cart ) {
                    $('#printfulCart_container').replaceWith( response.cart );
                    $('#printfulCart_sep').removeClass('ipsHide');
                }

                loadingElem.remove();
            });
        }
    });
}(jQuery, _));