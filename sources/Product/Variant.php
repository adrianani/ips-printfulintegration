<?php

namespace IPS\printfulintegration\Product;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Variant extends \IPS\Node\Model {

    protected static $multitons = array();

    public static $nodeTitle = 'menu__printfulintegration_overview_products';

    public static $databaseTable = 'printfulintegration_variants';

	public static $parentNodeColumnId = 'product_id';

    public function url() {
        return \IPS\Http\Url::internal('app=printfulintegration&module=store&controller=product')->setQueryString(['id' => $this->id, 'variant' => $this->variant_id]);
    }

    public function form( &$form ) {
        throw \BadFunctionCallException;
    }
  
    public static function constructFromData( $data, $updateMultitonStoreIfExists = TRUE ) {
        $obj = parent::constructFromData( $data, $updateMultitonStoreIfExists );
        $obj->price = json_decode( $obj->price, TRUE );

        return $obj;
    }

    public function get__title() {
        return $this->color . ' / ' . $this->size;
    }

}