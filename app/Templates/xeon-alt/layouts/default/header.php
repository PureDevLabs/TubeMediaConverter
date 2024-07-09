<?php 
	use MediaConverterPro\lib\Config;
	
	$ptitle = (isset($pagetitle)) ? $pagetitle : $action;
	$ptitle = ((isset($uppercase)) ? strtoupper($ptitle) : ucfirst($ptitle));
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php 
		echo $this->MetaData->PrintMetaTags(array(
			'rawQuery' => ((isset($params['q'])) ? $params['q'] : ''),
			'searchQuery' => ((!empty($SearchTerm)) ? $SearchTerm : ''),
			'action' => $action,
			'ptitle' => $ptitle,
			'urlLang' => $urlLang,
			'translations' => $translations,
			'protocol' => $protocol,
			'converter' => $converter
		)); 
		echo $this->MetaData->PrintCanonicalUrl(array(
			'protocol' => $protocol
		));		
		echo $this->MetaData->PrintHreflangTags(array(
			'langs' => $langs, 
			'protocol' => $protocol
		)); 
	?>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
	<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css" /></noscript>
	<link rel="preload" href="https://fonts.googleapis.com/icon?family=Material+Icons" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"></noscript>
	<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300,600,400italic,700'>
	<link rel="preload" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<noscript><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css"></noscript>
	<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/2.5.0/css/flag-icon.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/2.5.0/css/flag-icon.min.css"></noscript>
	<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css"></noscript>
	<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css"></noscript>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/black/pace-theme-minimal.min.css">
	<style>.pace .pace-progress {background: rgb(245, 66, 66);}</style>
	<?php echo $this->importCSS(array('assets/css/main.css')); ?>
	
	<!-- Favicon and Mobile Icons -->
	<?php 
		$appleIconSizes = array('57', '60', '72', '76', '114', '120', '144', '152', '180');
		foreach ($appleIconSizes as $appSize)
		{
			echo "\t". '<link rel="apple-touch-icon" sizes="' . $appSize . 'x' . $appSize . '" href="' . WEBROOT . 'app/Templates/' . TEMPLATE_NAME . '/assets/img/icons/apple-icon-' . $appSize . 'x' . $appSize . '.png">' . "\n";
		}
		$iconSizes = array('192', '32', '96', '16');
		foreach ($iconSizes as $iconSize)
		{	
			$iconfile = ($iconSize == '192') ? 'android-icon' : 'favicon';
			echo "\t". '<link rel="icon" type="image/png" sizes="' . $iconSize . 'x' . $iconSize . '" href="' . WEBROOT . 'app/Templates/' . TEMPLATE_NAME . '/assets/img/icons/' . $iconfile . '-' . $iconSize . 'x' . $iconSize . '.png">' . "\n";
		}
	?>
	<link rel="manifest" href="<?php echo WEBROOT; ?>app/Templates/<?php echo TEMPLATE_NAME; ?>/assets/img/icons/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="<?php echo WEBROOT; ?>app/Templates/<?php echo TEMPLATE_NAME; ?>/assets/img/icons/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">

	<script>
		var templateVars = {
			// Template config vars for "result" ajax request
			'thumbImgSize' : 'Medium',
			'videoData' : 'description',
			'flagCode' : 'flag-icon flag-icon-%s',
			'ajaxLoadImg' : 'ajax-loader.gif',
			'appSecretToken' : '<?php echo hash('sha256', $session['appSecretToken'] . APP_SECRET_KEY); ?>'
			<?php 
				echo (!empty($SearchTerm)) ? ", 'q' : '" . $SearchTerm . "'" : "";
			?>
		};
		var templateVarsQueryStr = '';
		for (var prop in templateVars)
		{
			templateVarsQueryStr += prop + '=' + encodeURIComponent(templateVars[prop]) + '&';
		}
		var ajaxUrlBase = '<?php echo WEBROOT . $urlLang; ?>';
		var suggestCallBack; // global var for autocomplete jsonp
		var useCaptcha = <?php echo (Config::_ENABLE_RECAPTCHA) ? 'true' : 'false'; ?>;
		var useSearchLinks = <?php echo (Config::_ENABLE_SEARCH_LINKS) ? 'true' : 'false'; ?>;
	</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js" defer></script> 
	<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/helpers/jquery.fancybox-media.js" defer></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script data-pace-options='{ "ajax": { "ignoreURLs": ["@grab"] }, "startOnPageLoad": <?php echo ($action != "index") ? "false" : "true"; ?> }' src="https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js"></script>
	<?php if (Config::_ENABLE_RECAPTCHA) { ?>
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<style>
			.grecaptcha-badge {z-index:1028;visibility:hidden !important;}
			.grecaptcha-badge:hover {right:0 !important;}
		</style>
	<?php } ?>

	<?php 
		$musicPlayer = (Config::_MUSIC_PLAYER) ? array('assets/js/music-player.js' => array('defer')) : array();
		echo $this->importJS(array_merge(array('assets/js/app.js' => array()), $musicPlayer));
	?>
</head>
<body>
	<?php if (Config::ENABLE_FACEBOOK_LIKE_BOX) { ?>
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.6&appId=329654673909678";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
	<?php } ?>

	<div class="mb_home" style="background-image: url('<?php echo WEBROOT; ?>app/Templates/<?php echo TEMPLATE_NAME; ?>/assets/img/header.jpg');" data-stellar-background-ratio="0.5">
		<div class="mb_header">
			<div class="container">
				<nav class="navbar navbar-default">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="<?php echo Config::_APPROOT . $urlLang; ?>"><?php echo Config::_WEBSITE_NAME; ?></a>
					</div>
					<div id="navbar" class="navbar-collapse collapse">
						<ul class="nav navbar-nav navbar-right">
							<?php echo $this->element('navbar_links'); ?>
						</ul>
					</div>
				</nav>
			</div>
		</div><!-- /.mb_header -->		
		<div class="gradient"></div>
		<div class="container">
			<div class="text-wrap">
				<div class="text-inner">
					<div class="row">
						<div class="col-md-8 col-md-offset-2">
							<?php 
								echo ($action == "index") ? $this->element('search_box', array(
									'sFormClass' => 'animated-form hidden',
									'sFormPrependTo' => '<h1 class="animated-text hidden">' . $translations['search_something'] . '</h1>'
								)) : '<h1 class="text-center">' . $translations['navbar_' . strtolower($ptitle)] . '</h1>'; 
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="slant"></div>	
	</div><!-- /.mb_home -->
	<div class="mb_content-area">
		<div class="container">
			<div class="mb_content">
				<?php echo (isset($appError) && !empty($appError)) ? '<p class="text-center">' . $appError . '</p>' : ''; ?>
				<div class="row">
					<div class="col-lg-9">			