<?php 
	use MediaConverterPro\lib\Config; 
?>
<div class="dropdown">
	<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
		<span class="flag-icon flag-icon-<?php echo $langs[$lang]['country_code']; ?>"></span> <?php echo $langs[$lang]['language']; ?> 
		<span class="caret"></span>
	</button>
	<ul class="dropdown-menu">
		<?php
			foreach ($langs as $lng => $lngInfo)
			{
				echo '<li><a href="' . Config::_APPROOT . $lng . '/"><span class="flag-icon flag-icon-' . $lngInfo['country_code'] . '"></span> ' . $lngInfo['language'] . '</a></li>';
			}
		?>
	</ul>
</div>