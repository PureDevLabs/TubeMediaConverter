<?php 

	namespace MediaConverterPro\lib;

	class Core
	{
		// Get Remote File Size
		// Deprecated - May be removed in future versions!
   		public static function GetRemoteFileSize($url, $converter=NULL)
   		{
			$result = -1;  // Assume failure.

			// Issue a HEAD request and follow any redirects.
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_NOBODY, true);
			curl_setopt($curl, CURLOPT_HEADER, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			if (Config::_ENABLE_IP_ROTATION_FOR_VIDEOS && !is_null($converter))
			{
				if ($converter->GetOutgoingIP() == '') $converter->SetOutgoingIP();
				curl_setopt($curl, CURLOPT_REFERER, '');
				curl_setopt($curl, CURLOPT_INTERFACE, $converter->GetOutgoingIP());
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			}
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_USERAGENT, Config::_REQUEST_USER_AGENT);
			$headers = curl_exec($curl);
			if (curl_errno($curl) == 0)
			{
				$result = (int)curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
			}
			curl_close($curl);
			return $result;
		}

   		public static function formatSize($bytes)
   		{
   			$size = '-';
   			switch ($bytes) 
	        {
	            case $bytes < 1024:
	                $size = $bytes .' B'; 
	                break;
	            case $bytes < 1048576:
	                $size = round($bytes / 1024, 2) .' KB'; 
	                break;
	            case $bytes < 1073741824:
	                $size = round($bytes / 1048576, 2) . ' MB'; 
	                break;
	            case $bytes < 1099511627776:
	                $size = round($bytes / 1073741824, 2) . ' GB'; 
	                break;
	        }

	        return $size; // return formatted size
   		}

		// Available MP3 Bitrate
		public static function Bitrate()
		{
			return Config::$_mp3Qualities;
		}

		// Calculate MP3 Output File Sizes
		public static function MP3OutputSize($duration)
		{
			$MP3Sizes = array();
			$bitrates = self::Bitrate();
			foreach ($bitrates as $br)
			{
				$MP3Sizes[] = $duration * ($br / 8) * 1000;
			}			
			return $MP3Sizes; // return MP3 Output sizes in bytes
		}

		public static function checkValidTimeFormat($value) 
		{
		  	return preg_match("/\d{2}:[0-5]\d:[0-5]\d/", $value) == 1;
	  	}

	  	public static function seconds($time)
	  	{
			$time = explode(':', $time);
			return (count($time) > 2) ? ((int)$time[0] * 3600) + ((int)$time[1] * 60) + (int)$time[2] : ((int)$time[0] * 60) + (int)$time[1];
		}

		public static function MobileNumbers($number, $precision=1) 
		{
		    if ($number < 1000000) 
		    {
		        $format = number_format($number / 1000, $precision) . 'k+';
		    } 
		    else if ($number < 1000000000) 
		    {
		        $format = number_format($number / 1000000, $precision) . 'M+';
		    } 
		    else 
		    {
		        $format = number_format($number / 1000000000, $precision) . 'B+';
		    }
		    return $format;
		}
		
		public static function httpProtocol()
		{
			$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443;
			$isHttps = (!$isHttps) ? isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' : $isHttps;
			if (!$isHttps && isset($_SERVER['HTTP_CF_VISITOR']))
			{
				$cfJson = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
				if (json_last_error() == JSON_ERROR_NONE)
				{
					$isHttps = !empty($cfJson) && current($cfJson) == 'https';
				}
			}
			return ($isHttps) ? "https://" : "http://";		
		}
		
		public static function refererIP()
		{
			$ipaddress = '';
			if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
				$ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];			
			elseif (isset($_SERVER['HTTP_CLIENT_IP']))
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			elseif (isset($_SERVER['HTTP_X_FORWARDED']))
				$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			elseif (isset($_SERVER['HTTP_FORWARDED_FOR']))
				$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			elseif (isset($_SERVER['HTTP_FORWARDED']))
				$ipaddress = $_SERVER['HTTP_FORWARDED'];
			elseif (isset($_SERVER['REMOTE_ADDR']))
				$ipaddress = $_SERVER['REMOTE_ADDR'];
			return $ipaddress;		
		}		
		
		public static function detectCountryInfo()
		{
			$cCode = Config::_DEFAULT_COUNTRY;
			$Continent = Config::_DEFAULT_COUNTRY_GROUP;
			$cCodeDetected = (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('/[a-z]+-([A-Z]+)/', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $cCodeMatch) == 1) ? strtolower($cCodeMatch[1]) : '';
			if (!empty($cCodeDetected))
			{
				foreach (Config::$_countries as $group => $countries)
				{
					if (isset($countries[$cCodeDetected])) 
					{
						$cCode = $cCodeDetected;
						$Continent = $group;
						break;
					}
				}
			}
			return compact('cCode', 'Continent');
		}
		
		public static function checkVideoBlocked($vidID)
		{
			return isset(Config::$_blockedVideos[$vidID]);
		}
	}

 ?>