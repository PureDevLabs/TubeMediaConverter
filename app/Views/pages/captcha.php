<?php 
	use MediaConverterPro\lib\Config;
	
	header('Content-Type: application/json; charset=UTF-8');
	echo (!empty($captchaResponse)) ? $captchaResponse : '';
?>