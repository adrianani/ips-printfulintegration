<?php


namespace IPS\printfulintegration\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * dashboard
 */
class _dashboard extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'dashboard_manage' );
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{	
		$stmtToStats = array( 'IPS\printfulintegration\Application', 'stmtToStats' );
		// Daily data
		$today = $stmtToStats(
			\IPS\Db::i()->select('n.i_currency AS currency, COUNT(*) AS orders, SUM(n.i_total) AS total', array('nexus_invoices', 'n'), "n.i_status='paid' AND DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d') = CURDATE() AND p.printful_order_id IS NOT NULL", NULL, NULL, 'n.i_currency')
					->join( array('printfulintegration_invoices', 'p'), 'n.i_id=p.invoice_id')
		);

		$yesterday = $stmtToStats(
			\IPS\Db::i()->select('n.i_currency AS currency, SUM(n.i_total) AS total', array('nexus_invoices', 'n'), "n.i_status='paid' AND DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d') = SUBDATE(CURDATE(), INTERVAL 1 DAY) AND p.printful_order_id IS NOT NULL", NULL, NULL, 'n.i_currency')
						->join( array('printfulintegration_invoices', 'p'), 'n.i_id=p.invoice_id')
		);

		// Weekly data
		$week = $stmtToStats(
			\IPS\Db::i()->select('n.i_currency AS currency, COUNT(*) AS orders, SUM(n.i_total) AS total', array('nexus_invoices', 'n'), "n.i_status='paid' AND p.printful_order_id IS NOT NULL AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d'), CURDATE()) <= 0 AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d'), CURDATE()) > -7", NULL, NULL, 'n.i_currency')
					->join( array('printfulintegration_invoices', 'p'), 'n.i_id=p.invoice_id')
		);

		$twoWeeks = $stmtToStats(
			\IPS\Db::i()->select('n.i_currency AS currency, SUM(n.i_total) AS total', array('nexus_invoices', 'n'), "n.i_status='paid' AND p.printful_order_id IS NOT NULL AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d'), CURDATE()) <= -7 AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d'), CURDATE()) > -14", NULL, NULL, 'n.i_currency')
						->join( array('printfulintegration_invoices', 'p'), 'n.i_id=p.invoice_id')
		);

		// Monthly data
		$month = $stmtToStats(
			\IPS\Db::i()->select('n.i_currency AS currency, COUNT(*) AS orders, SUM(n.i_total) AS total, SUM(p.printful_order_total) AS paid', array('nexus_invoices', 'n'), "n.i_status='paid' AND p.printful_order_id IS NOT NULL AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d'), CURDATE()) <= 0 AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d'), CURDATE()) > -30", NULL, NULL, 'n.i_currency')
					->join( array('printfulintegration_invoices', 'p'), 'n.i_id=p.invoice_id')
		);

		$twoMonths = $stmtToStats(
			\IPS\Db::i()->select('n.i_currency AS currency, SUM(n.i_total) AS total, SUM(p.printful_order_total) AS paid', array('nexus_invoices', 'n'), "n.i_status='paid' AND p.printful_order_id IS NOT NULL AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d'), CURDATE()) <= -30 AND DATEDIFF(DATE_FORMAT(FROM_UNIXTIME(n.i_date), '%Y-%m-%d'), CURDATE()) > -60", NULL, NULL, 'n.i_currency')
					->join( array('printfulintegration_invoices', 'p'), 'n.i_id=p.invoice_id')
		);
		
		// income chart
		$chart = new \IPS\Helpers\Chart\Database(
			\IPS\Http\Url::internal('app=printfulintegration&module=overview&controller=dashboard&do=chart'),
			'printfulintegration_invoices',
			'i_date',
			'',
			[
				'isStacked' => TRUE,
				'backgroundColor' 	=> '#ffffff',
				'colors'			=> array( '#10967e', '#ea7963', '#de6470', '#6b9dde', '#b09be4', '#eec766', '#9fc973', '#e291bf', '#55c1a6', '#5fb9da' ),
				'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
				'lineWidth'			=> 1,
				'areaOpacity'		=> 0.4
			],
			'AreaChart',
			'monthly'
		);

		$chart->joins = array( array( 'nexus_invoices', 'nexus_invoices.i_id=printfulintegration_invoices.invoice_id' ) );

		$chart->addSeries(
			'EUR',
			'number',
			'SUM(i_total)',
			FALSE
		);

		$chart->where[] = array( "i_status='paid' AND printful_order_id IS NOT NULL" );

		$chart->availableTypes = [ 'AreaChart', 'ColumnChart', 'BarChart' ];

		// invoices
		
		$url = \IPS\Http\Url::internal( 'app=printfulintegration&module=overview&controller=dashboard' );
		$where = array();

		$table = $table = new \IPS\Helpers\Table\Db( 'printfulintegration_invoices', $url, $where );
		$table->include = array(
			'i_status',
			'i_id',
			'i_title',
			'i_member',
			'i_total',
			'printful_order_total',
			'printful_order_id',
			'profit'
		);

		$table->parsers = array(
			'printful_order_id' => function($val, $row) {
				return "<a href='" . \IPS\Http\Url::external('https://www.printful.com/dashboard?order_id=' . $val ) . "' rel='nofollow' target='_blank'>" . \IPS\Member::loggedIn()->language()->addToStack('printful_check_order') . "</a>";
			},
			'i_status'	=> function( $val ) {
				return \IPS\Theme::i()->getTemplate( 'invoices', 'nexus' )->status( $val );
			},

			'profit' => function($val, $row) {
				return (string) new \IPS\nexus\Money( $row['i_total'] - $row['printful_order_total'], $row['i_currency'] );
			},
			'i_total' => function($val, $row) {
				return (string) new \IPS\nexus\Money( $val, $row['i_currency'] );
			},
			'printful_order_total' => function($val, $row) {
				return (string) new \IPS\nexus\Money( $val, $row['i_currency'] );
			},
			'i_member'	=> function ( $val, $row )
			{
				return \IPS\Theme::i()->getTemplate( 'global', 'nexus' )->userLink( \IPS\Member::load( $val ) );
			},
		);

		$table->joins[] = array(
			'select' => 'i_id, i_status, i_title, i_member, i_total, i_date, i_currency',
			'from' => "nexus_invoices",
			'where' => "nexus_invoices.i_id=printfulintegration_invoices.invoice_id"
		);

		$table->rowButtons = function($row) {
			return array(
				'view'	=> array(
					'icon'	=> 'search',
					'title'	=> 'invoice_view',
					'link'	=> \IPS\Http\Url::internal( "app=nexus&module=payments&controller=invoices&do=view&id={$row['i_id']}" )
				)
			);
		};

		// output

		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'dashboard.css', 'printfulintegration' ) );

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__printfulintegration_overview_dashboard');
		\IPS\Output::i()->output .= \IPS\Theme::i()->getTemplate('dashboard')->dashboard(
			array(
				(object) array(
					'total' => new \IPS\nexus\Money($today['total'], \IPS\Settings::i()->printful_default_currency),
					'title' => \IPS\Member::loggedIn()->language()->addToStack('printful_dashboard_daily', FALSE, array('pluralize' => [$today['orders']])),
					'progress' => $today['total']->subtract( $yesterday['total'] )->compare( new \IPS\Math\Number('0') ) == 1
				),
				(object) array(
					'total' => new \IPS\nexus\Money($week['total'], \IPS\Settings::i()->printful_default_currency),
					'title' => \IPS\Member::loggedIn()->language()->addToStack('printful_dashboard_weekly', FALSE, array('pluralize' => [$week['orders']])),
					'progress' => $week['total']->subtract( $twoWeeks['total'] )->compare( new \IPS\Math\Number('0') ) == 1
				),
				(object) array(
					'total' => new \IPS\nexus\Money($month['total'], \IPS\Settings::i()->printful_default_currency),
					'title' => \IPS\Member::loggedIn()->language()->addToStack('printful_dashboard_monthly', FALSE, array('pluralize' => [$month['orders']])),
					'progress' => $month['total']->subtract( $twoMonths['total'] )->compare( new \IPS\Math\Number('0') ) == 1
				),
				(object) array(
					'total' => new \IPS\nexus\Money($month['total']->subtract( new \IPS\Math\Number( (string) $month['paid'] ) ), \IPS\Settings::i()->printful_default_currency),
					'title' => \IPS\Member::loggedIn()->language()->addToStack('printful_dashboard_profit'),
					'progress' => $month['total']->subtract( new \IPS\Math\Number( (string) $month['paid'] ) )->compare( $twoMonths['total']->subtract( new \IPS\Math\Number( (string) $twoMonths['paid'] ) ) ) == 1
				)
			),
			$chart,
			$table
		);
	}

	protected function chart() {
		
		$chart = new \IPS\Helpers\Chart\Database(
			\IPS\Http\Url::internal('app=printfulintegration&module=overview&controller=dashboard&do=chart'),
			'printfulintegration_invoices',
			'i_date',
			'',
			[
				'isStacked' => TRUE,
				'backgroundColor' 	=> '#ffffff',
				'colors'			=> array( '#10967e', '#ea7963', '#de6470', '#6b9dde', '#b09be4', '#eec766', '#9fc973', '#e291bf', '#55c1a6', '#5fb9da' ),
				'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
				'lineWidth'			=> 1,
				'areaOpacity'		=> 0.4
			],
			'AreaChart',
			'daily'
		);

		$chart->joins = array( array( 'nexus_invoices', 'nexus_invoices.i_id=printfulintegration_invoices.invoice_id' ) );

		$chart->addSeries(
			'EUR',
			'number',
			'SUM(i_total)',
			FALSE
		);

		$chart->where[] = array( "i_status='paid' AND printful_order_id IS NOT NULL" );

		$chart->availableTypes = [ 'AreaChart', 'ColumnChart', 'BarChart' ];

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('printful_income_overview');
		\IPS\Output::i()->output .= $chart;
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}