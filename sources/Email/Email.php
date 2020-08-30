<?php

namespace IPS\printfulintegration;

if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

class _Email extends \IPS\Node\Model {
    
    protected static $multitons = array();

    public static $nodeTitle = "printful_email_campaign";

    public static $databaseTable = "printfulintegration_emails";

    public static $databasePrefix = "pe_";

    public static $databaseColumnOrder = "order";

    public static $nodeSortable = FALSE;

    public static $variablesMap = array(
        'global' => array(
            'member' => array(
                'id',
                'name',
                'joined',
                'url',
                'last_visit',
                'posts',
            ),
            'suite' => array(
                'name',
                'url',
                'members',
                'posts',
                'most_active',
                'most_active_date',
            ),
            'merch' => array(
                'url'
            )
        ),
        'order' => array(
            'order' => array(
                'id',
                'url',
                'total',
            ),
            'member' => array(
                'total_expenses',
            )
        ),
        'interval' => array(
            'merch' => array(
                'new_products'
            )
        )
    );

    public function getTemplateVariables( $type, $forEditor = FALSE ) {

        $return = static::$variablesMap[ $type ];

        foreach( static::$variablesMap['global'] as $key => $keyValue ) {
            
            if( isset( $return[ $key ] ) ) {

                $return[ $key ] = array_merge( static::$variablesMap['global'][ $key ], $return[ $key ] );
            } else {
                $return[ $key ] = static::$variablesMap['global'][ $key ];
            }
        }

        if( $forEditor ) {

            $editorReturn = array();
            foreach( $return as $key => $values ) {

                foreach( $values as $value ) {

                    $editorReturn["{{$key}.{$value}}"] = \IPS\Member::loggedIn()->language()->get("pe_var_{$key}_{$value}");
                }
            }

            return $editorReturn;
        }

        return $return;
    }

    public static function emailData( $id, $memberObj, $orderId = NULL ) {

        try {

            $emailTemplate = static::load( $id );

            $merch = array(
                'url' => \IPS\Http\Url::internal( "app=printfulintegration&module=store&controller=store", "front", "merch_store" )
            );

            if( $emailTemplate->type === 'intv' ) {
                $merch['new_products'] = \IPS\Theme::i()->getTemplate('store', 'printfulintegration', 'front');
            }
            
            $mostOnline = json_decode( \IPS\Settings::i()->most_online, TRUE );
            $count = \IPS\Session\Store::i()->getOnlineUsers( \IPS\Session\Store::ONLINE_GUESTS | \IPS\Session\Store::ONLINE_MEMBERS | \IPS\Session\Store::ONLINE_COUNT_ONLY );
            if( $count > $mostOnline['count'] )
            {
                $mostOnline = array( 'count' => $count, 'time' => time() );
                \IPS\Settings::i()->changeValues( array( 'most_online' => json_encode( $mostOnline ) ) );
            }
            
            $suite = array(
                'url' => \IPS\Settings::i()->base_url,
                'name' => \IPS\Settings::i()->board_name,
                'members' => \IPS\Db::i()->select( 'COUNT(*)', 'core_members', array( 'completed=?', true ) )->first(),
                'most_active' => $mostOnline['count'],
                'most_active_date' => $mostOnline['time'],
                'posts' => \IPS\Db::i()->select('SUM(`member_posts`)', 'core_members')->first(),
            );

            $member = array(
                'url' => $memberObj->url(),

            );

            foreach( array( 'name', 'joined', 'last_visit' ) as $key ) {
                $member[ $key ] = $memberObj->{$key};
            }

            foreach( array( 'id', 'posts' ) as $key ) {
                $objKey = "member_{$key}";
                $member[ $key ] = $memberObj->{$objKey};
            }

            if( $emailTemplate->type = 'ordr' ) {
            
                $expenses = "";

                foreach( \IPS\Db::i()->select('i_currency, SUM(`n`.`i_total`) AS total', array('nexus_invoices', 'n'), array('`p`.`printful_order_id` IS NOT NULL AND `n`.`i_member`=?', $memberObj->member_id), 'total', NULL, 'n.i_currency')->join( array('printfulintegration_invoices', 'p'), 'p.invoice_id=n.i_id' ) as $row ) {
                    $expenses .= (string) ( new \IPS\nexus\Money( round( $row['total'], 2 ), $row['i_currency'] ) ) . ', ';
                }

                $member['total_expenses'] = \substr($expenses, 0, -2);
            }

            $orderData = \IPS\Db::i()->select('*', 'printfulintegration_invoices', ["id=?", $orderId])->first();
            $invoice = \IPS\nexus\Invoice::load( $orderData['invoice_id'] );

            $order = array(
                'id' => $orderId,
                'url' => $invoice->url(),
                'total' => $invoice->total
            );
            
            return array(
                'title' => $emailTemplate->title,
                'content' => \preg_replace_callback( "/\{(?<object>suite|merch|member|order)\.(?<key>[a-z_]*)\}/i", function($matches) use ( $member, $suite, $order, $merch ) {
                    return ${$matches['object']}[ $matches['key'] ];
                }, $emailTemplate->content )
            );

        } catch ( \Exception $e ) {
            \IPS\Log::log($e, 'printful_email_failed');
            return FALSE;
        }

    }

