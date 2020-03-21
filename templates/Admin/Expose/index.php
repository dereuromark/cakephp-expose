<?php
/**
 * @var \App\View\AppView $this
 */

?>

<h1>Expose Plugin Backend</h1>

<div class="row">
	<div class="col-md-6 col-xs-12">
		<h2>Reverse UUIDs</h2>
		<p>This can be useful when having the UUID in a format that you cannot look it up in the DB.</p>
		<p>A UUID can be visible in different ways, e.g.</p>
		<ul>
			<li>36-char UUID (default) - e.g. `f7ac0123-a938-4e80-840e-efe892051332`</li>
			<li>34-char `0x...` prefixed in DB tool - e.g. `0xf7ac0123a9384e80840eefe892051332`)</li>
			<li>24-char binary (often through .bin file)</li>
			<li>22-char (or 21) shortened string (through any ConverterInterface compatible one) - e.g. `JG2n2fdRHcdMSiyDq5em5n`</li>
		</ul>

		<?php
		if (isset($result)) {
			echo '<h3>Result</h3>';
			echo '<p><code>' . h($result) . '</code></p>';
		}
		?>

		<?php echo $this->Form->create();?>
		<fieldset>
			<legend><?php echo __('Enter the UUID you want to reverse');?></legend>
			<?php
			echo $this->Form->control('uuid', []);
			//echo $this->Form->control('shortened', ['type' => 'checkbox', 'label' => 'This UUID is shortened']);
			?>
		</fieldset>
		<?php echo $this->Form->submit(__('Submit')); echo $this->Form->end();?>

	</div>

</div>
