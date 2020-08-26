//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class printfulintegration_hook_XF_Clients_Invoices extends _HOOK_CLASS_
{

	public function view() {
		\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'printful.invoice.core.app.js', 'printfulintegration', 'interface' ) );

		parent::view();
	}

}
