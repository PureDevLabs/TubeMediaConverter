<?php

	namespace MediaConverterPro\lib\extractors;

	use \MediaConverterPro\lib\VideoConverter;
	use \MediaConverterPro\lib\Core;
	use \MediaConverterPro\lib\Config;

	// Extraction Base Class
	abstract class Extractor
	{
		// Common Fields
		protected $_converter;
		protected $_isCurlError = false;
		protected $_headers = array();
		protected $_videoWebpageUrl = '';
		protected $_videoWebpage = '';
		protected $_reqParams = array();

		// Common Public Methods
		function __construct(VideoConverter $converter)
		{
			$this->_converter = $converter;
			$dbo = $converter->GetDbo();
		}

		function ReturnConfig($setting)
		{
			$config = NULL;
			$converter = $this->GetConverter();
			$vidHosts = $converter->GetVideoHosts();
			$currentVidHost = ($converter->GetCurrentVidHost() == '') ? current(current($vidHosts)) : $converter->GetCurrentVidHost();			
			foreach ($vidHosts as $host)
			{
				if ($host['name'] == $currentVidHost && isset($host[$setting]))
				{
					$config = $host[$setting];
					break;
				}
			}
			return $config;
		}
		
		function CheckIp($ip)
		{		
			$noWebpageUrl = empty($this->_videoWebpageUrl);
			$url = ($noWebpageUrl) ? current($this->ReturnConfig('url_root')) . $this->ReturnConfig('url_example_suffix') : $this->_videoWebpageUrl;

			$ipReqResult = array("isCurlErr" => false, "isBanned" => false);
			$this->_headers = array();
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			if ($noWebpageUrl)
			{
				curl_setopt($ch, CURLOPT_NOBODY, true);
				curl_setopt($ch, CURLOPT_HEADER, true);
			}
			else
			{
				curl_setopt($ch, CURLOPT_HEADER, 0);
			}
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, Config::_REQUEST_USER_AGENT);
			
			// Set IP options
			$isProxy = !empty($ip['port']) || !empty($ip['proxy_user']) || !empty($ip['proxy_pass']);
			curl_setopt($ch, CURLOPT_REFERER, '');
			if ($isProxy)
			{
				curl_setopt($ch, CURLOPT_PROXY, $ip['ip'] . ":" . $ip['port']);
				if (!empty($ip['proxy_user']) && !empty($ip['proxy_pass']))
				{
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $ip['proxy_user'] . ":" . $ip['proxy_pass']);
				}
				if (Config::_ENABLE_TOR_PROXY)
				{
					curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
				}
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::_IP_CONNECT_TIMEOUT);
				curl_setopt($ch, CURLOPT_TIMEOUT, Config::_IP_REQUEST_TIMEOUT);
			}
			else
			{
				curl_setopt($ch, CURLOPT_INTERFACE, $ip['ip']);
				curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);	
			}
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

			if (!empty($this->_reqParams))
			{
				extract($this->_reqParams);
				if (!empty($postData))
				{
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);					
				}
				if (!empty($reqHeaders))
				{
					curl_setopt($ch, CURLOPT_HTTPHEADER, $reqHeaders);
				}
			}
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'AppendHttpHeader'));
			$output = curl_exec($ch);
			//die(print_r(curl_getinfo($ch)));			
			$ipReqResult['isCurlErr'] = curl_errno($ch) != 0;
			if (curl_errno($ch) == 0)
			{
				$this->_videoWebpage = (!$noWebpageUrl) ? $output : $this->_videoWebpage;
				if (!$noWebpageUrl && !empty($output))
				{
					$ipReqResult['isBanned'] = $this->ReturnConfig('name') == "YouTube" && preg_match(YouTube::_CAPTCHA_PATTERN, $output) == 1;
				}
				$info = curl_getinfo($ch);
				//die(print_r($info));
				$ipReqResult['isBanned'] = (!$ipReqResult['isBanned']) ? $info['http_code'] == '429' || $info['http_code'] == '400' : $ipReqResult['isBanned'];
			}
			curl_close($ch);
			return $ipReqResult;
		}
		
		function CheckDownloadUrl($url, $signature)
		{
			$retVal = array('isValid' => false, 'filesize' => 0);
			$converter = $this->GetConverter();		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, Config::_REQUEST_USER_AGENT);
			if (Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && $converter->GetCurrentVidHost() == "YouTube")
			{
				if ($converter->GetOutgoingIP() == array()) $converter->SetOutgoingIP();
				$currentIP = $converter->GetOutgoingIP();
				$isProxy = !empty($currentIP['port']) || !empty($currentIP['proxy_user']) || !empty($currentIP['proxy_pass']);
				curl_setopt($ch, CURLOPT_REFERER, '');
				if ($isProxy)
				{
					curl_setopt($ch, CURLOPT_PROXY, $currentIP['ip'] . ":" . $currentIP['port']);
					if (!empty($currentIP['proxy_user']) && !empty($currentIP['proxy_pass']))
					{
						curl_setopt($ch, CURLOPT_PROXYUSERPWD, $currentIP['proxy_user'] . ":" . $currentIP['proxy_pass']);
					}
					if (Config::_ENABLE_TOR_PROXY)
					{
						curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
					}					
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::_IP_CONNECT_TIMEOUT);
					curl_setopt($ch, CURLOPT_TIMEOUT, Config::_IP_REQUEST_TIMEOUT);
				}
				else
				{
					curl_setopt($ch, CURLOPT_INTERFACE, $currentIP['ip']);
					if (Config::_REQUEST_IP_VERSION != 4)
					{
						curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
					}
				}
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);			
			}			
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			if (Config::_REQUEST_IP_VERSION != -1)
			{
				curl_setopt($ch, CURLOPT_IPRESOLVE, constant("CURL_IPRESOLVE_V" . (string)Config::_REQUEST_IP_VERSION));
			}
			$headers = curl_exec($ch);
			if (curl_errno($ch) == 0)
			{
				$info = curl_getinfo($ch);
				//die(print_r($info));
				$retVal['filesize'] = (int)$info['download_content_length'];
				if (method_exists($this, 'GetCypherUsed') && $this->GetCypherUsed() && $info['http_code'] == '403')
				{
					$this->UpdateSoftwareXml(compact('signature'));
				}
				else
				{
					$retVal['isValid'] = $info['http_code'] != '404' && $info['http_code'] != '403';
				}
			}
			curl_close($ch);
			return $retVal;
		}		

		// Common Protected Methods
		protected function FileGetContents($url, $postData='', $reqHeaders=array())
		{
			$urlRoot = preg_replace('/^(https?)/', "https", current($this->ReturnConfig('url_root')));
			$vidUrlPattern = '/^((' . preg_quote($urlRoot, "/") . ')|(' . preg_quote(YouTube::_PLAYER_API_URL, "/") . ')|(' . preg_quote(YouTube::_SEARCH_API_URL, "/") . ')|(' . preg_quote(YouTube::_SEARCH_URL_PREFIX, "/") . '))/';
			$vidUrlPattern2 = '/^(' . preg_quote(YouTube::_HOMEPAGE_URL, "/") . ')$/';
			$this->_videoWebpageUrl = (preg_match($vidUrlPattern, $url, $urlMatch) == 1 || preg_match($vidUrlPattern2, $url) == 1) ? $url : '';
			$isSearchScrapeReq = (isset($urlMatch[4]) && !empty($urlMatch[4])) || (isset($urlMatch[5]) && !empty($urlMatch[5]));

			$this->_reqParams = compact('postData', 'reqHeaders');
			$converter = $this->GetConverter();
			$file_contents = '';
			$tries = 0;
			do
			{
				$this->_headers = array();
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_USERAGENT, Config::_REQUEST_USER_AGENT);
				if ((Config::_ENABLE_PROXY_SUPPORT || (Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && !$isSearchScrapeReq) || (Config::_ENABLE_IP_ROTATION_FOR_SEARCH && Config::_ENABLE_SEARCH_SCRAPING && $isSearchScrapeReq)) && $converter->GetCurrentVidHost() == "YouTube")
				{
					$dbTableName = '_DB_IPS_TABLE' . (($isSearchScrapeReq) ? '2' : '');
					if (!Config::_ENABLE_PROXY_SUPPORT && ($converter->GetOutgoingIP() == array() || $tries > 0)) $converter->SetOutgoingIP($dbTableName);
					if (!empty($this->_videoWebpageUrl) && !empty($this->_videoWebpage))
					{					
						$videoWebpage = $this->_videoWebpage;
						$this->_videoWebpage = '';
						return $videoWebpage;
					}
					$currentIP = (Config::_ENABLE_PROXY_SUPPORT) ? $this->ParseProxyStr(Config::_HTTP_PROXY) : $converter->GetOutgoingIP();
					$isProxy = !empty($currentIP['port']) || !empty($currentIP['proxy_user']) || !empty($currentIP['proxy_pass']);
					curl_setopt($ch, CURLOPT_REFERER, '');
					if ($isProxy)
					{
						curl_setopt($ch, CURLOPT_PROXY, $currentIP['ip'] . ":" . $currentIP['port']);
						if (!empty($currentIP['proxy_user']) && !empty($currentIP['proxy_pass']))
						{
							curl_setopt($ch, CURLOPT_PROXYUSERPWD, $currentIP['proxy_user'] . ":" . $currentIP['proxy_pass']);
						}
						if (Config::_ENABLE_TOR_PROXY)
						{
							curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
						}						
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::_IP_CONNECT_TIMEOUT);
						curl_setopt($ch, CURLOPT_TIMEOUT, Config::_IP_REQUEST_TIMEOUT);
					}
					else
					{
						curl_setopt($ch, CURLOPT_INTERFACE, $currentIP['ip']);
						if (Config::_REQUEST_IP_VERSION != 4)
						{
							curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
						}	
					}
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				}
				if (!empty($postData))
				{
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);					
				}
				if (!empty($reqHeaders))
				{
					curl_setopt($ch, CURLOPT_HTTPHEADER, $reqHeaders);
				}				
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				if (!Config::_ENABLE_PROXY_SUPPORT && Config::_REQUEST_IP_VERSION != -1)
				{
					curl_setopt($ch, CURLOPT_IPRESOLVE, constant("CURL_IPRESOLVE_V" . (string)Config::_REQUEST_IP_VERSION));
				}
				curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'AppendHttpHeader'));
				$file_contents = curl_exec($ch);
				$this->_isCurlError = curl_errno($ch) != 0;
				$curlInfo = curl_getinfo($ch);
				if (curl_errno($ch) == 0)
				{
					if ($converter->GetCurrentVidHost() == "YouTube" && ($curlInfo['http_code'] == '302' || $curlInfo['http_code'] == '301'))
					{
						if (isset($curlInfo['redirect_url']) && !empty($curlInfo['redirect_url']))
						{
							$file_contents = $this->FileGetContents($curlInfo['redirect_url']);
						}
					}
				}
				curl_close($ch);
				$tries++;
			}
			while ((Config::_ENABLE_PROXY_SUPPORT || (Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && !$isSearchScrapeReq) || (Config::_ENABLE_IP_ROTATION_FOR_SEARCH && Config::_ENABLE_SEARCH_SCRAPING && $isSearchScrapeReq)) && $converter->GetCurrentVidHost() == "YouTube" && $tries < Config::_MAX_CURL_TRIES && ($this->_isCurlError || $curlInfo['http_code'] == '403' || $curlInfo['http_code'] == '429' || $curlInfo['http_code'] == '400' || empty($file_contents) || preg_match(YouTube::_CAPTCHA_PATTERN, $file_contents) == 1 || (preg_match(YouTube::_VID_URL_PATTERN, $url) == 1 && preg_match(YouTube::_AGE_GATE_PATTERN, $file_contents) != 1 && preg_match(YouTube::_VID_INFO_PATTERN2, $file_contents) != 1)));

			return $file_contents;
		}
		
		protected function AppendHttpHeader($ch, $headr)
		{
			$this->_headers[] = $headr;
			return strlen($headr);				
		}
		
		protected function ParseProxyStr($proxyStr)
		{
			$parsed = ['ip' => '', 'port' => '', 'proxy_user' => '', 'proxy_pass' => ''];
			if (preg_match('/^((https?:\/\/)(.+))$/', $proxyStr, $matches) == 1)
			{
				$strParts = (strpos($matches[3], "@") !== false) ? explode("@", $matches[3]) : [$matches[3]];
				$allParts = [];
				foreach ($strParts as $part)
				{
					$pieces = explode(":", $part);
					$allParts = (count($pieces) == 2) ? array_merge($allParts, $pieces) : $allParts;
				}
				$numParts = count($allParts);
				switch ($numParts)
				{
					case 2:
						$parsed = ['ip' => $allParts[0], 'port' => $allParts[1], 'proxy_user' => '', 'proxy_pass' => ''];
						break;
					case 4:
						$parsed = ['ip' => $allParts[2], 'port' => $allParts[3], 'proxy_user' => $allParts[0], 'proxy_pass' => $allParts[1]];
						break
				}
			}
			return $parsed;
		}

		// Force child classes to define these methods
		abstract public function RetrieveVidInfo($vidUrl);
		abstract public function ExtractVidSourceUrls();

		// Common Properties
		protected function GetConverter()
		{
			return $this->_converter;
		}
		
		public function GetVideoWebpage()
		{
			return $this->_videoWebpage;
		}
		
		protected function GetStoreDir()
		{
			return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'store' . DIRECTORY_SEPARATOR;
		}		
	}
?>