<?php


namespace IPS\printfulintegration\modules\front\store;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * orders
 */
class _orders extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'clients.css', 'nexus' ), \IPS\Theme::i()->css( 'nexus.css', 'nexus' ) );
		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		
		\IPS\Output::i()->breadcrumb['module'] = array(
			\IPS\Http\Url::internal( "app=printfulintegration&module=store&controller=store", 'front', 'merch_store' ),
			\IPS\Member::loggedIn()->language()->addToStack('frontnavigation_printfulintegration')
		);
		
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{	
		$where = array( 'i_member=?', \IPS\Member::loggedIn()->member_id );
		$parentContacts = \IPS\nexus\Customer::loggedIn()->parentContacts( array( 'billing=1' ) );
		$invoicesPerPage = 25;
		
		if ( \count( $parentContacts ) )
		{
			$or = array();
			foreach ( array_keys( iterator_to_array( $parentContacts ) ) as $id )
			{
				$where[0] .= ' OR i_member=?';
				$where[] = $id;
			}
		}

		$where = array( $where, 'p.printful_order_id IS NOT NULL' );

		$page = isset( \IPS\Request::i()->page ) ? \IPS\Request::i()->page : 1;
		
		if ( $page < 1 )
		{
			$page = 1;
		}
		
		$pages =  ceil( \IPS\Db::i()->select( 'COUNT(*)', 'nexus_invoices', $where )->join(['printfulintegration_invoices', 'p'], 'nexus_invoices.i_id=p.invoice_id')->first() / $invoicesPerPage );
		$pages = ($pages == 0) ? 1 : $pages;
		
		if ( $page > $pages )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=printfulintegration&module=store&controller=orders", 'front', 'merch_orders' ) );
		}
		
		$pagination = \IPS\Theme::i()->getTemplate( 'global', 'core', 'global' )->pagination( \IPS\Http\Url::internal( "app=printfulintegration&module=store&controller=orders", 'front', 'merch_orders' ), $pages, $page, $invoicesPerPage );
		
		$invoices = new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*, nexus_invoices.i_id AS id', 'nexus_invoices', $where, 'i_date DESC', array( ( $page - 1 ) * $invoicesPerPage, $invoicesPerPage ) )->join(['printfulintegration_invoices', 'p'], 'nexus_invoices.i_id=p.invoice_id'), 'IPS\nexus\Invoice' );

		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'printful.invoices.core.app.js', 'printfulintegration', 'interface' ) );
		\IPS\Output::i()->breadcrumb[] = array(
			\IPS\Http\Url::internal( "app=printfulintegration&module=store&controller=orders", 'front', 'merch_orders' ),
			\IPS\Member::loggedIn()->language()->addToStack('printful_orders')
		);

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('printful_orders');
		\IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('clients', 'nexus')->invoices( $invoices, $pagination );
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}