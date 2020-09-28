<?php


namespace IPS\printfulintegration\modules\front\store;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * cart
 */
class _cart extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{

		\IPS\Output::i()->breadcrumb['module'] =  array(
			\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=store', 'front', 'merch_store'),
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
		if( !isset( $_SESSION['printful_cart'] ) ) {
			$_SESSION['printful_cart'] = array();
		
			if ( \IPS\CACHE_PAGE_TIMEOUT and !\IPS\Member::loggedIn()->member_id )
			{
				\IPS\Request::i()->setCookie( 'noCache', 0, \IPS\DateTime::ts( time() - 86400 ) );
			}
		}
		
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('printful_cart');
		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		\IPS\Output::i()->breadcrumb[] = array(
			\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=cart', 'front', 'merch_cart'),
			\IPS\Member::loggedIn()->language()->addToStack('printful_cart')
		);
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_cart.js', 'printfulintegration', 'front' ) );
		\IPS\Output::i()->output .= \IPS\Theme::i()->getTemplate('store')->cart();
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it

	protected function quantity() {
		\IPS\Session::i()->csrfCheck();

		$item = \IPS\Request::i()->item;
		$quantity = \IPS\Request::i()->quantity;

		if($quantity == 0) {

			unset( $_SESSION['printful_cart'][$item] );
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal("app=printfulintegration&module=store&controller=cart")->csrf(), 'printful_item_removed' );
		}

		if( empty( $_SESSION['printful_cart'][$item] ) ) {
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal("app=printfulintegration&module=store&controller=cart")->csrf() );
		} else {
			$_SESSION['printful_cart'][$item]->quantity = $quantity;

			if( \IPS\Request::i()->isAjax() ) {
				\IPS\Output::i()->json(['cart' => \IPS\Theme::i()->getTemplate('store')->cartContents()]);
			} else {
				\IPS\Output::i()->redirect( \IPS\Http\Url::internal("app=printfulintegration&module=store&controller=cart")->csrf(), 'printful_item_quantity_updated' );
			}
		}
		
	}

	protected function clear() {
		\IPS\Request::i()->confirmedDelete();

		unset( $_SESSION['printful_cart'] );	
		
		if ( empty( $_SESSION['cart'] ) and \IPS\CACHE_PAGE_TIMEOUT and !\IPS\Member::loggedIn()->member_id )
		{
			\IPS\Request::i()->setCookie( 'noCache', 0, \IPS\DateTime::ts( time() - 86400 ) );
		}
			
		\IPS\Output::i()->redirect( \IPS\Http\Url::internal("app=printfulintegration&module=store&controller=store")->csrf(), 'printful_cleared_cart' );
	}

	public function checkout() {

		if( !\IPS\Member::loggedIn()->member_id ) {
			\IPS\Output::i()->error('no_module_permission_guest', '2P104/1', 403);
		}

		\IPS\Session::i()->csrfCheck();
		
		$form = new \IPS\Helpers\Form('checkout', 'continue', NULL, array(
			'data-shippingUrl' => \IPS\Http\Url::internal('app=printfulintegration&module=store&controller=cart&do=shipping')->csrf(),
			'data-shippingTitle' => \IPS\Member::loggedIn()->language()->addToStack('choose_shipping_method'),
		) );

		$addresses = \IPS\Db::i()->select( 'id, address, primary_shipping', 'nexus_customer_addresses', array( '`member`=?', \IPS\Member::loggedIn()->member_id ) );
		$options = [];
		$primaryShipping = NULL;
		
		foreach ( new \IPS\Patterns\ActiveRecordIterator( $addresses, 'IPS\nexus\Customer\Address' ) as $address )
		{
			$options[ $address->id ] = $address->address->toString(', ');
			if ( $address->primary_shipping )
			{
				$primaryShipping = $address->id;
			}
		}
	
		$options[0] = 'other';
		
		$form->add( new \IPS\Helpers\Form\Radio( 'shipping_address', $primaryShipping ?: 0, TRUE, array(
			'options' => $options,
			'toggles' => array(
				'0' => array(
					'new_shipping_address',
				),
			),
		) ) );
		$form->add( new \IPS\Helpers\Form\Address( 'new_shipping_address', NULL, FALSE, [], NULL, NULL, NULL, 'new_shipping_address' ) );
		
		if( $values = $form->values() ) {

			if( $values['shipping_address'] == 0 ) {
				$address = $values['new_shipping_address'];
			} else {
				$address = \IPS\nexus\Customer\Address::load( $values['shipping_address'] )->address;
			}
			
			if( !empty( $address->addressLines ) AND !empty( $address->city ) AND !empty( $address->country ) ) {
				
				// create internal invoice
				$currency = ( isset( \IPS\Request::i()->cookie['currency'] ) and \in_array( \IPS\Request::i()->cookie['currency'], \IPS\nexus\Money::currencies() ) ) ? \IPS\Request::i()->cookie['currency'] : \IPS\nexus\Customer::loggedIn()->defaultCurrency();
	
				$invoice = new \IPS\nexus\Invoice;
				$invoice->member = \IPS\nexus\Customer::loggedIn();
				$invoice->currency = $currency;

				$printfulItems = [];
				
				// add items to invoice and prepare for printful api
				foreach( $_SESSION['printful_cart'] as $i => $item ) {
					$item->paymentMethodIds = \IPS\Settings::i()->printful_methods;
					$invoice->addItem( $item, $i );

					$printfulItems[] = [
						'sync_variant_id' => $item->extra['variant_id'],
						'variant_id' => \IPS\printfulintegration\Api::i()->variantId($item->extra['variant_id']),
						'quantity' => $item->quantity,
						'retail_price' => $item->price->amount,
					];
				}

				$recipient = array(
					'name' => \IPS\nexus\Customer::loggedIn()->cm_name,
					'address1' => implode(',', $address->addressLines),
					'city' => $address->city,
					'country_code' => $address->country,
					'state_code' => empty(\IPS\printfulintegration\Api::$countries[ $address->country ]['states']) ? NULL : \IPS\printfulintegration\Api::$countries[ $address->country ]['states'][ $address->region ],
					'zip' => empty( $address->postalCode ) ? NULL : $address->postalCode,
				);

				$shippingRate = \IPS\printfulintegration\Api::i()->shippingRate($recipient, $printfulItems);

				$_SESSION['printful_doShipping'] = array( 'invoice' => $invoice, 'shippingRate' => $shippingRate, 'printfulItems' => $printfulItems, 'recipient' => $recipient );

				if( !\IPS\Request::i()->isAjax() ) {
					\IPS\Output::i()->redirect( \IPS\Http\Url::internal('app=printfulintegration&module=store&controller=cart&do=shipping')->csrf() );
				}

			}
		}

		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_cart.js', 'printfulintegration', 'front' ) );
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('choose_shipping_address');
		\IPS\Output::i()->breadcrumb = array_merge(
			\IPS\Output::i()->breadcrumb,
			array(
				'module' =>  array(
					\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=store', 'front', 'merch_store'),
					\IPS\Member::loggedIn()->language()->addToStack('frontnavigation_printfulintegration')
				),
				array(
					\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=cart', 'front', 'merch_cart'),
					\IPS\Member::loggedIn()->language()->addToStack('printful_cart')
				),
				array(
					\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=cart&do=checkout', 'front', 'merch_cart'),
					\IPS\Member::loggedIn()->language()->addToStack('choose_shipping_address')
				)
			)
		);
		\IPS\Output::i()->output .= $form->customTemplate( array( \IPS\Theme::i()->getTemplate( 'checkout', 'printfulintegration', 'front' ), 'shippingAddress' ) );
	}

	public function shipping() {

		try {
			if( !\IPS\Member::loggedIn()->member_id ) {
				\IPS\Output::i()->error('no_module_permission_guest', '2P104/2', 403);
			}

			\IPS\Session::i()->csrfCheck();
			
			if( empty( $_SESSION['printful_doShipping'] ) ) {
				\IPS\Output::i()->error('printful_address_error', '2P104/3', 400);
			}
			
			$data = $_SESSION['printful_doShipping'];
			$invoice = $data['invoice'];
			$shippingRate = $data['shippingRate'];
			$printfulItems = $data['printfulItems'];
			$recipient = $data['recipient'];
			$currency = $invoice->currency;
			$conversionRates = \IPS\printfulintegration\Application::conversionRates( TRUE );

			$form = new \IPS\Helpers\Form('shipping_method', 'continue');

			$options = array();

			foreach( $shippingRate as $method ) {

				$price = new \IPS\Math\Number((string) $method['rate']);
				if( $currency !== \IPS\Settings::i()->printful_default_currency ) {
					$price = $price->multiply( new \IPS\Math\Number( number_format( $conversionRates[ $currency ], 4, '.', '' ) ) );
				}

				if (\IPS\Settings::i()->printful_tax !== 0 )
				{
					try
					{
						$tax = \IPS\nexus\Tax::load( \IPS\Settings::i()->printful_tax );
						$rate = new \IPS\Math\Number( $tax->rate( \IPS\nexus\Customer::loggedIn()->estimatedLocation() ) );
						$price = $price->add( $price->multiply( $rate ) );
					}
					catch ( \OutOfRangeException $e ) { }
				}

				$price = $price->round(2, 2);

				$options[ $method['id'] ] = \IPS\Member::loggedIn()->language()->addToStack('shipping_method_option', FALSE, array( 
					'sprintf' => array( new \IPS\nexus\Money($price, $currency), $method['name'] )
				));
			}
			
			$form->add( new \IPS\Helpers\Form\Radio( 'choose_shipping_method', 'STANDARD', TRUE, array(
				'options' => $options,
			) ) );

			if($values = $form->values()) {
				$index = array_search( $values['choose_shipping_method'], array_column( $shippingRate, 'id' ) );

				$price = new \IPS\Math\Number((string) $shippingRate[ $index ]['rate']);
				if( $currency !== \IPS\Settings::i()->printful_default_currency ) {
					$price = $price->multiply( new \IPS\Math\Number( number_format( $conversionRates[ $currency ], 4, '.', '' ) ) );
				}

				if (\IPS\Settings::i()->printful_tax !== 0 )
				{
					try
					{
						$tax = \IPS\nexus\Tax::load( \IPS\Settings::i()->printful_tax );
						$rate = new \IPS\Math\Number( $tax->rate( \IPS\nexus\Customer::loggedIn()->estimatedLocation() ) );
						$price = $price->add( $price->multiply( $rate ) );
					}
					catch ( \OutOfRangeException $e ) { }
				}
				
				$price = $price->round(2, 2);

				$shipping = new \IPS\printfulintegration\extensions\nexus\Item\PrintfulProduct( 'Shipping: ' . $shippingRate[ $index ]['name'], new \IPS\nexus\Money($price, $currency) );
				$shipping->extra = array(
					'image' => "",
					'shipping' => TRUE,
				);
				$shipping->paymentMethodIds = \IPS\Settings::i()->printful_methods;
				
				$invoice->addItem($shipping, 'shipping');
				$invoice->save();

				$printfulOrder = \IPS\printfulintegration\Api::i()->createOrder($recipient, $printfulItems, $invoice->id, $values['choose_shipping_method'], $invoice->currency);

				// In case someone spams the save button
				try {
					\IPS\Db::i()->insert('printfulintegration_invoices', array(
						'printful_order_id' => $printfulOrder['id'],
						'invoice_id' => $invoice->id,
						'printful_order_total' => $printfulOrder['costs']['total'],
					));
				} catch( \Exception $e ) {}
				
					
				unset( $_SESSION['printful_doShipping'], $_SESSION['printful_cart'] );

				\IPS\Output::i()->redirect( $invoice->checkoutUrl() );
			}
			
			\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('choose_shipping_method');
			\IPS\Output::i()->breadcrumb = array_merge(
				\IPS\Output::i()->breadcrumb,
				array(
					'module' =>  array(
						\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=store', 'front', 'merch_store'),
						\IPS\Member::loggedIn()->language()->addToStack('frontnavigation_printfulintegration')
					),
					array(
						\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=cart', 'front', 'merch_cart'),
						\IPS\Member::loggedIn()->language()->addToStack('printful_cart')
					),
					array(
						\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=cart&do=checkout', 'front', 'merch_cart'),
						\IPS\Member::loggedIn()->language()->addToStack('choose_shipping_address')
					),
					array(
						\IPS\Http\Url::internal('app=printfulintegration&module=store&controller=cart&do=shipping', 'front', 'merch_cart'),
						\IPS\Member::loggedIn()->language()->addToStack('choose_shipping_method')
					)
				)
			);
			\IPS\Output::i()->output .= $form->customTemplate( array( \IPS\Theme::i()->getTemplate( 'forms', 'core' ), 'popupTemplate' ) );
			
	} catch( \Exception $e ) {
		\IPS\Log::debug( $e );
	}
	}

}