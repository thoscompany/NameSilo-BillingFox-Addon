<?php 
////////////////////////////////////////////
//          BillingFox COPYRIGHT          //
////////////////////////////////////////////

function nameSilo_call($apikey, $command, $returnJSON = 1, $isSandbox = false, array $args = null, $domain = null) {
$arg_uri = ''; $isSandbox = false; $getD = false; $getDAll = false;
$prefix = ($isSandbox == true) ? 'http://sandbox.namesilo.com' : 'https://www.namesilo.com';
if($command == 'registerDomain' && isset($_POST))
{
    $arg_uri = 'domain='.$args['domain_name'].'&years=1&private=1&auto_renew=0&ns1='.$args['ns1'].'&ns2='.$args['ns2'];
}
if($arg_uri != '') {
    $url = "$prefix/api/$command?version=1&type=xml&key=$apikey&$arg_uri";
}
else
{
    $url = "$prefix/api/$command?version=1&type=xml&key=$apikey";
}
if(domain != null) {
    $url .= "&domain=$domain";
}
$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
curl_setopt($curl, CURLOPT_HEADER,0);
curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($curl, CURLOPT_URL, $url);
$curl_response = curl_exec($curl);
if($returnJSON == 1) {
	$xml_snippet = simplexml_load_string($curl_response);
	$json_convert = json_encode($xml_snippet);
	$json = json_decode($json_convert, 1);
	if($getD == true)
	{
		$json = count($json['reply']['domains']['domain']);
	}
	else if($getDAll == true)
	{
		$json = $json['reply']['domains'];
	}
return $json; } else { return $curl_response; }
curl_close($curl);
}

$queryc = "SELECT * FROM `BF_NameSilo` WHERE 1";
$resultsc = mysqli_query($db, $queryc)
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
if(addon_activated('NameSilo_Reseller') == 'YES' && !empty($nsr_apikey)){
  	$woDomain = $_SESSION['item_name'];
  	$log = nameSilo_call($nsr_apikey, 'registerDomain', 1, true, array('ns1' => $nsr_ns1, 'ns2' => $nsr_ns2, 'domain_name' => $woDomain), $woDomain);
  	if($log['reply']['detail'] != 'success')
  	{
  	 $_SESSION['info'] = 'There was an error with domain registration, please contact us.<br>Error code:'.$log['reply']['code'];   
  	}
  	else
  	{
  	    $_SESSION['info'] = 'Thanks for your domain order.<br>You can manage your domain below.';
  	}
}
else
{
	$_SESSION['info'] = 'You can\'t manage this service instantly due an error in domain addon, please contact us.';
}