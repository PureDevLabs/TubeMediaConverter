<?php 

	namespace MediaConverterPro\lib;

	class FFMPEG
	{
		// Constants
		const _FFMPEG_LOG_PATH = '/store/ffmpeg-logs/';
		
		// Private Fields
		private static $_cacheVars = array(
			'fileIsCached' => false
		);

		public static function mergeVideo($videoSource, $audioSource, $ftype, $vidName, $duration) 
		{
			set_time_limit(0);
			$ftype = ($ftype == 'mp4') ? 'matroska' : $ftype;
			$vidName = preg_replace('/((\.mkv)?( \([^\)]+\))?(\.mp4))$/', "$3.mkv", $vidName);
			
			// Prepare execute cmd			
			$cmd = escapeshellarg(Config::_FFMPEG_PATH) . " -i " . escapeshellarg($videoSource) . " -i " . escapeshellarg($audioSource);
			$cmd .= " -c copy -t " . escapeshellarg($duration) . " -f " . escapeshellarg($ftype) . " pipe:1";
			$cmd .= (Config::_DEBUG_MODE) ? " 2>&1" : "";	

			$pipearray = array(1 => array('pipe', 'w'));

			// Check for valid resource 
			if (is_resource($process = proc_open($cmd, $pipearray, $pipes)))
			{
				self::prepareFFmpegDownload($vidName);
				self::readAndFlushBuffer($pipes[1], $process);
				// Close process
				proc_close($process);
			}	
		}

		public static function convertMP3($src, $mp3NiceName, $mp3Name, $dlsize, $quality, $vidDuration, $stime, $etime, $vidID, $converter=NULL)
		{
			set_time_limit(0);
			$vidHost = current(Config::$_videoHosts);
			
			// Get video and crop durations
			parse_str(parse_url($src, PHP_URL_QUERY), $iParams);
			$duration = (!isset($iParams['dur']) || (int)$iParams['dur'] == 0) ? $vidDuration : (int)$iParams['dur'];
			$cropDuration = (!empty($etime) && !empty($stime)) ? ((Core::checkValidTimeFormat($etime) && Core::checkValidTimeFormat($stime)) ? Core::seconds($etime) - Core::seconds($stime) : 0) : $vidDuration;
			$needsCropping = $cropDuration != $vidDuration && $cropDuration > 0;

			// Initialize caching, if enabled
			if (Config::_CACHE_FILES) self::cacheInit(compact('vidHost', 'vidID', 'quality', 'mp3NiceName', 'needsCropping'));

			if (self::$_cacheVars['fileIsCached'])
			{
				self::downloadCachedMP3($mp3Name);
			}
			else
			{
				// Prepare execute cmd
				$cmd = escapeshellarg(Config::_CURL_PATH);
				$cmd .= " -k -L " . escapeshellarg($src);
				//die($src);
				if (!Config::_ENABLE_SIMULATED_MP3)
				{
					$cmd .= " | " . escapeshellarg(Config::_FFMPEG_PATH) . " -i pipe:0 -b:a " . escapeshellarg($quality) . "k";
					$cmd .= ($needsCropping) ? " -ss " . escapeshellarg(Core::seconds($stime)) . " -t " . escapeshellarg($cropDuration) : "";
					$cmd .= " -f mp3 pipe:1";
				}
				$cmd .= (Config::_DEBUG_MODE) ? " 2>&1" : "";	
				$cmd = (Config::_CACHE_FILES) ? self::saveMP3($cmd, $vidID, $duration, $vidDuration, $cropDuration, $quality, $mp3NiceName) : $cmd;
				//die($cmd);

				// Execute process
				$pipearray = array(1 => array('pipe', 'w'));

				// Check for valid resource and if realtime datatransfer as mp3 audio chunks is allowed 
				if (is_resource($process = proc_open($cmd, $pipearray, $pipes)))
				{
					// Provide output mp3 size
					if ($duration > 0)
					{
						//header('Content-Length: ' . ($duration * ($quality / 8) * 1000));
					}
					self::prepareFFmpegDownload($mp3Name);
					$cacheFileParams = (Config::_CACHE_FILES && (bool)preg_match('/validate_cached_file\.php/', $cmd)) ? compact('duration') : array();
					self::readAndFlushBuffer($pipes[1], $process, $cacheFileParams);
					// Close process
					proc_close($process);   
				}
			}
		}
		
		public static function findCachedFiles($hostAbbrev, $vidID)
		{
			$bitrates = array();
			$CacheDir = dirname(__DIR__) . Config::_CACHE_PATH . $hostAbbrev . '/' . $vidID . '/mp3/';
			if (file_exists($CacheDir))
			{		
				$mp3Files = glob($CacheDir . "*.mp3");
				//die(print_r($mp3Files));
				if ($mp3Files !== false && !empty($mp3Files))
				{
					foreach ($mp3Files as $mf)
					{
						$bitrates[mt_rand(10000, 99999)] = array("file", current(explode("~", trim(strrchr($mf, "/"), "/"))), $mf);
					}
				}
			}
			return $bitrates;
		}
		
		private static function cacheInit(array $initVars)
		{
			// Prepare caching, if enabled
			extract($initVars);
			$CacheDir = dirname(__DIR__) . Config::_CACHE_PATH . $vidHost['abbreviation'] . '/' . $vidID . '/mp3/';
			$localName = $quality . '~' . preg_replace("/[^\p{L}\p{N}]+/u", "-", preg_replace('/[^\00-\255]+/u', '', $mp3NiceName));
			$localFile = $CacheDir . $localName . '.mp3';
			if (!file_exists($localFile) && file_exists($CacheDir))
			{		
				$mp3Files = glob($CacheDir . "*.mp3");
				//die(print_r($mp3Files));
				if ($mp3Files !== false && !empty($mp3Files))
				{
					foreach ($mp3Files as $mf)
					{
						if (preg_match('/((\/)(' . preg_quote($quality, "/") . '~)(.+?)(\.mp3))$/', $mf, $matches) == 1)
						{
							$localName = $matches[3] . $matches[4];
							$localFile = $CacheDir . $localName . '.mp3';
							break;
						}
					}
				}
			}
			$fileIsCached = !$needsCropping && file_exists($localFile);
			$logPath = dirname(__DIR__) . self::_FFMPEG_LOG_PATH;
			$ffmpegLog = $logPath . uniqid($localName . "~") . ".txt";
			if (!file_exists($logPath))
			{
				mkdir($logPath, 0777);
			}
			self::$_cacheVars = compact('CacheDir', 'fileIsCached', 'localFile', 'ffmpegLog');
		}

		private static function downloadCachedMP3($mp3Name)
		{
			$fh = fopen(self::$_cacheVars['localFile'], 'r');
			header('Content-Type: audio/mpeg3');
			header('Content-Disposition: attachment; filename="' . str_replace(['"', '?'], '', htmlspecialchars_decode($mp3Name, ENT_QUOTES)) . '"');
			header('Content-Length: ' . (int)filesize(self::$_cacheVars['localFile']));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Connection: Close');
			self::readAndFlushBuffer($fh);
			exit;
		}
		
		private static function prepareFFmpegDownload($fname)
		{
			// Send some important headers
			header('Content-Type: application/octet-stream');
			ignore_user_abort(true);
			header('Content-Disposition: attachment; filename="' . str_replace(['"', '?'], '', htmlspecialchars_decode($fname, ENT_QUOTES)) . '"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Connection: Close');

			// Activate flush
			if (function_exists('apache_setenv'))
			{
				apache_setenv('no-gzip', 1);
			}
			@ini_set('zlib.output_compression', false);
			ini_set('implicit_flush', true);
			ob_implicit_flush(true);
			ob_end_flush();			
		}
		
		private static function readAndFlushBuffer($resource, $process=false, array $cacheFileParams=array())
		{
			if (is_resource($resource))
			{
				// Get MP3 buffer
				while (!feof($resource)) 
				{
					$buffer = fread($resource, 1024 * 16);
					// Print MP3 buffer
					echo $buffer;
					// Provide mp3 audio as chunks
					if (ob_get_length() > 0) ob_flush();
					flush();
					if (connection_aborted() == 1 && is_resource($process))
					{
						proc_terminate($process);
						if (!empty($cacheFileParams))
						{
							extract(self::$_cacheVars);
							$args = array('p' => $localFile, 'd' => $cacheFileParams['duration'], 'l' => $ffmpegLog);
							include_once dirname(__DIR__) . "/inc/validate_cached_file.php";
						}
						break;
					}
				}
				// Close resource
				fclose($resource);	
			}
		}

		private static function saveMP3($cmd, $vidID, $duration, $vidDuration, $cropDuration, $quality, $mp3NiceName)
		{
			extract(self::$_cacheVars);
			$cacheText = $CacheDir . Config::_CACHE_AFTER_X . '.txt';
			$retVal = $cmd;
			if ((file_exists($CacheDir) && count(glob($CacheDir . "*.mp3")) > 0) || file_exists($cacheText))
			{
				//echo "youtube: " . $vidDuration;
				//echo "<br>cropped: " . $cropDuration;
				//die();
				if ($vidDuration == $cropDuration)
				{
					setlocale(LC_CTYPE, "en_US.UTF-8");
					$retVal = (Config::_DEBUG_MODE) ? preg_replace('/( 2>&1)$/', "", $retVal) : $retVal;
					$retVal .= (!Config::_ENABLE_SIMULATED_MP3) ? " -b:a " . escapeshellarg($quality) . "k -id3v2_version 3 -write_id3v1 1 -metadata title=" . escapeshellarg(htmlspecialchars_decode($mp3NiceName, ENT_QUOTES)) :  " 2> " . escapeshellarg($ffmpegLog) . " | tee";
					$retVal .= " " . escapeshellarg($localFile);
					$retVal .= (!Config::_ENABLE_SIMULATED_MP3) ? " 2> " . escapeshellarg($ffmpegLog) : ""; 
					$retVal .= "; php " . dirname(__DIR__) . "/inc/validate_cached_file.php -p " . escapeshellarg($localFile) . " -d " . escapeshellarg($duration) . " -l " . escapeshellarg($ffmpegLog);
					//die($retVal);
				}
			}
			else
			{
				if (!file_exists($CacheDir))
				{
					mkdir($CacheDir, 0777, true);
				}
				$ext = '.txt';
				$i = 1;
				$tmpname = $CacheDir . $i . $ext;
				while (file_exists($tmpname)) 
				{
					$tmpname = $CacheDir . ++$i . $ext;
				}
				file_put_contents($tmpname, '');
            }
			return $retVal;
		}
	}
 ?>