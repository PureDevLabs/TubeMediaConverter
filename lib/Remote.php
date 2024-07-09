<?php 

	namespace MediaConverterPro\lib;

	class Remote
	{
		public static function chunkedDownload($remoteURL, $vidName, $ftype, $fsize, $currentIP, $converter=NULL, $outputInline=false)
		{
			$vidHost = current(Config::$_videoHosts);
			if (preg_match('/(' . preg_quote($vidHost['download_host'], '/') . ')$/', (string)parse_url($remoteURL, PHP_URL_HOST)) == 1)
		    {
				self::sendHeaders($outputInline, $vidName, $fsize);
				
				// Activate flush
				if (function_exists('apache_setenv'))
				{
					apache_setenv('no-gzip', 1);
				}
				@ini_set('zlib.output_compression', false);
				ini_set('implicit_flush', true);				
				ob_implicit_flush(true);
		
				// CURL Process
				$tryAgain = false;
				$ch = curl_init();
				$chunkEnd = $chunkSize = Config::_DOWNLOAD_CHUNK_SIZE;
				$tries = $count = $chunkStart = 0;
				while ($fsize >= $chunkStart)
				{
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_URL, $remoteURL . "&range=" . $chunkStart.'-'.$chunkEnd);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					if (Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && (!Config::_DISABLE_IP_FOR_DOWNLOAD || $tryAgain) && !empty($currentIP))
					{
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
							curl_setopt($ch, CURLOPT_TIMEOUT, (int)ceil(3 * (round($chunkSize / 1048576, 2) / (1 / 8))));
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
					curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
					curl_setopt($ch, CURLOPT_USERAGENT, Config::_REQUEST_USER_AGENT);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					if (Config::_REQUEST_IP_VERSION != -1)
					{
						curl_setopt($ch, CURLOPT_IPRESOLVE, constant("CURL_IPRESOLVE_V" . (string)Config::_REQUEST_IP_VERSION));
					}
					//curl_setopt($ch, CURLOPT_RANGE, $chunkStart.'-'.$chunkEnd);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_BUFFERSIZE, $chunkSize); 

					$output = curl_exec($ch);
					$curlInfo = curl_getinfo($ch);
					$tryAgainConds = Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && Config::_DISABLE_IP_FOR_DOWNLOAD && $count == 0;
					$tryAgain = ($tryAgainConds && !$tryAgain) ? $curlInfo['http_code'] == "403" : false;
					if ($tryAgain) 
					{	
						if (!empty($currentIP)) continue;
						break;
					}
					elseif (($curlInfo['http_code'] != "200" && $curlInfo['http_code'] != "206") && $tries < 10) 
					{
						$tryAgain = $tryAgainConds;
						$tries++;
						continue;
					}
					else
					{	
						$tries = 0;
						echo $output;
						flush();
						if (ob_get_length() > 0) ob_end_flush();				
					}
					
					$chunkStart += $chunkSize;
					$chunkStart += ($count == 0) ? 1 : 0;
					$chunkEnd += $chunkSize;
					$count++;
				}
				curl_close($ch);
        	}				
		}

		public static function download($remoteURL, $vidName, $ftype, $fsize, $currentIP, $converter=NULL, $outputInline=false)
		{
			$vidHost = current(Config::$_videoHosts);
			if (preg_match('/(' . preg_quote($vidHost['download_host'], '/') . ')$/', (string)parse_url($remoteURL, PHP_URL_HOST)) == 1)
		    {
				self::sendHeaders($outputInline, $vidName, $fsize);
				ob_start();
	
				// CURL Process
				$tryAgain = false;
				$ch = curl_init();
				do
				{
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_URL, $remoteURL);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					if (Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && (!Config::_DISABLE_IP_FOR_DOWNLOAD || $tryAgain) && !empty($currentIP))
					{
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
							curl_setopt($ch, CURLOPT_TIMEOUT, (int)ceil(3 * (round($fsize / 1048576, 2) / (1 / 8))));
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
					curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
					curl_setopt($ch, CURLOPT_USERAGENT, Config::_REQUEST_USER_AGENT);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					if (Config::_REQUEST_IP_VERSION != -1)
					{
						curl_setopt($ch, CURLOPT_IPRESOLVE, constant("CURL_IPRESOLVE_V" . (string)Config::_REQUEST_IP_VERSION));
					}
					curl_exec($ch);
					$tryAgain = (Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && Config::_DISABLE_IP_FOR_DOWNLOAD && !$tryAgain) ? (int)curl_getinfo($ch, CURLINFO_HTTP_CODE) == 403 : false;
					if ($tryAgain) ob_end_clean();
				}
				while (!empty($currentIP) && $tryAgain);
				curl_close($ch);

				// Activate flush
				if (function_exists('apache_setenv'))
				{
					apache_setenv('no-gzip', 1);
				}
				@ini_set('zlib.output_compression', false);
				ini_set('implicit_flush', true);
				ob_implicit_flush(true);
				
				flush();				
				ob_end_flush();
        	}
		}
		
		private static function sendHeaders($outputInline, $vidName, $fsize)
		{
			// Send some headers
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			if (!$outputInline)
			{
				header('Content-Disposition: attachment; filename="' . str_replace(['"', '?'], '', htmlspecialchars_decode($vidName, ENT_QUOTES)) . '"');
			}
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			if ($fsize > 0) 
			{
				header('Content-Length: ' . $fsize);
			}	
			header('Connection: Close');
			flush();			
		}
	}

 ?>