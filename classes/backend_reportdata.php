<?
class Backend_ReportData{

	public $parent_columns = array();
	public $columns = array();
	public $rows = array();
	public $title;
	public $description;
	public $context;

	public function __construct($title=null,$description=null,$context=null){
		if($title)
			$this->set_report_title($title);

		if($description)
			$this->set_report_description($description);

		if($context)
			$this->set_report_context($context);
	}

	public function set_report_title($title){
		$this->title = $title;
	}

	public function set_report_description($description){
		$this->description = $description;
	}

	public function set_report_context($context){
		$this->context = $context;
	}

	public function add_parent_column($id, $title, $class=null, $options = array()){
		$default_options = array(
			'url' => null,
		);
		$options = array_merge($default_options, $options);
		$this->columns['parent'][] = array(
			'id' => $id,
			'title' => $title,
			'class' => $class,
			'options' => $options,
		);
	}

	public function has_parent_columns(){
		if(count($this->columns['parent'])){
			return true;
		}
		return false;
	}

	public function add_column($id, $title, $class=null, $options = array()){
		$default_options = array(
			'url' => null,
		);
		$options = array_merge($default_options, $options);
		$this->columns['main'][] = array(
			'id' => $id,
			'title' => $title,
			'class' => $class,
			'options' => $options,
		);
	}

	public function add_row($options=array(), $field_array = array() ){

		$default_options = array(
			'url' => null,
		);
		$options = array_merge($default_options, $options);

		if(count($field_array) > 0) {
			$row = $this->get_row_count() + 1;
			foreach ( $field_array as $key => $values ) {
				$this->rows[$row]['fields'][$key] = array(
					'value'  => $values['value'],
					'class'  => $values['class'],
					'extras' => $values['extras']
				);
			}
		} else {
			$this->rows[$this->get_row_count() + 1] = array();
		}
		$this->rows[$this->get_row_count()]['options'] = $options;
	}

	public function add_field($column_id, $value, $class=null, $extras=array()){
		$this->rows[$this->get_row_count()]['fields'][$column_id] =  array(
			'value' => $value,
			'class' => $class,
			'extras' => $extras,
		);
	}

	public function add_fields($id_values, $class=null, $extras=array()){
		foreach ($id_values as $id => $value){
			$this->add_field($id, $value, $class, $extras);
		}
	}



	public function get_columns($type='main'){
		 return $this->columns[$type];
	}

	public function get_column_count($type='main'){
		return count($this->columns[$type]);
	}

	public function get_rows(){
		return $this->rows;
	}


	public function get_row_count(){
		return count($this->rows);
	}

	public function output_table($id='reports_report_table'){

	}
	public static function get_number($number){
		$number = !is_numeric($number) ? 0 : $number;
		return  Phpr::$locale->get_number($number,2);
	}


}