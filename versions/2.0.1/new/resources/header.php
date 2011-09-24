<html>
<head>
	<title><?php echo $dm->siteTitle(); if(isset($__pageTitle)) { echo " - $__pageTitle"; } ?></title>
<?php if($dm->css()) : ?>
	<?php foreach($dm->css() as $css) : ?>
	<link rel="stylesheet" href="<?php echo $css; ?>">
	<?php endforeach; ?>
<?php endif; ?>
<?php if($dm->js()) : ?>
	<?php foreach($dm->js() as $js) : ?>
	<script type="text/javascript" src="<?php echo $js; ?>"></script>
	<?php endforeach; ?>
<?php endif; ?>
</head>
<body>
