<?php
	use MediaConverterPro\lib\Config;

	include_once 'autoload.php';  // Autoload class files
	include_once dirname(__DIR__) . '/vendors/getID3/getid3/getid3.php';

	$fileInfo = array('playtime_seconds' => 0);
	$durationDiff = 0;
	$args = (!isset($args)) ? getopt("p:d:l:") : $args;
	if (isset($args['p'], $args['d'], $args['l']) && is_file($args['p']) && is_file($args['l']))
	{
		$logFile = (string)file_get_contents($args['l']);
		if (filesize($args['p']) !== false && filesize($args['p']) > 10000 && ((Config::_ENABLE_SIMULATED_MP3 && preg_match('/([\r\n]{1,2}(100\s+\S+\s+){2}[^\r\n]+)$/', $logFile) == 1) || (!Config::_ENABLE_SIMULATED_MP3 && preg_match('/muxing overhead/i', $logFile) == 1)))
		{
			$durationDiff = 0;
			$getID3 = new getID3;
			$fileInfo = @$getID3->analyze($args['p']);
			//print_r($fileInfo);
			if (isset($fileInfo['playtime_seconds']))
			{
				$durationDiff = abs((float)$fileInfo['playtime_seconds'] - (float)$args['d']);
				if ($durationDiff > Config::_MAX_ALLOWED_DURATION_DIFFERENCE)
				{
					unlink($args['p']);
				}
				else
				{
					// Cached file is valid!
					$containingDir = dirname($args['p']);
					$txtFile = 0;
					while (is_file($containingDir . "/" . ++$txtFile . ".txt"))
					{
						unlink($containingDir . "/" . $txtFile . ".txt");
					}
					if (!Config::_DEBUG_MODE) unlink($args['l']);
				}
			}		
		}
		else
		{
			unlink($args['p']);
		}

		// Log values for testing
		// $fp = fopen(dirname(__DIR__) . '/store/validate.log', 'a');
		// fwrite($fp, 'cached file: ' . $args['p'] . "\n");
		// fwrite($fp, 'youtube reported duration: ' . $args['d'] . "\n");
		// fwrite($fp, 'cached file duration: ' . $fileInfo['playtime_seconds'] . "\n");
		// fwrite($fp, 'duration difference: ' . $durationDiff . "\n\n");
		// fclose($fp);
	}

?>
