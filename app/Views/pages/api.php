<?php 
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\Core;

	if ($api == 'search')
	{
		header('Content-Type: application/json; charset=UTF-8');
		$jsonResponse = array();
		$jsonResponse['items'] = (isset($videos) && !empty($videos)) ? $videos : array();
		if (empty($jsonResponse['items']))
		{
			$jsonResponse['error'] = true;
			$jsonResponse['errorMsg'] = $translations['search_scraping'];
		}
		$json = json_encode($jsonResponse, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
		if (json_last_error() != JSON_ERROR_NONE)
		{
			$jsonResponse = array();			
			$jsonResponse['items'] = array();
			$jsonResponse['error'] = true;
			$jsonResponse['errorMsg'] = 'JSON response is invalid';
			echo json_encode($jsonResponse, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);						
		}
		else
		{
			echo $json;
		}		
	}
	elseif ($api == 'json')
	{
		header('Content-Type: application/json; charset=UTF-8');
		$jsonResponse = array();
		$jsonResponse['vidID'] = $vidID;
		$jsonResponse['vidTitle'] = htmlspecialchars_decode($vidTitle, ENT_QUOTES);
		$jsonResponse['vidThumb'] = $vidThumb;
		$jsonResponse['duration'] = $duration;
		$jsonResponse['vidInfo'] = (!isset($videoData)) ? ((!isset($buttons)) ? array() : $buttons) : $videoData;
		if (isset($error) && !empty($error))
		{
			$jsonResponse['error'] = true;
			$jsonResponse['errorMsg'] = $error;
		}
		$json = json_encode($jsonResponse, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_FORCE_OBJECT);
		if (json_last_error() != JSON_ERROR_NONE)
		{
			$jsonResponse = array();
			$jsonResponse['vidID'] = '';
			$jsonResponse['vidTitle'] = '';	
			$jsonResponse['vidThumb'] = 'https://img.youtube.com/vi/oops/1.jpg';
			$jsonResponse['duration'] = 0;			
			$jsonResponse['vidInfo'] = array();
			$jsonResponse['error'] = true;
			$jsonResponse['errorMsg'] = 'JSON response is invalid';
			echo json_encode($jsonResponse, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_FORCE_OBJECT);						
		}
		else
		{
			echo $json;
		}		
	}
	else
	{
		?><div class="dl-result">	
			<div id="yt-results">
				<div class="download-result"></div>
			</div>
		</div>
		<script>
			$(".download-result").load("<?php echo WEBROOT . $urlLang; ?>@grab?vidID=<?php echo $vidID; ?>&format=<?php echo $format; ?>&streams=<?php echo $streams; ?>&api=<?php echo $api; ?>&appSecretToken=<?php echo hash('sha256', $session['appSecretToken'] . APP_SECRET_KEY); ?>", {sessID: '<?php echo session_id(); ?>'}, function(){
				$(".download-loading").hide();
			});		
		</script><?php
	}
?>