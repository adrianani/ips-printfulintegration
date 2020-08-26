<?php


namespace IPS\printfulintegration\modules\front\store;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * images
 */
class _images extends \IPS\Dispatcher\Controller
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

		if( !\IPS\Request::i()->isAjax() || ( \IPS\Request::i()->isAjax() && !\IPS\Request::i()->do ) ) {
			\IPS\Output::i()->error('404_error_title', '2P103/1', 404);
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
		\IPS\Output::i()->error('404_error_title', '2P103/2', 404);
	}

	protected function invoice() {

		try {
			// if this is not a printful invoice, it will throw an error, thus responding with an empty array
			\IPS\Db::i()->select('invoice_id', 'printfulintegration_invoices', ['invoice_id=?', \IPS\Request::i()->id])->first();
			// load the invoice
			$invoice = \IPS\nexus\Invoice::load( \IPS\Request::i()->id );
			$images = array();

			foreach( $invoice->items as $item ) {
				$images[] = $item->image();
			}

			\IPS\Output::i()->json( $images );

		} catch( \Exception $e ) {
			\IPS\Output::i()->json([]);
		}
	}

	protected function purchases() {
		$where = array( array( 'ps_member=?', \IPS\Member::loggedIn()->member_id ) );

		$parentContacts = \IPS\nexus\Customer::loggedIn()->parentContacts();
		if ( \count( $parentContacts ) )
		{
			$or = array();
			foreach ( $parentContacts as $contact )
			{
				$where[0][0] .= ' OR ' . \IPS\Db::i()->in( 'ps_id', $contact->purchaseIds() );
			}
		}
		$where[] = array( 'ps_show=1' );
		
		$where[] = array( "ps_app IN('" . implode( "','", array_keys( \IPS\Application::enabledApplications() ) ) . "')" );

		$purchases = array();
		foreach( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'nexus_purchases', $where, 'ps_active DESC, ps_expire DESC, ps_start DESC' ), 'IPS\nexus\Purchase' ) as $purchase )
		{
			$purchases[] = ( $purchase->type === "printfulItem" && !empty( $purchase->extra['image'] ) && \is_array( $purchase->extra['image'] ) ) ? 
						\IPS\File::get( "printfulintegration_ProductImage", $purchase->extra['image']['container'] . '/' . $purchase->extra['image']['filename'] )->url : '';
		}

		\IPS\Output::i()->json($purchases);
	}

	protected function purchase() {

		try {
			// load the purchase
			$purchase = \IPS\nexus\Purchase::load( \IPS\Request::i()->id );

			if( $purchase->type === "printfulItem" ) {

				if( $purchase->extra['shipping'] ) {

					\IPS\Output::i()->json([
						'shipping' => TRUE
					]);

				}
				if( !empty( $purchase->extra['image'] ) && \is_array( $purchase->extra['image'] ) ) {

					\IPS\Output::i()->json([
						'url' => \IPS\File::get( "printfulintegration_ProductImage", $purchase->extra['image']['container'] . '/' . $purchase->extra['image']['filename'] )->url
					]);
				}
			}

			\IPS\Output::i()->json([]);

		} catch( \Exception $e ) {
			\IPS\Output::i()->json([]);
		}
	}

}