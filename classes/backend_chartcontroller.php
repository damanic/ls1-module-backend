<?

	abstract class Backend_ChartController extends Backend_ReportingController {
		const rt_stacked_column = 'stacked_column';
		const rt_column = 'column';
		const rt_line = 'line';
		const rt_pie = 'pie';

		protected $timeUnits = array(
			'day'   => 'day',
			'week'  => 'week',
			'month' => 'month',
			'year'  => 'year'
		);

		protected $amountTypes = array(
			'revenue'  => 'Revenue',
			'totals'   => 'Totals',
			'tax'      => 'Tax',
			'shipping' => 'Shipping',
		);

		protected $orderStatuses = array(
			'all'  => 'All orders',
			'paid' => 'Paid only',
		);

		protected $chartColors = array(
			'#0D8ECF', '#FCD202', '#B0DE09', '#FF6600', '#2A0CD0', '#CD0D74', '#CC0000', '#00CC00', '#0000CC',
			"#63b598", "#ce7d78", "#ea9e70", "#a48a9e", "#c6e1e8", "#648177" ,"#0d5ac1" ,
			"#f205e6" ,"#1c0365" ,"#14a9ad" ,"#4ca2f9" ,"#a4e43f" ,"#d298e2" ,"#6119d0",
			"#d2737d" ,"#c0a43c" ,"#f2510e" ,"#651be6" ,"#79806e" ,"#61da5e" ,"#cd2f00" ,
			"#9348af" ,"#01ac53" ,"#c5a4fb" ,"#996635","#b11573" ,"#4bb473" ,"#75d89e" ,
			"#2f3f94" ,"#2f7b99" ,"#da967d" ,"#34891f" ,"#b0d87b" ,"#ca4751" ,"#7e50a8" ,
			"#c4d647" ,"#e0eeb8" ,"#11dec1" ,"#289812" ,"#566ca0" ,"#ffdbe1" ,"#2f1179" ,
			"#935b6d" ,"#916988" ,"#513d98" ,"#aead3a", "#9e6d71", "#4b5bdc", "#0cd36d",
			"#250662", "#cb5bea", "#228916", "#ac3e1b", "#df514a", "#539397", "#880977",
			"#f697c1", "#ba96ce", "#679c9d", "#c6c42c", "#5d2c52", "#48b41b", "#e1cf3b",
			"#5be4f0", "#57c4d8", "#a4d17a", "#225b8", "#be608b", "#96b00c", "#088baf",
			"#f158bf", "#e145ba", "#ee91e3", "#05d371", "#5426e0", "#4834d0", "#802234",
			"#6749e8", "#0971f0", "#8fb413", "#b2b4f0", "#c3c89d", "#c9a941", "#41d158",
			"#fb21a3", "#51aed9", "#5bb32d", "#807fb", "#21538e", "#89d534", "#d36647",
			"#7fb411", "#0023b8", "#3b8c2a", "#986b53", "#f50422", "#983f7a", "#ea24a3",
			"#79352c", "#521250", "#c79ed2", "#d6dd92", "#e33e52", "#b2be57", "#fa06ec",
			"#1bb699", "#6b2e5f", "#64820f", "#1c271", "#21538e", "#89d534", "#d36647",
			"#7fb411", "#0023b8", "#3b8c2a", "#986b53", "#f50422", "#983f7a", "#ea24a3",
			"#79352c", "#521250", "#c79ed2", "#d6dd92", "#e33e52", "#b2be57", "#fa06ec",
			"#1bb699", "#6b2e5f", "#64820f", "#1c271", "#9cb64a", "#996c48", "#9ab9b7",
			"#06e052", "#e3a481", "#0eb621", "#fc458e", "#b2db15", "#aa226d", "#792ed8",
			"#73872a", "#520d3a", "#cefcb8", "#a5b3d9", "#7d1d85", "#c4fd57", "#f1ae16",
			"#8fe22a", "#ef6e3c", "#243eeb", "#1dc18", "#dd93fd", "#3f8473", "#e7dbce",
			"#421f79", "#7a3d93", "#635f6d", "#93f2d7", "#9b5c2a", "#15b9ee", "#0f5997",
			"#409188", "#911e20", "#1350ce", "#10e5b1", "#fff4d7", "#cb2582", "#ce00be",
			"#32d5d6", "#17232", "#608572", "#c79bc2", "#00f87c", "#77772a", "#6995ba",
			"#fc6b57", "#f07815", "#8fd883", "#060e27", "#96e591", "#21d52e", "#d00043",
			"#b47162", "#1ec227", "#4f0f6f", "#1d1d58", "#947002", "#bde052", "#e08c56",
			"#28fcfd", "#bb09b", "#36486a", "#d02e29", "#1ae6db", "#3e464c", "#a84a8f",
			"#911e7e", "#3f16d9", "#0f525f", "#ac7c0a", "#b4c086", "#c9d730", "#30cc49",
			"#3d6751", "#fb4c03", "#640fc1", "#62c03e", "#d3493a", "#88aa0b", "#406df9",
			"#615af0", "#4be47", "#2a3434", "#4a543f", "#79bca0", "#a8b8d4", "#00efd4",
			"#7ad236", "#7260d8", "#1deaa7", "#06f43a", "#823c59", "#e3d94c", "#dc1c06",
			"#f53b2a", "#b46238", "#2dfff6", "#a82b89", "#1a8011", "#436a9f", "#1a806a",
			"#4cf09d", "#c188a2", "#67eb4b", "#b308d3", "#fc7e41", "#af3101", "#ff065",
			"#71b1f4", "#a2f8a5", "#e23dd0", "#d3486d", "#00f7f9", "#474893", "#3cec35",
			"#1c65cb", "#5d1d0c", "#2d7d2a", "#ff3420", "#5cdd87", "#a259a4", "#e4ac44",
			"#1bede6", "#8798a4", "#d7790f", "#b2c24f", "#de73c2", "#d70a9c", "#25b67",
			"#88e9b8", "#c2b0e2", "#86e98f", "#ae90e2", "#1a806b", "#436a9e", "#0ec0ff",
			"#f812b3", "#b17fc9", "#8d6c2f", "#d3277a", "#2ca1ae", "#9685eb", "#8a96c6",
			"#dba2e6", "#76fc1b", "#608fa4", "#20f6ba", "#07d7f6", "#dce77a", "#77ecca"
		);

		protected $timeline_charts = array();

		protected $chart_types = array();
		protected $maxChartValue = 0;

		protected static $colorStates = array();

		protected $paid_order_status_id = null;

		public function __construct() {
			parent::__construct();
			$this->layout = PATH_APP . '/modules/backend/layouts/chart_report.htm';
		}

		/*
		 * Time units
		 */

		protected function getTimeUnit() {
			$result = Db_UserParameters::get( 'report_time_unit_' . get_class( $this ), null, 'day' );
			if ( !strlen( $result ) ) {
				return 'day';
			}

			return $result;
		}

		protected function index_onSetTimeUnit() {
			Db_UserParameters::set( 'report_time_unit_' . get_class( $this ), post( 'time_unit' ) );
		}

		protected function index_onUpdateChart() {
			$this->renderPartial( PATH_APP . '/modules/backend/controllers/partials/_chart.htm', null, true, true );
		}

		/*
		 * Amount type selector
		 */

		protected function index_onSetAmountType() {
			Db_UserParameters::set( 'report_amount_type_' . get_class( $this ), post( 'type_id' ) );
		}

		protected function getAmountType() {
			return Db_UserParameters::get( 'report_amount_type_' . get_class( $this ), null, 'revenue' );
		}

		protected function getOrderAmountField() {
			$amountType = $this->getAmountType();

			switch ( $amountType ) {
				case 'revenue' :
					return '(shop_orders.total - shop_orders.goods_tax - shop_orders.shipping_tax - shop_orders.shipping_quote - ifnull(shop_orders.total_cost, 0))';
				case 'totals' :
					return 'shop_orders.total';
				case 'tax' :
					return '(shop_orders.shipping_tax + shop_orders.goods_tax)';
				case 'shipping' :
					return 'shop_orders.shipping_quote';
			}

			return 'shop_orders.total';
		}

		/*
		 * Universal parameters
		 */

		protected function index_onSetReportParameter() {
			Db_UserParameters::set( 'report_' . post( 'param' ) . '_' . get_class( $this ), post( 'value' ) );
		}

		protected function getReportParameter( $name, $default = null ) {
			return Db_UserParameters::get( 'report_' . $name . '_' . get_class( $this ), null, $default );
		}

		/*
		 * Order status selector (paid/all)
		 */

		protected function index_onSetOrderPaidStatus() {
			Db_UserParameters::set( 'report_order_paid_status_' . get_class( $this ), post( 'status_id' ) );
		}

		protected function getOrderPaidStatus() {
			return Db_UserParameters::get( 'report_order_paid_status_' . get_class( $this ), null, 'all' );
		}

		protected function getOrderPaidStatusFilter() {
			$status = $this->getOrderPaidStatus();
			if ( $status == 'all' ) {
				return null;
			}

			if ( !$this->paid_order_status_id ) {
				$this->paid_order_status_id = Db_DbHelper::scalar( "SELECT id FROM shop_order_statuses WHERE code = 'paid'" );
			}

			if ( !is_numeric( $this->paid_order_status_id ) ) {
				return null;
			}

			return "(EXISTS (SELECT id FROM shop_order_status_log_records WHERE order_id = shop_orders.id AND status_id=" . $this->paid_order_status_id . "))";
		}

		/*
		 * Timeline helpers
		 */

		protected function timeSeriesIdField() {
			return 'report_date';
		}

		protected function timeSeriesValueField() {
			return 'report_dates.report_date';
		}

		protected function timeSeriesDateFrameFields() {
			$timeUnit = $this->getTimeUnit();
			switch ( $timeUnit ) {
				case 'day':
					return '';
				case 'week':
					return ', week_start_formatted, week_end_formatted, week_number';
				case 'month':
					return ', month_start_formatted, month_end_formatted';
				case 'quarter':
					return ', quarter_start_formatted, quarter_end_formatted';
				case 'year':
					return ', year_start_formatted, year_end_formatted';
			}
		}

		protected function timelineFramedSerie( $record, $isFirst, $isLast, &$valueAltered ) {
			$timeUnit     = $this->getTimeUnit();
			$valueAltered = false;

			if ( !$isFirst && !$isLast || $timeUnit == 'day' ) {
				return $record->series_value;
			}

			$intStart = $this->get_interval_start();
			$intEnd   = $this->get_interval_end();

			if ( $isFirst && $isLast ) {
				switch ( $timeUnit ) {
					case 'week':
						if ( $record->week_start_formatted == $intStart && $record->week_end_formatted == $intEnd ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return '<b>' . $intStart . ' -<br>' . $intEnd . '</b>, #' . $record->week_number;
					case 'month':
						if ( $intStart == $record->month_start_formatted && $intEnd == $record->month_end_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
					case 'quarter':
						if ( $intStart == $record->quarter_start_formatted && $intEnd == $record->quarter_end_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
					case 'year':

						if ( $intStart == $record->year_start_formatted && $intEnd == $record->year_end_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
				}
			}

			if ( $isFirst ) {
				switch ( $timeUnit ) {
					case 'week':
						if ( $record->week_start_formatted == $intStart ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return '<b>' . $intStart . '</b> -<br>' . $record->week_end_formatted . ', #' . $record->week_number;

					case 'month' :
						if ( $intStart == $record->month_start_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
					case 'quarter':
						if ( $intStart == $record->quarter_start_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
					case 'year':
						if ( $intStart == $record->year_start_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
				}
			}

			if ( $isLast ) {
				switch ( $timeUnit ) {
					case 'week':
						if ( $record->week_end_formatted == $intEnd ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->week_start_formatted . ' -<br><b>' . $intEnd . '</b>, #' . $record->week_number;
					case 'month' :
						if ( $intEnd == $record->month_end_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
					case 'quarter':
						if ( $intEnd == $record->quarter_end_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
					case 'year':
						if ( $intEnd == $record->year_end_formatted ) {
							return $record->series_value;
						}

						$valueAltered = true;

						return $record->series_value . ' <b>(!)</b>';
				}
			}

		}

		/*
		 * Chart types
		 */

		protected function getChartType() {
			if ( !count( $this->chart_types ) ) {
				throw new Phpr_SystemException( 'There is no chart type defined for this report.' );
			}

			$type = Db_UserParameters::get( get_class( $this ) . '_report_type', null, $this->chart_types[0] );
			if ( !in_array( $type, $this->chart_types ) ) {
				return $this->chart_types[0];
			}

			return $type;
		}

		protected function index_onSetChartType() {
			Db_UserParameters::set( get_class( $this ) . '_report_type', post( 'chart_type' ) );
			$this->renderPartial( PATH_APP . '/modules/backend/controllers/partials/_chart.htm', null, true, true );
		}

		protected function getChartTypes() {
			return $this->chart_types;
		}

		/*
		 * Chart height
		 */

		protected function index_onSetChartHeight() {
			Db_UserParameters::set( 'report_chart_height', post( 'height' ) );
		}

		protected function getChartHeight() {
			return Db_UserParameters::get( 'report_chart_height', null, 300 );
		}

		/*
		 * Data helpers
		 */

		protected function addToArray( &$arr, $key, &$value, $keyParams = array(), $array_key = null ) {
			if ( !array_key_exists( $key, $arr ) ) {
				$arr[$key] = (object) array( 'values' => array(), 'params' => $keyParams );
			}

			if ( !strlen( $array_key ) ) {
				$arr[$key]->values[] = $value;
			} else {
				$arr[$key]->values[$array_key] = $value;
			}
		}

		protected function addMaxValue( $value ) {
			$this->maxChartValue = max( $value, $this->maxChartValue );

			return $value;
		}

		/*
		 * Records filter
		 */

		public function listPrepareData() {
			$obj = Shop_Order::create();
			$this->filterApplyToModel( $obj );
			$this->applyIntervalToModel( $obj );

			return $obj;
		}

		protected function applyIntervalToModel( $model ) {
			$start      = Phpr_DateTime::parse( $this->get_interval_start(), '%x' )->toSqlDate();
			$end        = Phpr_DateTime::parse( $this->get_interval_end(), '%x' )->toSqlDate();
			$paidFilter = $this->getOrderPaidStatusFilter();

			$model->where( 'date(shop_orders.order_datetime) >= ?', $start );
			$model->where( 'date(shop_orders.order_datetime) <= ?', $end );

			if ( $paidFilter ) {
				$model->where( $paidFilter );
			}
		}

		/*
		 * Chart helpers
		 */

		protected function onBeforeChartRender() {
		}

		protected function getValuesAxisMargin() {
			return strlen( Phpr::$lang->num( $this->maxChartValue, 2 ) ) * 10;
		}

		protected function getLegendWidth() {
			return strlen( Phpr::$lang->num( $this->maxChartValue, 2 ) ) * 9;
		}

		protected function getDataPath() {
			$module = Phpr::$router->param( 'module' );

			$reportName = strtolower( get_class( $this ) );
			$reportName = preg_replace( '/^' . $module . '_/', '', $reportName );

			return url( '/' . $module . '/' . $reportName . '/chart_data' );
		}

		protected function chartNoData( &$data ) {
			if ( !count( $data ) ) {
				$this->renderPartial( PATH_APP . '/modules/backend/controllers/partials/_nodata.htm', null, true, true );
			}
		}

		protected function chartColor( $index ) {
			return array_key_exists( $index, $this->chartColors ) ? 'color="' . $this->chartColors[$index] . '"' : null;
		}

		abstract public function chart_data();

		abstract public function refererUrl();

		abstract public function refererName();

		/*
		 * Report totals
		 */

		protected function index_onUpdateTotals() {
			$this->renderReportTotals();
		}

		protected function renderReportTotals() {
			$intervalLimit = $this->intervalQueryStrOrders();
			$filterStr     = $this->filterAsString();

			$paidFilter = $this->getOrderPaidStatusFilter();
			if ( $paidFilter ) {
				$paidFilter = 'and ' . $paidFilter;
			}

			$query_str = "from shop_orders, shop_order_statuses, shop_customers
			where shop_customers.id=customer_id and shop_orders.deleted_at is null and shop_order_statuses.id = shop_orders.status_id and $intervalLimit $filterStr $paidFilter";

			$query = "
				select (select count(*) $query_str) as order_num,
				(select sum(total) $query_str) as total,
				(select sum(total - goods_tax - shipping_tax - shipping_quote - ifnull(total_cost, 0)) $query_str) as revenue,
				(select sum(total_cost) $query_str) as cost,
				(select sum(goods_tax + shipping_tax) $query_str) as tax,
				(select sum(shipping_quote) $query_str) as shipping
			";

			$this->viewData['totals_data'] = Db_DbHelper::object( $query );
			$this->renderPartial( 'chart_totals' );
		}


		/**
		 * Extended by reports that support return of chart data as associative array
		 * Required to output charts in HTML format
		 * @return array()
		 */
		protected function getChartData() {
			return false;
		}

	}
?>