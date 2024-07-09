<?php 

	namespace MediaConverterPro\app\Core;
	
	use MediaConverterPro\app\Controllers\PagesController;
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\Core;
	use MediaConverterPro\lib\YouTubeData;

	class Router
	{
		// Private fields
		private static $_routePatterns = array(
			'homepage' => '/^(\/)$/',
			'language' => '/^((%languages%)\/)/',
			'download' => '/^((@(download))(\/([^\/]*)){5,7})$/',
			'api' => '/^((@(api))(\/([^\/]*)){3,})$/',
			'action' => '/^((@([^\/]+))(\/([^\/]+))*)$/',
			'sitemapindex' => '/^((sitemapindex)\.xml)$/',
			'yt_search' => '/^(watch\?v=([^&]+))/',
			'search' => '/^(.*)$/'
		);
		private static $_action = "index";
		private static $_protectedActions = array('grab', 'result');
		private static $_apiActions = array('api', 'grab', 'download');
		private static $_langs = array();
		
		#region Public Methods
		public static function dispatch($request)
		{
			self::SetLangs();
			if (Config::_WEBSITE_INTERFACE == 'hybrid' && (empty(Config::$_apiAllowedDomains['search']) || empty(Config::$_apiAllowedDomains['json']) || empty(Config::$_apiAllowedDomains['button']))) 
			{
				self::$_action = 'developers';
			}
			self::buildRoute($request);
			$action = (Config::_WEBSITE_INTERFACE == 'api' && !in_array(self::$_action, self::$_apiActions)) ? 'error403' : self::$_action;
			$PagesController = new PagesController($action);
			$session = $PagesController->GetSession();			
			echo (method_exists($PagesController, $action)) ? ((in_array($action, self::$_protectedActions) && (!isset($_GET['appSecretToken']) || $_GET['appSecretToken'] != hash('sha256', $session['appSecretToken'] . APP_SECRET_KEY))) ? $PagesController->error403() : $PagesController->$action()) : $PagesController->error404();
		}
		#endregion
		
		#region Private Methods
		private static function buildRoute($request)
		{
			$referer = (isset($_SERVER['HTTP_REFERER'])) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : '';
			foreach (self::$_routePatterns as $reqType => $pattern)
			{
				extract(self::filterRouteParams(compact('request', 'reqType', 'pattern')));
				if (preg_match($pattern, $request, $matches) == 1)
				{
					switch ($reqType)
					{
						case 'homepage':
							// Use default action
							break;						
						case 'language':
							$_GET['lang'] = $matches[2];
							$request = preg_replace($pattern, '', $request);
							$request = (empty($request)) ? '/' : $request;
							self::buildRoute($request);
							break;
						case 'download':
							self::$_action = $matches[3];
							$paramsStr = preg_replace('/^(' . preg_quote($matches[2], '/') . ')/', "", $request);
							$paramsArr = preg_split('/\//', $paramsStr, -1, PREG_SPLIT_NO_EMPTY);
							$_GET += array('token' => $paramsArr[0], 'stream' => $paramsArr[1], 'vid' => $paramsArr[2]);
							$numParams = count($paramsArr);
							$_GET += ($numParams != 5) ? (($numParams != 7) ? array() : array('stime' => $paramsArr[3], 'etime' => $paramsArr[4], 'name' => $paramsArr[5], 'secureToken' => $paramsArr[6])) : array('name' => $paramsArr[3], 'secureToken' => $paramsArr[4]);
							break;	
						case 'api':
							self::$_action = $matches[3];
							$paramsStr = preg_replace('/^(' . preg_quote($matches[2], '/') . ')/', "", $request);
							$paramsArr = preg_split('/\//', $paramsStr, -1, PREG_SPLIT_NO_EMPTY);
							$validApiReq = false;
							if ($paramsArr[0] == 'json' || $paramsArr[0] == 'button' || $paramsArr[0] == 'search')
							{
								$_GET += array('api' => $paramsArr[0]);
								$_GET += ($paramsArr[0] == 'search') ? array('extractor' => $paramsArr[1], 'query' => preg_replace('/^(https?:\/)/', "$1/", implode("/", array_slice($paramsArr, 2))) . ((isset($_GET['v'])) ? '?v=' . $_GET['v'] : '')) : array('format' => $paramsArr[1], 'vidID' => $paramsArr[2]);
								$validApiReq = empty(Config::$_apiAllowedDomains[$paramsArr[0]]);
								if (!$validApiReq)
								{
									$refererIP = Core::refererIP();
									foreach (Config::$_apiAllowedDomains[$paramsArr[0]] as $domain)
									{
										if (preg_match('/(' . preg_quote($domain, '/') . ')$/', $referer) == 1 || $domain == $refererIP)
										{
											$validApiReq = true;
											break;
										}
									}
								}
							}
							self::$_action = (!$validApiReq) ? 'error403' : self::$_action;
							break;							
						case 'action':
							self::$_action = $matches[3];
							if (self::$_action == "api" || (in_array(self::$_action, self::$_protectedActions) && $referer != $_SERVER['HTTP_HOST']))
							{
								self::$_action = 'error403';
							}
							else
							{
								$paramsStr = preg_replace('/^(' . preg_quote($matches[2], '/') . ')/', "", $request);
								$_GET['unnamed'] = preg_split('/\//', $paramsStr, -1, PREG_SPLIT_NO_EMPTY);
							}
							break;
						case 'sitemapindex':
							self::$_action = $matches[2];
							break;
						case 'yt_search':
							$_GET['q'] = $matches[2];
							self::$_action = (!Config::_ENABLE_SEARCH_LINKS) ? 'error404' : self::$_action;
							break;
						case 'search':
							if (!isset($_GET['q']) && !(isset($_GET['lang']) && $_GET['lang'] == $matches[1]))
							{
								$_GET['q'] = $matches[1];	
							}
							self::$_action = (!Config::_ENABLE_VIDEO_LINKS && preg_match(YouTubeData::_VID_TITLE_URL_PATTERN, urldecode(trim($_GET['q']))) == 1) ? 'error404' : self::$_action;
							self::$_action = (!Config::_ENABLE_SEARCH_LINKS && preg_match(YouTubeData::_VID_TITLE_URL_PATTERN, urldecode(trim($_GET['q']))) != 1) ? 'error404' : self::$_action;
							break;							
					}
					break;
				}
			}
		}
		
		private static function filterRouteParams(array $routeParams)
		{
			extract($routeParams);
			$request = ($reqType == 'search' && preg_match('/^((https?:\/)([^\/])(.+))$/', $request, $match) == 1) ? $match[2] . '/' . $match[3] . $match[4] : $request;
			$request = ($reqType == 'search' && isset($_GET['list']) && !isset($_GET['v'])) ? $request . "?list=" . $_GET['list'] : $request;
			$request = ($reqType == 'yt_search' && isset($_GET['v'])) ? $request . "?v=" . $_GET['v'] : $request;
			$pattern = ($reqType == 'language') ? preg_replace('/%languages%/', implode("|", array_keys(self::$_langs)), $pattern) : $pattern;
			return compact('request', 'reqType', 'pattern');
		}
		#endregion
		
		#region Private Properties
		private static function SetLangs()
		{
			self::$_langs = include dirname(__DIR__) . '/Languages/index.php';		
		}
		#endregion
	}
?>