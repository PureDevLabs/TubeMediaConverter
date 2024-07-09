<?php 
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\Core;

	if (isset($buttons) && !empty($buttons))
	{
		foreach ($buttons as $button) { ?>
			<a class="download-mp3-url btn audio q<?php echo $button['bitrate']; ?>" target="_blank" href="<?php echo $button['dloadUrl']; ?>">
			<div class='btn-border'><div class='btn-mp3'>MP3</div> <div class='btn-bitrate'><?php echo $button['bitrate'] . Config::$_mediaUnits['bitrate']; ?></div><div class='btn-line'></div><div class='btn-filesize'><?php echo (Config::_ENABLE_SIMULATED_MP3) ? '<span class="material-icons" style="font-size: 14px;vertical-align: -2px;text-shadow: 1px .3px 1px #000;">music_note</span>AUDIO' : $button['mp3size']; ?></div></div></a>
		<?php 
		} 
		if (empty($api) && !Config::_ENABLE_SIMULATED_MP3 && isset($isCachedOnly) && !$isCachedOnly) {	
			?><div id="time-frame" class="form-inline">
				<br />
				<label><?php echo $translations['time_frame']; ?></label><br>
				<div class="form-group">
					<label><?php echo $translations['start']; ?></label> <input type='input' class="form-control" id='stime' name='stime' value='00:00:00' placeholder='00:00:00' data-toggle="tooltip" data-placement="top" data-trigger="manual" title="Valid format is HH:MM:SS" />
					<label><?php echo $translations['end']; ?></label> <input type='input' class="form-control" id='etime' name='etime' value='<?php echo $etime; ?>' placeholder='<?php echo $etime; ?>' data-toggle="tooltip" data-placement="top" data-trigger="manual" title="Valid format is HH:MM:SS" />
				</div>
			</div><?php	
		}
	}
	elseif (isset($videoData) && !empty($videoData))
	{
		foreach ($videoData as $video) { 
			if (Config::_YOUTUBE_DIRECT_DOWNLOAD && empty($api)) { ?>
				<?php //$video['directurl'] = ($streams !== 'mergedstreams') ? preg_replace_callback('/ip=[^&]+/i', function($ipmatch) {return bin2hex($ipmatch[0]);}, $video['directurl']) : $video['directurl']; ?>
				<span class="videodownload" data-video-id="<?php echo $vidID;?>" data-video-itag="<?php echo $video['itag']; ?>" data-direct-url="<?php echo $video['directurl']; ?>" data-dload-url="<?php echo $video['dloadUrl']; ?>">
			<?php } ?>
				<a target="_blank" class="btn <?php echo (!empty($video['quality'])) ? "video" : "audio"; ?> q<?php echo (!empty($video['quality'])) ? $video['quality'] . "p" : $video['bitrate']; ?>" href="<?php echo (Config::_YOUTUBE_DIRECT_DOWNLOAD && empty($api) && $streams !== 'mergedstreams') ? $video['directurl'] : $video['dloadUrl']; ?>" download><?php echo (!empty($video['quality'])) ? '<div class="btn-quality">' . $video['quality'] . Config::$_mediaUnits['quality'] . '</div>' : '';?>
					<div class="btn-filetype"><?php echo $video['ftype']; ?></div>
						<?php
							echo (!empty($video['framerate'])) ? '<span class="btn-fps">(' . $video['framerate'] . Config::$_mediaUnits['framerate'] . ')</span>' : '';
							echo (!empty($video['codec'])) ? '<span class="btn-codec">(' . $video['codec'] .')</span>' : '';
							echo (!empty($video['bitrate'])) ? '<div class="btn-quality">' . $video['bitrate'] . Config::$_mediaUnits['bitrate'] . '</div>' : '';
						?>
					<div class="btn-line"></div><div class="btn-filesize"><?php echo $video['rSize']; ?></div>
				</a>
			<?php if (Config::_YOUTUBE_DIRECT_DOWNLOAD && empty($api)) { ?>	
				</span>
			<?php }  
		} 
		if (Config::_YOUTUBE_DIRECT_DOWNLOAD && empty($api) && $streams !== 'mergedstreams') { ?>
			<div class="alert highspeed">
				<input class="videoHighspeed" type="checkbox" checked="checked" name="videoHighspeed" tabindex="">
				<span class="checkbox-text checked"> <?php echo $translations['highspeed_label']; ?></span>
				<p><?php echo $translations['highspeed_text']; ?></p>
			</div>			
		<?php } 
	}
	else
	{
		echo '<br />' . $error;
	}
?>