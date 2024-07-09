<?php 
	use MediaConverterPro\lib\Config;
?>

				<div class="dl-result">
					<?php if (Config::_ENABLE_TOP_MUSIC_PER_COUNTRY) { ?>
						<div id="download">
							<noscript>
								<div class="videolist">
									<h1><?php echo Config::_WEBSITE_DOMAIN; ?></h1>
									<h2><?php echo $translations['download']; ?> MP3, MP4, WEBM, 3GP, M4A</h2>
									<?php foreach ($videoInfo as $video) { ?>
										<?php $isScrapedInfo = empty($video['likeCount']) && empty($video['dislikeCount']); ?>
										<div class="videoInfo">
											<h3><a href="<?php echo WEBROOT . $urlLang . preg_replace("/[^\p{L}\p{N}]+/u", "-", $video['title']) . "(" . $video['id'] . ")"; ?>" title="<?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>"><?php echo $video['title']; ?></a></h3>
											<img src="https://img.youtube.com/vi/<?php echo $video['id']; ?>/default.jpg" title="<?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>" />
											<blockquote>
												<p><?php echo $translations['nojs_vid_duration'] . " " . $video['duration']; ?></p>
												<p><?php echo $translations['nojs_vid_uploader'] . " " . $video['channelTitle']; ?></p>
												<p><?php echo $translations['nojs_vid_date'] . " " . $video['publishedAt']; ?></p>
												<p><?php echo $translations['nojs_vid_views'] . " " . $video['viewCount']; ?></p>
												<?php if (!$isScrapedInfo) { ?>
													<p><?php echo $translations['nojs_vid_likes'] . " " . $video['likeCount']; ?></p>
												<?php } ?>
												<?php if (false) { ?>
													<p><?php echo $translations['nojs_vid_dislikes'] . " " . $video['dislikeCount']; ?></p>
												<?php } ?>
											</blockquote>
										</div>
									<?php } ?>
								</div>
							</noscript>
						</div><!-- /.download -->
					<?php } else { ?>
						<div id="download">
							<div id="charts-alt" style="visibility:hidden">
								<div class="panel-group">
									<div class="panel panel-default">
										<div class="panel-heading">
											<h4 class="panel-title"><?php echo $translations['charts_alt_title']; ?></h4>
										</div>
                                        <div class="panel-body">
                                            <p><?php echo $translations['charts_alt_summary']; ?></p>
                                            <div class="row featuresMainRow">
                                                <div class="col-xs-12 featuresMainFrame">
                                                    <div class="col-xs-12 col-sm-3 col-md-2 text-center featuresIconFirst">
                                                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-9 col-md-10 featuresText">
                                                        <h4><?php echo $translations['charts_alt_card1_title']; ?></h4>
                                                        <p><?php echo $translations['charts_alt_card1_body']; ?></p>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 featuresMainFrame2">
                                                    <div class="col-xs-12 col-sm-9 col-md-10 featuresText2">
                                                        <h4><?php echo $translations['charts_alt_card2_title']; ?></h4>
                                                        <p><?php echo $translations['charts_alt_card2_body']; ?></p>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-3 col-md-2 text-center">
                                                        <span class="glyphicon glyphicon-headphones featuresIcon" aria-hidden="true"></span>
                                                    </div>
                                                </div>
                                                <div class="col-xs-12 featuresMainFrame">
                                                    <div class="col-xs-12 col-sm-3 col-md-2 text-center">
                                                        <span class="glyphicon glyphicon-save featuresIcon" aria-hidden="true"></span>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-9 col-md-10 featuresText">
                                                        <h4><?php echo $translations['charts_alt_card3_title']; ?></h4>
                                                        <p><?php echo $translations['charts_alt_card3_body']; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
				</div><!-- /.dl-result -->