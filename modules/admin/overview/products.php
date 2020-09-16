<?php

namespace IPS\printfulintegration\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Categories
 */
class _products extends \IPS\Node\Controller
{

    protected $nodeClass = 'IPS\printfulintegration\Category';
    
	public static $csrfProtected = TRUE;

    protected function import() {

        \IPS\Session::i()->csrfCheck();

        $parent = \IPS\Request::i()->parent;

        if( !isset( \IPS\Request::i()->process ) ) {

            if( \IPS\printfulintegration\Api::i()->apiKey() ) {
                $page = isset( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1;
                $limit = 20;
                $offset = ($page - 1) * $limit;
                $apiProducts = \IPS\printfulintegration\Api::i()->getProducts($offset, $limit, \IPS\Request::i()->printfulSearch);
                $products = $apiProducts['result'];
                $total = $apiProducts['paging']['total'];
                $pages = ceil($total / $limit);
                $pagination = \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->pagination( \IPS\Request::i()->url(), $pages, $page, $limit );
                
                if ( $pages and $page > $pages )
                {
                    \IPS\Output::i()->redirect( \IPS\Request::i()->url()->setQueryString('page', $pages), NULL, 303 );
                }
    
                if( \IPS\Request::i()->isAjax() ) {
    
                    \IPS\Output::i()->json([
                        'contents' 	=> \IPS\Theme::i()->getTemplate('products')->productsList( $products, $pagination ),
                    ]);
    
                    return;
                }
    
                $storeName = \IPS\printfulintegration\Api::i()->store()['name'];
                \IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin_products.js', 'printfulintegration' ) );
                \IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'products.css', 'printfulintegration' ) );
                \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('products')->products( $storeName, $products, $pagination, \IPS\Request::i()->printfulSearch, FALSE );
            } else {
                \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('products')->products( NULL, NULL, NULL, NULL, TRUE );
            }
        } else {
            $url = \IPS\Request::i()->url();
            \IPS\Output::i()->output = new \IPS\Helpers\MultipleRedirect(
                $url,
                function( $data ) {
                    $ids = explode( ',', \IPS\Request::i()->process );
                    $index = \IPS\Request::i()->mr;
                    $total = \count( $ids );

                    if( $index >= $total ) {
                        return NULL;
                    }

                    $printfulProduct = \IPS\printfulintegration\Api::i()->getProduct( $ids[ $index ] );

                    $product = new \IPS\printfulintegration\Product;
                    $product->printful_id = $printfulProduct['sync_product']['id'];
                    $product->parent = \IPS\Request::i()->parent;
                    $product->title = $printfulProduct['sync_product']['name'];

                    $product->save();

                    $images = array();

                    foreach( $printfulProduct['sync_variants'] as $i => $printfulVariant ) {
                        // get images for the product
                        $url = end($printfulVariant['files'])['preview_url'];

                        if($i === 0) {
                            $product->base_price = $printfulVariant['retail_price'];
                        }

                        $variant = new \IPS\printfulintegration\Product\Variant;
                        $variant->product_id = $product->id;
                        $variant->printful_id = $printfulVariant['id'];
                        $variant->printful_variant_id = $printfulVariant['product']['variant_id'];
                       
                        $price = [
                            'default' => [
                                'value' => $printfulVariant['retail_price'],
                                'currency' => $printfulVariant['currency']
                            ],
                            $printfulVariant['currency'] => $printfulVariant['retail_price']
                        ];

                        $variant->price = json_encode($price);
                        
                        // variant characteristics ( color, size )
                        $variant->color = NULL;
                        $variant->size = NULL;
                       
                        if( \strpos( $printfulVariant['sku'], '_' ) !== FALSE ) {
                            $properties = \explode('_', $printfulVariant['sku'])[1];

                            if( \strpos( $properties, '-' ) ) {

                                $properties = \explode( '-', $properties );
                                $lastItem = \count( $properties ) - 1;

                                if( \strpos( $properties[ $lastItem ], '×' ) || \strpos( $properties[ $lastItem ], 'x' ) || \in_array( $properties[ $lastItem ], array( 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL' ) ) ) {
                                    $variant->size = $properties[ $lastItem ];
                                    unset( $properties[ $lastItem ] );
                                    $variant->color = implode( '-', $properties );
                                }

                            } else if( \strpos( $properties, '×' ) || \strpos( $properties, 'x' ) || \in_array( $properties, array( 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL' ) ) ) {

                                $variant->size = $properties;
                            }
                        } 

                        $variant->save();

                        if( !\in_array( $url, $images ) ) {
                            $images[] = $url;
                            $response = \IPS\Http\Url::external( $url )->request()->get();
                            $extension = str_replace( 'image/', '', $response->httpHeaders['Content-Type'] );

                            $file = \IPS\File::create( 'printfulintegration_ProductImage', "{$product->printful_id}_{$variant->printful_id}.{$extension}", (string) $response );
                            
                            $primary = ($i === 0) ? 1 : 0;
                            if( $primary ) {
                                $product->image = $file;
                            }

                            \IPS\Db::i()->insert('printfulintegration_product_images', array(
                                'product_id' => $product->id,
                                'variant_color' => $variant->color,
                                'image_location' => $file,
                                'image_primary' => $primary
                            ));
                        }
                    }

                    $product->save();

                    $index++;

                    return [
                        $index,
                        \IPS\Member::loggedIn()->language()->addToStack( 'printful_import_progress', FALSE, [
                            'sprintf' => [
                                $index,
                                $total,
                            ]
                        ]),
                        ($index / $total) * 100
                    ];
                },
                function() {
                    \IPS\Output::i()->redirect( \IPS\Http\Url::internal('app=printfulintegration&module=overview&controller=products'), 'printful_import_complete' );
                }
            );
        }

    }

    protected function pricing() {
        
        \IPS\Session::i()->csrfCheck();

        try {
            $product = \IPS\printfulintegration\Product::constructFromData(
                \IPS\Db::i()->select('*', 'printfulintegration_products', array( 'id=?', \IPS\Request::i()->id ))->first()
            );

            \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('printful_product_pricing', FALSE, array( 'sprintf' => $product->title ));
            $matrix = new \IPS\Helpers\Form\Matrix;

            $matrix->manageable = FALSE;
            $columns = array(
                'printful_variant_name' => function($key, $value, $data) use ( $product ) {
                    return ( \count( $product->variants ) == 1 ) ? $product->_title : $data['name'];
                }
            );
            $rows = array();

            foreach( \IPS\nexus\Money::currencies() as $currency ) {
                $columns[ $currency ] = function( $key, $value, $data ) use ( $currency ) {
                    return new \IPS\Helpers\Form\Number( $key, ( empty( $data['price'][ $currency ] ) ? NULL : $data['price'][ $currency ] ), FALSE, array(
                        'decimals' => 2,
                    ) );
                };
            }

            foreach( $product->variants as $variant ) {
                $rows[ @\str_replace(' ', '-', $variant->color) . '_' . $variant->size ] = array(
                    'price' => $variant->price,
                    'name' => $variant->color . " / " . $variant->size,
                );
            }

            $matrix->columns = $columns;
            $matrix->rows = $rows;

            if ( $values = $matrix->values() ) {
                foreach( $product->variants as $variant ) {
                    $price = array(
                        'default' => $variant->price['default'],
                    );

                    foreach( \IPS\nexus\Money::currencies() as $currency ) {
                        if( !empty($values[ @\str_replace(' ', '-', $variant->color) . '_' . $variant->size ][$currency]) ) {
                            $price[ $currency ] = $values[ @\str_replace(' ', '-', $variant->color) . '_' . $variant->size ][$currency];
                        }
                    }

                    $variant->price = json_encode($price);

                    $variant->save();
                }
            }

            \IPS\Output::i()->output .= \IPS\Theme::i()->getTemplate('products')->pricing( $matrix );

        } catch ( \UnderflowException $e ) {
            \IPS\Output::i()->error('node_error', '2P102/1',403, '');
        }
    }

    protected function form() {
        if( \IPS\Request::i()->subnode == 1 ) {
            \IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'admin_store.js', 'nexus' ) );
        }

        parent::form();
    }

}