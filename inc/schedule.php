<?php
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\FFmpeg;

	// Set this file to run as a scheduled task on server. Depending on site traffic and the server's available HDD space, task frequency could range from 15 minutes to one day.
	// This script deletes old cached MP3 files as well as corresponding FFmpeg log files that were not otherwise deleted due to conversion failure or abandonment. 
	// Note: You MUST uncomment ALL unlink() and rmdir() commands to enable file and folder deletion !!

	ini_set('max_execution_time', 0);
	include 'autoload.php';  // Autoload class files
	DEFINE('DS', DIRECTORY_SEPARATOR);
	$appRoot = dirname(__DIR__);
	
	if (Config::_CACHE_FILES)
	{
		DEFINE('LOG_PURGE_INTERVAL', 3600);
		$maxCacheSize = Config::_MAX_CACHE_SIZE;

		$outputFolderFiles = array();
		$outputFolderSize = 0;
		$outputDirectory = new RecursiveDirectoryIterator($appRoot . Config::_CACHE_PATH, FilesystemIterator::SKIP_DOTS);
		$outputIterator = new RecursiveIteratorIterator($outputDirectory, RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD);
		try
		{
			foreach ($outputIterator as $outputMember)
			{
				//print_r($outputMember);
				//die(print_r($outputMember->getPathname()));
				$omPathname = $outputMember->getPathname();
				if ($outputMember->isDir())
				{
					$tempIterator = new FilesystemIterator($omPathname);
					if (!$tempIterator->valid())
					{
						// Directory is empty, so check if it's named 'mp3'. 
						// If it is, delete parent folder as well. If not, delete only this directory.
						$dirsToDelete = array($omPathname);
						$dirsToDelete += (preg_match('/^((.+?)(\/mp3))$/', $omPathname, $matched) == 1) ? array(1 => $matched[2]) : array();
						foreach ($dirsToDelete as $dir)
						{
							echo "deleting folder: " . $dir . "<br>";
							rmdir($dir);
						}
					}
					continue;
				}
				if ($outputMember->isFile())
				{
					$outputFolderSize += $outputMember->getSize() / 1000;
					$outputFolderFiles[$omPathname] = $outputMember->getMTime();
				}
			}
		}
		catch (Exception $e)
		{
			echo 'Caught exception (' . $e->getCode() . ') on line ' . $e->getLine() . ': "' .  $e->getMessage() . '"<br>';
		}
		//die(print_r($outputFolderFiles));
		//die($outputFolderSize);
		//die();

		if ($outputFolderSize > $maxCacheSize)
		{
			asort($outputFolderFiles);
			foreach ($outputFolderFiles as $file => $age)
			{
				if (is_file($file))
				{
					$fsize = (int)filesize($file) / 1000;
					echo "deleting file: " . $file . "<br>";
					unlink($file);
					$pathParts = explode(DS, $file);
					array_pop($pathParts);
					$path = implode(DS, $pathParts);
					$tempIterator = new FilesystemIterator($path);
					if (!$tempIterator->valid())
					{
						// Containing directory is empty, and it must be named 'mp3', so delete it and its parent folder.
						$dirsToDelete = array($path, preg_replace('/(\/mp3)$/', "", $path));
						foreach ($dirsToDelete as $dir)
						{
							echo "deleting folder: " . $dir . "<br>";
							rmdir($dir);	
						}						
					}
					$outputFolderSize = $outputFolderSize - $fsize;
					if ($outputFolderSize < $maxCacheSize - Config::_CACHE_SIZE_BUFFER) break;
				}
			}
		}

		echo "<br>";

		$logsDir = $appRoot . FFmpeg::_FFMPEG_LOG_PATH;
		if (is_dir($logsDir))
		{
			$logsIterator = new DirectoryIterator($logsDir);
			while ($logsIterator->valid())
			{
				$fname = $logsIterator->getFilename();
				if ($fname != '.' && $fname != '..' && $logsIterator->getMTime() + LOG_PURGE_INTERVAL < time())
				{
					echo $logsIterator->getPathname() . "<br>";
					unlink($logsIterator->getPathname());
				}
				$logsIterator->next();
			}
		}
	}

?>