<?php 
	use MediaConverterPro\lib\Config;
	use MediaConverterPro\lib\Core;
	
	$apiMethodNum = 0;
?>

				<div class="developers">
					<?php if (empty(Config::$_apiAllowedDomains['json']) || $isPreview) { ?>
						<h4 class="text-center" style="margin-top:30px"><?php echo (empty(Config::$_apiAllowedDomains['search']) || empty(Config::$_apiAllowedDomains['button']) || $isPreview) ? $translations['api_method'] . ++$apiMethodNum . ' - ' : ''; ?><?php echo $translations['api_json']; ?></h4>    
						<br />
						<h4 class="panel-title panel-heading"><?php echo $translations['api_request']; ?></h4>
						<pre class="prettyprint lang-html"><?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/json/<b><span class="nocode" style="color:#65b042">{format}</span></b>/<b><span class="nocode" style="color:#65b042">{YouTube-Video-ID}</span></b></pre>
						<h4 class="panel-title panel-heading"><?php echo $translations['api_where']; ?><code><b>{format}</b></code><?php echo $translations['api_following']; ?></h4>
						<div class="panel-title panel-heading">
							<ul style="margin-bottom:0">
								<li>"<b>mp3</b>" : <?php echo $translations['api_for'] . $translations['api_mp3'] . $translations['api_results']; ?></li> 
								<?php if (Config::_ENABLE_NONDASH_VIDEO) { ?>
									<li>"<b>videos</b>" : <?php echo $translations['api_for'] . $translations['api_videos'] . $translations['api_results']; ?></li>
								<?php } ?>
								<?php if (Config::_ENABLE_MERGED_VIDEO_STREAMS) { ?>
									<li>"<b>mergedstreams</b>" : <?php echo $translations['api_for'] . $translations['api_mergedstreams'] . $translations['api_results']; ?></li>
								<?php } ?>	
								<li>"<b>videostreams</b>" : <?php echo $translations['api_for'] . $translations['api_videostreams'] . $translations['api_results']; ?></li>
								<li>"<b>audiostreams</b>" : <?php echo $translations['api_for'] . $translations['api_audiostreams'] . $translations['api_results']; ?></li>
							</ul>
						</div>
						<h4 class="panel-title panel-heading"><?php echo $translations['api_and']; ?><code><b>{YouTube-Video-ID}</b></code><?php echo $translations['api_validId']; ?></h4>
						<h4 class="panel-title panel-heading"><?php echo $translations['api_json_eg']; ?></h4>
						<div>
							<!-- Nav tabs -->
							<div class="panel-heading">
								<ul class="nav nav-tabs" role="tablist">
									<li role="presentation" class="active"><a href="#mp3-json-api" aria-controls="home" role="tab" data-toggle="tab"><?php echo $translations['api_mp3']; ?></a></li>
									<?php if (Config::_ENABLE_NONDASH_VIDEO) { ?>
										<li role="presentation"><a href="#videos-json-api" aria-controls="profile" role="tab" data-toggle="tab"><?php echo $translations['api_videos']; ?></a></li>
									<?php } ?>
									<?php if (Config::_ENABLE_MERGED_VIDEO_STREAMS) { ?>
										<li role="presentation"><a href="#mergedstreams-json-api" aria-controls="profile" role="tab" data-toggle="tab"><?php echo $translations['api_mergedstreams']; ?></a></li>
									<?php } ?>
									<li role="presentation"><a href="#videostreams-json-api" aria-controls="messages" role="tab" data-toggle="tab"><?php echo $translations['api_videostreams']; ?></a></li>
									<li role="presentation"><a href="#audiostreams-json-api" aria-controls="settings" role="tab" data-toggle="tab"><?php echo $translations['api_audiostreams']; ?></a></li>
								</ul>
							</div>
							<!-- Tab panes -->
							<div class="tab-content">
								<div role="tabpanel" class="tab-pane fade in active" id="mp3-json-api">
									<pre class="prettyprint lang-html">GET <?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/json/mp3/CevxZvSJLk8 HTTP/1.1</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>
									<pre class="prettyprint lang-php">{
	"vidID": "CevxZvSJLk8",
	"vidTitle": "Katy Perry - Roar (Official)",
	"vidThumb": "https://img.youtube.com/vi/CevxZvSJLk8/0.jpg",
	"duration": 269,	
	"vidInfo": {
		"0": {
			"dloadUrl": "<?php echo WEBROOT; ?>@download/320-58e7c0e582ce2-10800000/mp3/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp3",
			"bitrate": 320,
			"mp3size": "10.3 MB"
		},
		"1": {
			"dloadUrl": "<?php echo WEBROOT; ?>@download/256-58e7c0e582ce2-8640000/mp3/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp3",
			"bitrate": 256,
			"mp3size": "8.24 MB"
		},
		"2": {
			"dloadUrl": "<?php echo WEBROOT; ?>@download/192-58e7c0e582ce2-6480000/mp3/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp3",
			"bitrate": 192,
			"mp3size": "6.18 MB"
		},
		"3": {
			"dloadUrl": "<?php echo WEBROOT; ?>@download/128-58e7c0e582ce2-4320000/mp3/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp3",
			"bitrate": 128,
			"mp3size": "4.12 MB"
		},
		"4": {
			"dloadUrl": "<?php echo WEBROOT; ?>@download/64-58e7c0e582ce2-2160000/mp3/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp3",
			"bitrate": 64,
			"mp3size": "2.06 MB"
		}
	}
}</pre>
								</div>
								<div role="tabpanel" class="tab-pane fade" id="videos-json-api">
									<pre class="prettyprint lang-html">GET <?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/json/videos/CevxZvSJLk8 HTTP/1.1</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>								
									<pre class="prettyprint lang-php">{
	"vidID": "CevxZvSJLk8",
	"vidTitle": "Katy Perry - Roar (Official)",
	"vidThumb": "https://img.youtube.com/vi/CevxZvSJLk8/0.jpg",
	"duration": 269,	
	"vidInfo": {
		"0": {
			"rSize": "67.79 MB",
			"quality": "720",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?ipbits=0&mm=31&mn=sn-5uh5o-f5f6&itag=22&mt=1492837642&dur=269.165&id=o-AB1T_kZWIIiA_ihhSlAK4RXegp3Z9A18zn39hF0Aa51G&initcwndbps=197500&pl=21&source=youtube&mv=m&ip=137.74.1.176&mime=video%2Fmp4&ms=au&ratebypass=yes&requiressl=yes&sparams=dur%2Cei%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpl%2Cratebypass%2Crequiressl%2Csource%2Cupn%2Cexpire&key=yt6&lmt=1478841669593636&upn=Wp652d9rFKo&ei=OuX6WLqMDJaLNK-MmLAE&expire=1492859290&signature=A8B328D48101C553D0659D2D9D1F1B2F249D2035.792897E2CF6BFEFC97C81A90EA9CBF9B76FD0283&type=video%252Fmp4%253B%2Bcodecs%253D%2522avc1.64001F%252C%2Bmp4a.40.2%2522&quality=hd720&title=Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/22-58fae53acfc14-mp4-71087585/videos/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp4",
			"ftype": "mp4",
			"framerate": "",
			"bitrate": "",
			"codec": "",
			"itag": "22"
		},
		"1": {
			"rSize": "27.24 MB",
			"quality": "360",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?ipbits=0&mm=31&mn=sn-5uh5o-f5f6&itag=43&mt=1492837642&dur=0.000&id=o-AB1T_kZWIIiA_ihhSlAK4RXegp3Z9A18zn39hF0Aa51G&initcwndbps=197500&pl=21&source=youtube&mv=m&ip=137.74.1.176&mime=video%2Fwebm&ms=au&ratebypass=yes&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpl%2Cratebypass%2Crequiressl%2Csource%2Cupn%2Cexpire&gir=yes&clen=28559516&key=yt6&lmt=1378406587282120&upn=Wp652d9rFKo&ei=OuX6WLqMDJaLNK-MmLAE&expire=1492859290&signature=034E22ACDCD519B206F57F4E83531F20E02B44CE.0F2F6F68AEDA962A6FE339B396CEB1C9AFC76BEB&type=video%252Fwebm%253B%2Bcodecs%253D%2522vp8.0%252C%2Bvorbis%2522&quality=medium&title=Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/43-58fae53acfc14-webm-28559516/videos/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "",
			"bitrate": "",
			"codec": "",
			"itag": "43"
		},
		"2": {
			"rSize": "23.04 MB",
			"quality": "360",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?ipbits=0&mm=31&mn=sn-5uh5o-f5f6&itag=18&mt=1492837642&dur=269.165&id=o-AB1T_kZWIIiA_ihhSlAK4RXegp3Z9A18zn39hF0Aa51G&initcwndbps=197500&pl=21&source=youtube&mv=m&ip=137.74.1.176&mime=video%2Fmp4&ms=au&ratebypass=yes&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpl%2Cratebypass%2Crequiressl%2Csource%2Cupn%2Cexpire&gir=yes&clen=24160662&key=yt6&lmt=1478829646729343&upn=Wp652d9rFKo&ei=OuX6WLqMDJaLNK-MmLAE&expire=1492859290&signature=270317DE0C7738F41DCCD1C57CB00E4016E72863.9534418D093B61007460C6EE1E3442BF14F923E0&type=video%252Fmp4%253B%2Bcodecs%253D%2522avc1.42001E%252C%2Bmp4a.40.2%2522&quality=medium&title=Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/18-58fae53acfc14-mp4-24160662/videos/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp4",
			"ftype": "mp4",
			"framerate": "",
			"bitrate": "",
			"codec": "",
			"itag": "18"
		},
		"3": {
			"rSize": "7.3 MB",
			"quality": "240",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?ipbits=0&mm=31&mn=sn-5uh5o-f5f6&itag=36&mt=1492837642&dur=269.306&id=o-AB1T_kZWIIiA_ihhSlAK4RXegp3Z9A18zn39hF0Aa51G&initcwndbps=197500&pl=21&source=youtube&mv=m&ip=137.74.1.176&mime=video%2F3gpp&ms=au&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&gir=yes&clen=7651100&key=yt6&lmt=1384321001624346&upn=Wp652d9rFKo&ei=OuX6WLqMDJaLNK-MmLAE&expire=1492859290&signature=35D4FA5FB1407310FFBAD2B025B7A90589C3DDDC.AF6B508B592A2D99C22E09D8AF595D5A6F18DE17&type=video%252F3gpp%253B%2Bcodecs%253D%2522mp4v.20.3%252C%2Bmp4a.40.2%2522&quality=small&ratebypass=yes&title=Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/36-58fae53acfc14-3gp-7651100/videos/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.3gp",
			"ftype": "3gp",
			"framerate": "",
			"bitrate": "",
			"codec": "",
			"itag": "36"
		},
		"4": {
			"rSize": "2.64 MB",
			"quality": "144",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?ipbits=0&mm=31&mn=sn-5uh5o-f5f6&itag=17&mt=1492837642&dur=269.492&id=o-AB1T_kZWIIiA_ihhSlAK4RXegp3Z9A18zn39hF0Aa51G&initcwndbps=197500&pl=21&source=youtube&mv=m&ip=137.74.1.176&mime=video%2F3gpp&ms=au&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&gir=yes&clen=2769777&key=yt6&lmt=1384320873661266&upn=Wp652d9rFKo&ei=OuX6WLqMDJaLNK-MmLAE&expire=1492859290&signature=6E5A508E54E1CFA5E692F4D38962DF6DAD8AD9D4.7225B93B923384F0F6319249B81E2A9FE68FF8F6&type=video%252F3gpp%253B%2Bcodecs%253D%2522mp4v.20.3%252C%2Bmp4a.40.2%2522&quality=small&ratebypass=yes&title=Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/17-58fae53acfc14-3gp-2769777/videos/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.3gp",
			"ftype": "3gp",
			"framerate": "",
			"bitrate": "",
			"codec": "",
			"itag": "17"
		}
	}
}</pre>
								</div>						
								<div role="tabpanel" class="tab-pane fade" id="mergedstreams-json-api">
									<pre class="prettyprint lang-html">GET <?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/json/mergedstreams/CevxZvSJLk8 HTTP/1.1</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>								
									<pre class="prettyprint lang-php">{
	"vidID": "CevxZvSJLk8",
	"vidTitle": "Katy Perry - Roar (Official)",
	"vidThumb": "https://img.youtube.com/vi/CevxZvSJLk8/0.jpg",
	"duration": 269,	
	"vidInfo": {
		"0": {
			"rSize": "79.79 MB",
			"quality": "1080",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/137:140-5bef3b8359ed2-mp4:m4a-79386956:4275999-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mkv",
			"ftype": "mkv",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "fullhd-mp4"
		},
		"1": {
			"rSize": "74.74 MB",
			"quality": "1080",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/248:251-5bef3b8359ed2-webm:webm-74198272:4175640-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "fullhd-webm"
		},
		"2": {
			"rSize": "54.49 MB",
			"quality": "720",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/136:140-5bef3b8359ed2-mp4:m4a-52857716:4275999-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mkv",
			"ftype": "mkv",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "hd-mp4"
		},
		"3": {
			"rSize": "45.51 MB",
			"quality": "720",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/247:251-5bef3b8359ed2-webm:webm-43542505:4175640-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "hd-webm"
		},
		"4": {
			"rSize": "36.58 MB",
			"quality": "480",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/135:140-5bef3b8359ed2-mp4:m4a-34078954:4275999-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mkv",
			"ftype": "mkv",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "480-mp4"
		},
		"5": {
			"rSize": "25.31 MB",
			"quality": "480",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/244:171-5bef3b8359ed2-webm:webm-22526530:4015788-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "480-webm"
		},
		"6": {
			"rSize": "23.22 MB",
			"quality": "360",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/134:140-5bef3b8359ed2-mp4:m4a-20067591:4275999-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mkv",
			"ftype": "mkv",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "360-mp4"
		},
		"7": {
			"rSize": "15.58 MB",
			"quality": "360",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/243:171-5bef3b8359ed2-webm:webm-12323596:4015788-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "360-webm"
		},
		"8": {
			"rSize": "12.79 MB",
			"quality": "240",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/133:140-5bef3b8359ed2-mp4:m4a-9130140:4275999-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mkv",
			"ftype": "mkv",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "240-mp4"
		},
		"9": {
			"rSize": "8.31 MB",
			"quality": "240",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/242:250-5bef3b8359ed2-webm:webm-6624642:2093780-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "240-webm"
		},
		"10": {
			"rSize": "7.2 MB",
			"quality": "144",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/160:140-5bef3b8359ed2-mp4:m4a-3275706:4275999-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mkv",
			"ftype": "mkv",
			"framerate": "15",
			"bitrate": "",
			"codec": "",
			"itag": "144-mp4"
		},
		"11": {
			"rSize": "4.42 MB",
			"quality": "144",
			"directurl": "",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/278:249-5bef3b8359ed2-webm:webm-3052666:1578649-269/mergedstreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "15",
			"bitrate": "",
			"codec": "",
			"itag": "144-webm"
		}
	}
}</pre>
								</div>
								<div role="tabpanel" class="tab-pane fade" id="videostreams-json-api">
									<pre class="prettyprint lang-html">GET <?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/json/videostreams/CevxZvSJLk8 HTTP/1.1</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>								
									<pre class="prettyprint lang-php">{
	"vidID": "CevxZvSJLk8",
	"vidTitle": "Katy Perry - Roar (Official)",
	"vidThumb": "https://img.youtube.com/vi/CevxZvSJLk8/0.jpg",
	"duration": 269,	
	"vidInfo": {
		"0": {
			"rSize": "116.17 MB",
			"quality": "1080",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=1920x1080&initcwndbps=215000&dur=269.102&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=121811386&itag=137&requiressl=yes&ip=193.70.125.81&mime=video%2Fmp4&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1478838201195116&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=1080p&signature=8AF48B22377AF753307928612997EBFC0C08B054.6B9606A89C62EEC56D229FCDD81DC1FDB19F656F&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/137-58fae9948011d-mp4-121811386/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp4",
			"ftype": "mp4",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "137"
		},
		"1": {
			"rSize": "84.36 MB",
			"quality": "1080",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=1920x1080&initcwndbps=215000&dur=269.060&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=88457561&itag=248&requiressl=yes&ip=193.70.125.81&mime=video%2Fwebm&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1449556089462409&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=1080p&signature=1744D650EF084EBB6FCEAD6E0D81B5613EFDFE4B.777290054FE1278E26E74E478A62A5E107683EE6&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/248-58fae9948011d-webm-88457561/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "248"
		},
		"2": {
			"rSize": "63.73 MB",
			"quality": "720",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=1280x720&initcwndbps=215000&dur=269.102&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=66825779&itag=136&requiressl=yes&ip=193.70.125.81&mime=video%2Fmp4&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1478841623640509&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=720p&signature=09522FA5C11047F974F657618AE8EBB4F6A74357.B23C4D708531BF2EEE743316CF2205CA0C62DD09&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/136-58fae9948011d-mp4-66825779/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp4",
			"ftype": "mp4",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "136"
		},
		"3": {
			"rSize": "47.71 MB",
			"quality": "720",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=1280x720&initcwndbps=215000&dur=269.060&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=50030690&itag=247&requiressl=yes&ip=193.70.125.81&mime=video%2Fwebm&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1449556446128414&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=720p&signature=BA1B00A4CF28F1777A102F00A164D554AE960E7B.26761B8279B43EBD9DFF95E9C44D43956987AA51&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/247-58fae9948011d-webm-50030690/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "247"
		},
		"4": {
			"rSize": "32.97 MB",
			"quality": "480",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=854x480&initcwndbps=215000&dur=269.102&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=34568157&itag=135&requiressl=yes&ip=193.70.125.81&mime=video%2Fmp4&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1478841623657218&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=480p&signature=1B3533F6C960B14DF1C74E3E94BC34EFBF3B9FA0.04294BAC58A3440A100464FF53B1B5CE9978C724&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/135-58fae9948011d-mp4-34568157/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp4",
			"ftype": "mp4",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "135"
		},
		"5": {
			"rSize": "24.28 MB",
			"quality": "480",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=854x480&initcwndbps=215000&dur=269.060&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=25458088&itag=244&requiressl=yes&ip=193.70.125.81&mime=video%2Fwebm&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1449556444988251&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=480p&signature=21CCCAFF261EFB14C6754AA1DB32D994CFF6BA3B.6427E42361C1678BBE283865E13D37916EC1A6B9&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/244-58fae9948011d-webm-25458088/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "244"
		},
		"6": {
			"rSize": "17.48 MB",
			"quality": "360",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=640x360&initcwndbps=215000&dur=269.102&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=18325768&itag=134&requiressl=yes&ip=193.70.125.81&mime=video%2Fmp4&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1478841619832595&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=360p&signature=9E0C1DC96E9DC16263F5A6C4F6F3C6BBC11EA27E.153CCC7776ABDDAA73B85C155E86AC399F46AC5C&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/134-58fae9948011d-mp4-18325768/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp4",
			"ftype": "mp4",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "134"
		},
		"7": {
			"rSize": "13.18 MB",
			"quality": "360",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=640x360&initcwndbps=215000&dur=269.060&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=13821316&itag=243&requiressl=yes&ip=193.70.125.81&mime=video%2Fwebm&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1449556444122717&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=360p&signature=407D6EB829F979FD6FE07B6A1B03D256F8FF8722.DB4051B0582C0F75C9E6DC4690B301C1C6464D59&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/243-58fae9948011d-webm-13821316/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "243"
		},
		"8": {
			"rSize": "7.28 MB",
			"quality": "240",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=426x240&initcwndbps=215000&dur=269.102&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=7637886&itag=133&requiressl=yes&ip=193.70.125.81&mime=video%2Fmp4&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1478841618531765&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=240p&signature=BB53FD73632DA30E807F914F671D546824DDF91E.1172799335FD03BC679608A0C2E068CE3641997E&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/133-58fae9948011d-mp4-7637886/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp4",
			"ftype": "mp4",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "133"
		},
		"9": {
			"rSize": "7.16 MB",
			"quality": "240",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=426x240&initcwndbps=215000&dur=269.060&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=7505220&itag=242&requiressl=yes&ip=193.70.125.81&mime=video%2Fwebm&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1449556444140323&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=240p&signature=E39882E6EDD10AD5E7A76B602F47F2E7ED2E7397.38E334B584817BB3809C984B4D94A51638F75CF6&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/242-58fae9948011d-webm-7505220/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "30",
			"bitrate": "",
			"codec": "",
			"itag": "242"
		},
		"10": {
			"rSize": "3.28 MB",
			"quality": "144",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=24&size=256x144&initcwndbps=215000&dur=269.102&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=3440468&itag=160&requiressl=yes&ip=193.70.125.81&mime=video%2Fmp4&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1478841619229633&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=144p&signature=4EADC74169CFA8D0EBCBCBD9C2DC5736C1DC0D67.4531249CE707CF142EED7C7BE1E4BD2390601E35&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/160-58fae9948011d-mp4-3440468/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.mp4",
			"ftype": "mp4",
			"framerate": "15",
			"bitrate": "",
			"codec": "",
			"itag": "160"
		},
		"11": {
			"rSize": "3.18 MB",
			"quality": "144",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?fps=12&size=256x144&initcwndbps=215000&dur=269.018&upn=M8eDc-KBrLU&key=yt6&keepalive=yes&clen=3338486&itag=278&requiressl=yes&ip=193.70.125.81&mime=video%2Fwebm&mn=sn-5uh5o-f5f6&pl=23&source=youtube&mm=31&ms=au&id=o-AD09h7_FeC3HX73KtXA-t8LLln90DWmWeQ5Dcfx9FjOU&mv=m&mt=1492838725&expire=1492860403&gir=yes&lmt=1449556443708071&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2cms%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&ei=k-n6WPDgJo_Cdo_Fm7AO&ipbits=0&pcm2cms=yes&quality_label=144p&signature=3AED4867AFD392B513BC04698AE858DAE9B7F003.4FE03F31B31F237CBE01CC5DCE9A55107B27ED60&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/278-58fae9948011d-webm-3338486/videostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "15",
			"bitrate": "",
			"codec": "",
			"itag": "278"
		}
	}
}</pre>
								</div>
								<div role="tabpanel" class="tab-pane fade" id="audiostreams-json-api">
									<pre class="prettyprint lang-html">GET <?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/json/audiostreams/CevxZvSJLk8 HTTP/1.1</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>								
									<pre class="prettyprint lang-php">{
	"vidID": "CevxZvSJLk8",
	"vidTitle": "Katy Perry - Roar (Official)",
	"vidThumb": "https://img.youtube.com/vi/CevxZvSJLk8/0.jpg",
	"duration": 269,	
	"vidInfo": {
		"0": {
			"rSize": "4.08 MB",
			"quality": "",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?mt=1492841663&mv=m&initcwndbps=183750&ipbits=0&ms=au&mm=31&mn=sn-5uh5o-f5f6&ip=137.74.1.176&key=yt6&keepalive=yes&source=youtube&clen=4275631&pcm2=no&lmt=1478829623441921&gir=yes&upn=3Cks0aHaVpw&pl=21&expire=1492863364&id=o-AGPeW3bkX1nrk-fjCVQFZIVsUdAaHNbcsWsG0M2V8e4u&itag=140&mime=audio%2Fmp4&dur=269.165&ei=JPX6WJ3rJJegd7zqodgJ&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&signature=D602D3604E3B7911EDE4AF1E7D9EDD61F8524DF6.0CFF23E3A835992CA48134DB1CE2A20297591671&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/140-58faf5254fc43-m4a-4275631/audiostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.m4a",
			"ftype": "m4a",
			"framerate": "",
			"bitrate": "128",
			"codec": "AAC",
			"itag": "140"
		},
		"1": {
			"rSize": "3.83 MB",
			"quality": "",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?mt=1492841663&mv=m&initcwndbps=183750&ipbits=0&ms=au&mm=31&mn=sn-5uh5o-f5f6&ip=137.74.1.176&key=yt6&keepalive=yes&source=youtube&clen=4015919&pcm2=no&lmt=1449555875921501&gir=yes&upn=3Cks0aHaVpw&pl=21&expire=1492863364&id=o-AGPeW3bkX1nrk-fjCVQFZIVsUdAaHNbcsWsG0M2V8e4u&itag=171&mime=audio%2Fwebm&dur=269.098&ei=JPX6WJ3rJJegd7zqodgJ&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&signature=A383A78202B32CF3799B954DA0DBF875919E4CFF.B45576B572987FBEA731120E2682D5E612CE3354&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/171-58faf5254fc43-webm-4015919/audiostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "",
			"bitrate": "128",
			"codec": "Vorbis",
			"itag": "171"
		},
		"2": {
			"rSize": "1.56 MB",
			"quality": "",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?mt=1492841663&mv=m&initcwndbps=183750&ipbits=0&ms=au&mm=31&mn=sn-5uh5o-f5f6&ip=137.74.1.176&key=yt6&keepalive=yes&source=youtube&clen=1637862&pcm2=no&lmt=1449555874424077&gir=yes&upn=3Cks0aHaVpw&pl=21&expire=1492863364&id=o-AGPeW3bkX1nrk-fjCVQFZIVsUdAaHNbcsWsG0M2V8e4u&itag=249&mime=audio%2Fwebm&dur=269.121&ei=JPX6WJ3rJJegd7zqodgJ&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&signature=B99461AC678080521E516D1AB93E61D9A9CB1DEB.D2EE6D4117F3EE066A73CE0124E36EBDEDEE9D8C&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/249-58faf5254fc43-webm-1637862/audiostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "",
			"bitrate": "48",
			"codec": "Opus",
			"itag": "249"
		},
		"3": {
			"rSize": "2.04 MB",
			"quality": "",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?mt=1492841663&mv=m&initcwndbps=183750&ipbits=0&ms=au&mm=31&mn=sn-5uh5o-f5f6&ip=137.74.1.176&key=yt6&keepalive=yes&source=youtube&clen=2137068&pcm2=no&lmt=1449555875441543&gir=yes&upn=3Cks0aHaVpw&pl=21&expire=1492863364&id=o-AGPeW3bkX1nrk-fjCVQFZIVsUdAaHNbcsWsG0M2V8e4u&itag=250&mime=audio%2Fwebm&dur=269.121&ei=JPX6WJ3rJJegd7zqodgJ&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&signature=06CD7B4B370845B7F55BB784F13EB76B9B853D3E.0D5E87A668E790C98573C96E9F56AEF7C6071002&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/250-58faf5254fc43-webm-2137068/audiostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "",
			"bitrate": "64",
			"codec": "Opus",
			"itag": "250"
		},
		"4": {
			"rSize": "4.04 MB",
			"quality": "",
			"directurl": "https://r2---sn-5uh5o-f5f6.googlevideo.com/videoplayback?mt=1492841663&mv=m&initcwndbps=183750&ipbits=0&ms=au&mm=31&mn=sn-5uh5o-f5f6&ip=137.74.1.176&key=yt6&keepalive=yes&source=youtube&clen=4235503&pcm2=no&lmt=1449555873227770&gir=yes&upn=3Cks0aHaVpw&pl=21&expire=1492863364&id=o-AGPeW3bkX1nrk-fjCVQFZIVsUdAaHNbcsWsG0M2V8e4u&itag=251&mime=audio%2Fwebm&dur=269.121&ei=JPX6WJ3rJJegd7zqodgJ&requiressl=yes&sparams=clen%2Cdur%2Cei%2Cgir%2Cid%2Cinitcwndbps%2Cip%2Cipbits%2Citag%2Ckeepalive%2Clmt%2Cmime%2Cmm%2Cmn%2Cms%2Cmv%2Cpcm2%2Cpl%2Crequiressl%2Csource%2Cupn%2Cexpire&signature=CB151F0C7872AA59F15613AAB82020AEEDABF196.0C5BA6371382B6346348FB4725B2DB7397127C99&ratebypass=yes",
			"dloadUrl": "<?php echo WEBROOT; ?>@download/251-58faf5254fc43-webm-4235503/audiostreams/CevxZvSJLk8/Katy%2BPerry%2B-%2BRoar%2B%2528Official%2529.webm",
			"ftype": "webm",
			"framerate": "",
			"bitrate": "160",
			"codec": "Opus",
			"itag": "251"
		}
	}
}</pre>
								</div>
							</div>
						</div>

						<br />
					<?php } ?>
					<?php if (empty(Config::$_apiAllowedDomains['button']) || $isPreview) { ?>	
						<h4 class="text-center" style="margin-top:30px"><?php echo (empty(Config::$_apiAllowedDomains['search']) || empty(Config::$_apiAllowedDomains['json']) || $isPreview) ? $translations['api_method'] . ++$apiMethodNum . ' - ' : ''; ?><?php echo $translations['api_button']; ?></h4>        
						<br />
						<h4 class="panel-title panel-heading"><?php echo $translations['api_src']; ?></h4>
						<pre class="prettyprint lang-html"><?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/button/<b><span class="nocode" style="color:#65b042">{format}</span></b>/<b><span class="nocode" style="color:#65b042">{YouTube-Video-ID}</span></b></pre>						
						<h4 class="panel-title panel-heading"><?php echo $translations['api_where']; ?><code><b>{format}</b></code><?php echo $translations['api_following']; ?></h4>
						<div class="panel-title panel-heading">
							<ul style="margin-bottom:0">
								<li>"<b>mp3</b>" : <?php echo $translations['api_for'] . $translations['api_mp3'] . $translations['api_results']; ?></li> 
								<?php if (Config::_ENABLE_NONDASH_VIDEO) { ?>
									<li>"<b>videos</b>" : <?php echo $translations['api_for'] . $translations['api_videos'] . $translations['api_results']; ?></li>
								<?php } ?>
								<?php if (Config::_ENABLE_MERGED_VIDEO_STREAMS) { ?>
									<li>"<b>mergedstreams</b>" : <?php echo $translations['api_for'] . $translations['api_mergedstreams'] . $translations['api_results']; ?></li>
								<?php } ?>
								<li>"<b>videostreams</b>" : <?php echo $translations['api_for'] . $translations['api_videostreams'] . $translations['api_results']; ?></li>
								<li>"<b>audiostreams</b>" : <?php echo $translations['api_for'] . $translations['api_audiostreams'] . $translations['api_results']; ?></li>
							</ul>
						</div>
						<h4 class="panel-title panel-heading"><?php echo $translations['api_and']; ?><code><b>{YouTube-Video-ID}</b></code><?php echo $translations['api_validId']; ?></h4>
						<h4 class="panel-title panel-heading"><?php echo $translations['api_button_eg']; ?></h4>
						<div>
							<!-- Nav tabs -->
							<div class="panel-heading">
								<ul class="nav nav-tabs" role="tablist" id="button-api-tabs">
									<li role="presentation" class="active"><a href="#mp3-button-api" aria-controls="home" role="tab" data-toggle="tab"><?php echo $translations['api_mp3']; ?></a></li>
									<?php if (Config::_ENABLE_NONDASH_VIDEO) { ?>
										<li role="presentation"><a href="#videos-button-api" aria-controls="profile" role="tab" data-toggle="tab"><?php echo $translations['api_videos']; ?></a></li>
									<?php } ?>
									<?php if (Config::_ENABLE_MERGED_VIDEO_STREAMS) { ?>
										<li role="presentation"><a href="#mergedstreams-button-api" aria-controls="profile" role="tab" data-toggle="tab"><?php echo $translations['api_mergedstreams']; ?></a></li>
									<?php } ?>
									<li role="presentation"><a href="#videostreams-button-api" aria-controls="messages" role="tab" data-toggle="tab"><?php echo $translations['api_videostreams']; ?></a></li>
									<li role="presentation"><a href="#audiostreams-button-api" aria-controls="settings" role="tab" data-toggle="tab"><?php echo $translations['api_audiostreams']; ?></a></li>
								</ul>
							</div>
							<!-- Tab panes -->
							<div class="tab-content">
								<div role="tabpanel" class="tab-pane fade in active" id="mp3-button-api">
									<pre class="prettyprint lang-html">&#x3C;iframe class="button-api-frame" src="<?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/button/mp3/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"&#x3E;&#x3C;/iframe&#x3E;

&#x3C;!-- Optional script that automatically makes iframe content responsive. --&#x3E;
&#x3C;script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.min.js"&#x3E;&#x3C;/script&#x3E;
&#x3C;script&#x3E;iFrameResize({checkOrigin: false}, '.button-api-frame');&#x3C;/script&#x3E;</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>
									<?php if (!$isPreview || $apiAllowedHere) { ?>
										<div class="panel-heading">
											<iframe class="button-api-frame" src="<?php echo WEBROOT; ?>@api/button/mp3/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"></iframe>
										</div>
									<?php } else { ?>
										<div class="text-center">
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/mp3.png" alt="mp3 button api" class="hidden-xs visible-sm visible-md visible-lg" /></div>
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/mobile/mp3.png" alt="mp3 button api" class="visible-xs hidden-sm hidden-md hidden-lg" /></div>
										</div>
									<?php } ?>
								</div>	
								<div role="tabpanel" class="tab-pane fade" id="videos-button-api">
									<pre class="prettyprint lang-html">&#x3C;iframe class="button-api-frame" src="<?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/button/videos/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"&#x3E;&#x3C;/iframe&#x3E;

&#x3C;!-- Optional script that automatically makes iframe content responsive. --&#x3E;
&#x3C;script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.min.js"&#x3E;&#x3C;/script&#x3E;
&#x3C;script&#x3E;iFrameResize({checkOrigin: false}, '.button-api-frame');&#x3C;/script&#x3E;</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>
									<?php if (!$isPreview || $apiAllowedHere) { ?>
										<div class="panel-heading">
											<iframe class="button-api-frame" src="<?php echo WEBROOT; ?>@api/button/videos/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"></iframe>
										</div>
									<?php } else { ?>
										<div class="text-center">
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/video.png" alt="videos button api" class="hidden-xs visible-sm visible-md visible-lg" /></div>
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/mobile/video.png" alt="videos button api" class="visible-xs hidden-sm hidden-md hidden-lg" /></div>
										</div>	
									<?php } ?>
								</div>
								<div role="tabpanel" class="tab-pane fade" id="mergedstreams-button-api">
									<pre class="prettyprint lang-html">&#x3C;iframe class="button-api-frame" src="<?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/button/mergedstreams/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"&#x3E;&#x3C;/iframe&#x3E;

&#x3C;!-- Optional script that automatically makes iframe content responsive. --&#x3E;
&#x3C;script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.min.js"&#x3E;&#x3C;/script&#x3E;
&#x3C;script&#x3E;iFrameResize({checkOrigin: false}, '.button-api-frame');&#x3C;/script&#x3E;</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>
									<?php if (!$isPreview || $apiAllowedHere) { ?>
										<div class="panel-heading">
											<iframe class="button-api-frame" src="<?php echo WEBROOT; ?>@api/button/mergedstreams/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"></iframe>
										</div>
									<?php } else { ?>
										<div class="text-center">
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/merged.png" alt="mergedstreams button api" class="hidden-xs visible-sm visible-md visible-lg" /></div>
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/mobile/merged.png" alt="mergedstreams button api" class="visible-xs hidden-sm hidden-md hidden-lg" /></div>
										</div>		
									<?php } ?>
								</div>
								<div role="tabpanel" class="tab-pane fade" id="videostreams-button-api">
									<pre class="prettyprint lang-html">&#x3C;iframe class="button-api-frame" src="<?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/button/videostreams/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"&#x3E;&#x3C;/iframe&#x3E;

&#x3C;!-- Optional script that automatically makes iframe content responsive. --&#x3E;
&#x3C;script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.min.js"&#x3E;&#x3C;/script&#x3E;
&#x3C;script&#x3E;iFrameResize({checkOrigin: false}, '.button-api-frame');&#x3C;/script&#x3E;</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>
									<?php if (!$isPreview || $apiAllowedHere) { ?>
										<div class="panel-heading">
											<iframe class="button-api-frame" src="<?php echo WEBROOT; ?>@api/button/videostreams/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"></iframe>
										</div>
									<?php } else { ?>
										<div class="text-center">
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/video-only.png" alt="videostreams button api" class="hidden-xs visible-sm visible-md visible-lg" /></div>
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/mobile/video-only.png" alt="videostreams button api" class="visible-xs hidden-sm hidden-md hidden-lg" /></div>
										</div>
									<?php } ?>
								</div>		
								<div role="tabpanel" class="tab-pane fade" id="audiostreams-button-api">
									<pre class="prettyprint lang-html">&#x3C;iframe class="button-api-frame" src="<?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/button/audiostreams/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"&#x3E;&#x3C;/iframe&#x3E;

&#x3C;!-- Optional script that automatically makes iframe content responsive. --&#x3E;
&#x3C;script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.min.js"&#x3E;&#x3C;/script&#x3E;
&#x3C;script&#x3E;iFrameResize({checkOrigin: false}, '.button-api-frame');&#x3C;/script&#x3E;</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>
									<?php if (!$isPreview || $apiAllowedHere) { ?>
										<div class="panel-heading">
											<iframe class="button-api-frame" src="<?php echo WEBROOT; ?>@api/button/audiostreams/CevxZvSJLk8" width="100%" height="100%" allowtransparency="true" scrolling="no" style="border:none"></iframe>
										</div>	
									<?php } else { ?>
										<div class="text-center">
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/audio-only.png" alt="audiostreams button api" class="hidden-xs visible-sm visible-md visible-lg" /></div>
											<div style="display:inline-block"><img src="<?php echo WEBROOT; ?>docs/img/button-api/mobile/audio-only.png" alt="audiostreams button api" class="visible-xs hidden-sm hidden-md hidden-lg" /></div>
										</div>
									<?php } ?>
								</div>		
							</div>
						</div>
						
						<br />
					<?php } ?>
					<?php if (empty(Config::$_apiAllowedDomains['search']) || $isPreview) { ?>
						<h4 class="text-center" style="margin-top:30px"><?php echo (empty(Config::$_apiAllowedDomains['json']) || empty(Config::$_apiAllowedDomains['button']) || $isPreview) ? $translations['api_method'] . ++$apiMethodNum . ' - ' : ''; ?><?php echo $translations['api_search']; ?></h4>    
						<br />
						<h4 class="panel-title panel-heading"><?php echo $translations['api_request']; ?></h4>
						<pre class="prettyprint lang-html"><?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/search/YouTube/<b><span class="nocode" style="color:#65b042">{YouTube-Search-Term}</span></b></pre>
						<h4 class="panel-title panel-heading"><?php echo $translations['api_where']; ?><code><b>{YouTube-Search-Term}</b></code><?php echo $translations['api_validSearchTerm']; ?></h4>
						<h4 class="panel-title panel-heading"><?php echo $translations['api_json_eg']; ?></h4>
						<div>
							<!-- Nav tabs -->
							<div class="panel-heading">
								<ul class="nav nav-tabs" role="tablist">
									<li role="presentation" class="active"><a href="#search-json-api" aria-controls="home" role="tab" data-toggle="tab">YouTube</a></li>
								</ul>
							</div>
							<!-- Tab panes -->
							<div class="tab-content">
								<div role="tabpanel" class="tab-pane fade in active" id="search-json-api">
									<pre class="prettyprint lang-html">GET <?php echo Core::httpProtocol() . ltrim(WEBROOT, '/'); ?>@api/search/YouTube/Katy+Perry+Roar HTTP/1.1</pre>
									<h4 class="panel-title panel-heading"><?php echo $translations['api_generates']; ?></h4>
									<pre class="prettyprint lang-php">{
	"items": [
		{
			"id": "CevxZvSJLk8",
			"title": "Katy Perry - Roar (Official)",
			"thumbDefault": "https://i.ytimg.com/vi/CevxZvSJLk8/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLDY5ZX7PoN7KNzXXEdQ_Yvch8qqQQ",
			"channelTitle": "Katy Perry",
			"channelId": "UCYvmuw-JtVrTZQ-7Y4kd63Q",
			"publishedAt": "6 years ago",
			"duration": "4:30",
			"viewCount": "3068657449"
		},
		{
			"id": "UW5SMrURVbM",
			"title": "Roar - Katy Perry (Lyrics) ??",
			"thumbDefault": "https://i.ytimg.com/vi/UW5SMrURVbM/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLBFmmujndykkc75SeCIl05VPHaglw",
			"channelTitle": "DopeLyrics",
			"channelId": "UCvR2R7j218tzejtTsb_X6Rw",
			"publishedAt": "1 year ago",
			"duration": "3:51",
			"viewCount": "14073787"
		},
		{
			"id": "FqkfBzRb43o",
			"title": "Katy Perry - Roar : Part 2 (Official Cover by 10 year-old Mariangeli from HitStreak)",
			"thumbDefault": "https://i.ytimg.com/vi/FqkfBzRb43o/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLBnXgcoefqzxawvmhB-QbCvMUBENg",
			"channelTitle": "ShowMobile",
			"channelId": "UCYBFx-THKRWqAxq66qZk_ww",
			"publishedAt": "6 years ago",
			"duration": "3:47",
			"viewCount": "307490768"
		},
		{
			"id": "Rh47oTsRf-w",
			"title": "Katy Perry - Roar (From “The Prismatic World Tour Live”)",
			"thumbDefault": "https://i.ytimg.com/vi/Rh47oTsRf-w/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLAsKxSyujUT_ZCQqsAdpVQAGnxP3Q",
			"channelTitle": "Katy Perry",
			"channelId": "UCYvmuw-JtVrTZQ-7Y4kd63Q",
			"publishedAt": "4 years ago",
			"duration": "4:33",
			"viewCount": "21381621"
		},
		{
			"id": "KRlhDkwJkHU",
			"title": "Katy Perry - Roar (OFFICIAL 1 Hour)",
			"thumbDefault": "https://i.ytimg.com/vi/KRlhDkwJkHU/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLCOvC3IMGpgbitf9nn58BxMFpqJnw",
			"channelTitle": "SONGS 1 HOUR",
			"channelId": "UCEo7MW5ArVEzQCQRpTuNGDg",
			"publishedAt": "4 years ago",
			"duration": "1:01:11",
			"viewCount": "27253"
		},
		{
			"id": "empcJOD-bA0",
			"title": "Roar by Katy Perry Lyrics",
			"thumbDefault": "https://i.ytimg.com/vi/empcJOD-bA0/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLBcizW9oc4KLGEo5o3Z2kOmKbKOkQ",
			"channelTitle": "mulero ibrahim",
			"channelId": "UCEl6sNUx9xHQ8zyypGJnlWQ",
			"publishedAt": "4 years ago",
			"duration": "3:45",
			"viewCount": "423025"
		},
		{
			"id": "K-w2sYAqMKw",
			"title": "Katy Perry - Roar (One Love Manchester)",
			"thumbDefault": "https://i.ytimg.com/vi/K-w2sYAqMKw/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLDjF35LoaRotGJ8WvhB4Nc9oZPvqQ",
			"channelTitle": "BBC Music",
			"channelId": "UCZtDUmC3W7j25XHZWFT_XgQ",
			"publishedAt": "2 years ago",
			"duration": "3:50",
			"viewCount": "12996443"
		},
		{
			"id": "sLZvdAdlQq4",
			"title": "Play Doh ROAR - Katy Perry Barbie Doll Inspired Costume",
			"thumbDefault": "https://i.ytimg.com/vi/sLZvdAdlQq4/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLA-i8iXAj-galqb2-0P1w82JdDXcQ",
			"channelTitle": "Fondanista",
			"channelId": "UCVNrpO7KiVMdwOdFZwLkWRA",
			"publishedAt": "4 years ago",
			"duration": "2:58",
			"viewCount": "14677198"
		},
		{
			"id": "0KSOMA3QBU0",
			"title": "Katy Perry - Dark Horse (Official) ft. Juicy J",
			"thumbDefault": "https://i.ytimg.com/vi/0KSOMA3QBU0/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLDa1ue7JhfY14YUSCsVhgf7o2kWyw",
			"channelTitle": "Katy Perry",
			"channelId": "UCYvmuw-JtVrTZQ-7Y4kd63Q",
			"publishedAt": "6 years ago",
			"duration": "3:45",
			"viewCount": "2773465353"
		},
		{
			"id": "Z0Q8j3HJagE",
			"title": "Katy Perry - Making of the \"Roar\" Music Video",
			"thumbDefault": "https://i.ytimg.com/vi/Z0Q8j3HJagE/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLAj6fVgZFc8eblIa5hC6CwvxasbNQ",
			"channelTitle": "Katy Perry",
			"channelId": "UCYvmuw-JtVrTZQ-7Y4kd63Q",
			"publishedAt": "6 years ago",
			"duration": "22:31",
			"viewCount": "20739183"
		},
		{
			"id": "EyLCXC3rV2M",
			"title": "**LYRICS** Katy Perry - Roar",
			"thumbDefault": "https://i.ytimg.com/vi/EyLCXC3rV2M/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLBGnc2_UQBdmVbEvfzHuZf6GVldlQ",
			"channelTitle": "ilpescepalla",
			"channelId": "UCTnd7uvo-uWwlB9qvh0aucA",
			"publishedAt": "6 years ago",
			"duration": "4:04",
			"viewCount": "2940062"
		},
		{
			"id": "pQQ24JwtRUI",
			"title": "Olivia Sings Roar | The Voice Kids Australia 2014",
			"thumbDefault": "https://i.ytimg.com/vi/pQQ24JwtRUI/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLCKE6eUp7Iye7_Wx8_0YOOPwpWamw",
			"channelTitle": "The Voice Kids Australia",
			"channelId": "UCUDR5r3ow3goI1iZgGlnoyw",
			"publishedAt": "5 years ago",
			"duration": "4:28",
			"viewCount": "95223791"
		},
		{
			"id": "aqhCJW890So",
			"title": "Mariangeli from HitStreak - ROAR : Part 1",
			"thumbDefault": "https://i.ytimg.com/vi/aqhCJW890So/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLCj4EwhFnnFVcjJtF_3hUBYQ83WZw",
			"channelTitle": "ShowMobile",
			"channelId": "UCYBFx-THKRWqAxq66qZk_ww",
			"publishedAt": "6 years ago",
			"duration": "0:33",
			"viewCount": "32701801"
		},
		{
			"id": "KlyXNRrsk4A",
			"title": "Katy Perry - Last Friday Night (T.G.I.F.)",
			"thumbDefault": "https://i.ytimg.com/vi/KlyXNRrsk4A/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLAR2QvImCYQhf_dP5WFJiWVKU00fw",
			"channelTitle": "Katy Perry",
			"channelId": "UCYvmuw-JtVrTZQ-7Y4kd63Q",
			"publishedAt": "8 years ago",
			"duration": "8:11",
			"viewCount": "1239885938"
		},
		{
			"id": "UUT2GTujJI0",
			"title": "Katy Perry - Roar Lyrics",
			"thumbDefault": "https://i.ytimg.com/vi/UUT2GTujJI0/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLDUKvQC8DQIg7dD39mEjDetWdHMsA",
			"channelTitle": "K- Hype",
			"channelId": "UCcoS9-5e2PPrO5gIqC9Fy4Q",
			"publishedAt": "6 years ago",
			"duration": "3:45",
			"viewCount": "90138"
		},
		{
			"id": "QGJuMBdaqIw",
			"title": "Katy Perry - Firework (Official)",
			"thumbDefault": "https://i.ytimg.com/vi/QGJuMBdaqIw/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLCVFJ6nMr1OyDp5P9wNafG76miRoA",
			"channelTitle": "Katy Perry",
			"channelId": "UCYvmuw-JtVrTZQ-7Y4kd63Q",
			"publishedAt": "9 years ago",
			"duration": "3:54",
			"viewCount": "1233775759"
		},
		{
			"id": "ZKmuyy6xEBU",
			"title": "Katy Perry - Firework, Roar & Never Really Over LIVE - SHEIN Together",
			"thumbDefault": "https://i.ytimg.com/vi/ZKmuyy6xEBU/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLAqsqPbUkw6GHnIB_my6JIuS9ondA",
			"channelTitle": "KPEZ",
			"channelId": "UCv1DBOB5sc6vrjYEkwNsHcw",
			"publishedAt": "1 day ago",
			"duration": "11:52",
			"viewCount": "2688"
		},
		{
			"id": "qvBzVWdwZFg",
			"title": "Katy Perry - Roar (Live @ BBC Radio 1's Big Weekend)",
			"thumbDefault": "https://i.ytimg.com/vi/qvBzVWdwZFg/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLDujpTCwyTAWK3t-MozoxcjvBhtjw",
			"channelTitle": "HeyHey Hey",
			"channelId": "UCMYxD0p4_-GxQ1qYYDsK-FQ",
			"publishedAt": "3 years ago",
			"duration": "4:38",
			"viewCount": "98831"
		},
		{
			"id": "Sl8Wl4EFBGQ",
			"title": "KATY PERRY WITH ZERO BUDGET! (Roar PARODY)",
			"thumbDefault": "https://i.ytimg.com/vi/Sl8Wl4EFBGQ/hqdefault.jpg?sqp=-oaymwEYCKgBEF5IVfKriqkDCwgBFQAAiEIYAXAB&rs=AOn4CLCybyCTPmI82feN8Rzcmu_0ERrVpA",
			"channelTitle": "LankyBox",
			"channelId": "UCSf0s2ogUVYpJPuzW1zpAOg",
			"publishedAt": "6 months ago",
			"duration": "1:24",
			"viewCount": "2759481"
		}
	]
}</pre>
								</div>
							</div>
						</div>

						<br />
					<?php } ?>					
					
					<?php echo $this->importCSS(array('assets/css/prettify.css')); ?>
					<?php echo $this->importJS(array('assets/js/prettify.js' => array())); ?>
					<script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.min.js"></script>
					<script>
						prettyPrint();
						$('.button-api-frame:first').iFrameResize();
						$('#button-api-tabs a[data-toggle="tab"]').on('shown.bs.tab', function(e){
							$($(this).attr('href')).find('.button-api-frame').iFrameResize();
						});						
					</script>					
				</div><!-- /.developers -->