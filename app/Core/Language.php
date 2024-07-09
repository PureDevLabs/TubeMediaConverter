<?php 

	namespace MediaConverterPro\app\Core;

	use MediaConverterPro\lib\Config;

	class Language
	{
		// Private Fields
		private $_controller;

	    // Public Methods
	    public function __construct(Controller &$controller)
	    {
	    	$this->_controller = $controller;
	    	$params = $controller->GetParams();
	    	$session = $controller->GetSession();
	    	$oldLang = (isset($session['lang'])) ? $session['lang'] : "";
	    	$langs = $this->GetLanguages();
	    	$lang = $this->GetLanguage($langs);
	    	$translations = $this->GetTranslations($lang, $oldLang);
	    	$urlLang = ($lang == Config::_DEFAULT_LANGUAGE || preg_match('/((sitemapindex\.xml)|(@sitemap\/.+))$/', $_SERVER['REQUEST_URI']) == 1) ? '' : $lang . '/';
	    	
	    	$urlPathEnd = preg_replace('/^(' . preg_quote(Config::_APPROOT, '/') . ')/', "", $_SERVER['REQUEST_URI']);
	    	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($urlLang) && preg_match('/^((' . implode("|", array_keys($langs)) . ')\/)/', $urlPathEnd) != 1)
	    	{
	    		header('Location: ' . Config::_APPROOT . $urlLang . $urlPathEnd);
	    	}
			if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $urlPathEnd == Config::_DEFAULT_LANGUAGE . "/")
			{
				header('Location: ' . Config::_APPROOT, true, 301);
			}
	    	
	    	$langInfo = array('langObj' => $this) + compact('langs', 'lang', 'translations', 'urlLang');	    	
	    	if ($lang != $oldLang || !isset($session['langs']))
	    	{
	    		$controller->SetSession($langInfo);
	    	}
	    	$controller->SetVars($langInfo);
	    }
	    
	    public function ReplacePlaceholders($translation, array $placeholderData)
	    {
	    	$count = -1;
	    	$newTranslation = preg_replace_callback('/\{\{[^\}]+\}\}/', function($match) use (&$placeholderData, &$count) { 
	    		return $placeholderData[++$count]; 
	    	}, $translation);
	    	return $newTranslation;
	    }
	    
	    // Private Properties
		private function GetLanguages()
		{
	        $session = $this->_controller->GetSession();
	        $languages = (isset($session['langs']) && !empty($session['langs'])) ? $session['langs'] : array();		
	        if (empty($languages))
	        {
				// Get langauges
				$file = Controller::_LANGUAGES_PATH . '/index.php';
				if (is_readable($file)) 
				{
					$languages = include $file;
				} 	
			}
			return $languages;
		}	    
	    
		private function GetLanguage(array $langs)
		{
			$session = $this->_controller->GetSession();
			$params = $this->_controller->GetParams();
			$langCode = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('/[a-z]+/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $langCodeMatch) == 1) ? $langCodeMatch[0] : '';
			$vars = (isset($params['lang'])) ? $params : $session;
			$lang = (isset($vars['lang'])) ? trim($vars['lang']) : $langCode;
			return (!empty($lang) && in_array($lang, array_keys($langs))) ? $lang : Config::_DEFAULT_LANGUAGE;
		}
		
	    private function GetTranslations($lang, $oldLang)
	    {
	        $session = $this->_controller->GetSession();
	        $translations = ($lang == $oldLang && isset($session['translations']) && !empty($session['translations'])) ? $session['translations'] : array();
	        $defaultTranslations = array();	     
	        if (empty($translations))
	        {
				// $lang translations
				$file = Controller::_LANGUAGES_PATH . '/' . $lang . '.php';
				if (is_readable($file)) 
				{
					$translations = include $file;
				} 

				// Default language translations
				$file = Controller::_LANGUAGES_PATH . '/' . Config::_DEFAULT_LANGUAGE . '.php';
				if (is_readable($file))
				{
					$defaultTranslations = include $file;
				}

				// Add missing translations, if any
				$missingTranslations = array_diff_key($defaultTranslations, $translations);
				if (!empty($missingTranslations))
				{
					$translations += $missingTranslations;
				}
			}	
	        return $translations;
	    }
	}
?>