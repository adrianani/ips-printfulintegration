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

				// send PM
				try {
					$pm = \IPS\printfulintegration\Email::constructFromData( \IPS\Db::i()->select('*', 'printfulintegration_email', ['( `pe_groups`=\'*\' OR JSON_CONTAINS(`pe_groups`, \'' . $this->member->member_group_id . '\') ) AND `pe_send_type`=1'])->first() );
					$pmData = $pm->emailData( $this->member, $this );
					// create conversation
					$pmSender = \IPS\Member::load( $pm->sender );
					$conversation = \IPS\core\Messenger\Conversation::createItem( $pmSender, $pmSender->ip_address, \IPS\DateTime::ts( time() ) );
					$conversation->title = $pmData['title'];
					$conversation->to_member_id = $this->member->member_id;
					$conversation->save();

					$message = \IPS\core\Messenger\Message::create( $conversation, $pmData['content'], TRUE, NULL, NULL, $pmSender );
					$conversation->first_msg_id = $message->id;
					$conversation->save();

					$conversation->authorize( $this->member );
					$conversation->authorize( $pmSender );

					// send notification for the conversation
					$notification = new \IPS\Notification( \IPS\Application::load('core'), 'private_message_added', $conversation, array( $conversation, $pmSender ) );
					$notification->send();

					$this->member->msg_count_reset = time() - 1;
					\IPS\core\Messenger\Conversation::rebuildMessageCounts( $this->member );

				} catch ( \UnderflowException $e ) {

				} catch ( \Exception $e ) {
					\IPS\Log::log( $e, 'thanks_pm_failed' );
				}

				// send email
				if( $this->member->allow_admin_mails )
				{
					try {

						$email = \IPS\printfulintegration\Email::constructFromData( \IPS\Db::i()->select('*', 'printfulintegration_email', ['( `pe_groups`=\'*\' OR JSON_CONTAINS(`pe_groups`, \'' . $this->member->member_group_id . '\') ) AND `pe_send_type`=0'])->first() );
						$emailData = $email->emailData( $this->member, $this );
						
						$toSend = \IPS\Email::buildFromTemplate( 'printfulintegration', 'thanksEmail', array( $emailData['title'], $emailData['content'] ) );

						$toSend->from = $email->sender;

						$toSend->send( $this->member );

						\IPS\Log::log('made it here');

					} catch ( \UnderflowException $e ) {
						
					} catch ( \Exception $e ) {
						\IPS\Log::log( $e, 'thanks_email_failed' );
					}
				}
			
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
