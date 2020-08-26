//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class printfulintegration_hook_F_Core_global extends _HOOK_CLASS_
{

/* !Hook Data - DO NOT REMOVE */
public static function hookData() {
 return array_merge_recursive( array (
  'userBar' => 
  array (
    0 => 
    array (
      'selector' => '#elUserNav > li.cNotifications.cUserNav_icon',
      'type' => 'add_before',
      'content' => '{template="cartUserBar" app="printfulintegration" location="front" group="store" params=""}',
    ),
  ),
), parent::hookData() );
}
/* End Hook Data */


}
