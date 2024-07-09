<?php 
	use MediaConverterPro\lib\Config;
?>		
		
		<div class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading">
				  <h4 class="panel-title">
					<a data-toggle="collapse" href="#collapse1"><?php echo $translations['top']; ?> <?php echo (Config::_TOP_VIDEOS_CATEGORY == 10) ? $translations['music'] . ' ' : ''; ?><?php echo $translations['videos']; ?> - <?php echo $countries[$Continent][$cCode] ?></a>
				  </h4>
				</div>
				<div id="collapse1" class="panel-collapse collapse">
					<div class="panel-body">
					<?php 
						foreach ($countries as $continent => $contCountries)
						{
							echo "<h4>" . $continent . "</h4>";
							echo '<ul class="list-group row">';
							foreach ($contCountries as $isoCode => $country)
							{
								echo '<li class="list-group-item col-lg-3" data-country-code="' . $isoCode . '" data-continent="' . $continent . '">';
								echo (isset($params['flagCode'])) ? '<span class="' . sprintf(urldecode($params['flagCode']), $isoCode) . '"></span> ' : '';
								echo $country .'</li>';							
							}
							echo '</ul>';
						}
					?>	
					</div>
				</div>
			</div>
		</div>