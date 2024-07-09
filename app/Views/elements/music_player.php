<div id="video" style="position:absolute;top:0;left:-10000px"></div>
<div id="audio-player" style="display: none;">
	<div class="container">
		<div id="playerError">Sorry, you can't play this video because it is blocked in your Country!<p class="close">Close</p></div>
	</div><!-- /.container -->
	<div id="musicControls">
		<div class="container">	
			<div class="row">
				<div class="col-lg-1 col-md-1 col-sm-1 col-xs-2">
					<i id="play" class="material-icons">play_arrow</i>
					<i id="pause" class="material-icons">pause</i>
					</div><!-- /.col-lg-1 -->
				<div class="col-lg-11 col-md-11 col-sm-11 col-xs-10">
					<p id="videoTitle">Loading Video...</p>
					<p class="musicDuration"><span id="current-time">0:00</span> / <span id="duration">0:00</span></p>
					<div class="progress"><input type="range" id="progress-bar" value="0"></div>
					<p class="volume-control"><i id="mute-toggle" class="material-icons">volume_up</i><span class="volume"><input id="volume-input" type="range" max="100" min="0"></span></p><!-- /.volume-control -->
					<p class="close">Close</p>
				</div><!-- /.col-lg-11 -->
			</div><!-- /.row -->
		</div><!-- /.container -->
	</div><!-- /.musicControls -->
</div><!-- /#audio-player -->
