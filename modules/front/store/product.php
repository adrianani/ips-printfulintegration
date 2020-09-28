<?php


namespace IPS\printfulintegration\modules\front\store;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * product
 */
class _product extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return	void
     */
    public function execute()
    {
        \IPS\Output::i()->breadcrumb['module'] = array(
            \IPS\Http\Url::internal("app=printfulintegration&module=store&controller=store", 'front', 'merch_store'),
            \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_printfulintegration')
        );
        
        try {
            $product = \IPS\printfulintegration\Product::load(\IPS\Request::i()->id);

            if (!$product->enabled) {
                \IPS\Output::i()->error('node_error', '2P101/4', 404, '');
            }
        } catch (\OutOfRangeException $e) {
            \IPS\Output::i()->error('node_error', '2P101/3', 404, '');
        }
        
        parent::execute();
    }

    /**
     * ...
     *
     * @return	void
     */
    protected function manage()
    {
        try {
            if (!isset($_SESSION['printful_cart'])) {
                $_SESSION['printful_cart'] = array();
            }

            $product = \IPS\printfulintegration\Product::load(\IPS\Request::i()->id);
            $form = $this->_productForm($product);

            foreach ($product->parents() as $parent) {
                $bcItems[] = array(
                    $parent->url(),
                    $parent->_title
                );
            }

            $bcItems[] = array(
                $product->url(),
                $product->_title
            );
            
            \IPS\Output::i()->cssFiles = array_merge(\IPS\Output::i()->cssFiles, \IPS\Theme::i()->css('product.css', 'printfulintegration'));
            \IPS\Output::i()->jsFiles = array_merge(\IPS\Output::i()->jsFiles, \IPS\Output::i()->js('slick.min.js', 'printfulintegration', 'interface'), \IPS\Output::i()->js('front_product.js', 'printfulintegration', 'front'));
            
    
            \IPS\Output::i()->title = $product->_title;

            \IPS\Output::i()->breadcrumb = array_merge(
                \IPS\Output::i()->breadcrumb,
                $bcItems
            );
            \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('store')->product($product, $form);
        } catch (\OutOfRangeException $e) {
            \IPS\Output::i()->error('node_error', '2P101/2', 404, '');
        }
    }

    protected function _productForm($product = NULL)
    {
        if( $product === NULL ) {
            try {
                $product = \IPS\printfulintegration\Product::load( \IPS\Request::i()->id );
            } catch (\OutOfRangeException $e) {
                \IPS\Output::i()->error('node_error', '2P101/7', 404, '');
            }
        }

        $form = new \IPS\Helpers\Form("addProductToCart_{$product->id}", 'add_to_cart');
        $form->class = "ipsForm_vertical";

        if (!empty($product->colors)) {
            $form->add(new \IPS\Helpers\Form\Select('printful_item_color', 0, true, [ 'options' => $product->colors ]));
        }
        if (!empty($product->sizes)) {
            $form->add(new \IPS\Helpers\Form\Select('printful_item_size', 0, true, [ 'options' => $product->sizes ]));
        }

        $form->add(new \IPS\Helpers\Form\Number('printful_item_quantity', 1, true, [ 'min' => 1 ]));

        if ($values = $form->values()) {
            $color = !empty($product->colors) ? $product->colors[ $values['printful_item_color'] ] : null;
            $size  = !empty($product->sizes) ? $product->sizes[ $values['printful_item_size'] ] : null;
            $price = $product->priceToDisplay(null, array( 'size' => $size, 'color' => $color ), false);

            if ($price === 'printful_not_available') {
                if (\IPS\Request::i()->isAjax()) {
                    \IPS\Output::i()->json([
                        'content' => \IPS\Theme::i()->getTemplate('store')->cartReview()
                    ]);
                }

                \IPS\Output::i()->redirect(\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=product&id=' . $product->id, 'front', 'merch_product'), 'printful_not_available');
                return 0;
            }

            $item = new \IPS\printfulintegration\extensions\nexus\Item\PrintfulProduct($product->getTitle(), $price);
            $item->id = $product->id;
            $item->quantity = $values['printful_item_quantity'];
            
            $image = \IPS\File::get('printfulintegration_ProductImage', \IPS\Db::i()->select('image_location', 'printfulintegration_product_images', ['product_id=? AND image_primary=?', $product->id, 1])->first());

            try {
                $image = \IPS\File::get('printfulintegration_ProductImage', \IPS\Db::i()->select('image_location', 'printfulintegration_product_images', ['product_id=? AND variant_color=?', $product->id, $color])->first());
            } catch (\UnderflowException $e) {
            }

            $item->extra = [
                'printful_id' => $product->printful_id,
                'variant_id' => \IPS\Db::i()->select('printful_id', 'printfulintegration_variants', ['product_id=? AND size=? AND color=?', $product->id, $size, $color])->first(),
                'color' => $color,
                'size' => $size,
                'image' => $image,
                'shipping' => false,
            ];
            $item->url = $product->url();

            foreach ($_SESSION['printful_cart'] as $id => $cartItem) {
                if ($cartItem->id == $item->id && $cartItem->extra['variant_id'] == $item->extra['variant_id']) {
                    $item->quantity += $cartItem->quantity;
                    unset($_SESSION['printful_cart'][$id]);
                    break;
                }
            }

            $_SESSION['printful_cart'][] = $item;
            
            /* Turn off guest page caching */
            if (\IPS\CACHE_PAGE_TIMEOUT and !\IPS\Member::loggedIn()->member_id) {
                \IPS\Request::i()->setCookie('noCache', 1);
            }

            if (\IPS\Request::i()->isAjax() && empty(\IPS\Request::i()->noAjax)) {
                \IPS\Output::i()->json([
                    'content' => \IPS\Theme::i()->getTemplate('store')->cartReview(true, $values['printful_item_quantity'], $product->_title),
                    'cart' => \IPS\Theme::i()->getTemplate('store')->cartUserBar(false)
                ]);
            }

            \IPS\Output::i()->redirect($product->url(), 'printful_added_to_cart');
        }

        return $form;
    }

    protected function price()
    {
        $size = \IPS\Request::i()->size ?: null;
        $color = \IPS\Request::i()->color ?: null;
        if (empty($size)) {
            \IPS\Output::i()->error('bad_request', '2P101/5', 403, '');
        } else {
            try {
                $product = \IPS\printfulintegration\Product::constructFromData(\IPS\Db::i()->select('*', 'printfulintegration_products', ['id=?', \IPS\Request::i()->id])->first());

                \IPS\Output::i()->json([
                    'price' => $product->priceToDisplay(null, array(
                        'size' => $size,
                        'color' => $color,
                    ))
                ]);
            } catch (\UnderflowException $e) {
                \IPS\Output::i()->error('node_error', '2P101/6', 404, '');
            }
        }
    }
    
    // Create new methods with the same name as the 'do' parameter which should execute it
}
