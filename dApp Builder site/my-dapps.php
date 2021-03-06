<?php

require_once('classes/Helper.php');
session_start();

$currentUser = Helper::getCurrentUser();

if (!$currentUser) {
	header('Location: /builder/');
	exit;
}

//Profile data
$id = $currentUser->getId();
$username = $currentUser->getUsername();
$application = $currentUser->getApplication();
$api_id = $currentUser->getApiId();
$api_key = $currentUser->getApiKey();
$deployed_dapps = $currentUser->getDeployedDapps();
$undeployed_dapps = $currentUser->getUndeployedDapps();
$added_dapps = $currentUser->getAddedDapps();

if (!$deployed_dapps) {
	header('Location: /builder/new-dapp.php');
	exit;
}

require_once('common/header.php');
?>
<div id="page-wrapper">
	<div id="hackathon-container" class="container-fluid page-content">
		<h1 class="text-center">My dApps</h1>
		<div class="row">
			<div class="col-md-4 col-md-offset-1">
				<div class="list-group" id="dapps-list">
					<?php $first = true; foreach ($deployed_dapps as $dapp) { ?>
						<a data-id="<?php echo $dapp->getId(); ?>" class="list-group-item <?php echo ($first) ? 'active' : 'inactive'; ?>">
							<?php echo $dapp->getName(); ?>
						</a>
					<?php $first = false; } ?>
				</div>
				<p><button id="add-widget" type="button" class="btn btn-success">Add selected dApp as widget into mobile App</button></p>
				<p><a id="customize-template" style="cursor:pointer;">Customize the dApp's template</a></p>
			</div>
			<div class="col-md-4 col-md-offset-2">
				<iframe id="dapp-iframe" style="width:100%;height:500px;"></iframe>
			</div>
		</div>
	</div>
</div>

<?php foreach ($deployed_dapps as $dapp) { $interface = $dapp->getInterface(); if ($dapp->getDappType() == 'voting') { ?>
	<div id="templateModal<?php echo $dapp->getId(); ?>" class="modal fade" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header text-center">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h2>Template Settings</h2>
				</div>
				<div class="modal-body">
					<form method="post" class="template-form">
						<p class="text-center">Background color:</p>
						<div class="input-group colorpicker-component colorpicker-input">
							<input name="background_color" type="text" class="form-control input-lg" value="<?php echo $interface['background_color']; ?>" required>
							<span class="input-group-addon"><i></i></span>
						</div>
						<p class="text-center">Text color:</p>
						<div class="input-group colorpicker-component colorpicker-input">
							<input name="text_color" type="text" class="form-control input-lg" value="<?php echo $interface['text_color']; ?>" required>
							<span class="input-group-addon"><i></i></span>
						</div>
						<p class="text-center">Links color:</p>
						<div class="input-group colorpicker-component colorpicker-input">
							<input name="links_color" type="text" class="form-control input-lg" value="<?php echo $interface['links_color']; ?>" required>
							<span class="input-group-addon"><i></i></span>
						</div>
						<p class="text-center">Ethereum addresses color:</p>
						<div class="input-group colorpicker-component colorpicker-input">
							<input name="eth_addresses_color" type="text" class="form-control input-lg" value="<?php echo $interface['eth_addresses_color']; ?>" required>
							<span class="input-group-addon"><i></i></span>
						</div>
						<p class="text-center">Vote buttons color:</p>
						<div class="input-group colorpicker-component colorpicker-input">
							<input name="vote_buttons_color" type="text" class="form-control input-lg" value="<?php echo $interface['vote_buttons_color']; ?>" required>
							<span class="input-group-addon"><i></i></span>
						</div>
						<p class="text-center">Finish button color:</p>
						<div class="input-group colorpicker-component colorpicker-input">
							<input name="finish_button_color" type="text" class="form-control input-lg" value="<?php echo $interface['finish_button_color']; ?>" required>
							<span class="input-group-addon"><i></i></span>
						</div>
						<div class="text-center" style="padding-top:15px;">
							<button type="submit" class="btn btn-success">Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php } } ?>

<script type="text/javascript" src="assets/js/bootstrap-colorpicker.min.js"></script>

<script type="text/javascript">
	var current_dapp;
	$(document).ready(function(){
		showInterface();
		$('.colorpicker-input').colorpicker();
	});
	$("#dapps-list a").click(function(){
		if (!$(this).hasClass("active")) {
			$("#dapps-list a").removeClass("active");
			$("#dapps-list a").addClass("inactive");
			$(this).removeClass("inactive");
			$(this).addClass("active");
			showInterface();
		}
	});
	function showInterface(time = false) {
		var id = $("#dapps-list a.active").attr("data-id");
		current_dapp = id;
		var link = "https://dapps.ibuildapp.com/builder/dapp.php?id=" + id;
		if (time) link += "&time=" + time;
		$("#dapp-iframe").attr("src", link);
	}
	$("#add-widget").click(function(){
		if (!current_dapp) return false;
		$.ajax({
			url: '/builder/add_widget.php',
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: 'id=' + current_dapp,
			error: function(jqXHR, error){},
			success: function(data, status){
				if (!data.error && data.success) {
					$.cookie('adding_dapp', current_dapp);
					location.href = '/builder/mobile-app.php';
				} else {
				}
			}
		});
	});
	$("#customize-template").click(function(){
		$("#templateModal" + current_dapp).modal("show");
	});
	$(".template-form").submit(function(e){
		e.preventDefault();
		var data = $(this).serialize();
		$.ajax({
			url: '/builder/change_template.php?id=' + current_dapp,
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: data,
			error: function(jqXHR, error){},
			success: function(data, status){
				if (!data.error && data.success) {
					showInterface(data.success);
				}
			}
		});
		return false;
	});
</script>
<?php require_once('common/footer.php'); ?>