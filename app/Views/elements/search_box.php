<?php 
	use MediaConverterPro\lib\Config;
?>
			<?php echo (isset($sFormPrependTo)) ? $sFormPrependTo : ''; ?>
			<form method="post" class="<?php echo (isset($sFormClass)) ? $sFormClass : ''; ?>">
				<p id="show-info" class="material-icons" data-html="true" data-container="body" data-placement="bottom"  
				title="<?php echo $translations['searchfrom_info_1']; ?> <br />https://www.youtube.com/watch?v=videoID <br /> https://m.youtube.com/watch?v=videoID <br /> http://youtu.be/videoID <br /><br /><?php echo $translations['searchfrom_info_2']; ?> <br /> xxxxx <br /><br /><?php echo $translations['searchfrom_info_3']; ?> <br />https://www.youtube.com/playlist?list=PlaylistID <br />https://www.youtube.com/list=ID<br /><br /><?php echo $translations['searchfrom_info_4']; ?> <br />i.e. Adele & 2Pac - Hello ">help_outline</p>
				<div class="embed-submit-field">
					<input type="text" name="videoURL" id="videoURL" placeholder="<?php echo $translations['searchform_placeholder']; ?>" value="<?php echo (!empty($SearchTerm)) ? htmlspecialchars($SearchTerm, ENT_QUOTES) : ''; ?>" />
					<button type="submit" name="submitForm" class="material-icons<?php echo (Config::_ENABLE_RECAPTCHA) ? ' g-recaptcha' : ''; ?>"<?php echo (Config::_ENABLE_RECAPTCHA) ? ' data-sitekey="' . Config::_RECAPTCHA_KEY . '" data-callback="onSearchSubmit"' : ''; ?>>search</button>
				</div>
			</form>