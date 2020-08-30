<?php


namespace IPS\printfulintegration\modules\front\store;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * store
 */
class _store extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Output::i()->breadcrumb['module'] = array(
			\IPS\Http\Url::internal( "app=printfulintegration&module=store&controller=store", 'front', 'merch_store' ),
			\IPS\Member::loggedIn()->language()->addToStack('frontnavigation_printfulintegration')
		);

		if( !\IPS\Member::loggedIn()->member_id ) {
			\IPS\Output::i()->error('no_module_permission_guest', '2P100/1', 403, 'no_module_permission_guest');
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
		$categories = \IPS\printfulintegration\Category::roots();
		$where = 'enabled=1';
		$page = !empty( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1;

		if( $page < 1 ) {
			$page = 1;
		}
		
		$limit = 25;
		$skip = ($page - 1) * $limit;
		$baseUrl = \IPS\Http\Url::internal('app=printfulintegration&module=store&controller=store', 'front', 'merch_store');

		if ( isset( \IPS\Request::i()->currency ) and \in_array( \IPS\Request::i()->currency, \IPS\nexus\Money::currencies() ) )
		{
			if ( isset( \IPS\Request::i()->csrfKey ) and \IPS\Login::compareHashes( (string) \IPS\Session::i()->csrfKey, (string) \IPS\Request::i()->csrfKey ) )
			{
				$_SESSION['printful_cart'] = array();
				\IPS\Request::i()->setCookie( 'currency', \IPS\Request::i()->currency );
			}
			$currency = \IPS\Request::i()->currency;
		}
		else
		{
			$currency = $currency = ( isset( \IPS\Request::i()->cookie['currency'] ) and \in_array( \IPS\Request::i()->cookie['currency'], \IPS\nexus\Money::currencies() ) ) ? \IPS\Request::i()->cookie['currency'] : \IPS\nexus\Customer::loggedIn()->defaultCurrency();
		}

		if( !empty( \IPS\Request::i()->category ) ) {
			try {
				$category = \IPS\printfulintegration\Category::load( \IPS\Request::i()->category );
				$parents = $category->children();
				$IDs = array( $category->id );

				foreach($category->children('view', NULL, FALSE) as $parent) {
					$IDs[] = $parent->id;
				}

				$where .= ' AND ' . \IPS\Db::i()->in('parent', $IDs);

			} catch( \OutOfRangeException $e ) {
				\IPS\Output::i()->error( 'page_not_found', '2P100/2', 404, '' );
			}
		}
		
		$pages = ceil( \IPS\Db::i()->select('COUNT(*)', 'printfulintegration_products', $where)->first() / $limit );

		$products = new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select('*', 'printfulintegration_products', $where, NULL, array($skip, $limit)), 'IPS\printfulintegration\Product' );
		$pagination = ($pages > 1) ? \IPS\Theme::i()->getTemplate('global', 'core', 'global')->pagination( $baseUrl, $pages, $page, $limit ) : NULL;
		

		\IPS\Output::i()->sidebar['contextual'] = \IPS\Theme::i()->getTemplate('store')->menu( $categories );
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'store.css', 'printfulintegration' ) );
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_merch.js', 'printfulintegration', 'front' ) );

		if( !empty( \IPS\Request::i()->category ) ) {
			foreach( $category->parents() as $parent ) {
				\IPS\Output::i()->breadcrumb[] = array(
					$parent->url(),
					\IPS\Member::loggedIn()->language()->addToStack("printful_category_{$parent->id}")
				);
			}

			\IPS\Output::i()->breadcrumb[] = array(
				$category->url(),
				\IPS\Member::loggedIn()->language()->addToStack("printful_category_{$category->id}")
			);
		}
		

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_printfulintegration');

		if(\IPS\Request::i()->isAjax()) {
			\IPS\Output::i()->json(array(
				'content' => \IPS\Theme::i()->getTemplate('store')->storeContent($products, $pagination, $currency),
			));
		} else {
			\IPS\Output::i()->output .= \IPS\Theme::i()->getTemplate('store')->store($products, $pagination, $currency);
		}
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}