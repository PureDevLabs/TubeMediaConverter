<?php
	use MediaConverterPro\lib\Config;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
	<?php echo $this->importCSS(array('assets/css/main.css')); ?>
	<style type="text/css">
		body {background-color:transparent !important;}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>  
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.contentWindow.min.js"></script>
</head>
<body>	
	<div class="download-loading" style="text-align:center"><img src="<?php echo WEBROOT; ?>app/Templates/<?php echo TEMPLATE_NAME; ?>/assets/img/ajax-loader.gif" alt="<?php echo htmlspecialchars($translations['preparing_msg'], ENT_QUOTES); ?>" /><br /><?php echo $translations['preparing_msg']; ?></div><!-- /.download-loading -->