<?php


namespace IPS\printfulintegration\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
	public static $csrfProtected = TRUE;

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'settings_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{

		$form = new \IPS\Helpers\Form;
		$form->addTab('printful_tab_api');
		$form->add( new \IPS\Helpers\Form\Text('printful_api_key', \IPS\Settings::i()->printful_api_key ) );
		$form->add( new \IPS\Helpers\Form\YesNo( 'printful_product_enabled_default', \IPS\Settings::i()->printful_product_enabled_default, FALSE ) );
		if( \IPS\printfulintegration\Api::i()->apiKey() ) {
			$storeCurrency = \IPS\Settings::i()->printful_default_currency;
			$form->addHeader( 'printful_packing_slip_data' );
			$form->add( new \IPS\Helpers\Form\Email('printful_ps_email', \IPS\Settings::i()->printful_ps_email ) );
			$form->add( new \IPS\Helpers\Form\Text('printful_ps_phone', \IPS\Settings::i()->printful_ps_phone) );
			$form->add( new \IPS\Helpers\Form\Text('printful_ps_message', \IPS\Settings::i()->printful_ps_message ) );
			$form->addTab('printful_tab_store');
			$form->add( new \IPS\Helpers\Form\Node( 'printful_methods', ( !\IPS\Settings::i()->printful_methods or \IPS\Settings::i()->printful_methods === '*' ) ? 0 : explode( ',', \IPS\Settings::i()->printful_methods ), TRUE, array( 'class' => 'IPS\nexus\Gateway', 'multiple' => TRUE, 'zeroVal' => 'any' ) ) );			
			$form->add( new \IPS\Helpers\Form\Node( 'p_tax', (int) \IPS\Settings::i()->printful_tax, FALSE, array( 'class' => 'IPS\nexus\Tax', 'zeroVal' => 'do_not_tax' ) ) );
			$form->add( new \IPS\Helpers\Form\YesNo( 'printful_price_exchange', \IPS\Settings::i()->printful_price_exchange, FALSE, array(
				'togglesOn' => array(
					'printful_use_exchange_api',
					'printful_rates',
				)
			) ) );
			$form->add( new \IPS\Helpers\Form\YesNo( 'printful_use_exchange_api', \IPS\Settings::i()->printful_use_exchange_api, FALSE, array(), NULL, NULL, NULL, 'printful_use_exchange_api') );

			$matrix = new \IPS\Helpers\Form\Matrix;
			$matrix->manageable = FALSE;
			$matrix->columns = array(
				'currency' => function( $key, $value, $data ) { return $data['currency']; },
				'printful_your_val' => function( $key, $value, $data ) {
					return new \IPS\Helpers\Form\Number( $key, $data['user_val'], FALSE, array(
                        'decimals' => 4,
                    ) );
				},
				'printful_api_val' => function( $key, $value, $data ) {
					return new \IPS\Helpers\Form\Number( $key, $data['api_val'], FALSE, array(
						'decimals' => 4,
						'disabled' => TRUE,
					) );
				}
			);

			$rows = array();

			foreach( array_filter( \IPS\nexus\Money::currencies(), function($val) { return $val !== \IPS\Settings::i()->printful_default_currency; } ) as $currency ) {
				if( empty(\IPS\Settings::i()->printful_conversion_rates) ) {
					\IPS\printfulintegration\Application::conversionRates(TRUE);
				}

				$apiVals = \IPS\Settings::i()->printful_conversion_rates;
				$userVals = \IPS\Settings::i()->printful_conversion_rates_custom;
				$rows[$currency] = array(
					'user_val' => !empty( $userVals[ $currency ] ) ? $userVals[ $currency ] : NULL,
					'api_val' => !empty( $apiVals[ $currency ] ) ? $apiVals[ $currency ] : NULL,
					'currency' => $currency,
				);
			}
			
			$matrix->rows = $rows;

			$form->addMatrix('printful_rates', $matrix);
		}

		if( $values = $form->values() ) {

			$refresh = FALSE;

			if( isset( $values['printful_methods'] ) && $values['printful_methods'] !== '*' ) {

				$values['printful_methods'] = array_map( function( $method ) {

					if( $method !== "*" ) {
						return $method->id;	
					}

					return NULL;
				}, $values['printful_methods']);
			}

			if( $values['printful_api_key'] !== \IPS\Settings::i()->printful_api_key ) {

				if( !empty($values['printful_api_key']) ) {
					\IPS\Settings::i()->changeValues([
						'printful_api_key' => $values['printful_api_key']
					]);

					$storeCurrency = \IPS\printfulintegration\Api::i()->store()['currency'];
					
					\IPS\Settings::i()->changeValues( array(
						'printful_default_currency' => $storeCurrency,
					) );

					unset( $values['printful_api_key'] );
				}
					
				$refresh = TRUE;
			}
			
			if(!empty($values['printful_api_key']) && \IPS\Settings::i()->printful_api_key && \IPS\printfulintegration\Api::i()->apiKey() ) {

				if( isset( $values['p_tax'] ) )
				{
					$values['printful_tax'] = $values['p_tax'] ? $values['p_tax']->id : 0;
				}

				if( isset( $values['printful_rates'] ) ) {
					$rates = array();

					foreach( $values['printful_rates'] as $currency => $rate ) {

						if( $rate['printful_your_val'] !== 0.0 ) {
							$rates[ $currency ] = $rate['printful_your_val'];
						}
					}

					$values['printful_conversion_rates_custom'] = $rates;

					unset( $values['printful_rates'] );
				}
			} else {

				foreach( $values as $key => $value ) {

					if( $key !== 'printful_api_key' ) {
						unset( $values[ $key ] );
					}
				}
			}

			unset( $values['p_tax'] );

			$form->saveAsSettings( $values );

			if( $refresh ) {
				\IPS\Output::i()->redirect( \IPS\Request::i()->url() );
			}
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__printfulintegration_overview_settings');
		\IPS\Output::i()->output .= $form;

		// Output
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}