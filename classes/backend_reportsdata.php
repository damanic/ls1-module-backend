<?

	/**
	 * @has_documentable_methods
	 */
	class Backend_ReportsData
	{
		protected static $startReportingDate = false;
		
		public static function listReportYears()
		{
			$date = self::getEndReportingDate();
			$startDate = self::getStartReportingDate();
			if (!$startDate)
				$startDate = $date;
				
			$records =  Db_DbHelper::objectArray('
				select 
					distinct year as name, 
					year_start as start, 
					year_end as end
				from 
					report_dates
				where 
					report_dates.report_date >= :start_date
					and report_dates.report_date <= :now_date
				order by report_dates.report_date
			', array('now_date'=>$date, 'start_date'=>$startDate));

			foreach ($records as $record)
			{
				$record->start = Phpr_Datetime::parse($record->start, Phpr_Datetime::universalDateFormat)->format('%x');
				$record->end = Phpr_Datetime::parse($record->end, Phpr_Datetime::universalDateFormat)->format('%x');
			}

			return $records;
		}
		
		public static function listReportMonths()
		{
			$date = self::getEndReportingDate();
			$startDate = self::getStartReportingDate();
			if (!$startDate)
				$startDate = $date;

			$records = Db_DbHelper::objectArray('
				select 
					distinct month_start as name, 
					month_start as start, 
					month_end as end
				from 
					report_dates
				where 
					report_dates.report_date >= :start_date
					and report_dates.report_date <= :now_date
				order by report_dates.report_date
			', array('now_date'=>$date, 'start_date'=>$startDate));
			
			foreach ($records as $record)
			{
				$record->name = Phpr_Datetime::parse($record->name, Phpr_Datetime::universalDateFormat)->format('%n, %Y');
				$record->start = Phpr_Datetime::parse($record->start, Phpr_Datetime::universalDateFormat)->format('%x');
				$record->end = Phpr_Datetime::parse($record->end, Phpr_Datetime::universalDateFormat)->format('%x');
			}

			return $records;
		}
		
		protected static function getEndReportingDate()
		{
			$result = Phpr_Date::userDate(Phpr_DateTime::now())->getDate();
			
			$api_dates = Backend::$events->fireEvent('shop:onGetEndReportingDate', $result->format(Phpr_Datetime::universalDateFormat));
			foreach ($api_dates as $api_date)
			{
				if ($api_date && is_string($api_date))
				{
					$result = Phpr_Datetime::parse($api_date, Phpr_Datetime::universalDateFormat);
					break;
				}
			}
			
			return $result;
		}
		
		protected static function getStartReportingDate()
		{
			if (self::$startReportingDate !== false)
				return self::$startReportingDate;

			$result = Db_DbHelper::scalar('select date(order_datetime) from shop_orders order by id limit 0,1');
			
			$api_dates = Backend::$events->fireEvent('shop:onGetStartReportingDate', $result);
			foreach ($api_dates as $api_date)
			{
				if ($api_date && is_string($api_date))
				{
					$result = $api_date;
					break;
				}
			}
			
			return self::$startReportingDate = $result;
		}
		
		/**
		 * Allows to override the reporting start date. 
		 * The event handler should return the new reporting start date as string. The default reporting start date 
		 * matches the first order date.
		 * Example:
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('shop:onGetStartReportingDate', $this, 'get_start_reporting_date');
		 * }
		 *  
		 * public function get_start_reporting_date($default_date)
		 * {
		 *   return '2009-10-01';
		 * }
		 * </pre>
		 * @event shop:onGetStartReportingDate
		 * @package shop.events
		 * @see shop:onGetEndReportingDate
		 * @author LemonStand eCommerce Inc.
		 * @param string $default_date Specifies the default reporting start date calculated by LemonStand.
		 * @return string Returns the new reporting start date in the following format: yyyy-mm-dd
		 */
		private function event_onGetStartReportingDate($default_date) {}
			
		/**
		 * Allows to override the reporting end date. 
		 * The event handler should return the new reporting end date as string. The default reporting end date 
		 * matches the current date in the application time zone.
		 * Example:
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('shop:onGetEndReportingDate', $this, 'get_end_reporting_date');
		 * }
		 *  
		 * public function get_end_reporting_date($default_date)
		 * {
		 *   return '20015-10-01';
		 * }
		 * </pre>
		 * @event shop:onGetEndReportingDate
		 * @package shop.events
		 * @see shop:onGetStartReportingDate
		 * @author LemonStand eCommerce Inc.
		 * @param string $default_date Specifies the default reporting end date calculated by LemonStand.
		 * @return string Returns the new reporting start date in the following format: yyyy-mm-dd
		 */
		private function event_onGetEndReportingDate($default_date) {}
	}

?>