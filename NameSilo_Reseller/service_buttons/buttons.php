<?php
if(addon_activated('NameSilo_Reseller') == "YES")
{
    $domain_id = e($_GET['domain_id']);
	$serv = getServiceVar($_GET['domain_id']);
	$domain = $serv['svdo_name'];

function nameSilo_call($apikey, $command, $returnJSON = 1, $isSandbox = false, array $args = null, $domain = null) {
    $arg_uri = ''; $isSandbox = false; $getD = false; $getDAll = false;
    $prefix = ($isSandbox == true) ? 'http://sandbox.namesilo.com' : 'https://www.namesilo.com';
	if($command == 'listDomains')
	{
		$getDAll = true;
	}
	if($command == 'getNumberOfDomains')
	{
		$getD = true;
		$command = 'listDomains';
	}
	if($command == 'changeNameServers')
	{
	    $nsnum = 0;
	    foreach($args as $key => $value)
	    {
	        $nsnum += 1;
	        if($nsnum > 0)
	        {
	            if($value != '' && isset($value)) {
	            $nsuri .= '&'.$key.'='.$value; }
	        }
	        else
	        {
	            $nsuri = $key.'='.$value;
	        }
	    }
	    $arg_uri = $nsuri;
	}
	if($command == 'transferUpdateChangeEPPCode')
	{
	    $arg_uri = 'auth='.$_POST['epp'];
	}
	if($command == 'registerDomain' && isset($_POST))
	{
	    $arg_uri = 'domain='.$_POST['reg_name'].'&years='.$_POST['reg_years'].'&private='.$_POST['reg_privacy'].'&auto_renew='.$_POST['reg_auto_renew'].'&ns1='.$_POST['reg_ns1'].'&ns2='.$_POST['reg_ns2'];
	}
	if($arg_uri != '') {
	    $url = "$prefix/api/$command?version=1&type=xml&key=$apikey&$arg_uri";
	}
	else
	{
	    $url = "$prefix/api/$command?version=1&type=xml&key=$apikey";
	}
	if($domain != null) {
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
    unset($getD);
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
$response = nameSilo_call($nsr_apikey, 'getDomainInfo', 1, true, null, $domain);
if(isset($_GET) && $_GET['process'] == 'changens')
{
    $responsens = nameSilo_call($nsr_apikey, 'changeNameServers', 1, true, $_POST, $domain);
     if($responsens['reply']['detail'] == 'success')
     {
        echo 'Nameservers operation completed with success, please wait some seconds until nameservers change.';
     }
     else
     {
         echo 'API Warning: '.print_r($responsens, 1);
     }
}
?>

  	<p>Domain name: <?php echo $domain; ?></br>
  	Registration date: <?php echo  $response['reply']['created']; ?></br>
  	Expiration date: <?php echo $response['reply']['expires']; ?></br>
  	Status: <?php echo $response['reply']['status']; ?></br>
  	Locked: <?php echo $response['reply']['locked']; ?></br>
  	WHOIS protection: <?php echo  $response['reply']['private']; ?></br>
  	Traffic type: <?php echo  $response['reply']['traffic_type']; ?></br>
  	Email verification required: <?php echo  $response['reply']['email_verification_required']; ?></br>
  	Portfolio: <?php echo  $response['reply']['portfolio']; ?></br>
  	Forward URL: <?php echo  $response['reply']['forward_url']; ?></br>
  	Forward type: <?php echo  $response['reply']['forward_type']; ?></br>
  	Nameservers: <?php $nsnumber = 0; foreach($response['reply']['nameservers']['nameserver'] as $ns) {  if($nsnumber != 0) { echo ', '; } $nsnumber += 1; echo $ns; } ?></p><br>
  	 <form method="post" action="client_area?manage_domain=1&domain_id=<?php echo $domain_id; ?>&process=changens">
  	     Nameserver 1: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver" name="ns1" value="<?php echo $response['reply']['nameservers']['nameserver'][0]; ?>" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 2: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver" name="ns2"  value="<?php echo $response['reply']['nameservers']['nameserver'][1]; ?>" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 3: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][2]; ?>" name="ns3" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 4: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][3]; ?>" name="ns4" aria-describedby="button-addon3" class="form-control border-0">
        </div>Nameserver 5: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][4]; ?>" name="ns5" aria-describedby="button-addon3" class="form-control border-0">
        </div>Nameserver 6: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][5]; ?>" name="ns6" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 7: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][6]; ?>" name="ns7" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 8: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][7]; ?>" name="ns8" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 9: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][8]; ?>" name="ns9" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 10: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][9]; ?>" name="ns10" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 11: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][10]; ?>" name="ns11" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 12: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][11]; ?>" name="ns12" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        Nameserver 13: 
        <div class="input-group mb-4 border p-2">
            <input type="text" placeholder="Enter nameserver"  value="<?php echo $response['reply']['nameservers']['nameserver'][12]; ?>" name="ns13" aria-describedby="button-addon3" class="form-control border-0">
        </div>
        <button type="submit" class="btn btn-success">SET NAMESERVERS</button>
  	     </form>

<?php
}
?>