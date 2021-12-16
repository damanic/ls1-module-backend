<?php

	class Backend_ReportBehavior extends Phpr_ControllerBehavior {
		public $report_name = null;
		public $report_no_data_message = 'No data to report';
		public $report_load_indicator = null;
		public $report_data_context = null;

		public $report_date_search_enabled = true;
		public $report_default_start_date;
		public $report_default_end_date;


		public $report_display_variants = array();


		public $report_scrollable = false;

		public $report_custom_body_cells = null;
		public $report_custom_head_cells = null;
		public $report_cell_partial = false;
		public $report_custom_partial = null;
		public $report_cell_individual_partial = array();
		public $report_top_partial = null;
		public $report_control_panel = null;
		public $report_sidebar_panel = null;

		public $report_csv_export = true;
		public $report_csv_export_url = null;

		public $report_pdf_export = true;
		public $report_pdf_export_url = null;

		public $report_columns = array();
		public $report_options = array();
		public $report_display_view = null;

		protected $_report_data = null;
		protected $_report_settings = null;

		public function __construct( $controller=null ) {
			parent::__construct( $controller );
		}

		public function init_extension() {
			if ( !$this->_controller ) {
				return;
			}

			$this->report_load_resources();

			$public_actions_hidden_from_url = array(
				'report_prepare_data',
				'report_display_table',
				'report_render',
				'report_get_name',
				'report_get_form_id',
				'report_get_container_id',
				'report_render_partial',
			);
			$this->hideAction( $public_actions_hidden_from_url );

			$this->addEventHandler( 'on_report_date_search' );
			$this->addEventHandler( 'on_report_change_display' );
			$this->addEventHandler( 'on_report_search_cancel');
			$this->addEventHandler('on_report_reload');

		}


		//
		// Asset management
		// 

		protected function report_load_resources() {
			$phpr_url = Phpr::$config->get( 'PHPR_URL', 'phpr' );


			$this->_controller->addCss( $this->get_behavior_url( 'assets/stylesheets/css/daterangepicker.css?' . module_build( 'backend' ) ) );
			$this->_controller->addCss( $this->get_behavior_url( 'assets/stylesheets/css/presentation.css?' . module_build( 'backend' ) ) );
			$this->_controller->addJavaScript( $this->get_behavior_url( 'assets/scripts/js/control.js?' . module_build( 'backend' ) ) );


			if ( !$this->report_load_indicator ) {
				$this->report_load_indicator = $phpr_url . '/assets/images/loading_50.gif';
			}

		}

		//
		// Public methods - available to call in views
		// 

		public function report_render( $options = array(), $partial = null ) {

			$this->_report_settings       = null;
			$this->apply_options( $options );

			$this->prepare_render_data();

			if ( !$partial ) {
				$this->renderPartial( 'report_container' );
			} else {
				$this->renderPartial( $partial );
			}
		}

		public function report_get_name()
		{
			if ($this->_controller->report_name !== null)
				return $this->_controller->report_name;

			return get_class($this->_controller).'_'.Phpr::$router->action.'_report';
		}


		public function report_get_form_id()
		{
			return 'reportform'.$this->report_get_name();
		}

		public function report_get_start_date(){
			$int = $this->report_get_date_interval();
			return $int->start;
		}

		public function report_get_end_date(){
			$int = $this->report_get_date_interval();
			return $int->end;
		}

		public function report_get_date_interval(){
			$interval = Phpr::$session->get($this->report_get_name().'_date_search');
			if(!$interval || empty($interval)){
				$interval = new stdClass();
				$interval->start = $this->_controller->report_default_start_date ? $this->_controller->report_default_start_date : date('Y-m-d');
				$interval->end = $this->_controller->report_default_end_date ? $this->_controller->report_default_end_date : date('Y-m-d');
				Phpr::$session->set( $this->report_get_name() . '_date_search', json_encode($interval) );
			} else {
				$interval = json_decode($interval);
			}

			return $interval;
		}

		public function get_behavior_url($endpoint=null){
			return '/modules/backend/behaviors/backend_reportbehavior/'.$endpoint;
		}

		public function get_behavior_dir($endpoint=null){
			return PATH_APP.'/modules/backend/behaviors/backend_reportbehavior/'.$endpoint;
		}

		public function get_report_view(){
			$users_selection = Phpr::$session->get($this->report_get_name().'_report_display_view');
			return $users_selection ? $users_selection : $this->report_display_view;
		}

		public function report_get_container_id()
		{
			return 'report'.$this->report_get_name();
		}

		public function report_render_partial($view, $params=array(), $throw_not_found=true)
		{
			$this->renderControllerPartial('Backend_ReportingController', $view, $params, false, $throw_not_found);
		}

		public function get_views_path() {
			return $this->get_behavior_dir('partials');
		}

		public function report_display_table()
		{
			$this->_controller->suppressView();
			$this->display_table();
		}

		public function report_get_export_pdf_url(){
			return $this->report_pdf_export_url ? url($this->report_pdf_export_url) :  root_url(Phpr::$router->getControllerRootUrl()).'/report_download_pdf/'.$this->report_get_name().'/'.$this->_controller->report_custom_prepare_func.'/';
		}

		public function report_download_pdf($name=null,$prep_function=null){
			if(strlen($name)){
				$this->_controller->report_name = $name;
			}

			if(strlen($prep_function) && $prep_function != 'null'){
				$this->_controller->report_custom_prepare_func = $prep_function;
			}

			$this->report_export_pdf();
		}

		protected function report_export_pdf( $options = array()) {
			Phpr::$events->fire_event('backend:on_before_report_pdf_export', $this->_controller);
			$this->_controller->suppressView();
		}

		public function report_get_export_csv_url(){
			return $this->report_csv_export_url ? url($this->report_csv_export_url) :  root_url(Phpr::$router->getControllerRootUrl()).'/report_download_csv/'.$this->report_get_name().'/'.$this->_controller->report_custom_prepare_func.'/';
		}

		public function report_download_csv($name=null,$prep_function=null){
			if(strlen($name)){
				$this->_controller->report_name = $name;
			}

			if(strlen($prep_function) && $prep_function != 'null'){
				$this->_controller->report_custom_prepare_func = $prep_function;
			}

			$this->report_export_csv();
		}
		protected function report_export_csv( $options = array(), $filter_callback = null, $no_column_info_init = false, $extend_csv_callback = array())
		{
			$spacer = array(''=>'');

			Phpr::$events->fire_event('backend:on_before_report_export', $this->_controller);

			$this->apply_options($options);

			$data = $this->load_data();
			$filename = $this->report_get_name().'.csv';


			header("Expires: 0");
			header("Content-Type: Content-type: text/csv");
			header("Content-Description: File Transfer");
			header("Cache-control: private");
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header("Content-disposition: attachment; filename=$filename");


			$this->_controller->suppressView();

			$iwork = array_key_exists('iwork', $options) ? $options['iwork'] : false;
			$separator = $iwork ? ';' : ',';

			if(strlen($data->title)){
				$title_row = array($data->title, $data->description, $data->context);
				Phpr_Files::outputCsvRow($title_row, $separator);
				Phpr_Files::outputCsvRow(array(), $separator);
			}


			$header = array();
			foreach ($data->get_columns() as $column_count => $column_data) {
				$header[] = $column_data['title'];
			}


			if (array_key_exists('header_callback', $extend_csv_callback))
				$header = call_user_func($extend_csv_callback['header_callback'], $header);

			Phpr_Files::outputCsvRow($header, $separator);

			foreach ($data->get_rows() as $row_count => $row)
			{


				if ($filter_callback) {
					if (!call_user_func($filter_callback, $row))
						continue;
				}

				$row_data = array();
				$spacer = array();
				foreach ($data->get_columns() as $column_count => $column_data) {
					$field      = $row['fields'][$column_data['id']];
					$row_data[] = $field['value'];
					$spacer[] = '';
				}

				if (array_key_exists('row_callback', $extend_csv_callback)) {
					call_user_func( $extend_csv_callback['row_callback'], $row, $row_data, $separator );
				} else {
					if($row['options']['class'] == 'heading'){
						Phpr_Files::outputCsvRow($spacer, $separator);
					}
					Phpr_Files::outputCsvRow( $row_data, $separator );
				}
			}
		}






		// Event handlers
		//

		public function on_report_change_display(){
			$this->report_display_view = post('report_display_view');
			$this->_controller->report_name = post('report_name', $this->report_get_name());
			Phpr::$session->set( $this->_controller->report_name . '_report_display_view', $this->report_display_view  );
			$this->display_table();
		}

		public function on_report_date_search() {
			$interval_string = trim( post( 'date_interval' ) );
			$interval        = json_decode( trim( $interval_string ) );
			$this->_controller->report_name = post('report_name', $this->report_get_name());
			Phpr::$session->set( $this->_controller->report_name . '_date_search', $interval_string );
			$this->display_table();
		}

		public function on_report_search_cancel() {
			$this->_controller->report_name = post('report_name', $this->report_get_name());
			Phpr::$session->set( $this->_controller->report_name . '_search', '' );
			$this->display_table();
		}

		public function on_report_reload(){
			$this->_controller->report_name = post('report_name', null);
			$this->report_display_table();
		}








		
		//Internals
		protected function load_data($use_cache = false)
		{
			if($this->_report_data && $use_cache) {
				return $this->_report_data;
			}

			$date_start = null;
			$date_end = null;

			// Apply date search
			if ($this->_controller->report_date_search_enabled) {
					$date_start  = Phpr_DateTime::parse( $this->report_get_start_date(), '%Y-%m-%d' );
					$date_end    = Phpr_DateTime::parse( $this->report_get_end_date(), '%Y-%m-%d' );
			}

			// Apply display option
			$this->_controller->report_options['report_display_view'] = $this->get_report_view();

			//run prepare function
			if (strlen($this->_controller->report_custom_prepare_func))
			{
				$func = $this->_controller->report_custom_prepare_func;
				$data = $this->_controller->$func($date_start, $date_end, $this->_controller->report_options);
			}
			else
			{
				$data = $this->_controller->report_prepare_data($date_start, $date_end);
			}


			if(!is_a($data,'Backend_ReportData')){
				throw new Phpr_ApplicationException('Data provided to the report behavior must be instance of Backend_ReportData');
			}

			$this->_report_data = $data;
			return $this->_report_data;
		}

		protected function apply_options($options)
		{
			$this->_controller->report_options = $options;
			foreach ($options as $key=>$value)
			{
				$this->_controller->$key = $value;
			}
		}

		protected function prepare_render_data()
		{
			$context = $this->_controller->report_data_context;

			$this->_report_data = $this->viewData['report_data'] =  $this->load_data();

			$report_settings = $this->load_report_settings();

			$this->viewData['report_no_data_message'] = $this->_controller->report_no_data_message;
			$this->viewData['report_load_indicator'] = $this->_controller->report_load_indicator;
			$this->viewData['report_date_start'] = $this->report_get_start_date();
			$this->viewData['report_date_end'] = $this->report_get_end_date();
			$this->viewData['report_display_view'] = $this->get_report_view(); //Phpr::$session->get($this->report_get_name().'_report_display_view');

		}

		protected function load_report_settings()
		{
			if ($this->_report_settings === null)
			{
				$this->_report_settings = Db_UserParameters::get($this->report_get_name().'_settings');

				if (!is_array($this->_report_settings))
					$this->_report_settings = array();

			}
			return $this->_report_settings;
		}

		protected function save_report_settings($settings)
		{
			$this->_report_settings = $settings;
			Db_UserParameters::set($this->report_get_name().'_settings', $settings);
		}

		protected function display_table($params=array())
		{
			$this->prepare_render_data();

			if (!$this->_controller->report_custom_partial)
				$this->renderPartial('report_report', $params);
			else
				$this->renderPartial($this->_controller->report_custom_partial);
		}


		protected function report_set_date_interval($start_date, $end_date){
			$interval = new stdClass();
			$interval->start = $start_date;
			$interval->end = $end_date;
			Phpr::$session->set( $this->report_get_name() . '_date_search', json_encode($interval) );
		}





		//common methods for override
		public function report_before_display_row($row) {

		}
}

?>