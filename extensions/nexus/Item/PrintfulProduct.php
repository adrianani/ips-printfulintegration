<?php
/**
 * @brief		PrintfulProduct
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Printful Commerce Integration
 * @since		19 Jul 2020
 */

namespace IPS\printfulintegration\extensions\nexus\Item;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * PrintfulProduct
 */
class _PrintfulProduct extends \IPS\nexus\Invoice\Item\Purchase
{
	/**
	 * @brief	Application
	 */
	public static $application = 'printfulintegration';
	
	/**
	 * @brief	Application
	 */
	public static $type = 'printfulItem';
	
	/**
	 * @brief	Icon
	 */
	public static $icon = 'shopping-bag';
	
	/**
	 * @brief	Title
	 */
	public static $title = 'printful_item';
	
	/**
	 * Generate Invoice Form
	 *
	 * @param	\IPS\Helpers\Form	$form		The form
	 * @param	\IPS\nexus\Invoice	$invoice	The invoice
	 * @return	void
	 */
	public static function form( \IPS\Helpers\Form $form, \IPS\nexus\Invoice $invoice )
	{
		
	}
	
	/**
	 * Create From Form
	 *
	 * @param	array				$values	Values from form
	 * @param	\IPS\nexus\Invoice	$invoice	The invoice
	 * @return	\IPS\nexus\extensions\nexus\Item\MiscellaneousCharge
	 */
	public static function createFromForm( array $values, \IPS\nexus\Invoice $invoice )
	{		
		
	}
	
	/**
	 * On Paid
	 *
	 * @param	\IPS\nexus\Invoice	$invoice	The invoice
	 * @return	void
	 */
	public function onPaid( \IPS\nexus\Invoice $invoice )
	{
		
	}

	public function image() {
		try {
			try {
				$url = \IPS\Db::i()->select('image_location', 'printfulintegration_product_images', ['product_id=? AND variant_color=?', $this->id, $this->extra['color']], NULL, [0, 1])->first();
			} catch( \UnderflowException $e ) {
				$url = \IPS\Db::i()->select('image_location', 'printfulintegration_product_images', ['product_id=?', $this->id], 'image_primary DESC', [0, 1])->first();
			}

			if( !$url ) {
				return NULL;
			}

			return \IPS\File::get('printfulintegration_ProductImage', $url);
			
		} catch( \Exception $e ) {}

		return NULL;
	}
	
	// Other actions available. See the class you are extending for more information
}