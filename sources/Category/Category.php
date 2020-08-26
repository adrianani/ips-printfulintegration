<?php

namespace IPS\printfulintegration;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Category extends \IPS\Node\Model {

    protected static $multitons = array();

    public static $nodeTitle = 'menu__printfulintegration_overview_products';

    public static $databaseTable = 'printfulintegration_categories';

	public static $titleLangPrefix = 'printful_category_';

    public static $databaseColumnOrder = 'position';

	public static $databaseColumnParent = 'parent';

    public static $nodeSortable = TRUE;
    
	public static $subnodeClass = 'IPS\printfulintegration\Product';

    public function url() {
        return \IPS\Http\Url::internal("app=printfulintegration&module=store&controller=store&category={$this->id}", 'front', 'merch_category', \IPS\Http\Url\Friendly::seoTitle( \IPS\Member::loggedIn()->language()->get( "printful_category_{$this->id}" ) ) );
    }

	public static function loadFromUrl( \IPS\Http\Url $url )
	{
		$qs = array_merge( $url->queryString, $url->hiddenQueryString );
		
		if ( isset( $qs['category'] ) )
		{
			return static::load( $qs['category'] );
		}
		
		throw new \InvalidArgumentException;
	}

    public function form( &$form ) {
        $form->add( new \IPS\Helpers\Form\Translatable( 'printful_category_title', NULL, TRUE, array( 'app' => 'printfulintegration', 'key' => ( $this->id ? "printful_category_{$this->id}" : NULL ) ) ) );

        $form->add( new \IPS\Helpers\Form\Node( 'printful_category_parent', $this->parent ?: 0, FALSE, array(
			'class'		      => '\IPS\printfulintegration\Category',
			'disabled'	      => false,
			'zeroVal'         => 'node_no_parentd',
			'permissionCheck' => function( $node )
			{
                $class = '\IPS\printfulintegration\Category';

				if( isset( $class::$subnodeClass ) AND $class::$subnodeClass AND $node instanceof $class::$subnodeClass )
				{
					return FALSE;
				}

				return !isset( \IPS\Request::i()->id ) or ( $node->id != \IPS\Request::i()->id and !$node->isChildOf( $node::load( \IPS\Request::i()->id ) ) );
			}
        ) ) );
    }

    public function formatFormValues( $values ) {

		if ( !$this->id )
		{
            $this->save();
        }
        
        if ( isset( $values['printful_category_parent'] ) )
		{
            $values['parent'] = $values['printful_category_parent'] ? \intval( $values['printful_category_parent']->id ) : 0;
            unset( $values['printful_category_parent'] );
		}

        \IPS\Lang::saveCustom( 'printfulintegration', "printful_category_{$this->id}", $values['printful_category_title'] );
        $this->seo_title = \IPS\Http\Url\Friendly::seoTitle( $values['printful_category_title'][ \IPS\Lang::defaultLanguage() ] );
        unset( $values['printful_category_title'] );

        return $values;
    }

    public function delete() {

        \IPS\Lang::deleteCustom( 'printfulintegration', "printful_category_{$this->id}" );
        parent::delete();
    }

    public function getButtons( $url, $subnode=FALSE ) {
        $buttons = parent::getButtons($url, $subnode);

        if( isset( $buttons['add'] ) ) {
            $buttons['add']['link'] = $buttons['add']['link']->setQueryString('subnode', 0);
        }

        return array_merge( array(
            'add_product' => array(
                'icon'	=> 'cart-plus',
                'title'	=> 'printful_add_product',
                'link'	=> $url->setQueryString( array( 'do' => 'import', 'parent' => $this->_id ) )->csrf(),
                'data'	=> ( static::$modalForms ? array( 'ipsDialog' => '', 'ipsDialog-title' => \IPS\Member::loggedIn()->language()->addToStack('add') ) : array() )
            ),
        ), $buttons );
    }

    public function canCopy() {

        return FALSE;
    }

}