<?php

	namespace MediaConverterPro\app\Controllers;

	use MediaConverterPro\app\AppController;
	use MediaConverterPro\lib\YouTubeData;
	use MediaConverterPro\lib\Config;	
	use MediaConverterPro\lib\VideoConverter;
	use MediaConverterPro\lib\Core;	
	use MediaConverterPro\lib\FFmpeg;
	use MediaConverterPro\lib\Remote;
	use MediaConverterPro\lib\Cache;

	// Pages Controller class
	class PagesController extends AppController
	{	
		// Constants
		const _PAGES_DIR = 'pages';
		
		// Fields
		public $_converter = NULL;
		
		#region Callbacks
		protected function beforeAction()
		{
			parent::beforeAction();
			$this->_converter = new VideoConverter($this->_globalAppVars, $this->GetDatabaseObject());
			$this->SetVars(array('protocol' => Core::httpProtocol(), 'converter' => $this->_converter));
		}		
		#endregion

		#region Pages
		public function index()
		{
			$vars = $this->GetParams();
			$SearchTerm = (isset($vars['q'])) ? trim($vars['q']) : '';
			if (preg_match('/^((.+?)(\([a-zA-Z0-9_-]{11}\)))$/', $SearchTerm, $smatch) == 1)
			{
				// If this is video title link in search results, replace hyphens with spaces to better ensure results
				$SearchTerm = preg_replace('/-+/', " ", $smatch[2]) . $smatch[3];
			}
			$YouTube = (Config::_ENABLE_TOP_MUSIC_PER_COUNTRY) ? new YouTubeData($this->_converter) : null;
			$countryInfo = Core::detectCountryInfo();
			$cCode = (!empty($SearchTerm)) ? Config::_DEFAULT_COUNTRY : $countryInfo['cCode'];
			$videoInfo = (!is_null($YouTube)) ? array_slice($YouTube->VideoInfo($SearchTerm, $cCode), 0, '10') : [];
			$this->SetVars(compact('SearchTerm', 'videoInfo'));
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__, TEMPLATE_NAME);
		}
		
		public function result()
		{
			$vars = $this->GetParams();
			$SearchTerm = (isset($vars['q'])) ? trim($vars['q']) : '';
			$moreVideos = (isset($vars['moreVideos'])) ? trim($vars['moreVideos']) : '';
			$maxResults = (empty($moreVideos)) ? Config::_RESULTS_PER_PAGE : (int)$moreVideos;

			$countryInfo = Core::detectCountryInfo();
			$cCode = (!isset($vars['cCode'])) ? ((!empty($SearchTerm)) ? Config::_DEFAULT_COUNTRY : $countryInfo['cCode']) : trim($vars['cCode']);
			$Continent = (isset($vars['cCont'])) ? trim($vars['cCont']) : $countryInfo['Continent'];

			$YouTube = new YouTubeData($this->_converter);
			$videoInfo = array_slice($YouTube->VideoInfo($SearchTerm, $cCode), 0, $maxResults);
			$countries = Config::$_countries;
			
			$this->SetVars(compact('SearchTerm', 'countries', 'Continent', 'cCode', 'videoInfo', 'maxResults'));
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__);
		}
		
		public function grab()
		{
			$vars = $this->GetParams();
			$vidID = (isset($vars['vidID'])) ? trim($vars['vidID']) : '';
			$format = (isset($vars['format'])) ? trim($vars['format']) : '';
			$streams = (isset($vars['streams'])) ? trim($vars['streams']) : '';
			$api = (isset($vars['api'])) ? trim($vars['api']) : '';

			$controllerVars = $this->GetVars();
			$pageVars = (Core::checkVideoBlocked($vidID)) ? array('error' => $this->_globalAppVars['translations']['blocked_video']) : compact('api', 'streams') + $this->_converter->GenerateVideoDataForOutput($vidID, $format, $streams, $controllerVars['urlLang']);
			$this->SetVars($pageVars);
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__);
		}	
		
		public function download()
		{
			$fileBrand = (Config::_FILE_BRAND) ? ' ('. Config::_WEBSITE_DOMAIN .')' : '';
			$vars = $this->GetParams();
			//die(print_r($vars));
			
			if (isset($vars['token']))
			{
				$tokenData = explode("-", trim($vars['token']));
				if (count($tokenData) > 1)
				{
					$uid = $tokenData[1];
					if (isset($vars['stream'], $vars['vid'], $vars['name'], $vars['secureToken'])) 
					{
						$streams = trim($vars['stream']);
						$vidID = trim($vars['vid']);
						$vidName = trim($vars['name']);
						$secureToken = trim($vars['secureToken']);
						$stime = (isset($vars['stime'])) ? trim($vars['stime']) : '';
						$etime = (isset($vars['etime'])) ? trim($vars['etime']) : '';
						$highspeed = (isset($vars['highspeed'])) ? trim($vars['highspeed']) : 'no';
						
						$isMp3Track = $streams == "mp3" && preg_match('/(\.mp3)$/', $vidName) != 1;
						$isMergedTrack = $streams == "mergedstreams" && preg_match('/\w+:\w+/', trim($vars['token'])) != 1;
						
						$secureTokenParts = explode("-", $secureToken);
						if (count($secureTokenParts) == 2)
						{
							$secureToken = $secureTokenParts[0];
							$isJsonApi = (bool)$secureTokenParts[1];
							$dloadHash = preg_replace('/(-\d?)$/', "", $this->_converter->GenerateVideoDownloadHash($vidID, $isJsonApi));
							$secureDload = (function_exists("hash_equals")) ? hash_equals($dloadHash, $secureToken) : $dloadHash === $secureToken;
							if ($secureDload || $isMp3Track || $isMergedTrack)
							{
								//die('secure');
								$links = Cache::Cache(compact('vidID', 'uid', 'streams'));

								$streams = ($isMp3Track) ? ((isset(Config::$_itags['audiostreams'][$tokenData[0]])) ? "audiostreams" : "videos") : $streams;
								$streams = ($isMergedTrack) ? ((isset(Config::$_itags['videostreams'][$tokenData[0]])) ? "videostreams" : "audiostreams") : $streams;
								$tokenId = $tokenData[0] . "-" . $uid;
								if ($streams != "mergedstreams" && isset($links[$tokenId]))
								{
									$src = base64_decode($links[$tokenId]);
								}
								$currentIP = (isset($links['reqip'])) ? json_decode(base64_decode($links['reqip']), true) : array();

								switch ($streams)
								{
									case "mp3":						
										if (isset($src))
										{
											$srcItag = $tokenData[0];
											$dlsize = $tokenData[2];
											$quality = $tokenData[4];
											$vidDuration = $tokenData[3];
											$mp3NiceName = explode(".mp3", $vidName);				
											$mp3Name = urldecode(urldecode($mp3NiceName[0])) . " (" . $quality . " " . Config::$_mediaUnits['bitrate'] . ")" . $fileBrand . ".mp3";										

											$controllerVars = $this->GetVars();
											$requestUrlBase = $controllerVars['protocol'] . $_SERVER['HTTP_HOST'] . Config::_APPROOT . $controllerVars['urlLang'] . "@download/";
											$srcFormat = $tokenData[5];
											$srcSize = $tokenData[6];
											$requestUrlSuffix = "/" . $streams . "/" . $vidID . "/" . urlencode(urlencode(htmlspecialchars($mp3NiceName[0], ENT_QUOTES))) . ".";
											$src = $requestUrlBase . $srcItag . "-" . $uid . "-" . $srcFormat . "-" . $srcSize . $requestUrlSuffix . $srcFormat . "/" . implode("-", $secureTokenParts);											

											//die($src);
											FFmpeg::convertMP3($src, $mp3NiceName[0], $mp3Name, $dlsize, $quality, $vidDuration, $stime, $etime, $vidID, $this->_converter);
										}
										break;
									case "mergedstreams":
										$controllerVars = $this->GetVars();
										$requestUrlBase = $controllerVars['protocol'] . $_SERVER['HTTP_HOST'] . Config::_APPROOT . $controllerVars['urlLang'] . "@download/";
										$itags = explode(":", $tokenData[0]);
										$ftypes = explode(":", $tokenData[2]);
										$fsizes = explode(":", $tokenData[3]);
										$duration = $tokenData[4];
										$ftype = current($ftypes);
										$videoNiceName = explode("." . $ftype, $vidName);	
										$requestUrlSuffix = "/" . $streams . "/" . $vidID . "/" . urlencode(urlencode(htmlspecialchars($videoNiceName[0], ENT_QUOTES))) . ".";
										$videoSource = $requestUrlBase . current($itags) . "-" . $uid . "-" . $ftype . "-" . current($fsizes) . $requestUrlSuffix . $ftype . "/" . implode("-", $secureTokenParts);
										$audioSource = $requestUrlBase . end($itags) . "-" . $uid . "-" . end($ftypes) . "-" . end($fsizes) . $requestUrlSuffix . end($ftypes) . "/" . implode("-", $secureTokenParts);								
										$vidName = urldecode(urldecode($videoNiceName[0])) . $fileBrand . "." . $ftype;	
										FFmpeg::mergeVideo($videoSource, $audioSource, $ftype, $vidName, $duration);
										break;
									default:
										if (isset($src))
										{								
											$ftype = $tokenData[2];
											$fsize = $tokenData[3];	
											$videoNiceName = explode("." . $ftype, $vidName);			
											$vidName = urldecode(urldecode($videoNiceName[0])) . $fileBrand . "." . $ftype;	
											$outputInline = $isMp3Track || $isMergedTrack;
											$remote = $this->_globalAppVars['AntiCaptcha']['Remote'];						
											if (Config::_ENABLE_CHUNKED_DOWNLOAD)
											{
												$remote::chunkedDownload($src, $vidName, $ftype, $fsize, $currentIP, $this->_converter, $outputInline);
											}
											else
											{
												$remote::download($src, $vidName, $ftype, $fsize, $currentIP, $this->_converter, $outputInline);
											}
										}
								}
							}
							else
							{
								die('Download is not authorized!');
							}
						}
						else
						{
							die('Download is not authorized!');
						}
					}	
				}
			}		
		}
		
		public function api()
		{
			$vars = $this->GetParams();
			$api = (isset($vars['api'])) ? trim($vars['api']) : '';
			$extractor = (isset($vars['extractor'])) ? trim($vars['extractor']) : '';
			$query = (isset($vars['query'])) ? trim($vars['query']) : '';
			$vidID = (isset($vars['vidID'])) ? trim($vars['vidID']) : '';
			$format = (isset($vars['format'])) ? trim($vars['format']) : '';			
			$streams = $format; 
			$format = ($format != 'mp3') ? (($format != 'mergedstreams') ? 'mp4' : 'video') : $format;
			
			$this->SetVars(compact('api', 'vidID', 'format', 'streams'));
			if ($api == 'json' || $api == 'search')
			{
				if ($api == 'search')
				{
					$YouTube = new YouTubeData($this->_converter);
					$videos = $YouTube->VideoInfo($query, Config::_DEFAULT_COUNTRY);
					$this->SetVars(compact('videos'));
				}
				else
				{
					$controllerVars = $this->GetVars();
					$pageVars = (Core::checkVideoBlocked($vidID)) ? array('vidTitle' => '', 'error' => $this->_globalAppVars['translations']['blocked_video']) : $this->_converter->GenerateVideoDataForOutput($vidID, $format, $streams, $controllerVars['urlLang'], $api);
					$this->SetVars($pageVars);
				}
				if (!empty(Config::$_apiAllowedDomains[$api]))
				{
					$referer = (isset($_SERVER['HTTP_REFERER'])) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : '';
					$protocol = (!empty($referer)) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME) : '';
					foreach (Config::$_apiAllowedDomains[$api] as $domain)
					{
						if (preg_match('/(' . preg_quote($domain, '/') . ')$/', $referer) == 1)
						{
							header('Access-Control-Allow-Origin: '. $protocol . '://' . $referer);
						}
					}
				}
				else
				{
					header('Access-Control-Allow-Origin: *');
				}
			}
			
			$template = ($api == 'button') ? TEMPLATE_NAME : '';
			$this->SetLayout('api');
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__, $template);
		}
		
		public function shareimg()
		{
			$vars = $this->GetParams();
			$img = (isset($vars['unnamed'][0])) ? trim($vars['unnamed'][0]) : '';
			$validReq = !empty($img) && preg_match('/share-(.+?)\.png/', $img, $matches) == 1;
			if (!$validReq)
			{
				return $this->error404();				
			}
			$vid = $matches[1];
			$this->SetVars(compact('vid'));
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__);
		}	
		
		public function sitemap()
		{
			$vars = $this->GetParams();
			$pages = $videoInfo = [];
			$ccode = (isset($vars['unnamed'][0])) ? trim($vars['unnamed'][0]) : '';
			$validReq = !empty($ccode);
			if (!$validReq)
			{
				return $this->error404();				
			}
			$pages = ($ccode == "static" && count(Config::$_sitemapStaticPages) > 0) ? Config::$_sitemapStaticPages : $pages;
			if ($ccode != "static" && Config::_ENABLE_DYNAMIC_SITEMAPS)
			{
				$YouTube = new YouTubeData($this->_converter);
				$videoInfo = $YouTube->VideoInfo('', trim($ccode));
			}
			if (empty($pages) && empty($videoInfo))
			{
				return $this->error404();
			}
			$timeUTC = date('Y-m-d') . 'T' . date('H:i:s') . '+00:00';
			$this->SetVars(compact('pages', 'videoInfo', 'timeUTC'));
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__);
		}	
		
		public function sitemapindex()
		{
			$hasStaticPages = count(Config::$_sitemapStaticPages) > 0;
			$countryCodes = array();
			if (Config::_ENABLE_DYNAMIC_SITEMAPS)
			{
				foreach (Config::$_countries as $group => $countries)
				{
					$countryCodes = array_merge($countryCodes, array_keys($countries));
				}
			}
			if (!$hasStaticPages && empty($countryCodes))
			{
				return $this->error404();
			}
			$timeUTC = date('Y-m-d') . 'T' . date('H:i:s') . '+00:00';
			$this->SetVars(compact('hasStaticPages', 'countryCodes', 'timeUTC'));
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__);			
		}

		public function faq()
		{
			$this->SetVars(array('uppercase' => true));
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__, TEMPLATE_NAME);
		}
		
		public function developers()
		{
			$pagetitle = 'API';			
			$vars = $this->GetParams();
			$isPreview = isset($vars['unnamed'][0]) && $vars['unnamed'][0] == "preview";
			$referer = (isset($_SERVER['HTTP_REFERER'])) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) : '';
			$apiAllowedHere = empty(Config::$_apiAllowedDomains['button']) || preg_match('/((' . str_replace('.', "\.", implode(")|(", Config::$_apiAllowedDomains['button'])) . '))$/', $referer) == 1;
			$this->SetVars(compact('pagetitle', 'isPreview', 'apiAllowedHere'));
			return (!empty(Config::$_apiAllowedDomains['search']) && !empty(Config::$_apiAllowedDomains['json']) && !empty(Config::$_apiAllowedDomains['button']) && !$isPreview) ? $this->error404() : $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__, TEMPLATE_NAME);
		}
		
		public function contact()
		{
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__, TEMPLATE_NAME);
		}
		
		public function captcha()
		{
			$captchaResponse = '';
			$data = $this->GetData();
			if (isset($data['response']))
			{
				$captchaToken = $data['response'];
				$captchaSecret = Config::_RECAPTCHA_SECRET;
				$captchaResponse = $this->FileGetContents('https://www.google.com/recaptcha/api/siteverify', 'response=' . $captchaToken . '&secret=' . $captchaSecret);
			}
			$this->SetVars(compact('captchaResponse'));
			return $this->render(dirname(__DIR__) . "/" . self::_VIEWS_PATH . "/" . self::_PAGES_DIR, __FUNCTION__);			
		}		
		#endregion Pages
	}

?>
