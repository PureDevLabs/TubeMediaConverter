<?php

	namespace MediaConverterPro\app\Core;

	// Controller Class
	abstract class Controller
	{
		// Constants
		const _TEMPLATES_PATH = "app/Templates";
		const _HELPERS_PATH = "Views/helpers";
		const _LANGUAGES_PATH = "app/Languages";
		const _VIEWS_PATH = "Views";
		const _ELEMENTS_DIR = 'elements';
		const _ERRORS_DIR = 'errors';
		const _LAYOUTS_DIR = 'layouts';
		
		// Private Fields
		private $_action = '';
		private $_dbo = NULL;
		private $_data;
		private $_params;
		private $_vars = array();
		private $_helpers = array();
		private $_layout = 'default';
		
		#region Public Methods
		public function __construct($action)
		{
			$this->_data = $_POST;
			$this->_params = $_GET;
			$this->_action = $action;
			$this->beforeAction();
		}	
		
		public function __destruct()
		{
			$this->afterAction();
		}
		
		final public function error404()
		{
			header('HTTP/1.0 404 Not Found', true, 404);
			$params = $this->GetParams();
			$vars = ['pagetitle' => "Error 404 - Not Found"];
			$vars += (isset($params['q'])) ? ['search' => trim($params['q'])] : [];
			$this->SetVars($vars);	
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_ERRORS_DIR, __FUNCTION__, TEMPLATE_NAME);
		}
		final public function error403()
		{
			header('HTTP/1.0 403 Forbidden', true, 403);
			$this->SetVars(array('pagetitle' => "Error 403 - Forbidden"));	
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_ERRORS_DIR, __FUNCTION__, TEMPLATE_NAME);
		}		
		#endregion
		
		#region Protected Methods
		final protected function render($viewDir, $viewName, $template='')
		{
			$this->beforeRender();
			$view = new View($this);
			$renderedView = (!empty($template)) ? $view->render(dirname(dirname(__DIR__)) . "/" . self::_TEMPLATES_PATH . "/" . $template . "/" . self::_LAYOUTS_DIR . "/" . $this->GetLayout() . "/header.php") : '';
			$renderedView .= $view->render($viewDir . DIRECTORY_SEPARATOR . $viewName . ".php");
			$renderedView .= (!empty($template)) ? $view->render(dirname(dirname(__DIR__)) . "/" . self::_TEMPLATES_PATH . "/" . $template . "/" . self::_LAYOUTS_DIR . "/" . $this->GetLayout() . "/footer.php") : '';
			$this->afterRender();
			return $renderedView;
		}
		
		// Callback functions - Intended to be overridden in child controllers
		protected function beforeAction()
		{
			new Language($this);
			$action = $this->GetAction();
			$this->SetVars(compact('action'));			
		}		
		
		protected function afterAction()
		{	
		}			
		
		protected function beforeRender()
		{	
		}
		
		protected function afterRender()
		{
		}
		#endregion
		
		#region Properties			
		final public function GetAction()
		{
			return $this->_action;
		}	
		
		final protected function SetDatabaseObject(array $login)
		{
			$this->_dbo = new Database($login);
		}
		final protected function GetDatabaseObject()
		{
			return $this->_dbo;
		}		
		
		final public function SetVars(array $vars)
		{
			$this->_vars = $vars + $this->_vars;
		}		
		final public function GetVars()
		{
			return $this->_vars;
		}		
		
		final public function GetData()
		{
			return $this->_data;
		}
		
		final public function GetParams()
		{
			return $this->_params;
		}
		
		final public function SetSession(array $vars)
		{
			$_SESSION = $vars + $_SESSION;
		}		
		final public function GetSession()
		{
			return $_SESSION;
		}		
		
		final public function SetHelpers(array $helpers)
		{
			$this->_helpers = $helpers;
		}
		final public function GetHelpers()
		{
			return $this->_helpers;
		}
		
		final protected function SetLayout($layout)
		{
			$this->_layout = $layout;
		}
		final protected function GetLayout()
		{
			return $this->_layout;
		}		
		#endregion
	}

?>