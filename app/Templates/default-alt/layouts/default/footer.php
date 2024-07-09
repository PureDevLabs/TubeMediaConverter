<?php 
	use MediaConverterPro\lib\Config;
?>
	
				</div><!-- /.col-lg-9 -->
				<div class="col-lg-3">
					<div class="sidebar">
					<?php if (Config::ENABLE_FACEBOOK_LIKE_BOX) { ?>
						<div class="social-network">
							<div class="facebook">
								<div class="fb-page" data-href="https://www.facebook.com/<?php echo Config::FACEBOOK_PAGE_NAME; ?>" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><div class="fb-xfbml-parse-ignore"></div></div>
							</div><!-- /.facebook -->
						</div><!-- /.social-network -->
					<?php } ?>
					</div><!-- /.sidebar -->
				</div><!-- /.col-lg-2 -->
			</div><!-- /.row -->
		</div><!-- /.main-content -->
	</div><!-- /.container -->	
	<div class="footer">
		<div class="copyright">Copyright &copy <?php echo date('Y'); ?> by <?php echo Config::_WEBSITE_DOMAIN; ?></div><!-- /.copyright -->
		<div class="software-version">powered by <a href="#" target="_blank"><span>Tube Media Converter</span></a></div><!-- /.software-version -->
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
