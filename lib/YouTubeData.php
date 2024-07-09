<?php 
	
	namespace MediaConverterPro\lib;

	use \MediaConverterPro\lib\extractors\YouTube; 

	class YouTubeData
	{
		// Private Fields
		private $_apiKey = '';
		private $_numRequestTries = 0;
		private $_useScrapedVidInfo = false;
		private $_converter;
		private $_isVideoUrl = false;
		
		// Constants
		const _VID_TITLE_URL_PATTERN = '/((\(([A-Za-z0-9_-]{11})\))$)/';
		const _URL_FILTER = '/(v=([A-Za-z0-9_-]+))|((\/([A-Za-z0-9_-]{11}).*)$)|((\(([A-Za-z0-9_-]{11})\))$)/';
		const _PLAYLIST_FILTER = '/[\?\/]list=([A-Za-z0-9_-]+)/'; 
		const _API_URL_PREFIX = 'https://www.googleapis.com/youtube/v3/'; 
		const _MAX_API_RESULTS = 50;
		const _MAX_API_REQUEST_TRIES = 10; // If API request fails, the number of times to keep trying a new, random API key before giving up.

		#region Public Methods 
		public function __construct(VideoConverter $converter)
		{
			$this->_apiKey = $this->SelectApiKey();
			$this->_useScrapedVidInfo = Config::_ENABLE_SEARCH_SCRAPING && Config::_SEARCH_SCRAPING_POPULATES_VID_INFO;
			$this->_converter = $converter;
		}		
		
		public function VideoInfo($SearchTerm, $Location)  
		{
			$videoInfo = array();	
			$action = 'videos';
			$useScrapedVidInfo = $this->_useScrapedVidInfo;
			$isScrapedVidInfo = false;
			if (!empty($SearchTerm))
			{
				$ids = (preg_match(self::_URL_FILTER, urldecode($SearchTerm), $vidurl) == 1) ? end($vidurl) : '';
				$this->_isVideoUrl = $isVideo = !empty($ids) && strlen($ids) == 11;
				$isPlaylist = preg_match(self::_PLAYLIST_FILTER, urldecode($SearchTerm), $playlist) == 1;
				$isScrapedVidInfo = $useScrapedVidInfo && !$isPlaylist;
				$SearchTerm = ($isVideo) ? $ids : $SearchTerm;
				$SearchTerm = (!$isPlaylist) ? $SearchTerm : $playlist[1];				
			}
			$json = Cache::Cache(compact('action', 'SearchTerm', 'Location'));
			//die(print_r($json));
			$isCached = !empty($json);
			if (!$isCached)
			{
				if (!empty($SearchTerm) && (!$isVideo || $useScrapedVidInfo))
				{
					$videos = ($isPlaylist) ? $this->RetrieveVideos('playlist', $SearchTerm, $Location) : $this->RetrieveVideos('search', $SearchTerm, $Location);
					//die(print_r($videos));
					if ((!$useScrapedVidInfo || $isPlaylist) && !empty($videos))
					{
						$videos = (count($videos) > self::_MAX_API_RESULTS) ? array_slice($videos, 0, self::_MAX_API_RESULTS) : $videos;
						foreach ($videos as $video)
						{
							$ids .= $video['id'];
							$ids .= ($video != end($videos)) ? ',' : ''; 
						}
					}
					$isScrapedVidInfo = ($isScrapedVidInfo && $isVideo && empty($videos)) ? false : $isScrapedVidInfo;
				}
				$apiRequestUrl = self::_API_URL_PREFIX . 'videos?key=' . $this->_apiKey . '&part=snippet,statistics,contentDetails&type=video&regionCode=' . $Location . '&maxResults=' . self::_MAX_API_RESULTS;
				$apiRequestUrl .= (!empty($SearchTerm)) ? '&id=' . $ids : '&chart=mostPopular&videoCategoryId=' . Config::_TOP_VIDEOS_CATEGORY;
				//die($apiRequestUrl);
				$apiResponse = ($isScrapedVidInfo) ? $videos : file_get_contents($apiRequestUrl);   
				if (($isScrapedVidInfo || ($apiResponse !== false && $this->IsValidApiResponse($http_response_header))) && !empty($apiResponse))
				{
					$jsonArr = ($isScrapedVidInfo) ? array('items' => $apiResponse) : json_decode($apiResponse, true);
					$json = (json_last_error() == JSON_ERROR_NONE) ? $jsonArr : $json;
				}
			}
			if (isset($json['items']) && !empty($json['items']))
			{
				//if ($isScrapedVidInfo) die(print_r($json['items']));
				if (!$isCached) Cache::Cache(compact('action', 'SearchTerm', 'Location', 'json'));
				foreach ($json['items'] as $k => $item) 
				{
					$vid = (!isset($item['id']['videoId'])) ? ((!isset($item['id'])) ? '' : $item['id']) : $item['id']['videoId'];
					$videoInfo[] = array(
						'id' => $vid,
						'title' => ((!isset($item['snippet']['title'])) ? ((!isset($item['title'])) ? '' : $item['title']) : $item['snippet']['title']), 
						'description' => ((!isset($item['snippet']['description'])) ? ((!isset($item['description'])) ? '' : $item['description']) : $item['snippet']['description']),
						'tags' => ((isset($item['snippet']['tags']) && is_array($item['snippet']['tags']) && !empty($item['snippet']['tags'])) ? implode(", ", $item['snippet']['tags']) : ((isset($item['tags']) && is_array($item['tags']) && !empty($item['tags'])) ? implode(", ", $item['tags']) : '')),
						'thumbDefault' => $this->GenerateThumbImage($vid, 'lq'),
						'thumbMedium' => $this->GenerateThumbImage($vid, 'mq'),
						'thumbHigh' => $this->GenerateThumbImage($vid, 'hq'),
						'channelTitle' => ((!isset($item['snippet']['channelTitle'])) ? ((!isset($item['channelTitle'])) ? '' : $item['channelTitle']) : $item['snippet']['channelTitle']),
						'channelId' => ((!isset($item['snippet']['channelId'])) ? ((!isset($item['channelId'])) ? '' : $item['channelId']) : $item['snippet']['channelId']),
						'publishedAt' => ((!isset($item['snippet']['publishedAt'])) ? ((!isset($item['publishedAt'])) ? '' : $item['publishedAt']) : $this->Convdate($item['snippet']['publishedAt'])),
						'duration' => ((!isset($item['contentDetails']['duration'])) ? ((!isset($item['duration'])) ? '' : $item['duration']) : $this->Convtime($item['contentDetails']['duration'])),
						'definition' => ((isset($item['contentDetails']['definition'])) ? $item['contentDetails']['definition'] : ''),
						'dimension'  => ((isset($item['contentDetails']['dimension'])) ? $item['contentDetails']['dimension'] : ''),
						'viewCount' => ((!isset($item['statistics']['viewCount'])) ? ((!isset($item['viewCount'])) ? '' : $item['viewCount']) : $item['statistics']['viewCount']),
						'likeCount' => ((isset($item['statistics']['likeCount'])) ? $item['statistics']['likeCount'] : ''),
						'dislikeCount' => ((isset($item['statistics']['dislikeCount'])) ? $item['statistics']['dislikeCount'] : '')
					);
					$videoInfo[$k] = ($isScrapedVidInfo) ? array_filter($videoInfo[$k]) : $videoInfo[$k];
				}
			}
		    if (!$isScrapedVidInfo && empty($videoInfo) && ++$this->_numRequestTries < self::_MAX_API_REQUEST_TRIES)
		    {
		    	// Try the API requests again with another random API key
		    	$this->_apiKey = $this->SelectApiKey();
		    	$videoInfo = $this->VideoInfo($SearchTerm, $Location);
		    }		
			return $videoInfo;	
		}
		#endregion
		
		#region Private Methods
		private function RetrieveVideos($action, $SearchTerm, $Location=null)
		{
			$videos = array();
			$jsonArr = array();
			$json = Cache::Cache(compact('action', 'SearchTerm', 'Location'));	
			$isCached = !empty($json);
			if (!$isCached)
			{
				if (Config::_ENABLE_SEARCH_SCRAPING && $action != 'playlist')
				{
					$jsonArr = json_decode($this->ScrapeSearchWrapper($SearchTerm), true);
				}
				else
				{
					$apiRequestUrl = self::_API_URL_PREFIX;
					$apiRequestUrl .= ($action == 'playlist') ? 'playlistItems?key=' . $this->_apiKey .'&part=snippet,contentDetails,id,status&playlistId=' . urlencode($SearchTerm) . '&maxResults=' . self::_MAX_API_RESULTS : 'search?key=' . $this->_apiKey . '&part=snippet&type=video&regionCode=' . urlencode($Location) . '&maxResults=' . self::_MAX_API_RESULTS . '&q=' . urlencode($SearchTerm);
					$apiResponse = file_get_contents($apiRequestUrl); 
					//die($apiResponse);
					if ($apiResponse !== false && $this->IsValidApiResponse($http_response_header) && !empty($apiResponse))
					{
						$jsonArr = json_decode($apiResponse, true);
					}					
				}
				$json = (!empty($jsonArr) && json_last_error() == JSON_ERROR_NONE) ? $jsonArr : $json;
			}
			if (isset($json['items']) && !empty($json['items']))
			{
				if (!$isCached) Cache::Cache(compact('action', 'SearchTerm', 'Location', 'json'));
				foreach ($json['items'] as $item) 
				{
					switch ($action)
					{
						case 'playlist':
							if (isset($item['contentDetails']['videoId'], $item['status']['privacyStatus']) && !empty($item['contentDetails']['videoId']) && !empty($item['status']['privacyStatus'])) 
							{
								$videos[] = array('id' => $item['contentDetails']['videoId'], 'status' => $item['status']['privacyStatus']);
							}									
							break;
						default:
							if (isset($item['id']['videoId']) && !empty($item['id']['videoId']))
							{
								$videos[] = ($this->_useScrapedVidInfo) ? $item : array('id' => $item['id']['videoId']);
							}
					}
				}
			}
			return $videos;	
		}
		
		private function ScrapeSearchWrapper($SearchTerm)
		{
			$action = "search";
			$extractorName = "YouTube";
			$Location = Config::_DEFAULT_COUNTRY;
			$converter = $this->_converter;
			$converter->SetCurrentVidHost($extractorName);
			$converter->SetExtractor($extractorName);
			$extractor = $converter->GetExtractor();
			$json = (is_object($extractor) && method_exists($extractor, 'ScrapeSearch')) ? $extractor->ScrapeSearch($SearchTerm, $this->_isVideoUrl) : array();
			if (isset($json['items']) && !empty($json['items']))
			{
				Cache::Cache(compact('action', 'SearchTerm', 'Location', 'json'));
				$json = (string)json_encode($json, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
			}
			return (!is_array($json)) ? $json : "{}";			
		}
		
		private function Convtime($youtube_time)
		{
		    $retval = '';
		    $isException = false;
		    $time = $interval = null;
		    if (!empty($youtube_time))
		    {
		    	try
		    	{
		    		$time = new \DateTime('@0'); // Unix epoch
		    		$interval = new \DateInterval($youtube_time);
		    	}
		    	catch (\Exception $ex)
		    	{
		    		$isException = true;
		    	}
		    }
		    if ($time instanceof \DateTime && $interval instanceof \DateInterval && !$isException && $time->add($interval) !== false)
		    {
		    	$formattedTime = $time->format('H:i:s');    	
		    	if ($formattedTime !== false)
		    	{
		    		$retval = (Core::seconds($formattedTime) >= 3600) ? $formattedTime : $time->format('i:s');
		    	}
			}
			return ltrim((string)$retval, "0");
		}  

		private function Convdate($youtube_date)
		{
		    $retval = '';
		    $isException = false;
		    $time = null;
		    if (!empty($youtube_date))
		    {
		    	try
		    	{
					$time = new \DateTime($youtube_date);
		    	}
		    	catch (\Exception $ex)
		    	{
		    		$isException = true;
		    	}
		    }		    
		    if ($time instanceof \DateTime && !$isException)
		    {
		    	$formattedTime = $time->format('M d, Y');
		    	$retval = ($formattedTime !== false) ? $formattedTime : $retval;
		    }		    
		    return (string)$retval;
		}
		
		private function IsValidApiResponse(array $response)
		{
			return preg_match('/\d{3}/', $response[0], $matches) == 1 && (int)$matches[0] < 400;
		}
		
		private function SelectApiKey()
		{
			$keys = Config::$_youtubeApiKeys;
			$randomIndex = mt_rand(0, count($keys) - 1);
			return (isset($keys[$randomIndex])) ? $keys[$randomIndex] : '';
		}
		
		private function GenerateThumbImage($vid, $quality)
		{
			$defaultUrl = YouTube::_THUMB_URL_PREFIX . $vid . "/" . YouTube::_THUMB_FILES[$quality] . ".jpg";
			$webpUrl = YouTube::_WEBP_URL_PREFIX . $vid . "/" . YouTube::_THUMB_FILES[$quality] . ".webp";
			return (Config::_ENABLE_WEBP_THUMBS) ? $webpUrl : $defaultUrl;
		}
		#endregion
	}
                   
 ?>