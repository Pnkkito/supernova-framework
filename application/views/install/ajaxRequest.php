<?php if ($this->data['action'] == 'getRelatedModels'){	?>
	<div class='ajaxBox'>
		<?php echo $this->html->select('relatedModels','Select your related models to include',array('id' => 'multiselect','type' => 'multiple','options' => $relations)); ?>
	</div>
<?php } ?>
<script>
	$('select').chosen();
</script>