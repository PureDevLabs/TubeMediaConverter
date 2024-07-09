<?php 
	use MediaConverterPro\lib\Config;
?>
<div id="shareModal<?php echo $uniqueID; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $translations['share_modal_close']; ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="shareModalLabel"><?php echo sprintf($translations['share_modal_header'], $video['title']); ?></h4>
			</div>
			<div class="modal-body" style="text-align:center">
				<div class="<?php echo Config::_ADDTHIS_CSS_CLASS; ?>" data-url="<?php echo WEBROOT . $urlLang . preg_replace("/[^\p{L}\p{N}]+/u", "-", $video['title']) . '(' . $video['id'] . ')'; ?>" data-title="<?php echo $translations['share_modal_text']; ?> <?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>" data-description="<?php echo $translations['share_modal_text']; ?> <?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>" data-image="https://i.ytimg.com/vi/<?php echo $video['id']; ?>/default.jpg"></div>		
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $translations['share_modal_close']; ?></button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->