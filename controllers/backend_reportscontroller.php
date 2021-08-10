<?

	class Backend_ReportsController extends Backend_Controller
	{
		public function index()
		{
			$reports = Backend_Reports::listReports();
			if (!count($reports))
				Phpr::$response->redirect(url());

			$first_report_info = Backend_Reports::getFirstReportInfo();
			if($first_report_info){
				Phpr::$response->redirect(url($first_report_info['module_id'].'/'.$first_report_info['report_id'].'_report'));
			}
			
			Phpr::$response->redirect(url());
		}
	}

?>