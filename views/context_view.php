<?php
	function hn_ts_showContextTable(){
		?>
		<h3>Metadata Table</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>id</th>
					<th>Context Type</th>
					<th>Value</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>id</th>
					<th>Context Type</th>
					<th>Value</th>
				</tr>
			</tfoot>
			<tbody>
				<tr>
					<td>1</td>
					<td>Place</td>
					<td>Sherwood Forest</td>
				</tr>
				<tr>
					<td>2</td>
					<td>Longitude</td>
					<td>12345</td>
				</tr>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num">Displaying 2 of 2</span>
				<span class="page-numbers current">1</span>
				<?php //<a href="#" class="page-numbers">2</a> ?>
				<?php //<a href="#" class="next page-numbers">&raquo;</a> ?>
			</div>
		</div>
		<hr />
		<?php
	}
?>