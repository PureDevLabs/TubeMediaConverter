<?php 
	use MediaConverterPro\lib\Config;
?>
<li><a href="<?php echo Config::_APPROOT . $urlLang; ?>@<?php echo (Config::_WEBSITE_INTERFACE == 'hybrid' && (empty(Config::$_apiAllowedDomains['search']) || empty(Config::$_apiAllowedDomains['json']) || empty(Config::$_apiAllowedDomains['button']))) ? 'developers' : 'index'; ?>"><span><?php echo $translations['navbar_home']; ?></span></a></li>
<li><a href="<?php echo Config::_APPROOT . $urlLang; ?>@faq"><span><?php echo $translations['navbar_faq']; ?></span></a></li>
<?php if (Config::_WEBSITE_INTERFACE != 'hybrid' && (empty(Config::$_apiAllowedDomains['search']) || empty(Config::$_apiAllowedDomains['json']) || empty(Config::$_apiAllowedDomains['button']))) { ?>
	<li><a href="<?php echo Config::_APPROOT . $urlLang; ?>@developers"><span><?php echo $translations['navbar_api']; ?></span></a></li>
<?php } ?>
<li><a href="<?php echo Config::_APPROOT . $urlLang; ?>@contact"><span><?php echo $translations['navbar_contact']; ?></span></a></li>