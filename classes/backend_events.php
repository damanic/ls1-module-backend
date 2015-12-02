<?

	class Backend_Events extends Phpr_Extensible
	{
		public $implement = 'Phpr_Events';
		
		public function __construct() 
		{
			parent::__construct();
			
			register_shutdown_function(array($this, 'uninitialize'));
		}
		
		function uninitialize() 
		{
			$this->fireEvent('core:onUninitialize');
		}
	}

?>