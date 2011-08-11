<fieldset>
	<legend>Your Shared Files</legend>
	<table id="itemlist">
		<thead>
			<tr>
				<th>Item</th>
				<th>Shared With</th>
				<th>Permissions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($_['shared_items'] as $item):?>
				<tr class="item">
					<td class="source"><?php echo substr($item['source'], strlen("/".$_SESSION['user_id']."/files/"));?></td>
					<td class="uid_shared_with"><?php echo $item['uid_shared_with'];?></td>
					<td class="permissions"><?php echo "Read"; echo($item['permissions'] & OC_SHARE::WRITE ? ", Edit" : ""); echo($item['permissions'] & OC_SHARE::DELETE ? ", Delete" : "");?></td>
					<td><button class="delete" data-source="<?php echo $item['source'];?>" data-uid_shared_with="<?php echo $item['uid_shared_with'];?>">Delete</button></td>
				</tr>
			<?php endforeach;?>
			<tr id="share_item_row">
				<form action="#" id="share_item">
					<td class="source"><input placeholder="Item" id="source" /></td>
					<td class="uid_shared_with"><input placeholder="Share With" id="uid_shared_with" /></td>
					<td class="permissions"><input placeholder="Permissions" id="permissions" /></td>
					<td><input type="submit" value="Share" /></td>
				</form>
			</tr>
		</tbody>
	</table>
</fieldset>
