<?php


namespace IPS\printfulintegration\modules\front\store;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * ajax
 */
class _ajax extends \IPS\Dispatcher\Controller
{
	public function execute() {

		unset( \IPS\Output::i()->breadcrumb['module'] );

		if( !( \IPS\Dispatcher::i()->application instanceof \IPS\Application ) || !\IPS\Dispatcher::i()->application->canManageWidgets() ) {
			\IPS\Output::i()->error('page_not_found', '2P105/0', 404);
		}

		parent::execute();
	}

	protected function findItem() {
		
		$search = str_replace( array( '%', '_' ), array( '\%', '\_' ), mb_strtolower( \IPS\Request::i()->input ) );
		$query = \IPS\Db::i()->select('*', 'printfulintegration_products', (
			empty( $search ) ? NULL : [
			\IPS\Db::i()->in(
				'id',
				\IPS\Db::i()->select("SUBSTRING_INDEX(`word_key`, '_', -1) AS id", 'core_sys_lang_words', "`word_default` LIKE CONCAT('%', ?, '%') OR `word_custom` LIKE CONCAT('%', ?, '%')"),
			) . " OR `title` LIKE CONCAT('%', ?, '%')",
			$search, $search, $search
		] ) );

		$return = array();

		foreach( $query as $_product ) {

			$product = \IPS\printfulintegration\Product::constructFromData( $_product );
			$parents = "";
			foreach( $product->parents() as $category ) {
			    $parents .= $category->_title;
			    if( $category->_id !== $product->parent()->_id ) {
			        $parents .= '&nbsp;<i class="fa fa-angle-right" aria-hidden="true"></i>&nbsp;';
                }
            }
			$return[] = array(
			    'id' => $product->_id,
			    'image' => \IPS\File::get( 'printfulintegration_ProductImage', $product->image )->url,
			    'value' => $product->_title,
                'parents' => $parents,
            );
		}

		\IPS\Output::i()->json($return);

	}
}