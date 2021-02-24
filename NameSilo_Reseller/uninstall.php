<?php
$addon_db = 'BF_NameSilo';
if($addon_db != 'no_db')
{
	$queryucu = "DROP TABLE `$addon_db`";
	mysqli_query($db, $queryucu);
	$queryucu = "DROP TABLE `BF_NameSilo_products`";
	mysqli_query($db, $queryucu);
}
$queryuc = "DELETE FROM `BF_addons` WHERE `addon_name`='NameSilo_Reseller'";
mysqli_query($db, $queryuc);
array_push($successes2, "You uninstalled NameSilo Reseller addon!");
include('content/client_areas/' . $current_client_area . '/admin_actions/BF_addons.php');
?>