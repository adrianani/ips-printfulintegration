//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class printfulintegration_hook_N_Invoice extends _HOOK_CLASS_
{

	public function markPaid( \IPS\Member $member = NULL )
	{
		try
		{
			$return = parent::markPaid( $member );
	
			if( $pOrderId = \IPS\Db::i()->select('printful_order_id', 'printfulintegration_invoices', ['invoice_id=?', $this->id])->first() ) {
				\IPS\printfulintegration\Api::i()->confirmOrder($pOrderId);
			}
	
			return $return;
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

}
