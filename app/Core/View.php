<?php

	namespace MediaConverterPro\app\Core;

	class View
	{
		// Public Fields
		public $_controller;

		#region Public Methods
		public function __construct($controller)
		{
			$this->_controller = $controller;
			$this->LoadHelpers();
		}
		
		public function render($viewPath)
		{	
			extract($this->_controller->GetVars());  // Custom variables available in view
			$data = $this->_controller->GetData();  // POST variables available in view
			$params = $this->_controller->GetParams();  // GET variables available in view
			$session = $this->_controller->GetSession();  // SESSION variables available in view
			ob_start();
			include $viewPath;
			return ob_get_clean();
		}
		
		public function element($name, array $vars=array())
		{
			$this->setVars($vars);
			return $this->render(dirname(__DIR__) . "/" . Controller::_VIEWS_PATH . "/" . Controller::_ELEMENTS_DIR . "/" . $name . ".php");			
		}
		
		public function importCSS(array $CSSlist) 
		{
			$cssFiles = array();
			foreach ($CSSlist as $css) 
			{
				$cssFiles[] = '<link type="text/css" href="' . WEBROOT . Controller::_TEMPLATES_PATH . '/' . TEMPLATE_NAME . '/' . $css . '" rel="stylesheet">'; 
			}
			return implode("\n\t", $cssFiles) . "\n";  
		}

		public function importJS(array $JSlist)
		{
			$jsFiles = array();
			$count = -1;
			foreach ($JSlist as $jsSrc => $jsAttr)
			{
				$scriptID = str_replace(".", "-", trim(strrchr((string)$jsSrc, "/"), "/"));
				$templatePath = WEBROOT . Controller::_TEMPLATES_PATH . '/' . TEMPLATE_NAME . '/';
				$jsFiles[++$count] = '<script type="text/javascript" src="' . $templatePath . (string)$jsSrc . '?v='. mt_rand() .'" data-path="' . $templatePath . '" id="' . $scriptID . '"';
				if (is_array($jsAttr) && !empty($jsAttr))
				{
					foreach ($jsAttr as $attr) $jsFiles[$count] .= ' ' . $attr;
				}
				$jsFiles[$count] .= '></script>';
			}
			return implode("\n\t", $jsFiles) . "\n";
		}
		#endregion
		
		#region Private Methods
		private function LoadHelpers()
		{
			$helpers = $this->_controller->GetHelpers();
			if (!empty($helpers))
			{
				foreach ($helpers as $helper)
				{
					if (!isset($this->{$helper}))
					{
						$className = current(explode("Core", __NAMESPACE__)) . str_replace("/", "\\", Controller::_HELPERS_PATH) . "\\" . $helper;
						$this->{$helper} = new $className($this);
					}
				}
			}		
		}
		#endregion
		
		#region Properties	
		public function setVars(array $vars)
		{
			$this->_controller->SetVars($vars);
		}	
		#endregion
	}

?>