<?php
/**
 * @brief		Front Navigation Extension: MerchStore
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	Printful Commerce Integration
 * @since		22 Aug 2020
 */

namespace IPS\printfulintegration\extensions\core\FrontNavigation;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Front Navigation Extension: MerchStore
 */
class _MerchStore extends \IPS\core\FrontNavigation\FrontNavigationAbstract
{
	/**
	 * Get Type Title which will display in the AdminCP Menu Manager
	 *
	 * @return	string
	 */
	public static function typeTitle()
	{
		return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_printfulintegration_store');
	}
	
	/**
	 * Can this item be used at all?
	 * For example, if this will link to a particular feature which has been diabled, it should
	 * not be available, even if the user has permission
	 *
	 * @return	bool
	 */
	public static function isEnabled()
	{
		return TRUE;
	}
	
	/**
	 * Can the currently logged in user access the content this item links to?
	 *
	 * @return	bool
	 */
	public function canAccessContent()
	{
		return \IPS\Member::loggedIn()->canAccessModule( \IPS\Application\Module::get( 'printfulintegration', 'store' ) );
	}
	
	/**
	 * Get Title
	 *
	 * @return	string
	 */
	public function title()
	{
		return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_printfulintegration_store');
	}
	
	/**
	 * Get Link
	 *
	 * @return	\IPS\Http\Url
	 */
	public function link()
	{
		return \IPS\Http\Url::internal( "app=printfulintegration&module=store&controller=store", "front", "merch_store" );
	}
	
	/**
	 * Is Active?
	 *
	 * @return	bool
	 */
	public function active()
	{
		return \IPS\Dispatcher::i()->application->directory === 'printfulintegration' and \IPS\Dispatcher::i()->module and \IPS\Dispatcher::i()->module->key === 'store' and \in_array( \IPS\Dispatcher::i()->controller, array('store', 'product') );
	}

	/**
	 * Children
	 *
	 * @param	bool	$noStore	If true, will skip datastore and get from DB (used for ACP preview)
	 * @return	array
	 */
	public function children( $noStore=FALSE )
	{
		return NULL;
	}
}