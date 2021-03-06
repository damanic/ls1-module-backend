<?php

	/**
	 * Back-end controller generic class
	 * @has_documentable_methods
	 */
	class Backend_Controller extends Phpr_Controller
	{
		public $app_tab;
		public $app_page;
		public $app_module = null;
		public $app_page_title;
		public $app_module_name = null;
		public $app_page_subheader = null;
		public $override_module_name = null;
		public $hide_layout_tabs = false;
		public $hide_settings_links = false;
		
		protected $moduleId = null;
		protected $public_actions = array();
		
		protected $currentUser = null;
		protected $access_for_groups = null;
		protected $access_exceptions = null;

		public $list_control_panel;
		public $list_cell_individual_partial = array();

		/**
		 * Contains a list of user permissions required for accessing the controller
		 * Example: array('shop:access_reports', 'shop:manage_products')
		 * @var array
		 */
		protected $required_permissions = array();
		
		/*
		 * Creates a new BackEnd_Controller instance
		 */
		public function __construct()
		{
			Core_ModuleManager::listModules();

			parent::__construct();

			$protocol = Phpr::$request->protocol();
			if ($protocol === 'http' && Phpr::$config->get('FORCE_ADMIN_SSL'))
				Phpr::$response->redirect(Phpr::$request->getRootUrl('https').Phpr::$request->getCurrentUri());
			
			Backend::$events->fireEvent('backend:onControllerInit', $this);
			
			if (!Phpr::$request->isRemoteEvent())
			{
				$mod_build = module_build('backend') ;
				$this->addCss('/modules/backend/thirdparty/chosen/chosen.css?'.$mod_build );
				$this->addJavaScript('/modules/backend/thirdparty/chosen/chosen.jquery.min.js?'.$mod_build);
			}
			
			$this->globalHandlers[] = 'onHideHint';
			$this->globalHandlers[] = 'onFinishTour';
			$this->globalHandlers[] = 'onPingLock';
			$this->globalHandlers[] = 'onStealLock';
			$this->globalHandlers[] = 'onFullscreen';
			$this->globalHandlers[] = 'onCustomEvent';

			$this->layoutsPath = PATH_APP.'/modules/backend/layouts';
			$this->layout = 'backend';
			$this->viewPath = PATH_APP.'/modules/'.$this->getModuleId().'/controllers/'.strtolower(get_class($this));

			$isPublicAction = in_array(Phpr::$router->action, $this->public_actions);

			if (!$isPublicAction && !Phpr_Controller::$no_permissions_check)
			{
				if ( !$isPublicAction && !Phpr::$security->cookiesUpdated )
					Phpr::$security->baseAuthorization();
			
				$this->currentUser = Phpr::$security->getUser();
				
				if (is_array($this->access_for_groups))
				{
					if (!(is_array($this->access_exceptions) && in_array(Phpr::$router->action, $this->access_exceptions)))
					{
						if (!$this->currentUser || !$this->currentUser->belongsToGroups($this->access_for_groups))
							Phpr::$response->redirect(url('/'));
					}
				}
				
				if ($this->required_permissions)
				{
					$permission_found = false;
					foreach ($this->required_permissions as $permission)
					{
						$permission_info = explode(':', $permission);
						$cnt = count($permission_info);
						if ($cnt != 2)
							throw new Phpr_SystemException('Invalid permission qualifier: '. $permission);
						
						if ($this->currentUser->get_permission($permission_info[0], $permission_info[1]))
						{
							$permission_found = true;
							break;
						}
					}
					
					if (!$permission_found)
						Phpr::$response->redirect(url('/'));
				}
			}
			
			if (!Phpr::$request->isRemoteEvent())
				Core_Metrics::update_metrics();
			else
			{
				$event_name = isset($_SERVER['HTTP_PHPR_EVENT_HANDLER']) ? $_SERVER['HTTP_PHPR_EVENT_HANDLER'] : null;
				$event_name = substr($event_name, 3, -1);
				Backend::$events->fireEvent('backend:onBeforeRemoteEvent', $this, $event_name);
			}
			
			Backend::$events->fireEvent('backend:onControllerReady', $this);
			
			$display_tour = false;
			if (Phpr::$config->get('ENABLE_BACKEND_TOUR') && $this->currentUser)
			{
				if (!$this->isTourCompleted('backend-intro'))
					$display_tour = true;
			}
			
			$this->viewData['display_intro_tour'] = $display_tour;
		}
		
		public function _execEventHandler( $MethodName, $Parameters = array(), $ActionName = null )
		{
			parent::_execEventHandler($MethodName, $Parameters, $ActionName);

			$event_name = isset($_SERVER['HTTP_PHPR_EVENT_HANDLER']) ? $_SERVER['HTTP_PHPR_EVENT_HANDLER'] : null;
			$event_name = substr($event_name, 3, -1);

			Backend::$events->fireEvent('backend:onAfterRemoteEvent', $this, $event_name);
		}
		
		protected function onHideHint()
		{
			$hidden_hints = Db_UserParameters::get('hidden_hints', null, array());
			$hidden_hints[post('name')] = 1;
			Db_UserParameters::set('hidden_hints', $hidden_hints);
		}
		
		protected function onFinishTour()
		{
			$completed_tours = Db_UserParameters::get('completed_tours', null, array());
			$completed_tours[post('name')] = 1;
			Db_UserParameters::set('completed_tours', $completed_tours);
		}
		
		protected function isTourCompleted($name)
		{
			$completed_tours = Db_UserParameters::get('completed_tours', null, array());
			return array_key_exists($name, $completed_tours);
		}
		
		protected function onFullscreen()
		{
			Phpr::$session->set('backend_fullscreen_mode', post('fullscreen_mode'));
		}
		
		protected function onCustomEvent($id=null)
		{
			Backend::$events->fireEvent(post('custom_event_handler'), $this, $id);
		}
		
		protected function isHintVisible($name)
		{
			$hidden_hints = Db_UserParameters::get('hidden_hints', null, array());
			return !array_key_exists($name, $hidden_hints);
		}
		
		protected function onPingLock()
		{
			$lock_id = post('lock_id');
			if ($lock_id)
				Db_RecordLock::ping(post('lock_id'));
		}
		
		protected function onStealLock()
		{
			$hash = post('hash');
			if ($hash)
				Db_RecordLock::lock($hash);
				
			Phpr::$response->redirect(Phpr::$request->getReferer(post('url')));
		}
		
		// protected function onReleaseRecordLock()
		// {
		// 	Db_RecordLock::unlock(post('lock_id'));
		// }

		protected function getModuleId()
		{
			if ($this->moduleId !== null)
				return $this->moduleId;
				
			$refObj = new ReflectionObject($this);
			return $this->moduleId = basename(dirname(dirname($refObj->getFileName())));
		}
		
		public function handlePageError($exceptionObj)
		{
			Phpr::$session->flash['error'] = $exceptionObj->getMessage();
			$this->viewData['fatalError'] = true;
		}
		
		public function addPublicAction($action)
		{
			$this->public_actions[] = $action;
		}
		
		public function xmlData()
		{
			$this->layout = null;
			header("Content-type: text/xml; charset=utf-8"); 
		}
		
		public function loadView( $View, $SuppressLayout = false, $SuppressDefault = false )
		{
			Backend::$events->fireEvent('backend:onBeforeRenderPage', $this, $View);
			parent::loadView( $View, $SuppressLayout, $SuppressDefault);
		}

		public function renderPartial( $View, $Params = null, $PartialMode = true, $ForcePath = false )
		{
			Backend::$events->fireEvent('backend:onBeforeRenderPartial', $this, $View, $Params);
			$viewOverride = $this->frameworkPartialOverride($View);
			$View = $viewOverride ? $viewOverride : $View;
			parent::renderPartial( $View, $Params, $PartialMode, $ForcePath );
		}

		protected function frameworkPartialOverride($View){
			$override_paths = array(
				'behaviors/db_formbehavior/'
			);
			foreach($override_paths as $override_path){
				if(strstr($View,'phproad') && strstr($View,$override_path)){
					$path_parts = pathinfo($View);
					if($path_parts['extension'] == 'htm'){
						$override_path = PATH_APP.'/modules/backend/partials/'.$override_path;
						$partial = $override_path.basename($View);
						if(file_exists($partial)){
							return $partial;
						}
					}
				}
			}

			return false;
		}
		
		protected function renderLayout($Name = null)
		{
			Backend::$events->fireEvent('backend:onBeforeRenderLayout', $this, $Name);
			parent::renderLayout($Name);
		}
		
		/*
		 * Event descriptions
		 */
		
		/**
		 * Triggered before an Administration Area AJAX request is handled.
		 * In the event handler you can throw an exception to cancel the default
		 * event processing. Example:
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('backend:onBeforeRemoteEvent', $this, 'handle_remote_event');
		 * }
		 * 
		 * public function handle_remote_event($controller, $event)
		 * {
		 *  if ($controller instanceof Cms_Themes && $event == 'enable_onApply')
		 *    throw new Phpr_ApplicationException('We\'re sorry, you manage CMS themes .');
		 * }
		 * </pre>
		 * @event backend:onBeforeRemoteEvent
		 * @package backend.events
		 * @see backend:onAfterRemoteEvent
		 * @author LemonStand eCommerce Inc.
		 * @param Backend_Controller $controller Specifies the Administration Area controller object.
		 * @param string $event_name Specifies the AJAX event name.
		 */
		private function event_onBeforeRemoteEvent($controller, $event_name) {}
		
		/**
		 * Triggered after an Administration Area AJAX request is handled.
		 * Note that if the AJAX request handler stops the script execution (by redirecting to another page, for example), the
		 * backend:onAfterRemoteEvent event is not triggered. Example:
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('backend:onAfterRemoteEvent', $this, 'after_remote_event');
		 * }
		 * 
		 * public function after_remote_event($controller, $event)
		 * {
		 *   traceLog('After '.$event);
		 * }
		 * </pre>
		 * @event backend:onAfterRemoteEvent
		 * @package backend.events
		 * @see backend:onBeforeRemoteEvent
		 * @author LemonStand eCommerce Inc.
		 * @param Backend_Controller $controller Specifies the Administration Area controller object.
		 * @param string $event_name Specifies the AJAX event name.
		 */
		private function event_onAfterRemoteEvent($controller, $event_name) {}

		/**
		 * Triggered after an Administration Area controller object is completely configured.
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('backend:onControllerReady', $this, 'on_backend_controller_ready');
		 * }
		 * 
		 * public function on_backend_controller_ready($controller)
		 * {
		 *   // Do something
		 * }
		 * </pre>
		 * @event backend:onControllerReady
		 * @see backend:onControllerInit
		 * @package backend.events
		 * @author LemonStand eCommerce Inc.
		 * @param Backend_Controller $controller Specifies the Administration Area controller object.
		 */
		private function event_onControllerReady($controller) {}
			
		/**
		 * Triggered after an Administration Area controller object is created, but before it is configured.
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('backend:onControllerInit', $this, 'on_backend_controller_init');
		 * }
		 * 
		 * public function on_backend_controller_init($controller)
		 * {
		 *   // Do something
		 * }
		 * </pre>
		 * @event backend:onControllerInit
		 * @see backend:onControllerReady
		 * @package backend.events
		 * @author LemonStand eCommerce Inc.
		 * @param Backend_Controller $controller Specifies the Administration Area controller object.
		 */
		private function event_onControllerInit($controller) {}

		/**
		 * Triggered before an Administration Area page is rendered.
		 * In the event handler you can inject new variables to the view document.
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('backend:onBeforeRenderPage', $this, 'backend_render_page');
		 * }
		 * 
		 * public function backend_render_page($controller, $view)
		 * {
		 *   if ($controller instanceof Shop_Orders and basename($view) == 'create')
		 *   {
		 *     Phpr::$session->flash['error'] = 'We are sorry, you cannot create new orders in the Demo store.';
		 *     $controller->viewData['fatalError'] = true;
		 *   }
		 * }
		 * </pre>
		 * @event backend:onBeforeRenderPage
		 * @see backend:onBeforeRenderPartial
		 * @see backend:onBeforeRenderLayout
		 * @package backend.events
		 * @author LemonStand eCommerce Inc.
		 * @param Backend_Controller $controller Specifies the Administration Area controller object.
		 * @param string $view_path Specifies a path to the page's view document.
		 */
		private function event_onBeforeRenderPage($controller, $view_path) {}
			
		/**
		 * Triggered before an Administration Area partial is rendered.
		 * In the event handler you can update controller's properties and alter any data
		 * passed to the partial.
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('backend:onBeforeRenderPartial', $this, 'backend_render_partial');
		 * }
		 * 
		 * public function backend_render_partial($controller, $view, $params)
		 * {
		 *   if ($view == 'order_customer_notifications')
		 *   {
		 *     foreach ($controller->viewData['form_model']->customer_notifications as $record)
		 *       $record->email = 'xxx@xxxxxxxxx.xxx';
		 *   }
		 * }
		 * </pre>
		 * @event backend:onBeforeRenderPartial
		 * @see backend:onBeforeRenderPage
		 * @see backend:onBeforeRenderLayout
		 * @package backend.events
		 * @author LemonStand eCommerce Inc.
		 * @param Backend_Controller $controller Specifies the Administration Area controller object.
		 * @param string $view Specifies a name of the partial's view document.
		 * @param array $params Specifies the partial parameters array.
		 */
		private function event_onBeforeRenderPartial($controller, $view, $params) {}
			
		/**
		 * Triggered before an Administration Area layout is rendered.
		 * In the event handler you can update controller's properties.
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('backend:onBeforeRenderLayout', $this, 'backend_render_layout');
		 * }
		 * 
		 * public function backend_render_layout($controller, $name)
		 * {
		 *   // Do something
		 * }
		 * </pre>
		 * @event backend:onBeforeRenderLayout
		 * @see backend:onBeforeRenderPage
		 * @see backend:onBeforeRenderPartial
		 * @package backend.events
		 * @author LemonStand eCommerce Inc.
		 * @param Backend_Controller $controller Specifies the Administration Area controller object.
		 * @param string $name Specifies a name of the layout document.
		 */
		private function event_onBeforeRenderLayout($controller, $name) {}
			
		/**
		 * Allows to render custom content in the back-end footer.
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('backend:onExtendFooter', $this, 'backend_extend_footer');
		 * }
		 * 
		 * public function backend_extend_footer($controller)
		 * {
		 *   $controller->renderPartial(PATH_APP.'/mymodule/partials/_footer.htm')
		 * }
		 * </pre>
		 * @event backend:onExtendFooter
		 * @triggered /modules/backend/layouts/footer.htm
		 * @package backend.events
		 * @author LemonStand eCommerce Inc.
		 * @param Backend_Controller $controller Specifies the Administration Area controller object.
		 */
		private function event_onExtendFooter($controller) {}
	}

?>