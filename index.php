<?php 
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\Core;
	use MediaConverterPro\app\Core\Router;

	// Override PHP directives at runtime
	ini_set('error_reporting', 'E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED');
	ini_set('display_errors', '0');
	ini_set('output_buffering', 'On');
	ini_set('memory_limit', '128M');
	ini_set('allow_url_fopen', 'On');
	date_default_timezone_set('Europe/Paris');
	
	// Custom error handling
	include 'inc/error.php';
	mysqli_report(MYSQLI_REPORT_OFF);
	
	if (isset($_POST['sessID']))
	{
		session_id(trim($_POST['sessID']));
	}	
	session_start();
	
	// Autoload class files
	include 'inc/autoload.php';
	
	// Software config check logic
	if (!is_file('store/setup.log'))
	{
		if (isset($_GET['config']) && $_GET['config'] == "complete")
		{
			// Create setup.log to skip config check in the future
			$fp = @fopen("store/setup.log", "w");
			if ($fp !== false)
			{
				fwrite($fp, 'Delete this file to run the config check again.');
				fclose($fp);
			}
			
			// Create robots.txt file
			$fp2 = @fopen("store/robots.txt", "w");
			if ($fp2 !== false)
			{
				$robotsTxt = 'User-agent: *' . "\n" . 'Disallow:';
				$robotsTxt = (Config::_WEBSITE_INTERFACE != 'web') ? ((Config::_WEBSITE_INTERFACE != 'api') ? $robotsTxt : $robotsTxt . ' /') : 'Sitemap: http' . $_GET['ssl'] . '://' . $_SERVER['HTTP_HOST'] . Config::_APPROOT . 'sitemapindex.xml';
				fwrite($fp2, $robotsTxt);
				fclose($fp2);
			}			
		}
		else
		{
			include 'inc/check_config.php';
			die();
		}
	}	

	// Load other includes
	require_once('inc/version.php');

	// Other constants
	define('WEBROOT', '//' . $_SERVER['HTTP_HOST'] . Config::_APPROOT);
	define('TEMPLATE_NAME', Config::_TEMPLATE_NAME);
	define('APP_SECRET_KEY', sha1(Config::_WEBSITE_NAME));

	// Load Template
	$request = (isset($_GET['req'])) ? trim($_GET['req']) : "/";
	//die($request);
	Router::dispatch($request);
?>