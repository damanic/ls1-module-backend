<?

	class Backend_ChartData extends Backend_DashboardController
	{
		public function unique_visits()
		{
			$this->xmlData();
			$this->layout = null;

			$data = self::get_unique_visits_data();

			$this->viewData['sales_data'] = $data['sales'];
			$this->viewData['chart_data'] = $data['visits'];
		}

		public static function get_unique_visits_data(){
			$_this = new Backend_ChartData();
			$start = $_this->get_interval_start(true);
			$end = $_this->get_interval_end(true);

			$data = array(
				'sales' => Shop_Orders_Report::get_totals_chart_data($start, $end),
				'visits' => Cms_Analytics::getVisitorsChartData($start, $end),
			);

			return $data;
		}
	}

?>