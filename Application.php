<?php
/**
 * @brief		Printful Commerce Integration Application Class
 * @author		<a href='https://adrianani.github.com'>Adrian A.</a>
 * @copyright	(c) 2020 Adrian A.
 * @package		Invision Community
 * @subpackage	Printful Commerce Integration
 * @since		02 Jul 2020
 * @version		
 */
 
namespace IPS\printfulintegration;

/**
 * Printful Commerce Integration Application Class
 */
class _Application extends \IPS\Application
{
	protected function get__icon()
	{
		return 'shopping-bag';
	}

	public static function cartCount() {
		$count = 0;
		foreach( $_SESSION['printful_cart'] as $item ) {
			$count += $item->quantity;
		}

		return $count;
	}

	public static function conversionRates( $refresh = FALSE ) {
				
		if( $refresh ) {
			\IPS\Settings::i()->changeValues([
				'printful_conversion_rates' =>  \IPS\Http\Url::external('https://api.exchangeratesapi.io/latest')->setQueryString(array(
					'base' => \IPS\Settings::i()->printful_default_currency,
					'symbols' => implode(',', array_filter( \IPS\nexus\Money::currencies(), function($val) { return $val !== \IPS\Settings::i()->printful_default_currency; } ) )
				))->request()->get()->decodeJson()['rates']
			]);
		}

		if( \IPS\Settings::i()->printful_price_exchange ) {
			
			if( \IPS\Settings::i()->printful_use_exchange_api ) {
				return \IPS\Settings::i()->printful_conversion_rates;
			}

			return array_merge( \IPS\Settings::i()->printful_conversion_rates ?: [], \IPS\Settings::i()->printful_conversion_rates_custom ?: [] );
			
		}

		return FALSE;
	}

	public static function stmtToStats( $stmt ) {
		$conversionRates = \IPS\printfulintegration\Application::conversionRates();
		$total = new \IPS\Math\Number('0');
		$orders = 0;
		$paid = 0;

		foreach( $stmt as $data ) {

			$row = new \IPS\Math\Number( (string) $data['total'] );

			if( $data['currency'] != \IPS\Settings::i()->printful_default_currency ) {
				$row = $row->divide( new \IPS\Math\Number( number_format( $conversionRates[ $data['currency'] ], 4, '.', '' ) ) );
			}

			$total = $total->add( $row );

			if( isset($data['orders']) ) {
				$orders += $data['orders'];
			}

			if( isset($data['paid']) ) {
				$paid += $data['paid'];
			}
		} 

		return array(
			'total' => $total,
			'orders' => $orders,
			'paid' => $paid,
		);
	}

	public function defaultFrontNavigation(){
		return array(
			'rootTabs'		=> array(
				array(
					'key' => 'Merch',
					'children' => array(
						array(
							'key' => 'MerchStore',
						),
						array(
							'key' => 'MerchOrders',
						)
					)
				)
			),
			'browseTabs'	=> array(),
			'browseTabsEnd'	=> array(),
			'activityTabs'	=> array()
		);
	}
	
}