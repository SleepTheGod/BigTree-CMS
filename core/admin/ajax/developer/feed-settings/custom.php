<?php
	namespace BigTree;

	/**
	 * @global array $settings
	 * @global string $table
	 */

	if (!$table) {
?>
<p><?=Text::translate("Please select a table first.")?></p>
<?php
	} else {
?>
<fieldset>
	<label for="settings_field_sort"><?=Text::translate("Order By")?></label>
	<select id="settings_field_sort" name="sort">
		<?php SQL::drawColumnSelectOptions($table, $settings["sort"], true); ?>
	</select>
</fieldset>
<fieldset>
	<label for="settings_field_limit"><?=Text::translate("Limit <small>(defaults to 15)</small>")?></label>
	<input id="settings_field_limit" type="text" name="limit" value="<?=$settings["limit"]?>" />
</fieldset>
<?php
	} 
?>