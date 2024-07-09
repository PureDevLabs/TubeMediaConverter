<?php 
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\YouTubeData;
	
	$tests = array();
	$success = '<span style="color:green"><i class="fa fa-check"></i></span>';
	$failed = '<span style="color:red"><i class="fa fa-times"></i></span>';

	// Install binaries, if they are not already installed
	$possibleBins = array('curl', 'ffmpeg', 'node');
	$currentBin = '';
	$storeDir = dirname(__DIR__) . '/store/';
	if (!is_dir($storeDir . 'bin')) mkdir($storeDir . 'bin');
	$binDir = $storeDir . 'bin/';
	foreach ($possibleBins as $bin)
	{
		${$bin . 'InStore'} = is_file($binDir . $bin);
		$currentBin = (empty($currentBin) && isset($_POST['binToInstall']) && $_POST['binToInstall'] == $bin) ? $bin : $currentBin;
	}
	if (function_exists('exec') && !empty($currentBin) && !${$currentBin . 'InStore'})
	{
		exec('arch', $arch);
		//die(print_r($arch));	
		if (!empty($arch))
		{
			$archBits = ($arch[0] == "x86_64") ? '64' : '32';
			switch ($currentBin)
			{
				case "ffmpeg":
					exec('uname -r', $kernelVersion);
					//die(print_r($kernelVersion));
					if (!empty($kernelVersion))
					{
						$kvNum = current(explode("-", $kernelVersion[0]));
						if (strnatcmp($kvNum, '2.6.32') >= 0)
						{
							exec('wget -O ' . $binDir . $currentBin . ' http://puredevlabs.cc/builds/' . $currentBin . '/' . $archBits);
						}
					}					
					break;	
				case "curl":
					exec('wget -O ' . $binDir . $currentBin . ' http://puredevlabs.cc/builds/' . $currentBin . '/' . $archBits);					
					break;
				case "node":
					if ($archBits == '64')
					{
						exec('wget -O ' . $binDir . $currentBin . ' http://puredevlabs.cc/builds/' . $currentBin . '/' . $archBits);	
					}			
					break;							
			}
			${$currentBin . 'InStore'} = is_file($binDir . $currentBin);
			${$currentBin . 'InStore'} = (${$currentBin . 'InStore'}) ? chmod($binDir . $currentBin, 0755) : ${$currentBin . 'InStore'};			
		}
	}
	
	// Check app root directory
	$appRoot = pathinfo($_SERVER['PHP_SELF'], PATHINFO_DIRNAME);
	$appRoot .= ($appRoot != "/") ? "/" : "";
	//die($appRoot);
	$tests['app_root'] = $appRoot == Config::_APPROOT;
	//$tests['app_root'] = false;
	
	// "store" Folder permissions
	$appStorePerms = substr(decoct(fileperms($storeDir)), -4);
	$tmpFile = $storeDir . "tmp.txt";
	$fp = @fopen($tmpFile, "w");
	$isWritable = $fp !== false;
	if ($isWritable)
	{
		fclose($fp);
		unlink($tmpFile);
	}
	$tests['appStorePerms'] = $isWritable;
	//$tests['appStorePerms'] = false;

	// Check PHP version
	$tests['php_version'] = (version_compare(PHP_VERSION, '7.4.0') >= 0 && version_compare(PHP_VERSION, '8.0.0') < 0) || version_compare(PHP_VERSION, '8.1.0') >= 0;
	//$tests['php_version'] = false;
	
	// Check ionCube Loader PHP extension
	$tests['php_ioncube'] = extension_loaded("IonCube Loader");
	//$tests['php_ioncube'] = false;
	
	// Check GD PHP extension
	$tests['php_gd'] = extension_loaded("gd");
	//$tests['php_gd'] = false;

	// Check SimpleXML PHP extension
	$tests['php_xml'] = extension_loaded("simplexml");
	//$tests['php_xml'] = false;

	// Check for PHP open_basedir restriction
	$phpOpenBaseDir = ini_get('open_basedir');
	$noObdRestriction = empty($phpOpenBaseDir) || $phpOpenBaseDir == "no value";
	if (!empty($phpOpenBaseDir) && $phpOpenBaseDir != "no value")
	{
		$absAppDir = dirname(dirname(__FILE__)) . "/";
		$obDirs = explode(":", $phpOpenBaseDir);
		$dirPattern = '/^(';
		foreach ($obDirs as $dir)
		{
			$dirPattern .= '(' . preg_quote($dir, "/") . ')';
			$dirPattern .= ($dir != end($obDirs)) ? '|' : '';
		}
		$dirPattern .= ')/';
		$noObdRestriction = preg_match($dirPattern, $absAppDir) == 1;
	}
	$tests['phpOpenBaseDir'] = $noObdRestriction;
	//$tests['phpOpenBaseDir'] = false;

	// Check if PHP proc_open() is enabled and working
	$phpExecRuns = function_exists('proc_open');
	if ($phpExecRuns)
	{
		$pipearray = array(1 => array('pipe', 'w'));
		$process = @proc_open(Config::_FFMPEG_PATH . ' -version', $pipearray, $pipes);
		$phpExecRuns = is_resource($process) && !feof($pipes[1]);
	}
	$tests['phpExec'] = $phpExecRuns;
	//$tests['phpExec'] = false;
	
	// Check if PHP exec() is enabled and working
	$phpExecRuns2 = function_exists('exec');
	if ($phpExecRuns2)
	{
		$execData = array();
		@exec(Config::_FFMPEG_PATH . ' -version', $execData);
		//die(print_r($execData));
		$phpExecRuns2 = isset($execData[0]) && !empty($execData[0]);
	}
	$tests['phpExec2'] = $phpExecRuns2;
	//$tests['phpExec2'] = false;

	// Get default directories that PHP exec() is allowed to access
	$validExecPaths = getenv('PATH');
	$validPathArr = array_merge([dirname(__DIR__) . "/store/bin"], explode(":", $validExecPaths));
	$validPathArrTrunc = array_slice($validPathArr, 0, count($validPathArr) - 1);

	// Check for cURL
	$curlExists = array();
	@exec('type curl', $curlExists);
	//die(print_r($curlExists));
	$curlFound = false;
	$curlPath = array();
	if (!empty($curlExists))
	{
		$curlFound = preg_match('/^((curl is )(.+))/i', $curlExists[0], $curlPath) == 1;
	}
	if (!$curlFound && $curlInStore)
	{
		$curlFound = $curlInStore;
		$curlPath[3] = $binDir . 'curl';
	}	
	$tests['curlExists'] = $curlFound && Config::_CURL_PATH == trim($curlPath[3]);
	//$tests['curlExists'] = false;

	if ($tests['curlExists'])
	{
		// Check for cURL version
		$curlVersion = array();
		@exec(Config::_CURL_PATH . ' -V', $curlVersion);
		//die(print_r($curlVersion));
		$tests['curlVersion'] = isset($curlVersion[0]) && preg_match('/\d+\.\d+.\d+/', $curlVersion[0], $curlVersionNo) == 1;
		//$tests['curlVersion'] = false;	

		// Check for PHP cURL extension
		$tests['phpCurl'] = extension_loaded("curl");
		//$tests['phpCurl'] = false;

		if ($tests['phpCurl'])
		{
			$curlVersionInfo = curl_version();
			//die(print_r($curlVersionInfo));
			$curlVersionNo = array($curlVersionInfo['version']);

			// Check for DNS error resolving site domain name
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . $appRoot . "inc/version.php");
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			$result = curl_exec($ch);
			//echo 'Curl error: (' . curl_errno($ch) . ') ' . curl_error($ch) . '<br>';
			//die(print_r(curl_getinfo($ch)));
			$tests['dns'] = curl_errno($ch) == 0;
			curl_close($ch);
			//$tests['dns'] = false;

			$tests['ssl'] = false;
			if ($tests['dns'])
			{
				// Check for Url Rewriting
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . $appRoot . "test-url-rewriting");
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_NOBODY, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 15);
				$result = curl_exec($ch);
				//die(print_r(curl_getinfo($ch)));
				$tests['urlRewriting'] = curl_errno($ch) == 0 && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200;
				curl_close($ch);
				//$tests['urlRewriting'] = false;				
				
				// Check for SSL/TLS
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://" . $_SERVER['HTTP_HOST'] . $appRoot . "inc/version.php");
				curl_setopt($ch, CURLOPT_HEADER, 1);
				curl_setopt($ch, CURLOPT_NOBODY, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_TIMEOUT, 15);
				$result = curl_exec($ch);
				$tests['ssl'] = curl_errno($ch) == 0;
				curl_close($ch);
				//$tests['ssl'] = false;
			}
			
			// Check for valid YouTube API key or keys
			$validYoutubeKeys = $invalidYoutubeKeys = array();
			foreach (Config::$_youtubeApiKeys as $key)
			{
				$apiRequestUrl = YouTubeData::_API_URL_PREFIX . 'videos?key=' . $key . '&part=snippet,statistics,contentDetails&type=video&regionCode=' . Config::_DEFAULT_COUNTRY . '&maxResults=1&chart=mostPopular&videoCategoryId=10';
				$apiResponse = file_get_contents($apiRequestUrl);   
				if ($apiResponse !== false && !empty($apiResponse))
				{
					$json = json_decode($apiResponse, true);  
					if (json_last_error() == JSON_ERROR_NONE && isset($json['items']) && !empty($json['items']))
					{
						$validYoutubeKeys[] = $key;
					}
				}
				if (!in_array($key, $validYoutubeKeys))
				{
					$invalidYoutubeKeys[] = $key;
				}
			}
			$tests['youtubeAPI'] = !empty($validYoutubeKeys) && !empty(Config::$_youtubeApiKeys) && count($validYoutubeKeys) == count(Config::$_youtubeApiKeys);
			//$tests['youtubeAPI'] = false;

			// Check for YouTube CAPTCHA
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.youtube.com/watch?v=QcIy9NiNbmo");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			//die($result);
			$hasCaptcha = curl_errno($ch) == 0 && (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 429 || preg_match('/^((<form)(.+?)(das_captcha)(.+?)(<\/form>))$/msi', $result) == 1);
			curl_close($ch);
			$tests['captcha'] = $hasCaptcha;
			//$tests['captcha'] = true;
			
			if ($tests['captcha'])
			{
				// Check for Anti-Captcha Plugin
				$hasAcPlugin = isset(Config::$_plugins['AntiCaptcha']) && file_exists(dirname(__DIR__) . "/vendors/AntiCaptcha") && class_alias('MediaConverterPro\\vendors\\AntiCaptcha\\lib\\Http', 'Http') && class_alias('MediaConverterPro\\vendors\\AntiCaptcha\\lib\\Config', 'AcConfig') && class_exists('Http') && class_exists('AcConfig');
				if ($hasAcPlugin)
				{
					$testAcResponse = Http::request("https://api.anti-captcha.com/getBalance", array(
						"clientKey" => AcConfig::_ANTI_CAPTCHA_API_KEY
					), '', false);
					//die($testAcResponse);
					$hasAcPlugin = !empty($testAcResponse);
					if ($hasAcPlugin)
					{
						$aCjson = json_decode(trim($testAcResponse), true);
						$validAntiCaptchaKey = isset($aCjson['errorId']) && (int)$aCjson['errorId'] == 0;
						$hasAcPlugin = $validAntiCaptchaKey;
					}
				}
				$tests['AntiCaptcha'] = $hasAcPlugin;
				//$tests['AntiCaptcha'] = false;
			}
		}
	}

	// Check for MySQL
	$mysqlExists = array();
	@exec('type mysql', $mysqlExists);
	//die(print_r($mysqlExists));
	$tests['mysqlExists'] = !empty($mysqlExists) && preg_match('/^(mysql is )/i', $mysqlExists[0]) == 1;
	//$tests['mysqlExists'] = false;

	if ($tests['mysqlExists'])
	{
		// Get MySQL version
		$mysqlVersion = array();
		@exec('mysql -V', $mysqlVersion);
		//die(print_r($mysqlVersion));
		if (!empty($mysqlVersion)) preg_match('/\d+\.\d+.\d+/', $mysqlVersion[0], $mysqlVersionNo);

		// Check for PHP MySQLi extension
		$tests['phpMySQLi'] = extension_loaded("mysqli");
		//$tests['phpMySQLi'] = false;

		if ($tests['phpMySQLi'])
		{
			// Check database connection
			$mysqli = new mysqli(Config::_SERVER, Config::_DB_USER, Config::_DB_PASSWORD, Config::_DATABASE);
			$tests['dbconnect'] = is_null($mysqli->connect_error);
			//$tests['dbconnect'] = false;

			if ($tests['dbconnect'])
			{
				$mysqlVersionNo = array(mysqli_get_server_info($mysqli));
				//die(print_r($mysqlVersionNo));			
				
				// Check for "ips" database table
				$tests['ipsTable'] = $mysqli->query("SELECT * FROM " . Config::_DB_IPS_TABLE) !== false;
				//$tests['ipsTable'] = false;
				
				if ($tests['ipsTable'])
				{
					// Check that IP rotation is enabled
					$tests['ipRotEnabled'] = Config::_ENABLE_IP_ROTATION_FOR_VIDEOS;
					//$tests['ipRotEnabled'] = false;					
				}
			}
		}
	}
	
	// Check that Anti-Captcha Plugin and IP rotation are not running concurrently
	//$tests['captchaMethodErr'] = $tests['AntiCaptcha'] && $tests['mysqlExists'] && $tests['phpMySQLi'] && $tests['dbconnect'] && $tests['ipsTable'] && $tests['ipRotEnabled'];
	$tests['captchaMethodErr'] = false;
	//$tests['captchaMethodErr'] = true;

	// Check for FFmpeg install
	$ffmpegLocation = array();
	@exec('type ffmpeg', $ffmpegLocation);
	//die(print_r($ffmpegLocation));
	$ffmpegFound = false;
	$ffmpegPath = array();
	if (!empty($ffmpegLocation))
	{
		$ffmpegFound = preg_match('/^((ffmpeg is )(.+))/i', $ffmpegLocation[0], $ffmpegPath) == 1;
	}
	if (!$ffmpegFound && $ffmpegInStore)
	{
		$ffmpegFound = $ffmpegInStore;
		$ffmpegPath[3] = $binDir . 'ffmpeg';
	}
	if (!$ffmpegFound)
	{
		$isValidFFmpegPath = false;
		foreach ($validPathArr as $validPath)
		{
			$isValidFFmpegPath = preg_match('/^(' . preg_quote($validPath, "/") . ')/', Config::_FFMPEG_PATH) == 1;
			if ($isValidFFmpegPath) break;
		}
	}
	$tests['FFmpeg'] = $ffmpegFound && Config::_FFMPEG_PATH == trim($ffmpegPath[3]);
	//$tests['FFmpeg'] = false;
	//$isValidFFmpegPath = false;

	if ($tests['FFmpeg'])
	{
		// Check for FFmpeg version
		$ffmpegInfo = array();
		@exec(Config::_FFMPEG_PATH . ' -version', $ffmpegInfo);
		//die(print_r($ffmpegInfo));
		$tests['FFmpegVersion'] = isset($ffmpegInfo[0]) && !empty($ffmpegInfo[0]);
		//$tests['FFmpegVersion'] = false;

		// Check for codecs
		$libmp3lame = array();
		@exec(Config::_FFMPEG_PATH . ' -codecs | grep -E "(\s|[[:space:]])libmp3lame(\s|[[:space:]])"', $libmp3lame);
		//die(print_r($libmp3lame));
		$tests['libmp3lame'] = isset($libmp3lame[0]) && !empty($libmp3lame[0]) && preg_match('/E/', current(preg_split('/\s/', $libmp3lame[0], -1, PREG_SPLIT_NO_EMPTY))) == 1;
		//$tests['libmp3lame'] = false;
	}

	// Check that Dynamic IPv6 Generation is enabled
	$tests['dynaIPv6'] = Config::_ENABLE_DYNAMIC_IPV6;
	//$tests['dynaIPv6'] = false;
	
	if ($tests['dynaIPv6'])
	{
		// Check that IPv6 is forced for requests 
		$tests['reqIpVersion'] = Config::_REQUEST_IP_VERSION == 6;
		//$tests['reqIpVersion'] = false;
		
		// scheduler.php permissions
		$schedulerPerms = substr(decoct(fileperms(__DIR__ . '/scheduler.php')), -4);
		$tests['schedulerPerms'] = (int)$schedulerPerms >= 755;
		//$tests['schedulerPerms'] = false;
	}

	// Check for Node.js install
	$nodeJS = array();
	@exec('type node', $nodeJS);
	//die(print_r($nodeJS));
	$nodeFound = false;
	$nodePath = array();
	if (!empty($nodeJS))
	{
		$nodeFound = preg_match('/^((node is )(.+))/i', $nodeJS[0], $nodePath) == 1;
	}
	if (!$nodeFound && $nodeInStore)
	{
		$nodeFound = $nodeInStore;
		$nodePath[3] = $binDir . 'node';
	}
	if (!$nodeFound)
	{
		$isValidNodePath = false;
		foreach ($validPathArr as $validPath)
		{
			$isValidNodePath = preg_match('/^(' . preg_quote($validPath, "/") . ')/', Config::_NODEJS_PATH) == 1;
			if ($isValidNodePath) break;
		}
	}
	$tests['nodejs'] = $nodeFound && Config::_NODEJS_PATH == trim($nodePath[3]);
	//$tests['nodejs'] = false;
	//$isValidNodePath = false;

	if ($tests['nodejs'])
	{
		// Check for Node.js version
		$nodeInfo = array();
		@exec(Config::_NODEJS_PATH . ' -v', $nodeInfo);
		$tests['NodeVersion'] = isset($nodeInfo[0]) && !empty($nodeInfo[0]);
		//$tests['NodeVersion'] = false;
	}

	// Get Config constant and variable line numbers
	$configVars = array('_FFMPEG_PATH', '_CURL_PATH', '_NODEJS_PATH', '\$_youtubeApiKeys', '_APPROOT', '_SERVER', '_DB_USER', '_DB_PASSWORD', '_DATABASE', '_DB_IPS_TABLE', '_ENABLE_IP_ROTATION_FOR_VIDEOS', '_ENABLE_DYNAMIC_IPV6', '_REQUEST_IP_VERSION');
	$configLines = file(dirname(dirname(__FILE__)) . "/lib/Config.php");
	$linesPattern = '/^((\s*)((const )|(public static ))((' . implode(")|(", $configVars) . ')))/';
	$linesArr = preg_grep($linesPattern, $configLines);
	$lineNumsArr = array();
	foreach ($linesArr as $num => $line)
	{
		$lineNumsArr[trim(preg_replace('/^((\s*)((const )|(public static ))(\S+)(.*))$/', "$6", $line))] = $num + 1;
	}
	//die(print_r($lineNumsArr));
	
	// If Anti-Captcha is installed, get plugin Config constant line numbers
	if (isset($validAntiCaptchaKey))
	{
		$configVars = array('_ANTI_CAPTCHA_API_KEY');
		$configLines = file(dirname(__DIR__) . "/vendors/AntiCaptcha/lib/Config.php");
		$linesPattern = '/^((\s*)((const )|(public static ))((' . implode(")|(", $configVars) . ')))/';
		$linesArr = preg_grep($linesPattern, $configLines);
		$lineNumsArr2 = array();
		foreach ($linesArr as $num => $line)
		{
			$lineNumsArr2[trim(preg_replace('/^((\s*)((const )|(public static ))(\S+)(.*))$/', "$6", $line))] = $num + 1;
		}
		//die(print_r($lineNumsArr2));
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Check Configuration</title>
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css" />
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" />
  <style type="text/css">
	@import url(https://fonts.googleapis.com/css?family=Architects+Daughter);
	body {background-color:#ccc;font-family:Verdana,Arial;font-size:13px;line-height:16px;}
	h3 {font-size:20px;font-weight:bold;margin:15px 0 25px 0;text-align:center;}
	h4, h5 {font-size:22px;margin:25px 0 15px 0;font-family:"Architects Daughter",Verdana;color:#f9f9f9;padding:10px 12px;background:#111;}
	h5 {font-size:4px;padding:0;}
	ul {margin-left:11px;}
	ul li, p {margin:15px 0;}
	ul ul {margin-left:9px;}
	ul ul li {padding-left:9px;text-indent:-9px;}
	ul ul ul {margin-left:0;}
	#container {width:720px;margin:20px auto;padding:20px;background-color:#f9f9f9;}
	.response span {text-indent:2px;font-weight:bold;font-style:italic;font-size:18px;}
	.response span a {font-size:17px;}
	.popover-content {text-indent:0;font-style:normal;font-family:Verdana,Arial;font-size:13px;}
	.popover-title {background:#f2dede;border-color:#ebccd1;color:#a94442;font-style:normal;font-family:Verdana,Arial;font-size:13px;}
	.popover {padding:0;}
	.orange {color:#FB9904;font-size:15px;}
	.dark-orange {color:#cc0000;font-weight:bold;}
	.red {color:#ff4d4d;}
	.italic {font-style:italic;}
	.bold {font-weight:bold;}
	.buttons {text-align:center;margin:25px auto 5px auto;}
  </style>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
  <script type="text/javascript">
  	$(document).ready(function(){
		$(".ttip").popover();
		
		$(".rerun").click(function(){
			location.href = location.href;
		});

		$(".printpage").click(function(){
			window.print();
		});

		$(".popup").click(function(){
			runTests();
		});

		$("#leave").click(function(){
			location.href += "?config=complete&ssl=<?php echo (isset($tests['ssl']) && $tests['ssl']) ? 's' : ''; ?>";
		});

		$(".binInstall").submit(function(e){	
			if (!runTests('appStorePerms')) 
			{
				e.preventDefault();
			}
			else
			{
				$(this).find('i').removeClass('fa-cogs').addClass('fa-refresh fa-spin');
			}
		});
	});

	function runTests(testName)
	{
		var testsPassed = true;
		var isExit = typeof testName == "undefined";
		var tests = {
			phpExec: <?php echo ($tests['phpExec']) ? "true" : "false"; ?>,
			phpExec2: <?php echo ($tests['phpExec2']) ? "true" : "false"; ?>,
			appStorePerms: <?php echo ($tests['appStorePerms']) ? "true" : "false"; ?>,
			youtubeAPI: <?php echo ($tests['youtubeAPI']) ? "true" : "false"; ?>,
			dbConnect: <?php echo ($tests['dbconnect']) ? "true" : "false"; ?>,
			ionCube: <?php echo ($tests['php_ioncube']) ? "true" : "false"; ?>
		};
		if (!isExit && typeof tests[testName] != "undefined")
		{
			var testResult = tests[testName];
			tests = {};
			tests[testName] = testResult;
		}
		for (var test in tests)
		{
			if (!tests[test])
			{
				$("#fix-" + test).css("display", "inline");
				var offset = $("#fix-" + test).offset();
				offset.top -= 100;
				$("html, body").animate({
				    scrollTop: offset.top
				}, 400, function(){
					$("#fix-" + test).tooltip('show');
				});
				testsPassed = false;
				break;
			}
		}
		if (isExit && testsPassed) $('#exitModal').modal();
		return testsPassed;
	}	
  </script>
</head>
<body>
	<div id="container">
		<h3>Check Your Server/Software Configuration. . .</h3>
		<p>This page will check your server and software installations for errors. Please ensure that you read through the results thoroughly and do not proceed until all tests have passed.</p>
		<?php if (!$tests['php_version'] || !$tests['php_ioncube'] || !$tests['php_gd'] || !$tests['php_xml'] || !$tests['phpExec'] || !$tests['phpExec2'] || !$tests['phpOpenBaseDir'] || !$tests['curlExists'] || !$tests['curlVersion'] || !$tests['phpCurl'] || !$tests['dns'] || !$tests['urlRewriting'] || !$tests['FFmpeg'] || !$tests['FFmpegVersion'] || !$tests['libmp3lame'] || !$tests['appStorePerms'] || !$tests['app_root'] || !$tests['youtubeAPI'] || !$tests['mysqlExists'] || !$tests['phpMySQLi'] || !$tests['dbconnect'] || (isset($tests['captcha']) && $tests['captcha'] && ($tests['captchaMethodErr'] || (!$tests['AntiCaptcha'] && (!$tests['ipsTable'] || !$tests['ipRotEnabled']))))) { ?>
			<div class="alert alert-danger alert-dismissible fade in" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<b><i class="fa fa-exclamation-triangle"></i> Warning!</b> &nbsp;You should <b>at least</b> confirm that all <b>"Required"</b> settings are OK!
			</div>
		<?php } ?>
		<div class="alert alert-info alert-dismissible fade in" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<b><i class="fa fa-question-circle"></i> Questions?:</b> &nbsp;Get <b>help troubleshooting common issues</b> using "<a href="http://<?php echo $_SERVER['HTTP_HOST'] . $appRoot; ?>docs#faq" onclick="window.open(this.href); return false;" class="alert-link">The Official FAQ</a>".
		</div>
		<div class="alert alert-warning alert-dismissible fade in" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<b><i class="fa fa-info-circle"></i> Support:</b> &nbsp;Find the <b>full array of support options</b> in the <a href="http://<?php echo $_SERVER['HTTP_HOST'] . $appRoot; ?>docs/" onclick="window.open(this.href); return false;" class="alert-link">Software Documentation</a>.
		</div>
		<div class="buttons">
			<button class="btn btn-primary rerun"><i class="fa fa-refresh"></i> Run the tests again.</button> <button class="btn btn-success printpage"><i class="fa fa-print"></i> Print this page.</button> <button class="btn btn-danger popup"><i class="fa fa-sign-out"></i> Get me out of here!</button>
		</div>

		<h4><u>Required</u> settings. . .</h4>
		<?php if (isset($tests['captcha']) && $tests['captcha'] && ($tests['captchaMethodErr'] || (!$tests['AntiCaptcha'] && (!$tests['mysqlExists'] || !$tests['phpMySQLi'] || !$tests['dbconnect'] || !$tests['ipsTable'] || !$tests['ipRotEnabled'])))) { ?>
			<div class="alert alert-danger alert-dismissible fade in" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div><strong><i class="fa fa-exclamation-triangle"></i> Uh, oh!</strong> Your server is receiving the <b>YouTube CAPTCHA!</b></div>
				<div style="margin-top:15px;padding-left:18px">To download/convert YouTube videos, you <b>MUST</b> (AT LEAST) <!--do <b>ONE</b> (and only one) of the following:
					<ul style="list-style-type:disc;margin-left:17px;margin-bottom:0">
						<li>Install our Anti-Captcha Plugin, which will resolve the issue at a fraction of the cost of IP rotation! <b>(Recommended!)</b> &nbsp;<a href="#" class="alert-link">Learn More</a> <b><i class="fa fa-angle-double-right"></i></b></li>
						<li style="margin:15px 0 0 0">Install MySQL and the PHP MySQLi extension, -->acquire one or more additional IPs, confirm your database connection details in "lib/Config.php", create an "ips" database table, and fill this table with at least one IP for the purpose of IP rotation. &nbsp;<a href="http://<?php echo $_SERVER['HTTP_HOST'] . $appRoot; ?>docs/#yt-captcha" onclick="window.open(this.href); return false;" class="alert-link">Read More</a> <b><i class="fa fa-angle-double-right"></i></b></li>
					</ul>
				</div>
			</div>
		<?php } ?>		
		<ul>
			<li><span class="italic bold">Software Dependencies</span>
				<ul>
					<li>PHP version: &nbsp;&nbsp;&nbsp;<?php echo PHP_VERSION; ?><span class="response"><span> <?php echo ($tests['php_version']) ? $success : $failed; ?></span></span>
					<?php
						if (!$tests['php_version'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that your PHP version is at least 7.4 and not 8.0.</span></li></ul>';
						}
					?>
					</li>
					<li>PHP proc_open() enabled and working?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['phpExec']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['phpExec']) ? $success : $failed; ?></span></span><span id="fix-phpExec" style="display:none" data-toggle="tooltip" data-placement="right" data-trigger="manual" data-html="true" title="&nbsp;&nbsp;You must fix this first.">&nbsp;&nbsp;</span>
					<?php
						if (!$tests['phpExec'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the PHP proc_open() function is enabled and that proc_open() can run FFmpeg commands.</span></li></ul>';
						}
					?>
					</li>
					<li>PHP exec() enabled and working?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['phpExec2']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['phpExec2']) ? $success : $failed; ?></span></span><span id="fix-phpExec2" style="display:none" data-toggle="tooltip" data-placement="right" data-trigger="manual" data-html="true" title="&nbsp;&nbsp;You must fix this first.">&nbsp;&nbsp;</span>
					<?php
						if (!$tests['phpExec2'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the PHP exec() function is enabled and working.</span></li></ul>';
						}
					?>
					</li>					
					<li style="word-break:break-all">PHP "open_basedir": &nbsp;&nbsp;&nbsp;<?php echo (!empty($phpOpenBaseDir)) ? $phpOpenBaseDir : "no value"; ?><span class="response"><span> <?php echo ($tests['phpOpenBaseDir']) ? $success : $failed; ?></span></span>
					<?php
						if (!$tests['phpOpenBaseDir'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the PHP "open_basedir" directive is empty, set to no value, or includes the app root folder in the specified directory-tree.</span></li></ul>';
						}
					?>
					</li>
					<li>ionCube Loader installed?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['php_ioncube']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['php_ioncube']) ? $success : $failed; ?></span></span><span id="fix-ionCube" style="display:none" data-toggle="tooltip" data-placement="right" data-trigger="manual" data-html="true" title="&nbsp;&nbsp;You must fix this first.">&nbsp;&nbsp;</span>
					<?php
						if (!$tests['php_ioncube'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the most recent PHP "ionCube Loader" extension is installed correctly for your PHP version. &nbsp;<a href="https://puredevlabs.cc/knowledgebase/5/HowTo-install-ionCube-Loader-in-aaPanel.html?shop=1" onclick="window.open(this.href); return false;"><b>Read More &nbsp;&nbsp;<i class="fa fa-angle-double-right"></i></b></a></span></li></ul>';
						}
					?>
					</li>					
					<li>PHP GD installed?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['php_gd']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['php_gd']) ? $success : $failed; ?></span></span>
					<?php
						if (!$tests['php_gd'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the PHP GD extension is installed.</span></li></ul>';
						}
					?>
					</li>
					<li>PHP SimpleXML installed?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['php_xml']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['php_xml']) ? $success : $failed; ?></span></span>
					<?php
						if (!$tests['php_xml'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the PHP SimpleXML extension is installed.</span></li></ul>';
						}
					?>
					</li>										
					<li>cURL location: &nbsp;&nbsp;&nbsp;<?php echo (isset($curlPath[3])) ? trim($curlPath[3]) : 'Not found'; ?><span class="response"><span> <?php echo ($tests['curlExists']) ? $success : $failed; ?></span><?php echo (!$curlFound) ? ' &nbsp;&nbsp;<form method="post" style="display:inline" class="binInstall"><input type="hidden" name="binToInstall" value="curl" /><button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-cogs" aria-hidden="true"></i> Install cURL</button></form>' : ''; ?></span>
					<?php
						if (!$tests['curlExists'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that cURL is installed and that the _CURL_PATH constant value in "lib/Config.php" (line ' . $lineNumsArr['_CURL_PATH'] . ') is set correctly.</span></li></ul>';
						}
					?>
					</li>
					<?php if ($tests['curlExists']) { ?>
						<li>cURL version: &nbsp;&nbsp;&nbsp;<?php echo (isset($curlVersionNo[0]) && !empty($curlVersionNo[0])) ? $curlVersionNo[0] : 'Not found'; ?><span class="response"><span> <?php echo ($tests['curlVersion']) ? $success : $failed; ?></span></span>
						<?php
							if (!$tests['curlVersion'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Something is wrong with your cURL installation. Please delete "store/bin/curl" (if it exists) and try reinstalling cURL.</span></li></ul>';
							}
						?>
						</li>						
						<li>PHP cURL installed?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['phpCurl']) ? 'Yes' : 'Not found'; ?><span class="response"><span> <?php echo ($tests['phpCurl']) ? $success : $failed; ?></span></span>
						<?php
							if (!$tests['phpCurl'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the PHP cURL extension is installed.</span></li></ul>';
							}
						?>
						</li>
						<?php if ($tests['phpCurl']) { ?>
							<li>DNS is OK?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['dns']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['dns']) ? $success : $failed; ?></span></span>
							<?php
								if (!$tests['dns'])
								{
									echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> An HTTP request made by your server to "' . $_SERVER['HTTP_HOST'] . '" failed -- indicating a faulty DNS configuration. Please try another DNS provider, changing nameservers, and/or having a professional configure your DNS. &nbsp;<a href="http://' . $_SERVER['HTTP_HOST'] . $appRoot . 'docs#dns" onclick="window.open(this.href); return false;"><b>Read More &nbsp;&nbsp;<i class="fa fa-angle-double-right"></i></b></a></span></li></ul>';
								}
							?>
							</li>
							<?php if ($tests['dns']) { ?>
								<li>URL Rewriting enabled?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['urlRewriting']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['urlRewriting']) ? $success : $failed; ?></span></span>
								<?php
									if (!$tests['urlRewriting'])
									{
										echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that URL Rewriting is enabled on your web server.</span></li></ul>';
									}
								?>
								</li>							
							<?php } ?>
						<?php } ?>
					<?php } ?>
					<li>FFmpeg location: &nbsp;&nbsp;&nbsp;<?php echo (isset($ffmpegPath[3])) ? trim($ffmpegPath[3]) : 'Not found'; ?><span class="response"><span> <?php echo ($tests['FFmpeg']) ? $success : $failed; ?></span><?php echo (!$ffmpegFound) ? ' &nbsp;&nbsp;<form method="post" style="display:inline" class="binInstall"><input type="hidden" name="binToInstall" value="ffmpeg" /><button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-cogs" aria-hidden="true"></i> Install FFmpeg</button></form>' : ''; ?></span>
					<?php
						if (!$tests['FFmpeg'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that FFmpeg is installed and that the _FFMPEG_PATH constant value in "lib/Config.php" (line ' . $lineNumsArr['_FFMPEG_PATH'] . ') is set correctly.';
							echo (isset($isValidFFmpegPath) && !$isValidFFmpegPath) ? '<br /><br /><span class="dark-orange">Warning!</span>: The current _FFMPEG constant value contains a directory path that is NOT accessible to PHP! You must install FFmpeg in a valid, PHP-accessible directory path. Valid installation paths include <b>"' . implode('</b>", "<b>', $validPathArrTrunc) . '</b>", and "<b>' . end($validPathArr) . '</b>".' : '';
							echo '</span></li></ul>';
						}
					?>
					</li>
					<?php if ($tests['FFmpeg']) { ?>
						<li>FFmpeg version: &nbsp;&nbsp;&nbsp;<?php echo ($tests['FFmpegVersion']) ? $ffmpegInfo[0] : 'Not found'; ?><span class="response"><span> <?php echo ($tests['FFmpegVersion']) ? $success : $failed; ?></span></span>
						<?php
							if (!$tests['FFmpegVersion'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Something is wrong with your FFmpeg installation. Please delete "store/bin/ffmpeg" (if it exists) and try reinstalling FFmpeg.</span></li></ul>';
							}
						?>
						</li>
						<li>libmp3lame installed?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['libmp3lame']) ? 'Yes' : 'Not found'; ?><span class="response"><span> <?php echo ($tests['libmp3lame']) ? $success : $failed; ?></span></span>
						<?php
							if (!$tests['libmp3lame'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the libmp3lame codec is installed and compiled with FFmpeg.</span></li></ul>';
							}
						?>
						</li>
					<?php } ?>
					<li>MySQL version: &nbsp;&nbsp;&nbsp;<?php echo ($tests['mysqlExists'] && isset($mysqlVersionNo) && !empty($mysqlVersionNo)) ? $mysqlVersionNo[0] : 'Unknown'; ?><span class="response"><span> <?php echo ($tests['mysqlExists']) ? $success : $failed; ?></span></span>
					<?php
						if (!$tests['mysqlExists'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that MySQL is installed.</span></li></ul>';
						}
					?>
					</li>
					<?php if ($tests['mysqlExists']) { ?>
						<li>PHP MySQLi installed?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['phpMySQLi']) ? 'Yes' : 'Not found'; ?><span class="response"><span> <?php echo ($tests['phpMySQLi']) ? $success : $failed; ?></span></span>
						<?php
							if (!$tests['phpMySQLi'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the PHP MySQLi extension is installed.</span></li></ul>';
							}
						?>
						</li>
					<?php } ?>
					<!--<?php if (isset($tests['captcha']) && $tests['captcha']) { ?>
						<li style="background:#fcf8e3;border:1px solid #faebcc;border-radius:10px;width:97%">
							<ul>
								<li><span style="font-weight:bold;color:#66512c">Choose a method to bypass the YouTube CAPTCHA!</span> <span class="response"><span></span><span> <a href="http://<?php echo $_SERVER['HTTP_HOST'] . $appRoot; ?>docs#yt-captcha" onclick="window.open(this.href); return false;" class="ttip" data-toggle="popover" data-html="true" data-placement="top" data-trigger="hover" title="<i class='fa fa-exclamation-triangle'></i> Warning!" data-content="Your server IP is blocked by YouTube. To bypass this issue, you must <b>either</b> 1) purchase and install our Anti-Captcha Plugin (recommended), <b>OR</b> 2) acquire additional IPs, configure a MySQL database table, and enable IP rotation."><i class="fa fa-exclamation-circle" aria-hidden="true"></i></a></span></span>
									<ul>
										<?php if (!$tests['captchaMethodErr']) { ?>
											<?php if (!$tests['mysqlExists'] || !$tests['phpMySQLi'] || !$tests['dbconnect'] || !$tests['ipsTable'] || !$tests['ipRotEnabled']) { ?>
												<li style="background:#f0ecd8;border:1px solid #f0e1c2;border-radius:10px;padding:5px 0 5px 15px;width:97%">Anti-Captcha Plugin installed?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['AntiCaptcha']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['AntiCaptcha']) ? $success : $failed; ?></span></span>
												<?php
													if (!$tests['AntiCaptcha'])
													{
														echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please <a href="#">install</a> our Anti-Captcha Plugin';
														echo (isset($validAntiCaptchaKey)) ? ', and ensure that the _ANTI_CAPTCHA_API_KEY constant value in "vendors/AntiCaptcha/lib/Config.php" (line ' . $lineNumsArr2['_ANTI_CAPTCHA_API_KEY'] . ') is set correctly' : '';
														echo '.</span></li></ul>';
													}
												?>
												</li>
											<?php } ?>
											<?php if (!$tests['AntiCaptcha']) { ?>
												<li style="background:#f0ecd8;border:1px solid #f0e1c2;<?php
													if (!$tests['mysqlExists']) { ?>border-radius:10px;<?php } else { ?>border-bottom:0;border-radius:10px 10px 0 0;margin-bottom:0;<?php } ?>padding:5px 0 5px 15px;width:97%">MySQL version: &nbsp;&nbsp;&nbsp;<?php echo ($tests['mysqlExists'] && isset($mysqlVersionNo) && !empty($mysqlVersionNo)) ? $mysqlVersionNo[0] : 'Unknown'; ?><span class="response"><span> <?php echo ($tests['mysqlExists']) ? $success : $failed; ?></span></span>
												<?php
													if (!$tests['mysqlExists'])
													{
														echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that MySQL is installed.</span></li></ul>';
													}
												?>
												</li>
												<?php if ($tests['mysqlExists']) { ?>
													<li style="background:#f0ecd8;border:1px solid #f0e1c2;border-top:0;border-radius:0 0 10px 10px;margin-top:0;padding:5px 0 5px 15px;width:97%">PHP MySQLi installed?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['phpMySQLi']) ? 'Yes' : 'Not found'; ?><span class="response"><span> <?php echo ($tests['phpMySQLi']) ? $success : $failed; ?></span></span>
													<?php
														if (!$tests['phpMySQLi'])
														{
															echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the PHP MySQLi extension is installed.</span></li></ul>';
														}
													?>
													</li>
												<?php } ?>	
											<?php } ?>
										<?php } else { ?>
											<li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> You cannot run our Anti-Captcha Plugin and IP rotation concurrently! You must choose one or the other.</span></li>
										<?php } ?>
									</ul>
								</li>
							</ul>
						</li>
					<?php } ?>-->
				</ul>
			</li>
			<li><span class="italic bold">Folder/File Permissions</span>
				<ul>
					<li>"store" directory permissions: &nbsp;&nbsp;&nbsp;<?php echo $appStorePerms; ?><span class="response"><span> <?php echo ($tests['appStorePerms']) ? $success : $failed; ?></span></span><span id="fix-appStorePerms" style="display:none" data-toggle="tooltip" data-placement="right" data-trigger="manual" data-html="true" title="&nbsp;&nbsp;You must fix this first.">&nbsp;&nbsp;</span>
					<?php
						if (!$tests['appStorePerms'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the "store" directory is "chmod" to 0777 permissions. If that\'s not possible or practical, then at least ensure that permissions enable writing to this folder.</span></li></ul>';
						}
					?>
					</li>
				</ul>
			</li>
			<li><span class="italic bold">Software Settings</span>
				<ul>
					<li>App Root directory: &nbsp;&nbsp;&nbsp;<?php echo $appRoot; ?><span class="response"><span> <?php echo ($tests['app_root']) ? $success : $failed; ?></span></span>
					<?php
						if (!$tests['app_root'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the _APPROOT constant value in "lib/Config.php" (line ' . $lineNumsArr['_APPROOT'] . ') is set correctly.</span></li></ul>';
						}
					?>
					</li>
					<li>YouTube API Key(s): &nbsp;&nbsp;&nbsp;<?php echo (isset($validYoutubeKeys)) ? ((!empty($validYoutubeKeys)) ? implode(", ", $validYoutubeKeys) : 'Not found') : 'API connection failed!'; ?><span class="response"><span> <?php echo ($tests['youtubeAPI']) ? $success : $failed; ?></span><?php
						//echo (count($validYoutubeKeys) < 2) ? '<span> <a href="http://' . $_SERVER['HTTP_HOST'] . $appRoot . 'docs#youtubeAPIMultiple" onclick="window.open(this.href); return false;" class="ttip" data-toggle="popover" data-html="true" data-placement="top" data-trigger="hover" title="<i class=\'fa fa-exclamation-triangle\'></i> Warning!" data-content="Multiple API keys are encouraged! Ideally, you should have 1 key for every 1,000 daily website visitors."><i class="fa fa-exclamation-circle" aria-hidden="true"></i></a></span>' : '';
					?></span><span id="fix-youtubeAPI" style="display:none" data-toggle="tooltip" data-placement="right" data-trigger="manual" data-html="true" title="&nbsp;&nbsp;You must fix this first.">&nbsp;&nbsp;</span>
					<?php
						if (!$tests['youtubeAPI'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please add only valid API keys to the $_youtubeApiKeys array in "lib/Config.php" (line ' . $lineNumsArr['$_youtubeApiKeys'] . '). ' . ((isset($invalidYoutubeKeys) && !empty($invalidYoutubeKeys)) ? '[Note: The following keys are invalid: <span class="red bold">' . implode('</span>, <span class="red bold">', $invalidYoutubeKeys) . '</span>.] ' : '') . '&nbsp;<a href="http://' . $_SERVER['HTTP_HOST'] . $appRoot . 'docs#youtubeAPI" onclick="window.open(this.href); return false;"><b>Read More &nbsp;&nbsp;<i class="fa fa-angle-double-right"></i></b></a></span></li></ul>';
						}
					?>
					</li>					
					<?php if ($tests['phpMySQLi']) { ?>
						<li>Database connection works?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['dbconnect']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['dbconnect']) ? $success : $failed; ?></span></span><span id="fix-dbConnect" style="display:none" data-toggle="tooltip" data-placement="right" data-trigger="manual" data-html="true" title="&nbsp;&nbsp;You must fix this first.">&nbsp;&nbsp;</span>
						<?php
							if (!$tests['dbconnect'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the _SERVER, _DB_USER, _DB_PASSWORD, and _DATABASE constant values in "lib/Config.php" (starting on line ' . $lineNumsArr['_SERVER'] . ') are set correctly.</span></li></ul>';
							}
						?>
						</li>
					<?php } ?>
					<?php if (isset($tests['captcha']) && $tests['captcha'] && $tests['phpMySQLi'] && $tests['dbconnect']) { ?>
						<li style="background:#fcf8e3;border:1px solid #faebcc;border-radius:10px;width:97%">
							<ul>
								<li><span style="font-weight:bold;color:#66512c">Enable IP Rotation to bypass the YouTube CAPTCHA!</span> <span class="response"><span></span><span> <a href="http://<?php echo $_SERVER['HTTP_HOST'] . $appRoot; ?>docs#yt-captcha" onclick="window.open(this.href); return false;" class="ttip" data-toggle="popover" data-html="true" data-placement="top" data-trigger="hover" title="<i class='fa fa-exclamation-triangle'></i> Warning!" data-content="Your server IP is blocked by YouTube. To bypass this issue, you must <!--<b>either</b> 1) purchase and install our Anti-Captcha Plugin (recommended), <b>OR</b> 2) -->acquire additional IPs, configure a MySQL database table, and enable IP rotation."><i class="fa fa-exclamation-circle" aria-hidden="true"></i></a></span></span>
									<ul>						
										<li>"ips" database table exists?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['ipsTable']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['ipsTable']) ? $success : $failed; ?></span></span>
										<?php
											if (!$tests['ipsTable'])
											{
												echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the "ips" database table exists and that the _DB_IPS_TABLE constant value in "lib/Config.php" (line ' . $lineNumsArr['_DB_IPS_TABLE'] . ') is set correctly.</span></li></ul>';
											}
										?>
										</li>
										<?php if ($tests['ipsTable']) { ?>
											<li>IP rotation enabled?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['ipRotEnabled']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['ipRotEnabled']) ? $success : $failed; ?></span></span>
											<?php
												if (!$tests['ipRotEnabled'])
												{
													echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the _ENABLE_IP_ROTATION_FOR_VIDEOS constant value in "lib/Config.php" (line ' . $lineNumsArr['_ENABLE_IP_ROTATION_FOR_VIDEOS'] . ') is set to "true".</span></li></ul>';
												}
											?>
											</li>							
										<?php } ?>
									</ul>
								</li>
							</ul>
						</li>
					<?php } ?>
				</ul>
			</li>
		</ul>

		<?php if (isset($tests['phpCurl']) && isset($tests['dns'])) { ?>
			<h4><u>Recommended</u> settings. . .</h4>			
			<ul>
				<?php if ($tests['dynaIPv6']) { ?>
					<li><span class="italic bold">Folder/File Permissions</span>
						<ul>
							<li>"inc/scheduler.php" permissions: &nbsp;&nbsp;&nbsp;<?php echo $schedulerPerms; ?><span class="response"><span> <?php echo ($tests['schedulerPerms']) ? $success : $failed; ?></span></span>
							<?php
								if (!$tests['schedulerPerms'])
								{
									echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that "inc/scheduler.php" is "chmod" to at least 0755 permissions.</span></li></ul>';
								}
							?>
							</li>
						</ul>
					</li>
				<?php } ?>
				<li><span class="italic bold">Software Settings</span>
					<ul>
						<li>Dynamic IPv6 enabled?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['dynaIPv6']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['dynaIPv6']) ? $success : $failed; ?></span></span>
						<?php
							if (!$tests['dynaIPv6'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the _ENABLE_DYNAMIC_IPV6 constant value in "lib/Config.php" (line ' . $lineNumsArr['_ENABLE_DYNAMIC_IPV6'] . ') is set to true. <b>You MUST also set up the corresponding, required cron job!</b> &nbsp;<a href="http://' . $_SERVER['HTTP_HOST'] . $appRoot . 'docs/#cron" onclick="window.open(this.href); return false;"><b>Read More &nbsp;&nbsp;<i class="fa fa-angle-double-right"></i></b></a></span></li></ul>';
							}
						?>
						</li>
						<?php if ($tests['dynaIPv6']) { ?>
							<li>Request IP version: &nbsp;&nbsp;&nbsp;<?php echo Config::_REQUEST_IP_VERSION; ?><span class="response"><span> <?php echo ($tests['reqIpVersion']) ? $success : $failed; ?></span></span>
							<?php
								if (!$tests['reqIpVersion'])
								{
									echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that the _REQUEST_IP_VERSION constant value in "lib/Config.php" (line ' . $lineNumsArr['_REQUEST_IP_VERSION'] . ') is set to 6.</span></li></ul>';
								}
							?>
							</li>
						<?php } ?>						
					</ul>
				</li>							
				<li><span class="italic bold">Miscellaneous</span>
					<ul>
						<li>SSL certificate?: &nbsp;&nbsp;&nbsp;<?php echo ($tests['ssl']) ? 'Yes' : 'No'; ?><span class="response"><span> <?php echo ($tests['ssl']) ? $success : $failed; ?></span></span>
						<?php
							if (!$tests['ssl'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> A secure HTTPS connection to your server could not be established. Please ensure that a valid SSL certificate is installed.</span></li></ul>';
							}
						?>
						</li>
					</ul>
				</li>
			</ul>
		<?php } ?>

		<h4><u>Optional</u> settings. . .</h4>
		<ul>
			<li><span class="italic bold">Miscellaneous</span>
				<ul>
					<li>Node.js location: &nbsp;&nbsp;&nbsp;<?php echo (isset($nodePath[3])) ? trim($nodePath[3]) : 'Not found'; ?><span class="response"><span> <?php echo ($tests['nodejs']) ? $success : $failed; ?></span><?php echo (!$nodeFound) ? ' &nbsp;&nbsp;<form method="post" style="display:inline" class="binInstall"><input type="hidden" name="binToInstall" value="node" /><button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-cogs" aria-hidden="true"></i> Install Node.js</button></form>' : ''; ?></span>
					<?php
						if (!$tests['nodejs'])
						{
							echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Please ensure that Node.js is installed and that the _NODEJS_PATH constant value in "lib/Config.php" (line ' . $lineNumsArr['_NODEJS_PATH'] . ') is set correctly.';
							echo (isset($isValidNodePath) && !$isValidNodePath) ? '<br /><br /><span class="dark-orange">Warning!</span>: The current _NODEJS_PATH constant value contains a directory path that is NOT accessible to PHP! You must install Node.js in a valid, PHP-accessible directory path. Valid installation paths include <b>"' . implode('</b>", "<b>', $validPathArrTrunc) . '</b>", and "<b>' . end($validPathArr) . '</b>".' : '';
							echo '</span></li></ul>';
						}
					?>
					</li>
					<?php if ($tests['nodejs']) { ?>
						<li>Node.js version: &nbsp;&nbsp;&nbsp;<?php echo ($tests['NodeVersion']) ? $nodeInfo[0] : 'Not found'; ?><span class="response"><span> <?php echo ($tests['NodeVersion']) ? $success : $failed; ?></span></span>
						<?php
							if (!$tests['NodeVersion'])
							{
								echo '<ul><li><span style="color:#777"><i class="fa fa-exclamation-circle orange"></i> Something is wrong with your Node.js installation. Consider reinstalling Node.js.</span></li></ul>';
							}
						?>
						</li>
					<?php } ?>					
				</ul>
			</li>
		</ul>

		<h5>&nbsp;</h5>
		<div class="buttons">
			<button class="btn btn-primary rerun"><i class="fa fa-refresh"></i> Run the tests again.</button> <button class="btn btn-success printpage"><i class="fa fa-print"></i> Print this page.</button> <button class="btn btn-danger popup"><i class="fa fa-sign-out"></i> Get me out of here!</button>
		</div>
	</div>

	<!-- Exit Modal -->
	<div class="modal fade" id="exitModal" tabindex="-1" role="dialog" aria-labelledby="exitModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="exitModalLabel">Are you sure?</h4>
		  </div>
		  <div class="modal-body">
			<p>At the very least, <u>you should confirm that all "Required" settings are configured correctly</u>. Failure to do so will adversely affect software performance!</p>
			<p>Consider printing this page for future reference before you leave. After you leave, you will not see this page again.<span style="font-weight:bold">*</span></p>
			<div class="alert alert-danger" role="alert">
				<p style="margin-top:0;padding-left:14px;text-indent:-14px;"><b>*</b> <span class="italic">If you do ever want to return, then you can delete the "store/setup.log" file and navigate back to <a class="alert-link" href="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $appRoot; ?>">the home page</a>.</span></p>
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">No, take me back.</button>
			<button id="leave" type="button" class="btn btn-primary">Yes!</button>
		  </div>
		</div>
	  </div>
	</div>
</body>
</html>