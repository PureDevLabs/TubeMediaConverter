<?php

	namespace MediaConverterPro\lib\extractors;
	
	use \MediaConverterPro\lib\Config;
	use \MediaConverterPro\vendors\RakePhpPlus\src\RakePlus;

	// YouTube Extractor Class
	class YouTube extends Extractor
	{
		// Constants
		const _VID_URL_PATTERN = '/(watch\?v=[a-zA-Z0-9_-]+)$/';
		const _AGE_GATE_PATTERN = '/("og:restrictions:age" content="18\+")|("18\+" property="og:restrictions:age")/';
		const _VID_INFO_PATTERN = '/;ytplayer\.config\s*=\s*({.+?});ytplayer/s';
		const _VID_INFO_PATTERN2 = '/ytInitialPlayerResponse\s*=\s*({.+?})\s*;(?!\S*?")/';
		const _VID_PLAYER_PATTERN = '/((src="([^"]*player[^"]*\.js)"[^>]*><\/script>)|("(jsUrl|PLAYER_JS_URL)"\s*:\s*"([^"]+)"))/is';
		const _CAPTCHA_PATTERN = '/^((<form)(.+?)(das_captcha)(.+?)(<\/form>))$/msi';
		const _VID_SEARCH_PATTERN = '/var ytInitialData\s*=\s*({.+?})\s*;/';
		const _VID_URL_PREFIX = 'https://www.youtube.com/watch?v=';
		const _SEARCH_URL_PREFIX = 'https://www.youtube.com/results?q=';
		const _PLAYER_API_URL = 'https://www.youtube.com/youtubei/v1/player';
		const _SEARCH_API_URL = 'https://www.youtube.com/youtubei/v1/search';
		const _THUMB_URL_PREFIX = 'https://i.ytimg.com/vi/';
		const _WEBP_URL_PREFIX = 'https://i.ytimg.com/vi_webp/';
		const _THUMB_FILES = ['lq' => 'default', 'mq' => 'mqdefault', 'hq' => 'hqdefault'];
		const _HOMEPAGE_URL = 'https://www.youtube.com';
		const _COOKIES_FILE = 'ytcookies.txt';
		const _BASE_JS = 'base.js';
		const _TRUSTED_SESS_JSON = 'ytsession.json';
		
		// Fields
		protected $_cypherUsed = false;
		protected $_audioAvailable = false;
		private $_videoInfo = array();
		private $_signatures = array();
		private $_searchRecurseLevel = 0;
		private $_xmlFileHandle = null;
		private $_jsonTemp = '';
		private $_retrySearchParams = '';
		private $_trustedSessData = [];
		private $_nsigs = array();
		private $_nodeJS = '';
		private $_rake = null;
		private $_apiClients = array(
			// See https://github.com/zerodytrash/YouTube-Internal-Clients#clients
			"web" => ['name' => 'WEB_CREATOR', 'version' => '1.20240723.03.00', 'sts' => '19969'],
			"android" => ['name' => 'ANDROID_TESTSUITE', 'version' => '1.9', 'sts' => '19464']
			//"ios" => ['name' => 'IOS', 'version' => '17.33.2', 'sts' => '19464']
		);
		private $_renderers = array(
			'showingResultsForRenderer', 
			'videoRenderer', 
			'childVideoRenderer', 
			'watchCardCompactVideoRenderer', 
			'compactVideoRenderer'
		);

		#region Public Methods
		function RetrieveVidInfo($vidUrl)
		{
			$converter = $this->GetConverter();
			$videoInfo = array();
			$duration = array();
			$vidID = $converter->ExtractVideoId($vidUrl);
			$this->_videoInfo = $this->VideoInfo($vidID);
			//die(print_r($this->_videoInfo));
			$videoDetails = (!empty($this->_videoInfo['videoDetails'])) ? $this->_videoInfo['videoDetails'] : array();
			if (!empty($videoDetails))
			{
				$title = (isset($videoDetails['title']) && !empty($videoDetails['title'])) ? $videoDetails['title'] : '';
				$duration = (isset($videoDetails['lengthSeconds']) && !empty($videoDetails['lengthSeconds'])) ? array('duration' => $videoDetails['lengthSeconds']) : $duration;
			}
			$title = (empty($title)) ? 'unknown_' . time() : $title;
			if (!Config::_ENABLE_UNICODE_SUPPORT)
			{
				$title = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $title);
				$title = (!empty($title)) ? html_entity_decode($title) : 'unknown_'.time();
			}
			$videoInfo = array('id' => $vidID, 'title' => $title, 'thumb_preview' => 'https://img.youtube.com/vi/'.$vidID.'/0.jpg') + $duration;
			//die(print_r($videoInfo));
			return $videoInfo;
		}

		function ExtractVidSourceUrls()
		{
			// Populate vars required for extraction
			$converter = $this->GetConverter();
			$vidUrls = array();
			$ftype = $converter->GetConvertedFileType();
			$fcategory = $converter->GetConvertedFileCategory();
			$vidHost = $converter->GetCurrentVidHost();
			$vidInfo = $converter->GetVidInfo();

			$vidHosts = $converter->GetVideoHosts();
			$vidQualities = array();
			array_walk($vidHosts, function($vh, $key) use(&$vidQualities, $vidHost) {if ($vh['name'] == $vidHost) $vidQualities = $vh['video_qualities'];});

			// Start extraction
			$vidTrackTitle = $vidInfo['title'];
			$jsonInfo = $this->_videoInfo;
			if (isset($jsonInfo['playabilityStatus']) && $jsonInfo['playabilityStatus'])
			{
				$audioUrls = array();
				$fmtStreamMap = array();
				$adaptiveFmts = array();
				if (isset($jsonInfo['player_response']) && is_array($jsonInfo['player_response']))
				{
					$pr = $jsonInfo['player_response'];
					//die(print_r($pr));
					extract($this->FormatPlayerResponse($pr, $jsonInfo, 'fmt_stream_map'));
					extract($this->FormatPlayerResponse($pr, $jsonInfo, 'adaptive_fmts'));
					//die(print_r($jsonInfo));
				}
				if (isset($jsonInfo['adaptive_fmts']))
				{
					$adaptiveFmts = (empty($adaptiveFmts)) ? $this->ExtractAdaptiveFmts($jsonInfo['adaptive_fmts']) : $adaptiveFmts;
					//die(print_r($adaptiveFmts));
					array_walk($adaptiveFmts, function($url) use(&$audioUrls) {if (preg_match('/audio\/mp4/', $url) == 1) $audioUrls[] = $url;});
					$this->_audioAvailable = !empty($audioUrls);
				}
				if (isset($jsonInfo['fmt_stream_map']))
				{
					$fmtStreamMap = (empty($fmtStreamMap)) ? $this->ExtractFmtStreamMap($jsonInfo['fmt_stream_map']) : $fmtStreamMap;
					//die(print_r($fmtStreamMap));					
				}
				//die(print_r($audioUrls));

				$urls = array_merge($fmtStreamMap, $adaptiveFmts);
				//die(print_r($urls));
				
				// Detect cypher used
				$urlQueryStr = parse_url($fmtStreamMap[0], PHP_URL_QUERY);
				if ($urlQueryStr !== false && !is_null($urlQueryStr))
				{
					parse_str($urlQueryStr, $queryStrVars);
					//die(print_r($queryStrVars));
					$this->_cypherUsed = isset($queryStrVars['s']);
				}			
				
				foreach ($urls as $url)
				{
					$dloadFileVars = array();
					parse_str(parse_url($url, PHP_URL_QUERY), $dloadFileVars);
					preg_match('/^(((video|audio)\/)([^;]+))/', urldecode($dloadFileVars['type']), $matched);
					$dloadFileMime = $matched[2] . $matched[4];
					$dloadFileType = (!empty($dloadFileMime)) ? $dloadFileMime : '';
					$dloadFileQuality = (!isset($dloadFileVars['quality'])) ? ((!isset($dloadFileVars['quality_label'])) ? "au" : $dloadFileVars['quality_label']) : array_search($dloadFileVars['quality'], $vidQualities);

					$vidUrls[] = array($dloadFileType, $dloadFileQuality, $this->PrepareDownloadLink($url, $vidTrackTitle, !isset($dloadFileVars['quality'])));
				}
			}			
			//die(print_r($vidUrls));
			return array_reverse($vidUrls);
		}

		function UpdateSoftwareXml(array $updateVars=array())
		{
			$filePath = $this->GetStoreDir();
			if (is_null($this->GetSoftwareXml())) $this->SetSoftwareXml();
			$xmlFileHandle = $this->GetSoftwareXml();
			if (!is_null($xmlFileHandle))
			{
				$info = $xmlFileHandle->xpath('/software/info');
				if ($info !== false && !empty($info))
				{
					$lastError = (int)$info[0]->lasterror;
					$currentTime = time();					
					if ($currentTime - $lastError > 600)
					{
						$version = (string)$info[0]->version;
						$updateUrlPrefix = 'http://puredevlabs.cc/update-video-converter-v3/v:' . $version . '/';
						$updateUrl = '';
						if (isset($updateVars['signature']) && !empty($updateVars['signature']))
						{
							$sigLength = strlen($updateVars['signature']);
							$basejs = $this->TrustedSessData('basejs');
							$updateUrl = $updateUrlPrefix . 'sl:' . $sigLength . '/jp:' . base64_encode($basejs);
						}
						else
						{
							$updateUrl = $updateUrlPrefix . 'rp:1';
						}
						//die($updateUrl);
						$updateResponse = file_get_contents($updateUrl);
						if ($updateResponse !== false && !empty($updateResponse))
						{
							$cookies = $this->RetrieveCookies();
							if ($updateResponse != "You have the newest version.")
							{
								$sxe2 = new \SimpleXMLElement($updateResponse);
								$sxe2->info[0]->lasterror = $currentTime;
								$sxe2->requests[0]->cookies = $cookies;
								$newXmlContent = $sxe2->asXML();
							}
							else
							{
								$xmlFileHandle->info[0]->lasterror = $currentTime;
								$xmlFileHandle->requests[0]->cookies = $cookies;
								$newXmlContent = $xmlFileHandle->asXML();
							}
							$fp = fopen($filePath . 'software2.xml', 'w');
							if ($fp !== false)
							{
								$lockSucceeded = false;
								if (flock($fp, LOCK_EX))
								{
									$lockSucceeded = true;
									fwrite($fp, $newXmlContent);
									flock($fp, LOCK_UN);
								}
								fclose($fp);
								if ($lockSucceeded)
								{
									rename($filePath . "software2.xml", $filePath . "software.xml");
									chmod($filePath . "software.xml", 0777);
								}
								else
								{
									unlink($filePath . 'software2.xml');
								}
							}
						}
					}
				}
				else
				{
					unlink($filePath . 'software.xml');
				}			
			}
		}
		
		function ScrapeSearch($SearchTerm, $isVideoId)
		{
			$videos = array();
			$results = $this->VideoInfo($SearchTerm, true);
			//die($results);
			//if ($this->_searchRecurseLevel == 1) die($results);
			if (!empty($this->_retrySearchParams))
			{
				$this->_videoWebpage = '';
				$results = $this->VideoInfo($SearchTerm, true);
			}			
			if (!empty($results))
			{
				$items = $results;
				//die(print_r($items));
				$vidids = array();
				foreach ($items as $item)
				{
					$searchResult = $this->PopulateSearchResult($item, $vidids, $SearchTerm);
					$vidids = $searchResult[0];
					if (!empty($searchResult[1]))
					{
						if ($searchResult[2])
						{
							$videos['items'] = array($searchResult[1]);
							break;
						}
						else
						{
							$videos['items'][] = $searchResult[1];
						}
					}						
				}
			}
			//die(print_r($videos));
			$this->_searchRecurseLevel++;
			if (empty($videos) && preg_match('/^([a-zA-Z0-9_-]{11})$/', $SearchTerm) == 1 && $this->_searchRecurseLevel == 1)
			{
				$this->_videoWebpage = '';
				$videos = $this->ScrapeSearch(self::_VID_URL_PREFIX . $SearchTerm, $isVideoId);
			}
			if (!empty($videos) && $isVideoId && function_exists('array_column') && !in_array($SearchTerm, array_column(array_column($videos['items'], "id"), "videoId")) && $this->_searchRecurseLevel < Config::_MAX_CURL_TRIES)
			{
				$this->_videoWebpage = '';
				$videos = $this->ScrapeSearch($SearchTerm, $isVideoId);
			}
			if (empty($videos) && ($isVideoId || preg_match('/^([a-zA-Z0-9_-]{11})$/', $SearchTerm) == 1))
			{
				$this->_videoWebpage = '';
				$videos = $this->ScrapeVidInfo($SearchTerm);
			}			
			return $videos;
		}
		
		function ScrapeVidInfo($vidId)
		{
			$videos = array();
			$videoInfo = $this->VideoInfo($vidId);
			//die(print_r($videoInfo));
			if (isset($videoInfo['videoDetails']) && !empty($videoInfo['videoDetails']))
			{
				$vidids = array();
				$item = array(
					'videoId' => $vidId,
					'title' => array(
						'simpleText' => $videoInfo['videoDetails']['title']
					),
					'lengthText' => array(
						'simpleText' => $videoInfo['videoDetails']['duration']
					),
					'viewCountText' => array(
						'simpleText' => $videoInfo['videoDetails']['viewCount']
					)
				);
				$item['ownerText']['runs']['0']['navigationEndpoint']['browseEndpoint']['browseId'] = $videoInfo['videoDetails']['channelId'];
				$item['ownerText']['runs']['0']['text'] = $videoInfo['videoDetails']['author'];
				$searchResult = $this->PopulateSearchResult($item, $vidids, '');
				if (!empty($searchResult[1]))
				{
					$videos['items'] = array($searchResult[1]);
				}
			}
			return $videos;
		}		
		#endregion

		#region protected "Helper" Methods
		protected function RetrieveCookies()
		{
			$cookies = "";
			$cookieFile = $this->GetStoreDir() . self::_COOKIES_FILE;
			if (is_file($cookieFile) && (int)filesize($cookieFile) > 0)
			{
				$cookiefileArr = file($cookieFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				if (is_array($cookiefileArr) && !empty($cookiefileArr))
				{
					$cookieStr = array_pop($cookiefileArr);
					if (preg_match('/^(([^;]+=[^;]+;)+)$/', trim($cookieStr)) == 1)
					{
						$cookies = base64_encode(trim($cookieStr));
					}
				}
			}
			return $cookies;			
		}

		protected function CleanJson(array $json)
		{
			$retVal = array();
			$nestCount = 0;
			if (!empty($json))
			{
				$json = current($json);
				$jsonChars = str_split($json);
				$jsonCleaned = '';
				foreach ($jsonChars as $char)
				{
					$jsonCleaned .= $char;
					if ($char == "{") $nestCount++;
					if ($char == "}") $nestCount--;
					if ($nestCount == 0) break;
				}
				$retVal = array($jsonCleaned);
			}
			return $retVal;
		}

		protected function VideoInfoRequestParams($client='', $clientInfo=[])
		{
			$params = [];
			$clientInfo = (empty($clientInfo)) ? ['sts' => '', 'name' => '', 'version' => ''] : $clientInfo;
			if (is_null($this->GetSoftwareXml())) $this->SetSoftwareXml();
			$xmlFileHandle = $this->GetSoftwareXml();
			if (!is_null($xmlFileHandle))
			{
				$requestParams = $xmlFileHandle->xpath('/software/requests');
				if ($requestParams !== false && !empty($requestParams))
				{
					//die(print_r($requestParams));	
					$rp = $requestParams[0];
					$keyhash = (isset($rp->keyhash) && !empty($rp->keyhash)) ? trim(base64_decode((string)$rp->keyhash)) : '';
					$cookies = (isset($rp->cookies) && !empty($rp->cookies)) ? trim(base64_decode((string)$rp->cookies)) : '';
					$sts = (isset($rp->{$client}[0]->sts) && !empty($rp->{$client}[0]->sts)) ? trim(base64_decode((string)$rp->{$client}[0]->sts)) : $clientInfo['sts'];
					$cname = (isset($rp->{$client}[0]->cname) && !empty($rp->{$client}[0]->cname)) ? trim(base64_decode((string)$rp->{$client}[0]->cname)) : $clientInfo['name'];
					$cversion = (isset($rp->{$client}[0]->cversion) && !empty($rp->{$client}[0]->cversion)) ? trim(base64_decode((string)$rp->{$client}[0]->cversion)) : $clientInfo['version'];
					$params = compact('keyhash', 'cookies', 'sts', 'cname', 'cversion');
				}
			}
			return $params;
		}
		
		protected function VideoInfoRequest($vidIdOrSearchTerm, $reqType, $client='', $clientInfo=array())
		{
			$response = '';
			$params = $this->VideoInfoRequestParams($client, $clientInfo);
			if (!empty($params))
			{
				extract($params);
				switch ($reqType)
				{
					case "vidPage":													
						$response = $this->FileGetContents(self::_VID_URL_PREFIX . $vidIdOrSearchTerm . "&hl=" . Config::_DEFAULT_LANGUAGE . "&persist_hl=1", '', array('Cookie: ' . $cookies));
						//die($response);								
						break;
					case "searchPage":
						$searchUrl = (!empty($this->_retrySearchParams)) ? self::_HOMEPAGE_URL . $this->_retrySearchParams : self::_SEARCH_URL_PREFIX . urlencode($vidIdOrSearchTerm) . "&page=1";
						$searchUrl .= "&hl=" . Config::_DEFAULT_LANGUAGE . "&persist_hl=1";
						$response = $this->FileGetContents($searchUrl, '', array('Cookie: ' . $cookies));
						//die($response);								
						break;
					default:
						preg_match('/SAPISID\s*=\s*([^;]+)/i', $cookies, $cmatch);
						$cmatch[1] = $cmatch[1] ?? '';
						if (!empty($keyhash))
						{
							$isSearch = $reqType == "searchApi";
							$apiUrl = ($isSearch) ? self::_SEARCH_API_URL : self::_PLAYER_API_URL;
							$origin = self::_HOMEPAGE_URL;
							$timestamp = time();
							$authHash = 'SAPISIDHASH ' . $timestamp . '_' . sha1($timestamp . ' ' . $cmatch[1] . ' ' . $origin);
							$sessData = $this->TrustedSessData();

							$vidInfoPostData = [
								'context' => [
									'client' => [
										'clientName' => $cname,
										'clientVersion' => $cversion,
										'hl' => Config::_DEFAULT_LANGUAGE
									]
								],
								'playbackContext' => [
									'contentPlaybackContext' => [
										'signatureTimestamp' => $sessData['sigTimestamp'] ?? $sts
									]
								],
								'contentCheckOk' => true,
								'racyCheckOk' => true
							];
							if (!empty($this->_retrySearchParams))
							{
								$vidInfoPostData['params'] = $this->_retrySearchParams . '",';
							}
							if ($isSearch)
							{
								$vidInfoPostData['query'] = $vidIdOrSearchTerm;
							}
							else
							{
								$vidInfoPostData['videoId'] = $vidIdOrSearchTerm;
							}

							$vidInfoHeaders = [
								'Content-Type: application/json',
								'X-Goog-Api-Key: ' . $keyhash,
								'Cookie: ' . $cookies,
								'x-origin: ' . $origin
							];
							if (!empty($cmatch[1]))
							{
								$vidInfoHeaders[] = 'Authorization: ' . $authHash;
							}

							if ($client == "web")
							{
								$vidInfoPostData['context']['client']['visitorData'] = $sessData['visitorData'] ?? '';
								$vidInfoPostData['context']['client']['userAgent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36,gzip(gfe)';
								$vidInfoPostData['context']['user'] = [
									'lockedSafetyMode' => false
								];
								$vidInfoPostData['serviceIntegrityDimensions'] = [
									"poToken" => $sessData['poToken'] ?? ''
								];
								$vidInfoHeaders = array_diff_key($vidInfoHeaders, [1=>1, 2=>2, 4=>4]);
							}

							//die(print_r($vidInfoHeaders));
							$response = $this->FileGetContents($apiUrl, json_encode($vidInfoPostData), $vidInfoHeaders);
							//die($response);
						}
						break;
				}
			}
			$this->_videoWebpage = trim($response);
			return trim($response);
		}
		
		protected function VideoInfo($vidIdOrSearchTerm, $isSearch=false)
		{
			$jsonInfo = array();
			// Try Video/Search API first
			$clients = array_keys($this->_apiClients);
			foreach ($clients as $client)
			{
				$reqType = ($isSearch) ? "searchApi" : "vidApi";
				if ($isSearch && $client != "android") continue;
				$response = $this->VideoInfoRequest($vidIdOrSearchTerm, $reqType, $client, $this->_apiClients[$client]);
				if (!$this->CheckValidVidInfo($response, $reqType))
				{
					$this->UpdateSoftwareXml();
					$this->_videoWebpage = '';
				}
				else
				{						
					if (!empty($this->_jsonTemp))
					{
						$jsonInfo = $this->PopulateJsonData($isSearch);
						//die(print_r($jsonInfo));		
						if (json_last_error() == JSON_ERROR_NONE) break;
					}
				}
			}
			if (empty($jsonInfo))
			{
				// Try Scraping Video/Search Page
				$reqType = ($isSearch) ? "searchPage" : "vidPage";
				$response = $this->VideoInfoRequest($vidIdOrSearchTerm, $reqType);
				if (!$this->CheckValidVidInfo($response, $reqType))
				{
					$this->UpdateSoftwareXml();
				}
				else
				{									
					$jsonInfo = (!empty($this->_jsonTemp)) ? $this->PopulateJsonData($isSearch) : $jsonInfo;	
				}
			}
			return (json_last_error() == JSON_ERROR_NONE) ? $jsonInfo : array();
    	}

		protected function PopulateJsonData($isSearch)
		{
			$jsonObj = $this->_jsonTemp;
			//die(print_r($jsonObj));
			if ($isSearch)
			{
				$jsonInfo = $jsonObj;
			}
			else 
			{
				$jsonInfo = array(
					'adaptive_fmts' => isset($jsonObj['args']['adaptive_fmts']) ? $jsonObj['args']['adaptive_fmts'] : '',
					'fmt_stream_map' => isset($jsonObj['args']['url_encoded_fmt_stream_map']) ? $jsonObj['args']['url_encoded_fmt_stream_map'] : '',
					'player_response' => (!isset($jsonObj['args']['player_response'])) ? ((!isset($jsonObj['streamingData'])) ? '' : $jsonObj['streamingData']) : json_decode($jsonObj['args']['player_response'], true),
					'videoDetails' => (!isset($jsonObj['videoDetails'])) ? ((!isset($jsonObj['player_response']['videoDetails'])) ? '' : $jsonObj['player_response']['videoDetails']) : $jsonObj['videoDetails'],
					'playabilityStatus' => (isset($jsonObj['playabilityStatus']['status'])) ? preg_match('/^(OK)$/i', (string)$jsonObj['playabilityStatus']['status']) == 1 : true
				);
			}
			return $jsonInfo;
		}
    	
    	protected function CheckValidVidInfo($response, $reqType)
    	{
    		$isValid = !empty($response);
    		if ($isValid)
    		{
    			$response = ($reqType == "vidPage") ? $this->VidInfoPatternMatches($response) : $response;
				$response = ($reqType == "searchPage" || $reqType == "searchApi") ? $this->SearchInfoPatternMatches($response, $reqType) : $response;
    			$this->_jsonTemp = $json = json_decode($response, true);
    			//die(print_r($this->_headers));
    			$responseCode = (!empty($this->_headers) && preg_match('/^(HTTP\/\d(\.\d)?\s+(\d{3}))/i', $this->_headers[0], $rcmatches) == 1) ? $rcmatches[3] : '0';
    			$isValid = json_last_error() == JSON_ERROR_NONE && !isset($json['error']) && $responseCode == '200';
    		}
    		//return false;
    		return $isValid;
    	}

		protected function SearchInfoPatternMatches($searchPage, $reqType)
		{
			$json = '{}';
			$searchPage = (preg_match(self::_VID_SEARCH_PATTERN, $searchPage, $matches) == 1) ? $matches[1] : $searchPage;
			$jsonarr = json_decode($searchPage, true);
			//if (!empty($this->_retrySearchParams)) die(print_r($jsonarr));
			if (json_last_error() == JSON_ERROR_NONE)
			{
				$items = array();
				$iterator = new \RecursiveArrayIterator($jsonarr);
				$recursive = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
				foreach ($recursive as $key => $value) 
				{
					if (in_array($key, $this->_renderers, true)) 
					{
						if ($key === "showingResultsForRenderer")
						{
							$srf = $value;
							$this->_retrySearchParams = ($reqType == "searchApi" && isset($srf['originalQueryEndpoint']['searchEndpoint']['params']) && !empty($srf['originalQueryEndpoint']['searchEndpoint']['params'])) ? $srf['originalQueryEndpoint']['searchEndpoint']['params'] : (($reqType == "searchPage" && isset($srf['originalQueryEndpoint']['commandMetadata']['webCommandMetadata']['url']) && !empty($srf['originalQueryEndpoint']['commandMetadata']['webCommandMetadata']['url'])) ? $srf['originalQueryEndpoint']['commandMetadata']['webCommandMetadata']['url'] : '');							
							continue;
						}
						$items[] = $value;
					}
				}
				//die(print_r($items));
				$json = (!empty($items)) ? json_encode($items) : $json;
				$json = (json_last_error() != JSON_ERROR_NONE) ? '{}' : $json;
			}
			return $json;
		}
    	
		protected function VidInfoPatternMatches($videoPage)
		{
			$json = '{}';
			if (preg_match(self::_VID_INFO_PATTERN, $videoPage, $matches) == 1 || preg_match(self::_VID_INFO_PATTERN2, $videoPage, $matches2) == 1)
			{
				$matched = (!empty($matches)) ? $matches : $matches2;
				$json = $matched[1];
			}
			return $json;
		}    	
		
		protected function ExtractFmtStreamMap($fmtStreamMap)
		{
			$formats = array();
			$urls = urldecode(urldecode($fmtStreamMap));
			//die($urls);
			if (preg_match('/^((.+?)(=))/', $urls, $matches) == 1)
			{
				$urlsArr = preg_split('/,'.preg_quote($matches[0], '/').'/', $urls, -1, PREG_SPLIT_NO_EMPTY);
				//print_r($urls);
				//print_r($urlsArr);
				$urlsArr2 = array();
				foreach ($urlsArr as $url)
				{
					if (preg_match('/,([a-zA-Z0-9_-]+=)/', $url, $matchArr) == 1)
					{
						$urlArr = preg_split('/,([a-zA-Z0-9_-]+=)/', $url, -1, PREG_SPLIT_NO_EMPTY);
						foreach ($urlArr as $k => $u)
						{
							$urlsArr2[] = ($k > 0) ? $matchArr[1].$u : $u;
						}
					}
					else
					{
						$urlsArr2[] = $url;
					}
				}
				//print_r($urlsArr2);
				foreach ($urlsArr2 as $url)
				{
					$inUrlsArr = count(preg_grep('/^('.preg_quote($url, '/').')/', $urlsArr)) > 0;
					if (($urlsArr == $urlsArr2 && $matches[0] != 'url=') || ($urlsArr != $urlsArr2 && !$inUrlsArr && preg_match('/^(url=)/', $url) != 1) || ($urlsArr != $urlsArr2 && $inUrlsArr && $matches[0] != 'url='))
					{
						$url = ($url != $urlsArr2[0] && $inUrlsArr) ? $matches[0].$url : $url;
						$urlBase = preg_replace('/(.+?)(url=)(.+?)(\?)(.+)/', "$3$4", $url);
						$urlParams = preg_replace('/(.+?)(url=)(.+?)(\?)(.+)/', "$1$5", $url);
						$url = $urlBase . "&" . $urlParams;
					}
					else
					{
						$url = preg_replace('/^(url=)/', "", $url);
					}
					$formats[] = $url;
				}
			}
			//die(print_r($formats));
			return $formats;
		}

		protected function ExtractAdaptiveFmts($adaptiveFmts)
		{
			$formats = array();
			$adaptiveUrls = urldecode(urldecode($adaptiveFmts));
			//die($adaptiveUrls);
			if (preg_match('/^((.+?)(=))/', $adaptiveUrls, $matches) == 1)
			{
				$adaptiveUrlsArr = preg_split('/,'.preg_quote($matches[0], '/').'/', $adaptiveUrls, -1, PREG_SPLIT_NO_EMPTY);
				//die(print_r($adaptiveUrlsArr));
				$adaptiveUrlsArr2 = array();
				array_walk($adaptiveUrlsArr, function($url) use(&$adaptiveUrlsArr2, $adaptiveUrlsArr, $matches) {$adaptiveUrlsArr2[] = ($url != $adaptiveUrlsArr[0]) ? $matches[0] . $url : $url;});
				//die(print_r($adaptiveUrlsArr2));

				$adaptiveUrlsArr3 = array();
				$adaptiveAudioUrls = array();
				foreach ($adaptiveUrlsArr2 as $adaptiveUrl)
				{
					if (preg_match_all('/,(([^=,\&]+)(=))/', $adaptiveUrl, $matches2) > 0)
					{
						//die(print_r($matches2));
						$splitPattern = '';
						array_walk($matches2[0], function($m, $key) use(&$splitPattern, $matches2) {$splitPattern .= preg_quote($m, '/') . (($key != count($matches2[0])-1) ? "|" : "");});
						$audioUrls = preg_split('/('.$splitPattern.')/', $adaptiveUrl, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
						//die(print_r($audioUrls));
						$lastAdaptiveUrl = array_shift($audioUrls);
						//die(print_r($audioUrls));
						$audioUrls2 = array();
						array_walk($audioUrls, function($url, $key) use(&$audioUrls2, $audioUrls) {if ($key % 2 == 0 && isset($audioUrls[$key + 1])) $audioUrls2[] = trim($url, ",") . $audioUrls[$key + 1];});
						//die(print_r($audioUrls2));
						$adaptiveUrlsArr3[] = $lastAdaptiveUrl;
						$adaptiveAudioUrls = array_merge($adaptiveAudioUrls, $audioUrls2);
					}
					else
					{
						$adaptiveUrlsArr3[] = $adaptiveUrl;
					}
				}
				//die(print_r($adaptiveUrlsArr3));
				//die(print_r($adaptiveAudioUrls));
				$adaptiveUrlsArr3 = array_merge($adaptiveUrlsArr3, $adaptiveAudioUrls);
				foreach ($adaptiveUrlsArr3 as $url)
				{
					if (preg_match('/^(url=)/', $url) != 1)
					{
						$urlBase = preg_replace('/(.+?)(url=)(.+?)(\?)(.+)/', "$3$4", $url);
						$urlParams = preg_replace('/(.+?)(url=)(.+?)(\?)(.+)/', "$1$5", $url);
						$url = $urlBase . "&" . $urlParams;
					}
					else
					{
						$url = preg_replace('/^(url=)/', "", $url);
					}
					$formats[] = $url;
				}
			}
			//die(print_r($formats));
			return $formats;
		}

		protected function FormatPlayerResponse(array $pr, array $jsonInfo, $fmtType)
		{
			//die(print_r($pr));
			$isAdaptiveFmts = $fmtType == "adaptive_fmts";
			$arrName = ($isAdaptiveFmts) ? "adaptiveFmts" : "fmtStreamMap";
			${$arrName} = array();
			$fmtName = ($isAdaptiveFmts) ? "adaptiveFormats" : "formats";
			$streamingData = (!isset($pr['streamingData'][$fmtName])) ? ((!isset($pr[$fmtName])) ? '' : $pr[$fmtName]) : $pr['streamingData'][$fmtName];
			if (is_array($streamingData))
			{
				$jsonInfo[$fmtType] = '';
				foreach ($streamingData as $format)
				{
					if (isset($format['url']))
					{
						$urlParts = parse_url($format['url']);
						parse_str($urlParts['query'], $vars);
						if (!isset($vars['type'])) $vars['type'] = urlencode(stripslashes($format['mimeType']));
						if ($isAdaptiveFmts && preg_match('/^(video)/', $format['mimeType']) == 1 && !isset($vars['quality_label'])) $vars['quality_label'] = $format['qualityLabel'];
						if (!$isAdaptiveFmts && !isset($vars['quality'])) $vars['quality'] = $format['quality'];
						$queryStr = http_build_query($vars, '', '&');
						$format['url'] = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'] . '?' . $queryStr;
						${$arrName}[] = $format['url'];
					}
					elseif (isset($format['cipher']) || isset($format['signatureCipher']))
					{
						$jsonInfo[$fmtType] .= "type=" . urlencode(stripslashes($format['mimeType']));
						$jsonInfo[$fmtType] .= ($isAdaptiveFmts) ? ((preg_match('/^(video)/', $format['mimeType']) == 1) ? "&quality_label=" . $format['qualityLabel'] : "") : "&quality=" . $format['quality'];
						$jsonInfo[$fmtType] .= "&" . ((isset($format['signatureCipher'])) ? $format['signatureCipher'] : $format['cipher']);
						$jsonInfo[$fmtType] .= ($format != end($streamingData)) ? "," : "";
					}
				}
			}
			return compact('jsonInfo', $arrName);
		}

		protected function PrepareDownloadLink($url, $vidTrackTitle, $isAdaptiveFmt)
		{
			//$url = preg_replace('/(.*)(itag=\d+&)(.*?)/', '$1$3', $url, 1);
			//$url = preg_replace('/&sig=|&s=/', "&signature=", $url);
			$url = trim($url, ',');
			$urlParts = parse_url($url);
			parse_str($urlParts['query'], $vars);
			
			$sigParamNames = array('sig' => 0, 's' => 1, 'signature' => 2);
			$sigParamName = (!isset($vars['s'], $vars['sp'])) ? ((!isset($vars['sig'])) ? "signature" : "sig") : $vars['sp'];
			$vars[$sigParamName] = (!isset($vars['sig'])) ? ((!isset($vars['s'])) ? ((!isset($vars['signature'])) ? "" : $vars['signature']) : $vars['s']) : $vars['sig'];
			unset($sigParamNames[$sigParamName]);
			foreach ($sigParamNames as $pname => $num)
			{
				unset($vars[$pname]);
			}
			$this->_signatures[$vars['itag']] = $vars[$sigParamName];

			$params = $this->VideoInfoRequestParams("web", $this->_apiClients['web']);
			if (isset($vars['c'], $vars['n']) && !empty($params) && preg_match('/^(' . preg_quote($params['cname'], "/") . ')$/i', $vars['c']) == 1)
			{
				$vars['n'] = $this->DecryptNSigCypher($vars['n']);
				$vars['pot'] = $this->TrustedSessData('poToken');
			}
			
			if (isset($vars['type'])) $vars['type'] = urlencode($vars['type']);
			if (!isset($vars['requiressl'])) $vars['requiressl'] = "yes";
			if (!isset($vars['ratebypass'])) $vars['ratebypass'] = "yes";
			if (!isset($vars['title'])) $vars['title'] = rawurlencode($vidTrackTitle);
			if ($isAdaptiveFmt)
			{
				unset($vars['bitrate'], $vars['init'], $vars['title'], $vars['projection_type'], $vars['type'], $vars['xtags'], $vars['index']);
			}			
			if ($this->GetCypherUsed())
			{
				$vars[$sigParamName] = $this->DecryptCypher($vars[$sigParamName]);
			}
			//die(print_r($vars));
			$queryStr = http_build_query($vars, '', '&');
			$hostName = explode(".", $urlParts['host']);
			array_splice($hostName, 0, 1, array('redirector'));
			$url = $urlParts['scheme'] . '://' . implode(".", $hostName) . $urlParts['path'] . '?' . $queryStr;
			return $url;
		}

		protected function DecryptNSigCypher($nsig)
		{
			if (isset($this->_nsigs[$nsig])) return $this->_nsigs[$nsig];
			$nsigDecrypted = $nsig;
			if (empty($this->_nodeJS))
			{
				$playerJS = $this->TrustedSessData('basejsCode');
				if (!empty($playerJS) && preg_match('/(?x)(?:\.get\("n"\)\)&&\(b=|(?:b=String\.fromCharCode\(110\)|(?P<str_idx>[a-zA-Z0-9_$.]+)&&\(b="nn"\[\+(?P=str_idx)\])(?:,[a-zA-Z0-9_$]+\(a\))?,c=a\.(?:get\(b\)|[a-zA-Z0-9_$]+\[b\]\|\|null)\)&&\(c=|\b(?P<var>[a-zA-Z0-9_$]+)=)(?P<nfunc>[a-zA-Z0-9_$]+)(?:\[(?P<idx>\d+)\])?\([a-zA-Z]\)(?(var),[a-zA-Z0-9_$]+\.set\("n"\,(?P=var)\),(?P=nfunc)\.length)/', $playerJS, $pmatch) == 1)
				{
				    $fname = $pmatch['nfunc'];
				    $findex = $pmatch['idx'];
				    if (preg_match('/var ' . preg_quote($fname, "/") . '=\[([^\]]+)\];/', $playerJS, $pmatch2) == 1)
				    {
				        $funcs = explode(",", $pmatch2[1]);
				        if (isset($funcs[$findex]))
				        {
				            $fname = $funcs[$findex];
							$fNamePattern = preg_quote($fname, "/");
							if (preg_match('/((function\s+' . $fNamePattern . ')|([\{;,]\s*' . $fNamePattern . '\s*=\s*function)|(var\s+' . $fNamePattern . '\s*=\s*function))\s*\(([^\)]*)\)\s*\{(.+?)\};\n/s', $playerJS, $nsigFunc) == 1)
							{
								//die("<pre>" . print_r($nsigFunc, true) . "</pre>");
								$this->_nodeJS = $fname . ' = function(' . $nsigFunc[5] . '){' . $nsigFunc[6] . '}; console.log(' . $fname . '("%nsig%"));';
							}
						}
					}
				}
			}
			if (!empty($this->_nodeJS))
			{
				//die($this->_nodeJS);
				$nodeJS = preg_replace('/%nsig%/', $nsig, $this->_nodeJS);
				exec(Config::_NODEJS_PATH . ' ' . $this->GetStoreDir() . 'nsig.js ' . escapeshellarg($nodeJS) . ' 2>&1', $nodeOutput, $resultCode);
				//echo "encrypted nsig: " . $nsig . "<br><br>";
				//echo "decrypted nsig: " . $nodeOutput[0];
				//die(print_r($nodeOutput));	
				$nsigDecrypted = ($resultCode == 0 && !empty($nodeOutput) && count($nodeOutput) == 1) ? $nodeOutput[0] : $nsigDecrypted;
			}
			$this->_nsigs[$nsig] = $nsigDecrypted;
			return $nsigDecrypted;
		}

		protected function DecryptCypher($signature)
        {
			$s = $signature;
			if (is_null($this->GetSoftwareXml())) $this->SetSoftwareXml();
			$xmlFileHandle = $this->GetSoftwareXml();
			if (!is_null($xmlFileHandle))
			{
				$algo = $xmlFileHandle->xpath('/software/decryption/funcgroup[@siglength="' . strlen($s) . '"]/func');
				if ($algo !== false && !empty($algo))
				{
					//die(print_r($algo));
					foreach ($algo as $func)
					{
						$funcName = (string)$func->name;
						if (!function_exists($funcName))
						{
							eval('function ' . $funcName . '(' . (string)$func->args . '){' . preg_replace('/self::/', "", (string)$func->code) . '}');
						}
					}
					$s = call_user_func((string)$algo[0]->name, $s);
				}				
			}
			$s = ($s == $signature) ? $this->LegacyDecryptCypher($s) : $s;
			return $s;
		}

        // Deprecated - May be removed in future versions!
        protected function LegacyDecryptCypher($signature)
        {
            $s = $signature;
            $sigLength = strlen($s);
            switch ($sigLength)
            {
                case 93:
                	$s = strrev(substr($s, 30, 57)) . substr($s, 88, 1) . strrev(substr($s, 6, 23));
                	break;
                case 92:
                    $s = substr($s, 25, 1) . substr($s, 3, 22) . substr($s, 0, 1) . substr($s, 26, 16) . substr($s, 79, 1) . substr($s, 43, 36) . substr($s, 91, 1) . substr($s, 80, 3);
                    break;
                case 90:
                	$s = substr($s, 25, 1) . substr($s, 3, 22) . substr($s, 2, 1) . substr($s, 26, 14) . substr($s, 77, 1) . substr($s, 41, 36) . substr($s, 89, 1) . substr($s, 78, 3);
                	break;
                case 89:
                	$s = strrev(substr($s, 79, 6)) . substr($s, 87, 1) . strrev(substr($s, 61, 17)) . substr($s, 0, 1) . strrev(substr($s, 4, 56));
                	break;
                case 88:
                    $s = substr($s, 7, 21) . substr($s, 87, 1) . substr($s, 29, 16) . substr($s, 55, 1) . substr($s, 46, 9) . substr($s, 2, 1) . substr($s, 56, 31) . substr($s, 28, 1);
                    break;
                case 87:
                	$s = substr($s, 6, 21) . substr($s, 4, 1) . substr($s, 28, 11) . substr($s, 27, 1) . substr($s, 40, 19) . substr($s, 2, 1) . substr($s, 60);
                    break;
                case 84:
					$s = strrev(substr($s, 71, 8)) . substr($s, 14, 1) . strrev(substr($s, 38, 32)) . substr($s, 70, 1) . strrev(substr($s, 15, 22)) . substr($s, 80, 1) . strrev(substr($s, 0, 14));
                    break;
                case 81:
					$s = substr($s, 56, 1) . strrev(substr($s, 57, 23)) . substr($s, 41, 1) . strrev(substr($s, 42, 14)) . substr($s, 80, 1) . strrev(substr($s, 35, 6)) . substr($s, 0, 1) . strrev(substr($s, 30, 4)) . substr($s, 34, 1) . strrev(substr($s, 10, 19)) . substr($s, 29, 1) . strrev(substr($s, 1, 8)) . substr($s, 9, 1);
                    break;
                case 80:
					$s = substr($s, 1, 18) . substr($s, 0, 1) . substr($s, 20, 48) . substr($s, 19, 1) . substr($s, 69, 11);
                    break;
                case 79:
					$s = substr($s, 54, 1) . strrev(substr($s, 55, 23)) . substr($s, 39, 1) . strrev(substr($s, 40, 14)) . substr($s, 78, 1) . strrev(substr($s, 35, 4)) . substr($s, 0, 1) . strrev(substr($s, 30, 4)) . substr($s, 34, 1) . strrev(substr($s, 10, 19)) . substr($s, 29, 1) . strrev(substr($s, 1, 8)) . substr($s, 9, 1);
                	break;
                default:
                    $s = $signature;
            }
            return $s;
        }
        
		protected function PopulateSearchResult(array $item, array $vidids, $SearchTerm)
		{
			$searchResult = array();
			$isVidUrlOrIdSearch = false;
			$vidid = (!isset($item['videoId'])) ? ((!isset($item['navigationEndpoint']['watchEndpoint']['videoId'])) ? '' : $item['navigationEndpoint']['watchEndpoint']['videoId']) : $item['videoId'];
			$isLiveStream = isset($item['viewCountText']['runs']) && is_array($item['viewCountText']['runs']) && count((array)preg_grep('/watching/i', array_column($item['viewCountText']['runs'], 'text'))) > 0;
			if (!empty($vidid) && !in_array($vidid, $vidids) && !$isLiveStream) 
			{
				$vidDescription = $this->MakeVidDescription($item);
				$searchResult = array(
					'id' => array('videoId' => $vidid),
					'url' => self::_VID_URL_PREFIX . $vidid,
					'title' => ((!isset($item['title']['runs'][0]['text'])) ? ((!isset($item['title']['simpleText'])) ? '' : $item['title']['simpleText']) : $item['title']['runs'][0]['text']),
					'description' => $vidDescription,
					'tags' => $this->MakeVidTags($vidDescription),
					'thumbDefault' => self::_WEBP_URL_PREFIX . $vidid . '/' . self::_THUMB_FILES['lq'] . '.webp',
					'thumbMedium' => self::_WEBP_URL_PREFIX . $vidid . '/' . self::_THUMB_FILES['mq'] . '.webp',
					'thumbHigh' => self::_WEBP_URL_PREFIX . $vidid . '/' . self::_THUMB_FILES['hq'] . '.webp',				
					'channelTitle' => ((!isset($item['longBylineText']['runs'][0]['text'])) ? ((!isset($item['ownerText']['runs']['0']['text'])) ? ((!isset($item['byline']['runs'][0]['text'])) ? 'Unknown' : $item['byline']['runs'][0]['text']) : $item['ownerText']['runs']['0']['text']) : $item['longBylineText']['runs'][0]['text']),
					'channelId' => ((!isset($item['longBylineText']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId'])) ? ((!isset($item['ownerText']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId'])) ? ((!isset($item['byline']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId'])) ? '' : $item['byline']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId']) : $item['ownerText']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId']) : $item['longBylineText']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId']),
					'channelUrl' => ((!isset($item['longBylineText']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId'])) ? ((!isset($item['ownerText']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId'])) ? ((!isset($item['byline']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId'])) ? '' : self::_HOMEPAGE_URL . "/channel/" . $item['byline']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId']) : self::_HOMEPAGE_URL . "/channel/" . $item['ownerText']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId']) : self::_HOMEPAGE_URL . "/channel/" . $item['longBylineText']['runs'][0]['navigationEndpoint']['browseEndpoint']['browseId']),
					'publishedAt' => ((!isset($item['publishedTimeText']['runs'][0]['text'])) ? ((!isset($item['publishedTimeText']['simpleText'])) ? 'Unknown' : $item['publishedTimeText']['simpleText']) : $item['publishedTimeText']['runs'][0]['text']),
					'duration' => ((!isset($item['lengthText']['runs'][0]['text'])) ? ((!isset($item['lengthText']['simpleText'])) ? '' : $item['lengthText']['simpleText']) : $item['lengthText']['runs'][0]['text']),
					'viewCount' => ((!isset($item['viewCountText']['runs'][0]['text'])) ? ((!isset($item['viewCountText']['simpleText'])) ? '' : preg_replace('/\D/', "", $item['viewCountText']['simpleText'])) : preg_replace('/\D/', "", $item['viewCountText']['runs'][0]['text']))
				);	
				//die(print_r($searchResult));
				$isVidUrlOrIdSearch = trim($vidid) == preg_replace('/^(' . preg_quote(self::_VID_URL_PREFIX, "/") . ')/', "", trim($SearchTerm));
				$vidids[] = $vidid;
			}
			return array($vidids, $searchResult, $isVidUrlOrIdSearch);
		}
		
		protected function MakeVidDescription(array $item)
		{
			$description = '';
			if (isset($item['detailedMetadataSnippets'][0]['snippetText']['runs']) && is_array($item['detailedMetadataSnippets'][0]['snippetText']['runs']))
			{
				foreach ($item['detailedMetadataSnippets'][0]['snippetText']['runs'] as $itemText)
				{
					if (isset($itemText['text']) && !empty($itemText['text']))
					{
						$description .= $itemText['text'];
					}
				}
			}
			return $description;
		}

		protected function MakeVidTags($description)
		{
			$this->_rake = (is_null($this->_rake)) ? RakePlus::create('', 'en_US', 3) : $this->_rake;
			$tags = (!empty($description)) ? preg_replace('/(^[\W]+)|([\W]+$)/', "", preg_grep('/(^\W+$)|(^http)|(^\/)|(\/$)/', $this->_rake->extract($description)->sortByScore('desc')->get(), PREG_GREP_INVERT)) : array();
			//print_r($tags); echo "<br><br>";
			return $tags;
		}

		protected function TrustedSessData($key=null)
		{
			$data = (is_null($key)) ? $this->GetTrustedSessData() : (string)$this->GetTrustedSessData()[$key];
			if (empty($data)) 
			{
				$this->SetTrustedSessData();
				$data = (is_null($key)) ? $this->GetTrustedSessData() : (string)$this->GetTrustedSessData()[$key];
			}
			return $data;
		}
		#endregion

		#region Properties
		public function GetCypherUsed()
		{
			return $this->_cypherUsed;
		}

		public function AudioAvailable()
		{
			return $this->_audioAvailable;
		}
		
		public function GetSignature($itag)
		{
			return (!isset($this->_signatures[$itag])) ? "" : $this->_signatures[$itag];
		}
		
		private function SetSoftwareXml()
		{
			$xmlFileHandle = null;
			$isEx = false;
			$filePath = $this->GetStoreDir();
			if (is_file($filePath . 'software.xml'))
			{
				$xmlContent = file_get_contents($filePath . 'software.xml');
				if ($xmlContent !== false && !empty($xmlContent))
				{
					try {$sxe = @new \SimpleXMLElement($xmlContent);}
					catch (\Exception $ex) {$isEx = true;}
					if (!$isEx && is_object($sxe) && $sxe instanceof \SimpleXMLElement)
					{	
						$xmlFileHandle = $sxe;
					}
					else
					{
						unlink($filePath . 'software.xml');
					}					
				}
				else
				{
					unlink($filePath . 'software.xml');
				}				
			}
			else
			{
				$updateResponse = file_get_contents('http://puredevlabs.cc/update-video-converter-v2/v:0');
				if ($updateResponse !== false && !empty($updateResponse))
				{
					try {$sxe3 = new \SimpleXMLElement($updateResponse);}
					catch (\Exception $ex) {$isEx = true;}
					if (!$isEx && is_object($sxe3) && $sxe3 instanceof \SimpleXMLElement)
					{
						$sxe3->info[0]->lasterror = time();
						$sxe3->requests[0]->cookies = $this->RetrieveCookies();
						$fp = fopen($filePath . 'software.xml', 'w');
						if ($fp !== false)
						{
							$lockSucceeded = false;
							if (flock($fp, LOCK_EX))
							{
								$lockSucceeded = true;
								fwrite($fp, $sxe3->asXML());
								flock($fp, LOCK_UN);
							}
							fclose($fp);
							if ($lockSucceeded)
							{
								chmod($filePath . "software.xml", 0777);
								if (is_file($filePath . 'software.xml')) 
								{
									$this->SetSoftwareXml();
								}
							}
							else
							{
								unlink($filePath . 'software.xml');
							}
						}
					}
				}
			}			
			$this->_xmlFileHandle = ($xmlFileHandle != null) ? $xmlFileHandle : $this->_xmlFileHandle;
		}
		private function GetSoftwareXml()
		{
			return $this->_xmlFileHandle;
		}

		private function SetTrustedSessData()
		{
			$data = [];
			$sessJsonPath = $this->GetStoreDir() . YouTube::_TRUSTED_SESS_JSON;
			$sessJson = (is_file($sessJsonPath)) ? (string)file_get_contents($sessJsonPath) : '';
			if (!empty($sessJson))
			{
				$json = json_decode($sessJson, true);
				$data = (isset($json['visitorData'], $json['poToken'], $json['basejs'], $json['sigTimestamp'])) ? $json : $data;
			}
			$basejsPath = $this->GetStoreDir() . YouTube::_BASE_JS;
			$basejs = (is_file($basejsPath)) ? (string)file_get_contents($basejsPath) : '';
			if (!empty($basejs))
			{
				$data['basejsCode'] = $basejs;
			}
			// Use previous SetJsPlayerUrl() code below as backup for $data['basejs']
			if (empty($data['basejs']))
			{
				$vidPageSrc = $this->GetVideoWebpage();
				if (!empty($vidPageSrc) && preg_match(self::_VID_PLAYER_PATTERN, $vidPageSrc, $matches) == 1) 
				{
					$data['basejs'] = (empty($matches[3])) ? ((empty($matches[6])) ? '' : $matches[6]) : $matches[3];
				}
				//die(print_r($matches));
				if (empty($data['basejs']))
				{
					$ythome = $this->FileGetContents(self::_HOMEPAGE_URL);
					$data['basejs'] = (!empty($ythome) && preg_match(self::_VID_PLAYER_PATTERN, $ythome, $matches) == 1 && isset($matches[6]) && !empty($matches[6])) ? $matches[6] : '';
				}
				$data['basejs'] = (!empty($data['basejs']) && preg_match('/^((\/{1})(?=\w))/i', $data['basejs']) == 1) ? self::_HOMEPAGE_URL . $data['basejs'] : $data['basejs'];
			}
			$this->_trustedSessData = $data;
		}
		private function GetTrustedSessData()
		{
			return $this->_trustedSessData;
		}
		#endregion
	}
?>
