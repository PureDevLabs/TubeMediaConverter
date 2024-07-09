<?php

	namespace MediaConverterPro\lib;
	
	include dirname(__DIR__) . '/vendors/getID3/getid3/getid3.php';

	// Conversion Class
	class VideoConverter 
	{
		// Private Fields
		private $_convertedFileType = '';
		private $_convertedFileCategory = '';
		private $_vidSourceUrls = array();
		private $_currentVidHost = '';
		private $_vidInfo = array();
		private $_extractor;
		private $_outgoingIP = array();
		private $_dbo;
		private $_appVars = array();

		// Constants
		const _URL_WILDCARD_PATTERN = '[^\\\\/\?]+';

		#region Public Methods
		public function __construct(array $appVars, $dbo=NULL)
		{
			$this->_appVars = $appVars;
			$this->_dbo = $dbo;
		}
		
		public function GenerateVideoDataForOutput($vidID, $format, $streams, $urlLang, $api='')
		{
			$vidURL = 'https://youtu.be/' . $vidID;
			
			$vidHost = current(Config::$_videoHosts);
			$cachedUrls = (Config::_CACHE_FILES && $format == "mp3") ? FFmpeg::findCachedFiles($vidHost['abbreviation'], $vidID) : array();  // If available, use cached MP3 files when all download links are invalid
			$bitrates = Core::Bitrate();
			$skipExtractor = count($cachedUrls) >= count($bitrates);			
			
			$validVideoUrl = $this->ValidateVideo($vidURL, !$skipExtractor);
			$error = '';
			$langObj = $this->_appVars['langObj'];
			$translations = $this->_appVars['translations'];
			if ($validVideoUrl && !empty($format))
			{
				if (!$skipExtractor)
				{
					$extractor = $this->GetExtractor();
					$vidInfo = $this->GetVidInfo();
					$this->SetVidSourceUrls();
					$duration = (isset($vidInfo['duration'])) ? (int)$vidInfo['duration'] : 0;
					$vidTitle = htmlspecialchars($vidInfo['title'], ENT_QUOTES);
					$vidThumb = $vidInfo['thumb_preview'];					
				}
				else
				{
					$firstCachedFile = current($cachedUrls);
					$getID3 = new \getID3;
					$fileInfo = @$getID3->analyze($firstCachedFile[2]);
					//die(print_r($fileInfo));
					$duration = (isset($fileInfo['playtime_seconds'])) ? (int)$fileInfo['playtime_seconds'] : 0;
					$vidTitle = (isset($fileInfo['id3v2']['comments']['title'][0])) ? htmlspecialchars($fileInfo['id3v2']['comments']['title'][0], ENT_QUOTES) : 'Unknown';
					$vidThumb = ($vidHost['name'] == "YouTube") ? 'https://img.youtube.com/vi/'.$vidID.'/0.jpg' : '';						
				}
				
				if ($duration > 0 || !empty($cachedUrls))
				{
					if (count($this->GetVidSourceUrls()) > 0 || !empty($cachedUrls))
					{
						$vidSourceUrls = $this->GetVidSourceUrls();
						$vidHostQuals = array_keys($vidHost['video_qualities']);
						$json = array();
						$uid = uniqid();
						$success = array(false);
						$duplicateItags = array();	
						$urls = $this->SortVidSourceUrls($vidSourceUrls, $vidHostQuals);
						//die(print_r($urls));
						$isJsonApi = $api == "json";
						$dloadHash = $this->GenerateVideoDownloadHash($vidID, $isJsonApi);
						switch ($format)
						{
							case "mp3":
								$useDashFormats = Config::_MP3_DOWNLOAD_SOURCE == "DASH" && !empty($urls['audiostreams']);
								if (!empty($urls['videos']) || $useDashFormats || !empty($cachedUrls))
								{
									$buttons = array();
									$mp3Urls = array();
									$MP3Sizes = Core::MP3OutputSize($duration);
									$etime = gmdate('H:i:s', $duration);
									$streams = $format;
									if ($useDashFormats)
									{
										foreach ($urls['audiostreams'] as $itag => $url)
										{
											if ((int)Config::$_itags['audiostreams'][$itag]['bitrate'] >= 128)
											{
												$mp3Urls[$itag] = $url;
											}
										}
									}
									$mp3Urls += $urls['videos'];
									$mp3Urls += $cachedUrls;
									//die(print_r($mp3Urls));
									foreach ($mp3Urls as $itag => $url)
									{	
										$srcformat = $url[0];
										$srcqual = $url[1];
										$url = end($url);
										$isCachedOnly = $srcformat == "file";
										$dloadUrlInfo = ($isCachedOnly) ? array('isValid' => true, 'filesize' => (int)filesize($url)) : $extractor->CheckDownloadUrl($url, $extractor->GetSignature($itag));
										//$dloadUrlInfo = ($isCachedOnly) ? array('isValid' => true, 'filesize' => 0) : array('isValid' => false);
										if ($dloadUrlInfo['isValid'])
										{
											$token = $itag  . '-' . $uid;
											foreach ($bitrates as $key => $bitrate)
											{
												if ($isCachedOnly && (int)$srcqual != $bitrate) continue;
												$buttons[] = array(
													/*'url' => $url,*/
													'dloadUrl' => WEBROOT . $urlLang . '@download/' . $token . "-" . (($isCachedOnly || Config::_ENABLE_SIMULATED_MP3) ? $dloadUrlInfo['filesize'] : $MP3Sizes[$key]) . "-" . $duration . "-" . $bitrate . "-" . $srcformat . "-" . $dloadUrlInfo['filesize'] . "/mp3/" . $vidID . "/" . urlencode(urlencode(str_replace('/', "-", $vidTitle))) . ".mp3/" . $dloadHash,
													'bitrate' => $bitrate,
													'mp3size' => (($isCachedOnly || Config::_ENABLE_SIMULATED_MP3) ? Core::formatSize($dloadUrlInfo['filesize']) : Core::formatSize($MP3Sizes[$key]))
												);
											}
											$json[$token] = base64_encode($url);
											$success[0] = true;
											if (!$isCachedOnly) break;
										}
									}
								}
								$error = (!$success[0]) ? $translations['download_urls'] : $error;								
								break;
							case "mp4":
								$videoData = array();
								$mp4Urls = array();
								$invalidItags = array();
								if (!empty($streams) && !empty($urls[$streams]))
								{
									$mp4Urls = $urls[$streams];
									//die(print_r($mp4Urls));
									foreach ($mp4Urls as $itag => $url)
									{
										if (isset(Config::$_itags[$streams][$itag]) && !in_array(Config::$_itags[$streams][$itag], $duplicateItags))
										{
											$srcformat = $url[0];
											$token = $itag . '-' . $uid;
											$dloadUrlInfo = $extractor->CheckDownloadUrl($url[2], $extractor->GetSignature($itag));
											if ($dloadUrlInfo['isValid'])
											{
												$json[$token] = base64_encode($url[2]);
												$rSize = $dloadUrlInfo['filesize'];
												$videoData[] = array(
													'rSize' => Core::formatSize($rSize),
													'quality' => ((isset(Config::$_itags[$streams][$itag]['quality'])) ? Config::$_itags[$streams][$itag]['quality'] : ''),
													'directurl' => $url[2],
													'dloadUrl' => WEBROOT . $urlLang . '@download/' . $token . "-" . $srcformat . "-" . $rSize . "/" . $streams . "/" . $vidID . "/" . urlencode(urlencode(str_replace('/', "-", $vidTitle))) . "." . $srcformat . "/" . $dloadHash,
													'ftype' => $srcformat,
													'framerate' => ((isset(Config::$_itags[$streams][$itag]['framerate'])) ? Config::$_itags[$streams][$itag]['framerate'] : ''),
													'bitrate' => ((isset(Config::$_itags[$streams][$itag]['bitrate'])) ? Config::$_itags[$streams][$itag]['bitrate'] : ''),
													'codec' => ((isset(Config::$_itags[$streams][$itag]['codec'])) ? Config::$_itags[$streams][$itag]['codec'] : ''),
													'itag' => $itag
												);
												//print_r($videoData);
												$duplicateItags[] = Config::$_itags[$streams][$itag];
												$success = ($success == array(false)) ? array(true) : array_merge($success, array(true));
											}
											else
											{
												$invalidItags[] = $itag;
											}
										}
									}
								}
								//print_r($json);
								$error = ($success != array(false)) ? ((!empty($invalidItags)) ? $langObj->ReplacePlaceholders($translations['invalid_itags'], array(implode(", ", $invalidItags))) : $error) : $translations['download_urls'];
								break;
							case "video":
								$videoData = array();
								$invalidItags = array();
								if (!empty($streams) && !empty($urls['videostreams']) && !empty($urls['audiostreams']))
								{								
									$videoUrls = $urls['videostreams'];
									$audioUrls = $urls['audiostreams'];
									//die(print_r($videoUrls));
									foreach ($videoUrls as $itag => $url)
									{
										$vidSrcformat = $url[0];
										$displayVidSrcformat = ($vidSrcformat == "mp4") ? Config::_MERGED_VIDEO_STREAM_LABEL : $vidSrcformat;
										if (isset(Config::$_itags['videostreams'][$itag]) && !in_array(Config::$_itags['videostreams'][$itag], $duplicateItags))
										{
											$resolution = Config::$_itags['videostreams'][$itag]['resolution'];
											$audioItag = Config::$_itags[$streams][$resolution]['audio'];
											if (isset($audioUrls[$audioItag], Config::$_itags['audiostreams'][$audioItag]))
											{	
												$audioSrcformat = $audioUrls[$audioItag][0];
												$urlToken = $itag . ':' . $audioItag . '-' . $uid;												
												$videoDloadUrlInfo = $extractor->CheckDownloadUrl($url[2], $extractor->GetSignature($itag));
												$audioDloadUrlInfo = $extractor->CheckDownloadUrl($audioUrls[$audioItag][2], $extractor->GetSignature($audioItag));
												if ($videoDloadUrlInfo['isValid'] && $audioDloadUrlInfo['isValid'])
												{
													if (!isset($json[$itag . '-' . $uid]))
													{
														$json[$itag . '-' . $uid] = base64_encode($url[2]);
													}
													if (!isset($json[$audioItag . '-' . $uid]))
													{
														$json[$audioItag . '-' . $uid] = base64_encode($audioUrls[$audioItag][2]);
													}													
													$videoSize = $videoDloadUrlInfo['filesize'];
													$audioSize = $audioDloadUrlInfo['filesize'];
													parse_str(parse_url($url[2], PHP_URL_QUERY), $iParams);
													$duration = (!isset($iParams['dur']) || (int)$iParams['dur'] == 0) ? $duration : (int)$iParams['dur'];													
													$videoData[] = array(
														'rSize' => Core::formatSize($videoSize + $audioSize),
														'quality' => ((isset(Config::$_itags['videostreams'][$itag]['quality'])) ? Config::$_itags['videostreams'][$itag]['quality'] : ''),
														'directurl' => '',
														'dloadUrl' => WEBROOT . $urlLang . '@download/' . $urlToken . "-" . $vidSrcformat . ":" . $audioSrcformat . "-" . $videoSize . ":" . $audioSize . "-" . $duration . "/" . $streams . "/" . $vidID . "/" . urlencode(urlencode(str_replace('/', "-", $vidTitle))) . "." . $displayVidSrcformat . "/" . $dloadHash,
														'ftype' => $displayVidSrcformat,
														'framerate' => ((isset(Config::$_itags['videostreams'][$itag]['framerate'])) ? Config::$_itags['videostreams'][$itag]['framerate'] : ''),
														'bitrate' => ((isset(Config::$_itags['videostreams'][$itag]['bitrate'])) ? Config::$_itags['videostreams'][$itag]['bitrate'] : ''),
														'codec' => ((isset(Config::$_itags['videostreams'][$itag]['codec'])) ? Config::$_itags['videostreams'][$itag]['codec'] : ''),
														'itag' => $resolution
													);
													//print_r($videoData);
													$duplicateItags[] = Config::$_itags['videostreams'][$itag];
													
													$success = ($success == array(false)) ? array(true) : array_merge($success, array(true));
												}
												else
												{
													if (!$videoDloadUrlInfo['isValid'] && !isset($invalidItags[$itag])) $invalidItags[$itag] = $itag;
													if (!$audioDloadUrlInfo['isValid'] && !isset($invalidItags[$audioItag])) $invalidItags[$audioItag] = $audioItag;
												}
											}
										}
									}
								}
								//print_r($json);
								$error = ($success != array(false)) ? ((!empty($invalidItags)) ? $langObj->ReplacePlaceholders($translations['invalid_itags'], array(implode(", ", $invalidItags))) : $error) : $translations['download_urls'];
								break;	
							default:
								$error = $translations['selected_format'];
						}
						if (Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && $this->GetCurrentVidHost() == "YouTube" && !empty($json)) $json['reqip'] = base64_encode(json_encode($this->GetOutgoingIP()));
						if (!empty($streams)) Cache::Cache(compact('vidID', 'uid', 'streams', 'json'));
					}
					else
					{
						$error = $translations['download_urls'];
					}					
				}
				else
				{
					$error = $translations['no_streams'];
				}				
			}
			
			$newVars = array('error', 'duration', 'vidID', 'vidTitle', 'vidThumb', 'etime', 'buttons', 'videoData', 'isCachedOnly');
			$moreVars = array();
			foreach ($newVars as $newVar)
			{
				if (isset(${$newVar})) $moreVars[$newVar] = ${$newVar};
			}
			//die(print_r($moreVars));
			return $moreVars;
		}
	
		public function ExtractVideoId($vidUrl)
		{
			$id = '';
			$url = trim($vidUrl);
			$urlQueryStr = parse_url($url, PHP_URL_QUERY);
			if ($urlQueryStr !== false && !empty($urlQueryStr))
			{
				parse_str($urlQueryStr, $params);
				if (isset($params['v']) && !empty($params['v']))
				{
					$id = $params['v'];
				}
				else
				{
					$url = preg_replace('/(\?' . preg_quote($urlQueryStr, '/') . ')$/', "", $url);
					$id = trim(strrchr(trim($url, '/'), '/'), '/');
				}
			}
			else
			{
				$id = trim(strrchr(trim($url, '/'), '/'), '/');
			}
			return $id;
		}
		
		public function GenerateVideoDownloadHash($vidID, $isJsonApi)
		{
			$hashInfo = array('videoID' => $vidID);
			$hashInfo += (!$isJsonApi) ? array('refererIP' => Core::refererIP()) : array();
			return hash_hmac('sha256', json_encode($hashInfo), Config::_WEBSITE_NAME) . "-" . (string)$isJsonApi;		
		}
		#endregion
		
		#region Private "Helper" Methods
		private function ValidateVideo($vidUrl, $getVidInfo=false, $moreOptions=array())
		{
			$vidHostName = $convertedFtype = '';
			$vidHosts = $this->GetVideoHosts();
			foreach ($vidHosts as $host)
			{
				foreach ($host['url_root'] as $urlRoot)
				{
					//$urlRoot = preg_replace('/^(([^\?]+?)(\?{1})(.+))$/', "$2$3", $urlRoot);
					$rootUrlPattern = preg_replace('/#wildcard#/', "[^\\\\/]+", preg_quote($urlRoot, '/'));
					$rootUrlPattern = ($host['allow_https_urls']) ? preg_replace('/^(http)/', "https?", $rootUrlPattern) : $rootUrlPattern;
					if (preg_match('/^('.$rootUrlPattern.')/i', $vidUrl) == 1)
					{
						$vidHostName = $host['name'];
						break 2;
					}
				}
			}
			$convertedFtype = 'mp3';
			$convertedFcategory = 'audio';		
			if (!empty($vidHostName) && !empty($convertedFtype) && !empty($convertedFcategory))
			{
				$this->SetCurrentVidHost($vidHostName);
				$this->SetConvertedFileType($convertedFtype);
				$this->SetConvertedFileCategory($convertedFcategory);
				$this->SetExtractor($vidHostName);
				if ($getVidInfo)
				{
					$extractor = $this->GetExtractor();
					$this->_vidInfo = $extractor->RetrieveVidInfo($vidUrl);
				}
				return true;
			}
			return false;
		}	
		
		private function SortVidSourceUrls(array $vidSourceUrls, array $vidHostQuals)
		{
			$urls = array('audiostreams' => array(), 'videos' => array(), 'videostreams' => array());
			foreach ($vidSourceUrls as $url)
			{
				if (preg_match('/itag=([0-9]+)/', $url[2], $itag) == 1)
				{
					$itag = $itag[1];
					$url[0] = str_replace(array('video/', 'x-', '3gpp', 'audio/mp4', 'audio/webm'), array('', '', '3gp', 'm4a', 'webm'), $url[0]);
					if ($url[1] == "au" && isset(Config::$_itags['audiostreams'][$itag]))
					{
						$urls['audiostreams'][$itag] = $url;
					}
					elseif (in_array($url[1], $vidHostQuals) && isset(Config::$_itags['videos'][$itag]))
					{
						$urls['videos'][$itag] = $url;
					}
					elseif (isset(Config::$_itags['videostreams'][$itag]))
					{
						$urls['videostreams'][$itag] = $url;
					}
				}
			}
			foreach ($urls as $ftype => $ftypeUrls)
			{
				if (!empty($ftypeUrls))
				{
					$itags = Config::$_itags[$ftype];
					$firstItag = current($itags);
					$sortKey = (isset($firstItag['bitrate'])) ? 'bitrate' : 'quality';
					$hasFramerateKey = isset($firstItag['framerate']);
					$hasResolutionKey = isset($firstItag['resolution']);
					$hasFtypeKey = isset($firstItag['ftype']);
					uksort($ftypeUrls, function($a, $b) use($itags, $sortKey, $hasFramerateKey, $hasFtypeKey, $hasResolutionKey) {
						$isEqual1 = (int)$itags[$a][$sortKey] == (int)$itags[$b][$sortKey]; 
						$isEqual2 = ($hasFramerateKey) ? (int)$itags[$a]['framerate'] == (int)$itags[$b]['framerate'] : $isEqual1;
						$isEqual3 = ($hasResolutionKey) ? trim(strrchr($itags[$a]['resolution'], "-"), "-") == trim(strrchr($itags[$b]['resolution'], "-"), "-") : $isEqual2;
						$isEqual4 = ($hasFtypeKey) ? $itags[$a]['ftype'] == $itags[$b]['ftype'] : $isEqual1;
						if ($isEqual1 && $isEqual2 && $isEqual3 && $isEqual4) return 0;

						$isLess1 = (int)$itags[$a][$sortKey] < (int)$itags[$b][$sortKey];
						$isLess2 = ($isEqual1 && $hasFramerateKey) ? (int)$itags[$a]['framerate'] < (int)$itags[$b]['framerate'] : $isLess1;
						$isLess3 = ($isEqual1 && $isEqual2 && $hasResolutionKey) ? strcasecmp(trim(strrchr($itags[$a]['resolution'], "-"), "-"), trim(strrchr($itags[$b]['resolution'], "-"), "-")) > 0 : $isLess2;
						$isLess4 = ($isEqual1 && $hasFtypeKey) ? strcasecmp($itags[$a]['ftype'], $itags[$b]['ftype']) > 0 : $isLess1;
						return ($isLess1 || $isLess2 || $isLess3 || $isLess4) ? 1 : -1;
					});
					$urls[$ftype] = $ftypeUrls;
				}
			}
			return $urls;
		}
		#endregion

		#region Properties
		public function GetVidSourceUrls()
		{
			return $this->_vidSourceUrls;
		}
		private function SetVidSourceUrls()
		{
			$extractor = $this->GetExtractor();
			$this->_vidSourceUrls = $extractor->ExtractVidSourceUrls();
		}

		public function GetVideoHosts()
		{
			return Config::$_videoHosts;
		}

		public function GetCurrentVidHost()
		{
			return $this->_currentVidHost;
		}
		public function SetCurrentVidHost($hostName)
		{
			$this->_currentVidHost = $hostName;
		}

		public function GetVidInfo()
		{
			return $this->_vidInfo;
		}

		public function GetConvertedFileType()
		{
			return $this->_convertedFileType;
		}
		private function SetConvertedFileType($ftype)
		{
			$this->_convertedFileType = $ftype;
		}

		public function GetConvertedFileCategory()
		{
			return $this->_convertedFileCategory;
		}
		private function SetConvertedFileCategory($fcat)
		{
			$this->_convertedFileCategory = $fcat;
		}

		public function GetExtractor()
		{
			return $this->_extractor;
		}
		public function SetExtractor($vidHostName)
		{
			$classname = $this->_appVars['AntiCaptcha']['Extractors'] . $vidHostName;
			try {$this->_extractor = new $classname($this);}
			catch(\Exception $ex) {}
		}

		public function GetOutgoingIP()
		{
			return $this->_outgoingIP;
		}
		public function SetOutgoingIP($ipsTable='_DB_IPS_TABLE')
		{
			$dbTableName = constant(__NAMESPACE__ . '\\Config::' . $ipsTable);
			$noTor = !Config::_ENABLE_TOR_PROXY && !is_null($this->_dbo);
			$skipIP = false;
			$outgoingIP = (!$noTor) ? array('ip' => '127.0.0.1', 'port' => Config::_TOR_PROXY_PORT) : array();
			$tries = 0;
			$resetBan = array();
			do
			{
				if ($noTor)
				{
					$resetBan = array();
					if (Config::_IP_ROTATION_METHOD == "round-robin")
					{
						$ips = $this->_dbo->Find($dbTableName, array('order' => array('usage_count')));
						$outgoingIP = (!empty($ips)) ? $ips[0] : array();
					}
					else
					{
						$ips = $this->_dbo->Find($dbTableName, array('order' => array('id')));
						$allBanned = true;
						if (!empty($ips))
						{
							foreach ($ips as $ip)
							{
								if ($ip['banned'] == 0)
								{
									$outgoingIP = $ip;
									$allBanned = false;
									break;
								}
							}
							if ($allBanned)
							{
								$this->_dbo->UpdateAll($dbTableName, array('banned' => 0));
								$ips = $this->_dbo->Find($dbTableName, array('order' => array('id')));
								$outgoingIP = (!empty($ips)) ? $ips[0] : array();
							}
						}
					}
				}				
				if (!empty($outgoingIP))
				{
					$skipIP = ($noTor) ? $outgoingIP['banned'] != 0 && time() - $outgoingIP['banned'] < Config::_IP_BAN_PAUSE : false;
					if (!$skipIP)
					{
						$extractor = $this->GetExtractor();
						if (!is_object($extractor))
						{
							$vidHost = current($this->GetVideoHosts());
							$this->SetExtractor($vidHost['name']);
							$extractor = $this->GetExtractor();
						}
						$ipReqResult = $extractor->CheckIp($outgoingIP);
						$resetBan = ($noTor) ? (($ipReqResult['isBanned']) ? array('banned' => time()) : array('banned' => 0)) : $resetBan;
						$skipIP = $ipReqResult['isBanned'] || $ipReqResult['isCurlErr'];
						if (!$noTor && $skipIP)
						{
							$fp = fsockopen($outgoingIP['ip'], Config::_TOR_CONTROL_PORT, $error_number, $err_string, 10);	
							if ($fp !== false) 
							{
								fwrite($fp, "AUTHENTICATE \"" . Config::_TOR_PROXY_PASSWORD . "\"\n");
								$received = fread($fp, 512);								
								fwrite($fp, "signal NEWNYM\n");
								$received = fread($fp, 512);
								fclose($fp);
							}						
						}						
					}
					if ($noTor)
					{
						$this->_dbo->Save($dbTableName, array('id' => $outgoingIP['id'], 'usage_count' => ++$outgoingIP['usage_count']) + $resetBan);
					}
				}
				$tries++;
			}
			while ((empty($outgoingIP) || $skipIP) && $tries < Config::_MAX_CURL_TRIES);
			$this->_outgoingIP = (empty($outgoingIP)) ? array('ip' => $_SERVER['SERVER_ADDR']) : $outgoingIP;
		}
		
		public function GetDbo()
		{
			return $this->_dbo;
		}		
		#endregion		
	}

?>
