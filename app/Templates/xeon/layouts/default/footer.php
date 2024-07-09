<?php 
	use MediaConverterPro\lib\Config;
?>

					</div><!-- /.col-lg-9 -->
					<div class="col-lg-3">
						<div class="mb_sidebar">
							<div class="language-menu">
								<?php echo $this->element('language_menu'); ?>
							</div>
							<br />
							<?php if (Config::ENABLE_FACEBOOK_LIKE_BOX) { ?>
								<div class="facebook">
									<div class="fb-page" data-href="https://www.facebook.com/<?php echo Config::FACEBOOK_PAGE_NAME; ?>" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><div class="fb-xfbml-parse-ignore"></div></div>
								</div><!-- /.facebook -->
							<?php } ?>
						</div><!-- /.mb_sidebar -->
					</div><!-- /.col-lg-3 -->
				</div><!-- /.row -->
			</div><!-- /.mb_content -->
		</div><!-- /.container -->
	</div><!-- /.mb_content-area -->
	<div class="footer">
		<div class="container">
			<div class="">
				<div class="col-md-12 text-center">
					<p class="copyright">Copyright &copy <?php echo date('Y'); ?> by <?php echo Config::_WEBSITE_DOMAIN; ?></p>
					<p class="software-version">powered by <a href="#" target="_blank"><span>Tube Media Converter</span></a></p>
				</div>
			</div>
		</div>
	</div><!-- /.footer -->
	<?php if (Config::_MUSIC_PLAYER) echo $this->element('music_player'); ?> 
	<?php if (Config::_ENABLE_RECAPTCHA) { ?>
    	<div class="g-recaptcha" data-sitekey="<?php echo Config::_RECAPTCHA_KEY; ?>" data-callback="onSearchSubmit" data-size="invisible"></div>
    <?php } ?>
    <?php if (Config::_ENABLE_ADDTHIS_WIDGET) { ?>
    	<script type="text/javascript" src="<?php echo Config::_ADDTHIS_SCRIPT_URL; ?>" async="async"></script>
    <?php } ?>
</body>
</html>
