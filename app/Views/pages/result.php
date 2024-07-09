<?php
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\YouTubeData;
	use MediaConverterPro\lib\Core;
?>

<div id="yt-results">
	<?php 
		if (empty($SearchTerm)) 
		{	
			echo $this->element('countries');
		}
		if (!empty($videoInfo))
		{
			foreach ($videoInfo as $video)
			{
				$isScrapedInfo = empty($video['likeCount']) && empty($video['dislikeCount']);
				if (!$isScrapedInfo)
				{
					$RatingTotal = (int)$video['likeCount'] + (int)$video['dislikeCount'];
					$RatingPercent = ($RatingTotal > 0) ? round((((int)$video['likeCount'] - (int)$video['dislikeCount']) / $RatingTotal) * 100, 2) : "0.00";
				}

				?><div class="media">
					<div class="media-left">
						<div class="yt-thumbnail">
							<picture>
								<source media="(min-width:768px)" srcset="<?php echo $video['thumb' . $params['thumbImgSize']]; ?>">
								<source media="(max-width:768px)" srcset="<?php echo $video['thumbMedium']; ?>">
								<img class="media-object img-thumbnail" src="<?php echo $video['thumb' . $params['thumbImgSize']]; ?>">
							</picture>
							<span class="yt-duration"><?php echo $video['duration']; ?></span>
						</div>
					</div>
					<div class="media-body">
					<?php
						echo (isset($video['dimension']) && $video['dimension'] == "3d") ? '<div class="ribbon"><div class="ribbon definition-3d"><span>' . $video['dimension'] . '</span></div></div>' : '';
						echo (isset($video['definition']) && $video['definition'] == "hd") ? '<div class="ribbon"><div class="ribbon definition-hd"><span>' . $video['definition'] . '</span></div></div>' : ''; 
					?>	
						<h5 class="media-heading"><?php
							echo (Config::_ENABLE_VIDEO_LINKS) ? '<a style="color:#0e2f66" href="' . WEBROOT . $urlLang . preg_replace("/[^\p{L}\p{N}]+/u", "-", $video['title']) . '(' . $video['id'] . ')">' : '';
							echo $video['title'];
							echo (Config::_ENABLE_VIDEO_LINKS) ? '</a>' : '';
						?></h5>
						<div class="yt-channel"><?php echo $translations['by']; ?> <?php if (!empty($video['channelId'])) { ?><a target="_blank" href="https://www.youtube.com/channel/<?php echo $video['channelId'];?>"><?php } echo $video['channelTitle']; ?><?php if (!empty($video['channelId'])) { ?></a><?php } ?><span class="date-desktop"><?php echo ($isScrapedInfo) ? "," : " " . $translations['on']; ?> <?php echo $video['publishedAt']; ?></span></div>
						<div class="youtube-statistics">					
							<div class="yts-views">
								<i class="fa fa-eye" aria-hidden="true"></i><span class="views-desktop"><?php echo (!empty($video['viewCount'])) ? number_format($video['viewCount']) : "???"; ?> </span>
								<span class="views-mobile"><?php echo (!empty($video['viewCount'])) ? Core::MobileNumbers($video['viewCount']) : "???"; ?></span>
							</div><!-- /.yts-views -->
							<?php if (!$isScrapedInfo) { ?>
								<div class="yts-likes">
									 <i class="fa fa-thumbs-o-up" aria-hidden="true"></i><span class="likes-desktop"><?php echo (!empty($video['likeCount'])) ? number_format($video['likeCount']) : 0; ?></span> 
									 <span class="likes-mobile"><?php echo (!empty($video['likeCount'])) ? Core::MobileNumbers($video['likeCount']) : 0; ?></span>
								</div><!-- /.yts-likes -->
							<?php } ?>
							<?php if (false) { ?>
								<div class="yts-dislikes">
									 <i class="fa fa-thumbs-o-down" aria-hidden="true"></i><span class="dislikes-desktop"><?php echo (!empty($video['dislikeCount'])) ? number_format($video['dislikeCount']) : 0; ?> </span>
									 <span class="dislikes-mobile"><?php echo (!empty($video['dislikeCount'])) ? Core::MobileNumbers($video['dislikeCount']) : 0; ?></span>
								</div><!-- /.yts-dislikes -->
							<?php } ?>
						</div><!-- /.youtube-statistics -->
						<?php if (false) { ?>
							<div class="yt-rate">
								<i class="fa fa-star" aria-hidden="true"></i> <?php echo $RatingPercent; ?>% <?php echo $translations['by']; ?> <span class="rate-desktop"><?php echo number_format($RatingTotal); ?></span> <span class="rate-mobile"><?php echo Core::MobileNumbers($RatingTotal); ?></span> <?php echo $translations['users']; ?> 
							</div><!-- /.yt-rate -->
						<?php } ?>
						<?php if (preg_match('/tags/', $params['videoData']) == 1 && !empty($video['tags'])) { ?>
							<div class="media-tags"><i class="fa fa-tags" aria-hidden="true"></i><span><?php echo $translations['tags']; ?>: 
								<?php 
									//print_r($video['tags']);
									$videoTags = explode(", ", $video['tags']);
									$numTags = 0;
									$vidTags = '';
									do 
									{
										$tagsArr = array_slice($videoTags, 0, ++$numTags);
										$tagsArr = preg_replace('/(^(.{49})(.+)$)/s', "$2", $tagsArr);
										$tagsArr = (Config::_ENABLE_SEARCH_LINKS) ? preg_replace('/(^.+$)/', '<a href="' . WEBROOT . $urlLang . '$1">$1</a>', $tagsArr) : $tagsArr;
										$vidTags = implode(", ", $tagsArr);
										//echo strlen($vidTags) . " - ";
									}
									while (count($videoTags) > $numTags && strlen(strip_tags($vidTags) . ", " . $videoTags[$numTags]) < 50);
									echo $vidTags; 
								?>
							</span></div>
						<?php } ?>
						<?php if (preg_match('/description/', $params['videoData']) == 1) { ?>
							<div class="media-description"><?php echo $video['description']; ?></div>
						<?php } ?>
					</div><!-- /.media-body -->
					<div class="dl-line-dotted-scraped"></div><!-- /.dl-dividing-line -->
					<div class="play-buttons">
						<?php	
							echo (Config::_MUSIC_PLAYER) ? '<div title="' . $translations['audio_play_button'] . '" class="playMusic material-icons" data-video-id="' . $video['id'] . '" data-video-title="' . htmlspecialchars(substr($video['title'], 0, 75), ENT_QUOTES) . '">queue_music</div><!-- /.playMusic -->' : '';
							echo '<div title="' . $translations['video_preview_button'] . '" class="playVideo material-icons"><a class="fancybox-media" href="https://youtu.be/' . $video['id'] . '">videocam</a></div>';
							if (Config::_ENABLE_ADDTHIS_WIDGET)
							{
								$uniqueID = preg_replace('/\./', "", uniqid("", true));
								echo '<div title="' . $translations['share_button'] . '" class="playVideo material-icons"><a href="#shareModal' . $uniqueID . '" data-toggle="modal">share</a></div>';
								echo $this->element('share_modal', compact('uniqueID', 'video'));
							}
						?>
					</div>
					<div class="download-buttons">
						<div class="download-mp3" data-vid-id="<?php echo $video['id']; ?>"><span class="hide-mobile"><?php echo $translations['download']; ?></span> MP3</div><!-- /.download-mp3 -->
						<div class="download-mp4" data-vid-id="<?php echo $video['id']; ?>"><span class="hide-mobile"><?php echo $translations['download']; ?></span> <?php echo $translations['video_button']; ?></div><!-- /.download-mp4 -->
					</div><!-- /.download-buttons -->
					<div class="dl-line-dashed" data-vid-id="<?php echo $video['id']; ?>" style="display:none;"></div><!-- /.dl-line-dashed -->
					<div class="stream-buttons" data-vid-id="<?php echo $video['id']; ?>" style="display:none;">
						<?php if (Config::_ENABLE_NONDASH_VIDEO) { ?>
							<div class="videos" data-vid-id="<?php echo $video['id']; ?>"><?php echo $translations['videos']; ?></div><!-- /.videos -->
						<?php } ?>
						<?php if (Config::_ENABLE_MERGED_VIDEO_STREAMS) { ?>
							<div class="merged-streams" data-vid-id="<?php echo $video['id']; ?>"><?php echo $translations['video_streams']; ?></div><!-- /.merged-streams -->
						<?php } ?>
						<div class="video-streams" data-vid-id="<?php echo $video['id']; ?>"><?php echo $translations['video_only']; ?> <i class="fa fa-microphone-slash" aria-hidden="true"></i></div><!-- /.video-streams -->
						<div class="audio-streams" data-vid-id="<?php echo $video['id']; ?>"><?php echo $translations['audio_only']; ?></div><!-- /.audio-streams -->
					</div><!-- /.stream-buttons -->
					<div class="dl-line-dashed-streams" data-vid-id="<?php echo $video['id']; ?>" style="display:none;"></div><!-- /.dl-line-dashed-streams -->
					<div class="download-result" data-vid-id="<?php echo $video['id']; ?>" style="display:none;text-align:center"></div><!-- /.download-result -->
					<div class="download-loading" data-vid-id="<?php echo $video['id']; ?>" style="display:none"><img src="<?php echo WEBROOT; ?>app/Templates/<?php echo TEMPLATE_NAME; ?>/assets/img/<?php echo $params['ajaxLoadImg']; ?>" alt="<?php echo htmlspecialchars($translations['preparing_msg'], ENT_QUOTES); ?>" /><br /><span class="loading-msg"><?php echo $translations['preparing_msg']; ?></span><span class="loading-msg2" style="display:none"><?php echo $translations['preparing_msg2']; ?></span></div><!-- /.download-loading -->					
				</div><!-- /.media --><?php
			}
			
			if (count($videoInfo) > 1 && $maxResults > 1 && $maxResults < YouTubeData::_MAX_API_RESULTS)
			{
				echo '<div id="ajaxloader" style="display:none;text-align:center"><img src="' . WEBROOT . 'app/Templates/' . TEMPLATE_NAME . '/assets/img/' . $params['ajaxLoadImg'] . '" alt="' . htmlspecialchars($translations['load_more_msg'], ENT_QUOTES) . '" /><br />' . $translations['load_more_msg'] . '</div>';
				echo '<div id="loadMore" data-more-id="' . (Config::_RESULTS_PER_PAGE + $maxResults) . '" data-more-ccode="' . $cCode . '" data-more-ccont="' . $Continent . '" data-more-query="' . $SearchTerm . '"><span>' . $translations['load'] . ' ' . Config::_RESULTS_PER_PAGE . ' ' . $translations['more_videos'] . ' </span></div>';
			}			
		}
		else
		{
			echo '<p style="font-weight:bold;text-align:center;margin-top:50px">'. $translations['no_videos_found'] .'</p>';
		}
	?>
</div>