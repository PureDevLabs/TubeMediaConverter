<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<title>Update Check</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
	<style type="text/css">
		html, body {margin:0;text-align:center;font-size:16px;}
		div {height:53px;}
		.alert {margin-bottom:0;}
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>  
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script> 	
</head>
<body>
<?php 
	include "version.php";
	$versionFile = file_get_contents('http://puredevlabs.cc/TubeMediaConverter/version.txt');
	$remoteVersion = ($versionFile !== false && !empty($versionFile)) ? trim($versionFile) : SOFTWARE_VERSION;
	echo ((float)SOFTWARE_VERSION < (float)$remoteVersion) ? '<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><i class="fa fa-exclamation-circle" aria-hidden="true"></i> <b>An update is available!!</b> Please <a href="https://github.com/login" target="_blank">log into your GitHub account</a> to download the latest version.</div>' : '<div class="alert alert-success" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><i class="fa fa-check-circle" aria-hidden="true"></i> <b>Great news!</b> You have the latest version!</div>';
?>
</body>
</html>