<?php

	namespace MediaConverterPro\app\Core;

	abstract class Helper
	{
		// Common Fields
		protected $_view;
		private $_session = array();
		
		#region "Shared" Helper Methods
		public function __construct($view)
		{
			$this->_view = $view;
			$this->_session = $view->_controller->GetSession();
		}
		#endregion
		
		#region "Shared" Helper Properties
		protected function GetSession()
		{
			return $this->_session;
		}
		#endregion
	}
?>