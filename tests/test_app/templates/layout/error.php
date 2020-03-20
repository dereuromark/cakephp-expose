<?php
$description = 'Oops, something went wrong!';
?>
<!DOCTYPE html>
<html>
<head>
	<?= $this->Html->charset() ?>
	<title>
		<?= $description ?>:
		<?= $this->fetch('title') ?>
	</title>
	<?= $this->Html->meta('icon') ?>

	<?= $this->fetch('meta') ?>
	<?= $this->fetch('css') ?>
	<?= $this->fetch('script') ?>
</head>
<body>
	<div id="container">
		<div id="header">
			<h1><?php echo $description ?></h1>
		</div>
		<div id="content">
			<?= $this->Flash->render() ?>

			<?= $this->fetch('content') ?>
		</div>
	</div>
</body>
</html>
