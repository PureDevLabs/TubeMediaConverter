<?php 
	use MediaConverterPro\lib\Config;
	
	// Set header for XML
	header('Content-Type: application/xml; charset=UTF-8');

	// Print XML
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n\n";
	echo '<?xml-stylesheet type="text/xsl" href="' . WEBROOT . 'app/Templates/' . TEMPLATE_NAME . '/assets/xsl/sitemap.xsl"?>';
	echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	if ($hasStaticPages)
	{
		echo "\t<sitemap>\n";
			echo "\t\t<loc>" . $protocol . $_SERVER['HTTP_HOST'] . Config::_APPROOT . "@sitemap/static</loc>\n";
			echo "\t\t<lastmod>" . $timeUTC . "</lastmod>\n";
		echo "\t</sitemap>\n";	
	}
	if (!empty($countryCodes))
	{
		foreach ($countryCodes as $ccode) 
		{
			echo "\t<sitemap>\n";
				echo "\t\t<loc>" . $protocol . $_SERVER['HTTP_HOST'] . Config::_APPROOT . "@sitemap/" . $ccode . "</loc>\n";
				echo "\t\t<lastmod>" . $timeUTC . "</lastmod>\n";
			echo "\t</sitemap>\n";
		}
	}
	echo "</sitemapindex>";
 ?>