<?php


namespace IPS\printfulintegration\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * emails
 */
class _emails extends \IPS\Node\Controller
{

	public static $csrfProtected = TRUE;
	/**
	 * Node Class
	 */
	protected $nodeClass = '\IPS\printfulintegration\Email';
	
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'emails_manage' );
		parent::execute();
	}
}