    public function url() {
        return \IPS\Http\Url::internal('app=printfulintegration&module=overview&controller=emails&do=edit&id' . $this->id, 'admin');
    }

    public function form( &$form ) {
        $form->addMessage('pe_opted_out', 'ipsMessage ipsMessage_info ipsSpacer_bottom ipsSpacer_bottom');

        $types = array(
            'ordr' => 'pe_order',
            'intv' => 'pe_interval',
        );

        $form->add( new \IPS\Helpers\Form\Text('pe_title', $this->title, TRUE, array(
            'maxLength' => 255
        ) ) );
        $form->add( new \IPS\Helpers\Form\Radio('pe_type', $this->type, TRUE, array(
            'options' => $types,
            'toggles' => array(
                'ordr' => array(
                    'pe_order_content',
                    'pe_order_categories'
                ),
                'intv' => array(
                    'pe_interval_content',
                    'pe_interval_categories',
                    'pe_interval'
                )
            )
        ) ) );
        $form->add( new \IPS\Helpers\Form\Editor('pe_order_content', ( $this->type == 'ordr' ) ? $this->content : NULL, TRUE, array(
            'app' => "printfulintegration",
            'key' => "EmailContent",
            'autoSaveKey' => ( $this->id ) ? "pe_content_{$this->id}_ordr" : "pe_content_new_ordr",
            'attachIds' => array( ( $this->id ?: 'new' ) ),
            'tags' => static::getTemplateVariables('order', TRUE),
        ), NULL, NULL, NULL, 'pe_order_content' ) );
        $form->add( new \IPS\Helpers\Form\Editor('pe_interval_content', ( $this->type == 'intv' ) ? $this->content : NULL, TRUE, array(
            'app' => "printfulintegration",
            'key' => "EmailContent",
            'autoSaveKey' => ( $this->id ) ? "pe_content_{$this->id}_intv" : "pe_content_new_intv",
            'attachIds' => array( ( $this->id ?: 'new' ) ),
            'tags' => static::getTemplateVariables('interval', TRUE),
        ), NULL, NULL, NULL, 'pe_interval_content' ) );
        $form->add( new \IPS\Helpers\Form\Interval('pe_interval', $this->interval, TRUE, array(), NULL, NULL, NULL, 'pe_interval' ) );
        $form->add( new \IPS\Helpers\Form\Select( 'pe_groups', $this->groups ?: '*', TRUE, array( 
            'options' => \IPS\Member\Group::groups(), 
            'parse' => 'normal',
            'multiple' => TRUE,
            'unlimited' => "*",
            'unlimitedLang' => "all"
        ) ) );
    }

    public function formatFormValues( $values ) {

        $values['pe_content'] = ( $values['pe_type'] == 'ordr' ) ? $values['pe_order_content'] : $values['pe_interval_content'];
        unset( $values['pe_order_content'] );
        unset( $values['pe_interval_content'] );

        return $values;
    }

    public function get__title() {
        return $this->title;
    }
}