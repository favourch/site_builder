<h3>Site Builder Test Page</h3>
<table class="rows" cellspacing="0">
	<tr class="header">
		<td>Test</td>
		<td>Results</td>
	</tr>
<?php $row = 0; foreach($tests as $t) : ?>
	<tr class="row<?php echo $row++ % 2 + 1; ?>" valign="top">
		<td><?php echo $t['test']; ?></td>
		<td><?php echo $t['results']; ?></td>
	</tr>
<?php endforeach; ?>
</table>
