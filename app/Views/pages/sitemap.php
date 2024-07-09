<?php 
	use MediaConverterPro\lib\Config;
	
	// Set header for XML
	header('Content-Type: application/xml; charset=UTF-8');

	// Print XML
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n\n";
	echo '<?xml-stylesheet type="text/xsl" href="' . WEBROOT . 'app/Templates/' . TEMPLATE_NAME . '/assets/xsl/sitemap.xsl"?>';
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
	if (!empty($pages))
	{
		foreach ($pages as $page)
		{
			$page = ($page == "@index" || $page == "index") ? '' : $page;
			echo "\t<url>\n";
				echo "\t\t<loc>" . $protocol . $_SERVER['HTTP_HOST'] . Config::_APPROOT . $page . "</loc>\n";
				if (isset($langs) && !empty($langs))
				{
					foreach ($langs as $langCode => $langInfo)
					{
						$lngCode = ($langCode != Config::_DEFAULT_LANGUAGE) ? $langCode . '/' : '';
						echo  "\t\t" . '<xhtml:link rel="alternate" hreflang="' . $langCode . '" href="' . $protocol . $_SERVER['HTTP_HOST'] . Config::_APPROOT . $lngCode . $page . '" />' . "\n";
					}
				}
				echo "\t\t<lastmod>" . $timeUTC . "</lastmod>\n";
				echo "\t\t<changefreq>daily</changefreq>\n";
			echo "\t</url>\n";	
		}
	}
	if (!empty($videoInfo))
	{
		foreach ($videoInfo as $video) 
		{
			$urlName = preg_replace("/[^\p{L}\p{N}]+/u", "-", $video['title']);
			echo "\t<url>\n";
				echo "\t\t<loc>" . $protocol . $_SERVER['HTTP_HOST'] . Config::_APPROOT . $urlName . "(" . $video['id'] . ")</loc>\n";
				if (isset($langs) && !empty($langs))
				{
					foreach ($langs as $langCode => $langInfo)
					{
						$lngCode = ($langCode != Config::_DEFAULT_LANGUAGE) ? $langCode . '/' : '';
						echo  "\t\t" . '<xhtml:link rel="alternate" hreflang="' . $langCode . '" href="' . $protocol . $_SERVER['HTTP_HOST'] . Config::_APPROOT . $lngCode . $urlName . '(' . $video['id'] . ')" />' . "\n";
					}
				}
				echo "\t\t<lastmod>" . $timeUTC . "</lastmod>\n";
				echo "\t\t<changefreq>daily</changefreq>\n";
			echo "\t</url>\n";
		}
	}
	echo "</urlset>";
 ?>