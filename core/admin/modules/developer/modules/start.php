<?php
	namespace BigTree;
?>
<div class="container">
	<summary>
		<h2><?=Text::translate("Add Module")?></h2>
	</summary>
	<section>
		<p><?=Text::translate("Would you like to create a module from an existing database table or would you like to use the Module Designer to have BigTree build it for you?")?></p>
	</section>
	<footer>
		<a class="button blue" href="<?=DEVELOPER_ROOT?>modules/add/"><?=Text::translate("Use Existing Table")?></a>
		<a class="button" href="<?=DEVELOPER_ROOT?>modules/designer/"><?=Text::translate("Use Module Designer")?></a>
	</footer>
</div>