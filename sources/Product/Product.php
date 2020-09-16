<?php

namespace IPS\printfulintegration;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Product extends \IPS\Node\Model {

    protected static $multitons = array();

    public static $nodeTitle = 'menu__printfulintegration_overview_products';

    public static $databaseTable = 'printfulintegration_products';

    public static $databaseColumnOrder = 'position';

    public static $parentNodeColumnId = 'parent';
    
    public static $parentNodeClass = 'IPS\printfulintegration\Category';

    public static $databaseColumnEnabledDisabled = 'enabled';

    public function url() {
        $lang = \IPS\Lang::load( \IPS\Lang::defaultLanguage() );
        $seoTitle = \IPS\Http\Url\Friendly::seoTitle( ( $lang->checkKeyExists("printful_product_{$this->id}") ) ? $lang->get("printful_product_{$this->id}") : $this->title );

        if( $lang->checkKeyExists("printful_product_{$this->id}") ) {

            $seoTitle = \IPS\Http\Url\Friendly::seoTitle( $lang->get("printful_product_{$this->id}") );
        }

        return \IPS\Http\Url::internal("app=printfulintegration&module=store&controller=product&id={$this->id}", "front", "merch_product", $seoTitle );
    }

    public function form( &$form ) {

        $form->add( new \IPS\Helpers\Form\Translatable( 'printful_product_title', NULL, FALSE, array( 
            'key' => ( $this->id ) ? "printful_product_{$this->id}" : NULL 
        ) ) );

        $form->add( new \IPS\Helpers\Form\YesNo( 'printful_product_enabled', ( isset( $this->enabled ) && $this->enabled !== NULL ) ? $this->enabled : \IPS\Settings::i()->printful_product_enabled_default, FALSE ) );

        $form->add( new \IPS\Helpers\Form\Translatable( 'printful_product_desc', NULL, FALSE, array(
            'key' => ( $this->id ) ? "printful_product_{$this->id}_desc" : NULL,
            'editor' => array(
                'app' => "printfulintegration",
                'key' => "ProductDesc",
                'autoSaveKey' => ( $this->id ) ? "printful_product_{$this->id}_desc" : "printful_product_new_desc",
                'attachIds' => array( ( $this->id ?: 'new' ) )
            )
        ) ) );
        
        $form->add( new \IPS\Helpers\Form\Upload( 'printful_product_images', iterator_to_array( new \IPS\File\Iterator( \IPS\Db::i()->select( 'image_location', 'printfulintegration_product_images', array( 'product_id=?', $this->id ), 'image_primary DESC' ), 'printfulintegration_ProductImage' ) ), FALSE, array(
            'multiple' => TRUE,
            'storageExtension' => "printfulintegration_ProductImage",
            'image' => TRUE,
            'template' => "nexus.store.images",
        ) ) );

        $form->attributes['data-controller'] = "printfulintegration.admin.products.edit";
    }

    public function formatFormValues( $values ) {

        if( !empty( $values['printful_product_title'] ) ) {

            \IPS\Lang::saveCustom( 'printfulintegration', "printful_product_{$this->id}", $values['printful_product_title'] );
            unset( $values['printful_product_title'] );
        }

        if( !empty( $values['printful_product_desc'] ) ) {
            
            \IPS\Lang::saveCustom( 'printfulintegration', "printful_product_{$this->id}_desc", $values['printful_product_desc'] );
            unset( $values['printful_product_desc'] );
        }

        if( isset( $values['printful_product_images'] ) ) {
            \IPS\Db::i()->delete( 'printfulintegration_product_images', array( 'product_id=?', $this->id ) );

            if( !empty( $values['printful_product_images'] ) ) {

                foreach( $values['printful_product_images'] as $key => $image ) {

                    $primary = ( \IPS\Request::i()->printful_product_images_primary_image == $key ) ? 1 : 0;

					\IPS\Db::i()->insert( 'printfulintegration_product_images', array(
						'product_id'		=> $this->id,
						'image_location'	=> (string) $image,
						'image_primary'		=> $primary,
                    ) );
                    
                    if( $primary ) {
                        $this->image = (string) $image;
                    }
                }

            } else {
                $this->image = NULL;
            }
        }

        unset( $values['printful_product_images'] );

        foreach( $values as $key => $val ) {
            $values[ \substr( $key, 17 ) ] = $val;
            unset( $values[ $key ] );
        }

        return $values;
    }

    public function get__title() {
        if( \IPS\Member::loggedIn()->language()->checkKeyExists( "printful_product_{$this->id}" ) AND \IPS\Member::loggedIn()->language()->get( "printful_product_{$this->id}" ) !== "" ) {
			return \IPS\Member::loggedIn()->language()->addToStack( "printful_product_{$this->id}", NULL, array( 'escape' => TRUE ) );
        }

        return $this->title;
    }

    public function getTitle() {
        if( \IPS\Member::loggedIn()->language()->checkKeyExists( "printful_product_{$this->id}" ) AND \IPS\Member::loggedIn()->language()->get( "printful_product_{$this->id}" ) !== "" ) {
			return \IPS\Member::loggedIn()->language()->get( "printful_product_{$this->id}" );
        }

        return $this->title;
    }

    public function get__desc() {
        if( \IPS\Member::loggedIn()->language()->checkKeyExists( "printful_product_{$this->id}_desc" ) ) {
			return \IPS\Member::loggedIn()->language()->addToStack( "printful_product_{$this->id}_desc", NULL );
        }

        return NULL;
    }

