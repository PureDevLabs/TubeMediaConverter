<?php 

	namespace MediaConverterPro\app\Views\helpers;
	
	use MediaConverterPro\app\Core\Helper;
	use MediaConverterPro\app\Core\View;
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\YouTubeData;

	class MetaData extends Helper
	{		
		// Constants
		const _NUM_VIDEOS_PER_IMAGE = 4;
		const _EXCLUDED_ACTIONS = ['error403', 'error404', 'sitemap', 'sitemapindex'];		
		
		#region Public Methods
		public function PrintMetaTags(array $vars)
		{
			$meta = array();
			if (!in_array($vars['action'], self::_EXCLUDED_ACTIONS))
			{
				$isSearch = !empty($vars['rawQuery']) && !empty($vars['searchQuery']);
				$YouTube = new YouTubeData($vars['converter']);
				$videoInfo = $YouTube->VideoInfo($vars['searchQuery'], Config::_DEFAULT_COUNTRY);
				$videoInfo = (!empty($videoInfo)) ? $videoInfo : [['id' => '', 'title' => ''], ['id' => ''], ['id' => ''], ['id' => '']];
				$video = $videoInfo[0];
				$translations = $vars['translations'];
				
				$metaDesc = ($isSearch) ? sprintf($translations['search_description'], $video['title'], Config::_WEBSITE_DOMAIN) : $translations['website_description'];
				$metaKWords = (($isSearch) ? implode(", ", preg_split('/\s/', preg_replace('/[^\p{L}\p{N}\s-]/u', "", $video['title']), -1, PREG_SPLIT_NO_EMPTY)) . ', ' : '') . $translations['website_keywords'];
				$ogTitle = ($isSearch) ? $translations['download'] . " " . $video['title'] : $translations['website_title'];
				$ogUrl = $vars['protocol'] . Config::_WEBSITE_DOMAIN . Config::_APPROOT . $vars['urlLang'] . ((!$isSearch) ? (($vars['action'] != 'index') ? '@' . $vars['action'] : '') : $vars['rawQuery']);
				$shareImg = '@shareimg/share-';
				$shareImg .= ($isSearch) ? $video['id'] : $videoInfo[0]['id'] . ',' . $videoInfo[1]['id'] . ',' . $videoInfo[2]['id'] . ',' . $videoInfo[3]['id'];
				$shareImg .= '.png';
				$ptitle = (!$isSearch) ? (($vars['action'] != "index") ? $translations['navbar_' . strtolower($vars['ptitle'])] . ' - ' . Config::_WEBSITE_NAME : $translations['website_title']) : $translations['download'] . " " . $video['title'] . ' - ' . Config::_WEBSITE_NAME;
				
				$meta[] = '<meta name="description" content="' . htmlspecialchars($metaDesc, ENT_QUOTES) . '">';
				$meta[] = '<meta name="keywords" content="' . htmlspecialchars($metaKWords, ENT_QUOTES) . '" />';
				$meta[] = '<meta property="og:title" content="' .  htmlspecialchars($ogTitle, ENT_QUOTES) . '" />';
				$meta[] = '<meta property="og:description" content="' . htmlspecialchars($metaDesc, ENT_QUOTES) . '" />';
				$meta[] = '<meta property="og:url" content="' . $ogUrl . '" />';
				$meta[] = '<meta property="og:image" content="' . $vars['protocol'] . Config::_WEBSITE_DOMAIN . Config::_APPROOT . $shareImg . '" />';
				$meta[] = '<meta property="og:image:width" content="480" />';
				$meta[] = '<meta property="og:image:height" content="246" />';
				$meta[] = '<meta property="og:type" content="website" />';
			}
			else 
			{
				$meta[] = '<meta name="robots" content="noindex">';	
			}
			$meta[] = '<title>' . ($ptitle ?? $vars['ptitle']) . '</title>';

			return implode("\n\t", $meta) . "\n";
		}
		
		public function PrintCanonicalUrl(array $vars)
		{
			if (!in_array($this->_view->_controller->GetAction(), self::_EXCLUDED_ACTIONS))
			{
				$requestURL = preg_replace('/^((' . preg_quote(Config::_APPROOT, '/') . ')(' . preg_quote(Config::_DEFAULT_LANGUAGE, '/') . '\/))/', "$2", $_SERVER['REQUEST_URI']);
				$requestURL = preg_replace('/@index/', "", $requestURL);
				return "\t" . '<link rel="canonical" href="' . $vars['protocol'] . Config::_WEBSITE_DOMAIN . $requestURL . '" />' . "\n";
			}
			return '';
		}
		
		public function PrintHreflangTags(array $vars)
		{
			if (!in_array($this->_view->_controller->GetAction(), self::_EXCLUDED_ACTIONS))
			{
				$tags = array();
				$urlPathEnd = preg_replace('/^((' . preg_quote(Config::_APPROOT, '/') . ')((' . implode("|", array_keys($vars['langs'])) . ')\/)?)/', "", $_SERVER['REQUEST_URI']);
				$urlPathEnd = preg_replace('/@index/', "", $urlPathEnd);
				foreach ($vars['langs'] as $lng => $lngInfo)
				{
					$urlInLang = ($lng != Config::_DEFAULT_LANGUAGE) ? $lng . '/' : '';
					$tags[] = '<link rel="alternate" href="' . $vars['protocol'] . Config::_WEBSITE_DOMAIN . Config::_APPROOT . $urlInLang . $urlPathEnd . '" hreflang="' . $lng . '" />';
				}	
				$tags[] = '<link rel="alternate" href="' . $vars['protocol'] . Config::_WEBSITE_DOMAIN . Config::_APPROOT . $urlPathEnd . '" hreflang="x-default" />';
				return "\t" . implode("\n\t", $tags) . "\n";
			}
			return '';
		}
		
		public function GenerateShareImage($vid, $converter)
		{
			$videoIDs = explode(',', $vid);
			if (count($videoIDs) == self::_NUM_VIDEOS_PER_IMAGE)
			{
				$imgSrc = array();
				foreach ($videoIDs as $vidId)
				{
					if (!empty($vidId))
					{
						// Source Images
						$imgSrc[] = imagecreatefromjpeg('https://i.ytimg.com/vi/' . $vidId . '/mqdefault.jpg');
					}
				}
				
				// Set the image width and height 
			  	$outputImage = imagecreatetruecolor(640, 360); 

			  	// Prepare Images
				imagecopymerge($outputImage, $imgSrc[0], 0, 0, 0, 0, 320, 180, 100);
				imagecopymerge($outputImage, $imgSrc[1], 320, 0, 0, 0, 320, 180, 100);
				imagecopymerge($outputImage, $imgSrc[2], 0, 180, 0, 0, 320, 180, 100);
				imagecopymerge($outputImage, $imgSrc[3], 320, 180, 0, 0, 320, 180, 100);

				// Prepare font size and colors
				$white = imagecolorallocate($outputImage, 255, 255, 255);
				$black = imagecolorallocate($outputImage, 0, 0, 0);
				$red = imagecolorallocate($outputImage, 215, 40, 40);
				$fontSize = 12;
				$font = 'store/arial.ttf';

				// Wartermark Text
				$WaterMarkText = Config::_WEBSITE_DOMAIN;

				// Set the offset x and y for the text position
				$offset_x = 0;
				$offset_y = 25;

				// Get the size of the text area
				$dims = imagettfbbox($fontSize, 0, $font, $WaterMarkText);
				$text_width = $dims[4] - $dims[6] + $offset_x;
				$text_height = $dims[3] - $dims[6] + $offset_y;

				// Add text background
				imagefilledrectangle($outputImage, 0, 0, $text_width, $text_height, $white);

				// Add text
				imagettftext($outputImage, $fontSize, 0, 0, $offset_y, $red, $font, $WaterMarkText);

				// Output Image
				header("Content-Type: image/png");
				imagepng($outputImage);
				imagedestroy($outputImage);
			}			
			else
			{	
				$useScrapedVidInfo = Config::_ENABLE_SEARCH_SCRAPING && Config::_SEARCH_SCRAPING_POPULATES_VID_INFO;
				$YouTube = new YouTubeData($converter);
				$videoInfo = $YouTube->VideoInfo('https://youtu.be/' . $videoIDs[0], Config::_DEFAULT_COUNTRY);
				$video = $videoInfo[0];
				
				$imgSrc = imagecreatefromjpeg('https://i.ytimg.com/vi/' . $video['id'] . '/hqdefault.jpg');

				//Set the image width and height 
				$width = 480; 
				$height = 246;  

				$outputImage = imagecreatetruecolor($width, $height); 
				imagecopy($outputImage, $imgSrc, 0, 0, 0, 50, $width, $height);

				// Prepare font size and colors
				$white_transp = imagecolorallocatealpha($outputImage, 255, 255, 255, 85);
				$white = imagecolorallocate($outputImage, 255, 255, 255);
				$black = imagecolorallocate($outputImage, 0, 0, 0);
				$red = imagecolorallocate($outputImage, 215, 40, 40);
				$purple = imagecolorallocate($outputImage, 107, 64, 144);
				$green = imagecolorallocate($outputImage, 0, 186, 79);
				$gray = imagecolorallocate($outputImage, 170, 171, 171);
				$blue = imagecolorallocate($outputImage, 5, 76, 110);
				$fontSize = 10;
				$font = 'store/arial.ttf';
				$fontAwesome = 'store/fontawesome.ttf';

				// Text Variables
				$WaterMarkText = Config::_WEBSITE_DOMAIN;
				$channel = $video['channelTitle'];
				$channel = strlen($channel) > 4 && substr($channel, -4) == 'VEVO' ? substr($channel, 0, -4) : $channel;
				$channel = strlen($channel) > 18 ? substr($channel, 0, 18) . '.' : $channel;
				$pubdate = $video['publishedAt'];
				$views = number_format((float)$video['viewCount']);
				$likes = (isset($video['likeCount'])) ? number_format((float)$video['likeCount']) : "??";
				$dislikes = (isset($video['dislikeCount'])) ? number_format((float)$video['dislikeCount']) : "??";

				// Automatic x Text Moving
				$wm = imagettfbbox($fontSize, 0, $font, $WaterMarkText);
				$wm_tw = abs($wm[4] - $wm[0]) - 7;
				$x_wm = imagesx($imgSrc) - $wm_tw;
				$cb = imageTTFBbox($fontSize, 0, $font, $channel);
				$x_on = abs($cb[4]) + 30;
				$x_pub = $x_on + 17;
				$vb = imageTTFBbox($fontSize, 0, $font, $views);
				$x_li = abs($vb[4]) + 40;
				$x_like = $x_li + 15;
				$lb = imageTTFBbox($fontSize, 0, $font, $likes);
				$x_dli = abs($lb[4]) + 25 + $x_li;
				$x_dislike = $x_dli + 18;

				// Data Array
				$textData = array(
					array(
						'size' => $fontSize,
						'x' => '10',
						'y' => '216',
						'color' => $gray,
						'font' => $font,
						'text' => 'by'
					),
					array(
						'size' => $fontSize,
						'x' => '29',
						'y' => '216',
						'color' => $blue,
						'font' => $font,
						'text' => $channel
					),
					array(
						'size' => $fontSize,
						'x' => $x_on,
						'y' => '216',
						'color' => $gray,
						'font' => $font,
						'text' => (($useScrapedVidInfo) ? ' ~' : 'on')
					),
					array(
						'size' => '9',
						'x' => $x_pub,
						'y' => '216',
						'color' => $black,
						'font' => $font,
						'text' => $pubdate
					),
					array(
						'size' => '9',
						'x' => '10',
						'y' => '240',
						'color' => $purple,
						'font' => $fontAwesome,
						'text' => '&#xf06e;'
					),
					array(
						'size' => '9',
						'x' => '30',
						'y' => '240',
						'color' => $purple,
						'font' => $font,
						'text' => $views
					),
					array(
						'size' => '9',
						'x' => $x_wm,
						'y' => '215',
						'color' => $blue,
						'font' => $font,
						'text' => $WaterMarkText
					),
					array(
						'size' => '100',
						'x' => '180',
						'y' => '150',
						'color' => $white_transp,
						'font' => $fontAwesome,
						'text' => '&#xf16a;'
					)
				);
				$textDataLikes = array(
					array(
						'size' => '9',
						'x' => $x_li,
						'y' => '240',
						'color' => $green,
						'font' => $fontAwesome,
						'text' => '&#xf087;'
					),
					array(
						'size' => '9',
						'x' => $x_like,
						'y' => '240',
						'color' => $green,
						'font' => $font,
						'text' => $likes
					),
					array(
						'size' => '9',
						'x' => $x_dli,
						'y' => '240',
						'color' => $red,
						'font' => $fontAwesome,
						'text' => '&#xf088;'
					),
					array(
						'size' => '9',
						'x' => $x_dislike,
						'y' => '240',
						'color' => $red,
						'font' => $font,
						'text' => $dislikes
					)				
				);
				$textData = (!$useScrapedVidInfo) ? array_merge($textData, $textDataLikes) : $textData;

				// Add text background
				imagefilledrectangle($outputImage, 0, 200, 480, 300, $white);

				// Add text
				foreach ($textData as $text)
				{
					imagettftext($outputImage, $text['size'], 0, $text['x'], $text['y'], $text['color'], $text['font'], $text['text']); 
				}
				
				// Output
				header("Content-Type: image/png");
				imagepng($outputImage);
				imagedestroy($outputImage);
			}
		}
		#endregion
	}

?>