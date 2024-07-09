<?php 
	
	namespace MediaConverterPro\lib;

	class Cache
	{
		// Constants
		const _CACHE_CONFIG_PATH = '/store/cache.json';
		
		// Fields
		private static $_cacheVars = array(
			'links' => array(
				'dir' => '/store/cache/links/',
				'purge_interval' => 3600
			),
			'api' => array(
				'dir' => '/store/cache/api/',
				'purge_interval' => 86400
			)
		);
		
		// Methods
		public static function Cache(array $cacheVars)
	    {
	        // Check age of cached files, and delete any old files
			self::CheckCacheAge();
			// Read video links in cache
	        return self::ReadCache($cacheVars);
	    }
	    
	    private static function CheckCacheAge()
	    {
	        $config = self::RetrieveCacheConfig();
	        //die(print_r($config));
	        foreach (self::$_cacheVars as $cacheType => $cacheVar)
	        {
	        	if (isset($config[$cacheType]['lastpurge']))
	        	{
					$lastPurge = (int)$config[$cacheType]['lastpurge'];
					if (time() - $lastPurge >= $cacheVar['purge_interval'])
					{
						$config[$cacheType]['lastpurge'] = time();
						self::UpdateCacheConfig($config);
						self::ClearCache($cacheVar);
					}	        		
	        	}
	        }
	    }	
	    
	    private static function ReadCache(array $cacheVars)
    	{
	        extract($cacheVars);
	        clearstatcache();
	        $cache = array();
	        $isApiCache = isset($SearchTerm);
    		$CacheDir = dirname(__DIR__) . (($isApiCache) ? self::$_cacheVars['api']['dir'] : self::$_cacheVars['links']['dir']);
    		if (!file_exists($CacheDir)) 
    		{
			    mkdir($CacheDir, 0777, true);
			}
			$cacheFile = $CacheDir . (($isApiCache) ? $action . '_' . preg_replace(array('/\//', '/:/'), array("%2F", "%3A"), substr($SearchTerm, 0, 200)) . '_' . $Location : $uid . '_' . $streams . '_' . $vidID) . '.cache';
    		if (!file_exists($cacheFile) && isset($json) && !empty($json)) 
    		{			
				self::WriteCache($cacheFile, $json);
			}			
        	if (is_file($cacheFile)) 
        	{
            	// Open cache file
            	$file = file_get_contents($cacheFile);
            	if ($file !== false && !empty($file))
            	{
             		$arr = json_decode($file, true);
             		if (json_last_error() == JSON_ERROR_NONE)
             		{
						$cache = $arr;
             		}
             	}
            }
            return $cache;
    	}	    

		private static function WriteCache($cacheFile, array $json)
		{
			$json = json_encode($json);
			if (json_last_error() == JSON_ERROR_NONE)
			{
				file_put_contents($cacheFile, $json);
			}
		}

		private static function ClearCache(array $cacheVar)
	    {
			$now = time();
			$files = glob(dirname(__DIR__) . $cacheVar['dir'] . '*.cache');
			//die(print_r($files));
			if ($files !== false)
			{
				foreach ($files as $file)
				{
					if (is_file($file) && $now - filemtime($file) >= $cacheVar['purge_interval'])
					{
						unlink($file);
					}	
				}	
			}
        }
    	
    	private static function RetrieveCacheConfig()
    	{
    		$config = array();
    		$configFile = dirname(__DIR__) . self::_CACHE_CONFIG_PATH;
    		$fileExists = is_file($configFile);
    		if (!$fileExists || (int)filesize($configFile) == 0)
    		{
				$data = array();
				foreach (self::$_cacheVars as $cacheType => $cacheVar)
				{
					$data[$cacheType] = array('lastpurge' => time());
				}
    			$fileExists = self::UpdateCacheConfig($data);
    		}
    		if ($fileExists)
    		{
				$json = file_get_contents($configFile);
				if ($json !== false && !empty($json))
				{
					$configArr = json_decode($json, true);
					if (json_last_error() == JSON_ERROR_NONE)
					{
						$config = $configArr;
					}
				}
    		}
    		return $config;
    	}
    	
    	private static function UpdateCacheConfig(array $config)
    	{
    		$lockSucceeded = false;
    		$configFile = dirname(__DIR__) . self::_CACHE_CONFIG_PATH;
    		$json = json_encode($config);
			if (json_last_error() == JSON_ERROR_NONE)
			{
				$fp = fopen($configFile, 'w');
				if ($fp !== false)
				{
					if (flock($fp, LOCK_EX))
					{
						$lockSucceeded = true;
						fwrite($fp, $json);
						flock($fp, LOCK_UN);
					}
					fclose($fp);
					if ($lockSucceeded)
					{
						chmod($configFile, 0644);
					}
					else
					{
						unlink($configFile);
					}						
				}
			}
			return $lockSucceeded;
    	}
	}

 ?>