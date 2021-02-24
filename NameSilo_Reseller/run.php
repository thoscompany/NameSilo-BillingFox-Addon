<?php
$query_addon = "SELECT * FROM `BF_addons` WHERE `addon_name`='NameSilo_Reseller'";
$addon_result = mysqli_query($db, $query_addon);
if(mysqli_num_rows($addon_result) == 1 && addon_activated('NameSilo_Reseller') == 'YES')
{
	if($_GET['admin_area'])
	{
		include('content/client_areas/'.getWebVar('client_area').'/admin_actions/addon_manage.php');
	}
	else
	{
		include('content/addons/NameSilo_Reseller/frontend/frontend.php');
	}
}
else
{
	$install_db1 = "
	INSERT INTO `BF_addons` (`addon_status`, `addon_name`, `addon_description`) VALUES
	('1', 'NameSilo_Reseller', 'Resell domains with NameSilo BillingFox Addon.');";
	$install_db2 = "
	CREATE TABLE IF NOT EXISTS `BF_NameSilo` (
  `ns_api_key` varchar(255) DEFAULT NULL,
  `ns_default_ns1` varchar(255) DEFAULT NULL,
  `ns_default_ns2` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$install_db3 = "
	CREATE TABLE IF NOT EXISTS `BF_NameSilo_products` (
  `ns_domain_id` BIGINT AUTO_INCREMENT,
  `ns_domain_bf_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ns_domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	mysqli_query($db, $install_db1)
	or die ("SQL error(NameSilo_Reseller - addon(install1)): " .mysqli_error($db));
	mysqli_query($db, $install_db2)
	or die ("SQL error(NameSilo_Reseller - addon(install2)): " .mysqli_error($db));
	mysqli_query($db, $install_db3)
	or die ("SQL error(NameSilo_Reseller - addon(install3)): " .mysqli_error($db));
	$_SESSION['NameSilo_Reseller_INSTALL'] = "YES";
	include('content/client_areas/'.getWebVar('client_area').'/admin_actions/addon_manage.php');
}
?>