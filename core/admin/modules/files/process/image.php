<?php
	$permission = $admin->getResourceFolderPermission($bigtree["commands"][0]);

	if ($permission != "p") {
		$admin->stop("You do not have permission to create content in this folder.");
	}

	// Scale up images that don't meet our minimum
	$settings = $cms->getSetting("bigtree-internal-media-settings");
	$preset = $settings["presets"]["default"];
	$dir = opendir(SITE_ROOT."files/temporary/".$admin->ID."/");

	while ($file = readdir($dir)) {
		if ($file == "." || $file == "..") {
			continue;
		}

		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		if ($extension == "jpg" || $extension == "jpeg" || $extension == "png" || $extension == "gif") {
			$file_name = SITE_ROOT."files/temporary/".$admin->ID."/".$file;
			$min_height = intval($preset["min_height"]);
			$min_width = intval($preset["min_width"]);

			list($width, $height, $type, $attr) = getimagesize($file_name);

			if ($width < $min_width || $height < $min_height) {
				BigTree::createUpscaledImage($file_name, $file_name, $min_width, $min_height);
			}

			$field = [
				"title" => $file,
				"file_input" => [
					"tmp_name" => $file_name,
					"name" => $file,
					"error" => 0
				],
				"settings" => [
					"directory" => "files/resources/",
					"preset" => "default"
				]
			];

			$admin->processImageUpload($field);
		}
	}

	$_SESSION["bigtree_admin"]["form_data"]["crop_key"] = $cms->cacheUnique("org.bigtreecms.crops", $bigtree["crops"]);

	if (count($bigtree["errors"])) {
?>
<div class="container">
	<section>
		<div class="alert">
			<span></span>
			<p>Some images uploaded encountered <?=count($errors)?> error<?php if (count($errors) != 1) { ?>s<?php } ?>.</p>
		</div>
		<div class="table error_table">
			<header>
				<span class="view_column field">File</span>
				<span class="view_column error">Error</span>
			</header>
			<ul>
				<?php foreach ($errors as $error) { ?>
				<li>
					<section class="view_column field"><?=$error["field"]?></section>
					<section class="view_column error"><?=$error["error"]?></section>
				</li>
				<?php } ?>
			</ul>
		</div>
	</section>
	<footer>
		<a href="<?=ADMIN_ROOT?>files/crop/<?=intval($bigtree["commands"][0])?>/" class="button blue">Continue</a>
	</footer>
</div>
<?php
	} else {
		BigTree::redirect(ADMIN_ROOT."files/crop/".intval($bigtree["commands"][0])."/");
	}
	