<?php 
////////////////////////////////////////////
//          BillingFox COPYRIGHT          //
////////////////////////////////////////////
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/install/config.inc.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/install/config.inc.php');
	//////////////////////////////// DATABASE
	$db = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS);
	mysqli_select_db($db,$DB_NAME);
	//////////////////////////////// DATABASE
	$queryth = "SELECT addon_name FROM `BF_addons` WHERE `addon_name`='NameSilo_Reseller' AND `addon_status`=1 LIMIT 1";
	$result1th = mysqli_query($db, $queryth);
	if(mysqli_num_rows($result1th) > 0)
	{
		$addonstatus = 'YES';
	}
	else
	{
		$addonstatus = 'NO';
	}
	$queryc = "SELECT * FROM `BF_NameSilo` WHERE 1";
	$resultsc = @mysqli_query($db, $queryc)
	or die ("SQL error(c - admin(admin_area-get_namesilo-from_list)): " .mysqli_error($db));
	if(mysqli_num_rows($resultsc) > 0)
	{
		while($pstats = mysqli_fetch_assoc($resultsc))
		{
			$nsr_apikey = $pstats['ns_api_key'];
			$nsr_ns1 = $pstats['ns_default_ns1'];
			$nsr_ns2 = $pstats['ns_default_ns2'];
		}
	}
	else
	{
		die('Invalid instalation of NameSilo Reseller addon, please reinstall it.');
	}
	if($addonstatus == 'YES' && !empty($nsr_apikey)){
		?>
		<div>
			<input type="checkbox" name="register_on_create" value="1" id="register_on_create">
			<label for="register_on_create">Check this box if you want to register this domain.</label>
		</div>
		<div>
			<input type="text" placeholder="First nameserver" value="<?php echo $nsr_ns1; ?>" name="ns1">
			<input type="text" placeholder="Second nameserver" value="<?php echo $nsr_ns2; ?>" name="ns2">
		</div><br><?php
	}
}
else
{
	echo 'Your BillingFox instalation file cannot be found.';
}