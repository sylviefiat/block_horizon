<?php
	defined('C5_EXECUTE') or die(_("Access Denied."));	 
?>
<div class="block-horizon">
	<div>Reference <?php echo $type?>: <a href="<?php echo $name?>"><?php echo $name?></a></div>
	<?php
		echo $controller->getRemoteFile($name,$type);
	?>
</div>