    public static function constructFromData( $data, $updateMultitonStoreIfExists = TRUE ) {
        $obj = parent::constructFromData( $data, $updateMultitonStoreIfExists );

        $obj->variants = iterator_to_array( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select('*', 'printfulintegration_variants', ['product_id=?', $obj->id]), 'IPS\printfulintegration\Product\Variant' ) );
        $colors = array();
        $sizes  = array();
        $price = array();

        foreach( $obj->variants as $variant ) {

            if( $variant->size !== NULL && empty( $sizes[$variant->size] ) ) {
                $sizes[$variant->size] = $variant->size;
            }

            if( !\in_array( $variant->color, $colors ) && $variant->color !== NULL ) {
                $colors[] = $variant->color;
            }
        }

        $obj->sizes = $sizes;
        $obj->colors = $colors;

        try {
            $obj->price = json_decode(\IPS\Db::i()->select('price', 'printfulintegration_variants', ['product_id=?', $obj->id], "CAST(JSON_UNQUOTE(JSON_EXTRACT(price, '$.default.value')) AS DECIMAL(4,2)) ASC", [0, 1])->first(), TRUE);
        } catch ( \UnderflowException $e ) {}
        
        $obj->images = iterator_to_array( \IPS\Db::i()->select("image_location", 'printfulintegration_product_images', ['product_id=?', $obj->id], 'image_primary desc') );

        return $obj;
    }

    public static function contentTableTemplate() {

        return array( \IPS\Theme::i()->getTemplate( 'tables', 'core', 'front' ), 'rows' );
    }

    public function priceToDisplay( $customer = NULL, $properties = [], $html = TRUE ) {
		$customer = $customer ?: \IPS\nexus\Customer::loggedIn();
        $currency = ( $customer->member_id === \IPS\nexus\Customer::loggedIn()->member_id ) ? ( ( isset( \IPS\Request::i()->cookie['currency'] ) and \in_array( \IPS\Request::i()->cookie['currency'], \IPS\nexus\Money::currencies() ) ) ? \IPS\Request::i()->cookie['currency'] : $customer->defaultCurrency() ) : $customer->defaultCurrency();
        
        $defaultProperties = array(
            'color' => NULL,
            'size' => NULL,
        );

        if( empty( $properties ) ) {
            $price = $this->price;
        } else {
            $properties = array_merge(
                $defaultProperties,
                $properties
            );
            try {
                $price = json_decode(
                    \IPS\Db::i()->select(
                        "price",
                        "printfulintegration_variants",
                        [
                            "product_id=? AND color=? AND size=?",
                            $this->id,
                            ( $properties['color'] === 'undefined' ) ? NULL : $properties['color'],
                            ( $properties['size'] === 'undefined' ) ? NULL : $properties['size']
                        ]
                    )->first(),
                    TRUE
                );
            } catch( \UnderflowException $e ) {
                return $html ? \IPS\Theme::i()->getTemplate('store')->productPrice() : 'printful_not_available';
            }
        }
        
        if( empty( $price[ $currency ] ) ) {
            $price = new \IPS\Math\Number( (string) $price['default']['value'] );
            $conversionRates = \IPS\printfulintegration\Application::conversionRates();

            if($conversionRates === FALSE) {
                return $html ? \IPS\Theme::i()->getTemplate('store')->productPrice() : 'printful_not_available';
            }

            if( empty( $conversionRates[ $currency ] ) ) {
                $conversionRates = \IPS\printfulintegration\Application::conversionRates( TRUE );
            }

            $price = $price->multiply( new \IPS\Math\Number( number_format( $conversionRates[ $currency ], 4, '.', '' ) ) );

        } else {
            $price = new \IPS\Math\Number( \str_replace( ',', '.', (string) $price[ $currency ] ) );
        }

		if ( \IPS\Settings::i()->nexus_show_tax and \IPS\Settings::i()->printful_tax !== 0 )
		{
			try
			{
                $tax = \IPS\nexus\Tax::load( \IPS\Settings::i()->printful_tax );
                $rate = new \IPS\Math\Number( $tax->rate( $customer->estimatedLocation() ) );
                $price = $price->add( $price->multiply( $rate ) );
			}
			catch ( \OutOfRangeException $e ) { }
        }
        
		$price = $price->round(2, 2);

        return $html ? \IPS\Theme::i()->getTemplate('store')->productPrice( new \IPS\nexus\Money( $price, $currency ) ) : new \IPS\nexus\Money( $price, $currency );
    }

    public function getButtons($url, $subnode = FALSE) {
        $buttons = parent::getButtons($url, $subnode);

        return array(
            'edit' => $buttons['edit'],
            'pricing' => array(
                'icon'	=> 'usd',
                'title'	=> 'printful_pricing',
                'link'	=> $url->setQueryString([
                    'do' => "pricing",
                    'id' => $this->id,
                ])->csrf()
            ),
            'copy' => $buttons['copy'],
            'delete' => $buttons['delete'],
        );
    }

    public function save() {

        foreach( $this->changed as $k => $v ) {
            if( \in_array( $k, array(
                'variants',
                'sizes',
                'colors',
                'price',
                'images',
            ) ) ) {
                unset($this->changed[ $k ]);
            }
        }

        parent::save();
    }

    public function delete() {

        foreach( $this->variants as $variant ) {
            $variant->delete();
        }

        foreach( $this->images as $image ) {
            \IPS\File::get('printfulintegration_ProductImage', $image)->delete();
            \IPS\Db::i()->delete('printfulintegration_product_images', ['image_location=?', $image]);
        }

        parent::delete();

    }

}