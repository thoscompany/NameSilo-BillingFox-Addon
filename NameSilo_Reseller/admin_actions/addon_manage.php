<?php
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
	    return $json; 
	} 
	else 
	{ 
	    return $curl_response; 
	}
	curl_close($curl);
}
if(isset($_SESSION['NameSilo_Reseller_INSTALL']) && $_SESSION['NameSilo_Reseller_INSTALL'] == "YES")
{
	if(isset($_POST['btn_install_nsr']) && $_POST['btn_install_nsr'] == 1)
	{
		if(isset($_POST['mkey'][3]))
		{
			$mkey = e($_POST['mkey']);
		}
		else
		{
			array_push($errors, "Your NameSilo Reseller API key must have minimum 5 characters.");
		}
		if(isset($_POST['mns1'][0]))
		{
			$mns1 = e($_POST['mns1']);
		}
		else
		{
			array_push($errors, "Your first default nameserver must have minimum 2 characters.");
		}
		if(isset($_POST['mns2'][0]))
		{
			$mns2 = e($_POST['mns2']);
		}
		else
		{
			array_push($errors, "Your second default nameserver must have minimum 2 characters.");
		}
		if(count($errors) == 0)
		{
			$qlic = "INSERT INTO `BF_NameSilo` (`ns_api_key`, `ns_default_ns1`, `ns_default_ns2`) VALUES ('$mkey', '$mns1', '$mns2')";
			mysqli_query($db, $qlic);
			array_push($successes, 'You succesfully installed NameSilo Reseller addon!');
			$_SESSION['NameSilo_Reseller_INSTALL'] = 'DONE';
		}
	}
	if(isset($_SESSION['NameSilo_Reseller_INSTALL']) && $_SESSION['NameSilo_Reseller_INSTALL'] == 'YES') 
	{
	display_messages(); ?>
	<div style="text-align: center;"><h5 class="m-0 font-weight-bold text-primary">NameSilo Reseller Addon Instalation</h5></div>

					<div class="card-body">
						<form method="post" action="client_area?admin_area=1&addon_use=NameSilo_Reseller&install_process=1">
							<p>API Key: </p>
							<div class="input-group mb-4 border">
								<input type="text" placeholder="Enter API Key" name="mkey" aria-describedby="button-addon3" class="form-control border-0">
							</div>
							<p>First default nameserver: </p>
							<div class="input-group mb-4 border">
								<input type="text" placeholder="Enter first default nameserver" name="mns1" aria-describedby="button-addon3" class="form-control border-0">
							</div>
							<p>Second default nameserver: </p>
							<div class="input-group mb-4 border">
								<input type="text" placeholder="Enter second default nameserver" name="mns2" aria-describedby="button-addon3" class="form-control border-0">
							</div>
							<div style="text-align: center;">
							    <button id="button-addon3" type="submit" name="btn_install_nsr" value="1" class="btn btn-info px-4" style="color: white;"><i class="fa fa-archive mr-2"></i> INSTALL NAMESILO RESELLER ADDON</button>
							</div>
						</form>
					</div>
		<?php if($_SESSION['NameSilo_Reseller_INSTALL'] == "YES") 
		{ 
			die(); 
		} 
	}
}
if(!isset($_GET['manage'])) {
	if(isset($_GET['set_key']))
	{
		$set_key = e($_POST['set_key']);
		if(strlen($set_key) > 4)
		{
			$queryunphn = "UPDATE `BF_NameSilo` SET `ns_api_key`='$set_key' WHERE 1";
			mysqli_query($db, $queryunphn);
			array_push($successes, 'You set your NameSilo API key to <b>'.$set_key.'</b>.');
		}
		else
		{
			array_push($errors, 'Your NameSilo API key must have minimum 5 characters');	
		}
	}
	else if(isset($_GET['set_mns1']))
	{
		$set_mns1 = e($_POST['set_mns1']);
		if(strlen($set_mns1) > 1)
		{
			$queryunphn = "UPDATE `BF_NameSilo` SET `ns_default_ns1`='$set_mns1' WHERE 1";
			mysqli_query($db, $queryunphn);
			array_push($successes, 'You set your NameSilo first default nameserver to <b>'.$set_mns1.'</b>.');
		}
		else
		{
			array_push($errors, 'Your NameSilo first default nameserver must have minimum 2 characters');	
		}
	}
	else if(isset($_GET['set_mns2']))
	{
		$set_mns2 = e($_POST['set_mns2']);
		if(strlen($set_mns2) > 1)
		{
			$queryunphn = "UPDATE `BF_NameSilo` SET `ns_default_ns2`='$set_mns2' WHERE 1";
			mysqli_query($db, $queryunphn);
			array_push($successes, 'You set your NameSilo second default nameserver to <b>'.$set_mns2.'</b>.');
		}
		else
		{
			array_push($errors, 'Your NameSilo second default nameserver must have minimum 2 characters');	
		}
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
	display_messages(); ?>
	<div class="row">
		<div class="col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header py-3">
					<h6 class="m-0 font-weight-bold text-primary">NameSilo Addon Information</h6>
				</div>
				<div class="card-body">
					<i class="fa fa-cube"></i><b style="font-family: Muli, sans-serif;"> First default nameserver, second default nameserver:</b> <?php echo $nsr_ns1.', '.$nsr_ns2;?><br>
					<i class="far fa-file-code"></i><b style="font-family: Muli, sans-serif;">  Reseller API key:</b> <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" id="nsrapishow" onmouseover="info('show')" onmouseout="info('hide');">HOVER ME</button><br>
					<br>
					<script>
						function info(action)
						{
							if(action == 'show')
							{
								document.getElementById('nsrapishow').innerHTML = `<?php echo $nsr_apikey; ?>`;
							}
							else
							{
								document.getElementById('nsrapishow').innerHTML = `HOVER ME`;
							}
						}
					</script>
					<p>Manage:</p>
					<a class="btn btn-info" href="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&action=list">List & Manage NameSilo domain products</a>
					<a class="btn btn-info" href="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&action=viewinfo">View NameSilo reseller informations</a>
				</div>
			</div>
		</div>
	</div>

	  <!-- Content Row -->
    <div class="row">
      <div class="col-lg-12">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Manage NameSilo</h6>
          </div>
          <div class="card-body">
            <p>API KEY: </p>
            <form method="post" action="client_area?admin_area=1&addon_use=NameSilo_Reseller&set_key=1">
                    <div class="input-group mb-4 border">
                        <input type="text" placeholder="Enter your NameSilo Reseller API key" name="set_key" aria-describedby="button-addon3" class="form-control border-0">
                        <div class="input-group-append border-0">
                          <button id="button-addon3" type="submit" name="btn_ptype" value="1" class="btn btn-info px-4"><i class="fa fa-hashtag mr-2"></i> SET API KEY</button>
                        </div>
                    </div>
            </form><br>
          	<p>First default nameserver: </p>
            <form method="post" action="client_area?admin_area=1&addon_use=NameSilo_Reseller&set_mns1=1">
                    <div class="input-group mb-4 border">
                        <input type="text" placeholder="Enter your first default nameserver" name="set_mns1" aria-describedby="button-addon3" class="form-control border-0">
                        <div class="input-group-append border-0">
                          <button id="button-addon3" type="submit" name="btn_ptype" value="1" class="btn btn-info px-4"><i class="fa fa-hashtag mr-2"></i> SET FIRST DEFAULT NAMESERVER</button>
                        </div>
                    </div>
            </form><br>
            <p>Second default nameserver: </p>
            <form method="post" action="client_area?admin_area=1&addon_use=NameSilo_Reseller&set_mns2=1">
                    <div class="input-group mb-4 border">
                        <input type="text" placeholder="Enter your second default nameserver" name="set_mns2" aria-describedby="button-addon3" class="form-control border-0">
                        <div class="input-group-append border-0">
                          <button id="button-addon3" type="submit" name="btn_ptype" value="1" class="btn btn-info px-4"><i class="fa fa-hashtag mr-2"></i> SET SECOND DEFAULT NAMESERVER</button>
                        </div>
                    </div>
            </form><br>
            <br>
          </div>
        </div>
      </div>
    </div>
<?php } 
else
{
	if(isset($_GET['action']) && $_GET['action'] == 'list') {
	?>
	<div class="row">
		<div class="col-lg-12">
			<div class="card shadow mb-4">
				<div class="card-header py-3">
					<?php 
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
					display_messages(); ?>
					<h6 class="m-0 font-weight-bold text-primary">Your NameSilo registered domains (<?php echo nameSilo_call($nsr_apikey, 'getNumberOfDomains', 1, true); ?>)</h6>
				</div>
				<div class="card-body">
				    <div style="text-align: center;">
					    <a class="btn btn-info" style="margin-bottom: 5px;" href="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&action=registerdomain">Register a new domain</a>
					</div>
					<?php
					$dom = nameSilo_call($nsr_apikey, 'listDomains', 1, true);
					if(nameSilo_call($nsr_apikey, 'getNumberOfDomains', 1, true) > 0)
						{ ?>
						<div class="table-bordered">
					        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
		                    <thead>
		                      <tr>
		                        <th><?php echo $_LANG['client_area']['domain']; ?></th>
		                        <th><?php echo $_LANG['client_area']['manage']; ?></th>
		                      </tr>
		                    </thead>
		                    <tfoot>
		                      <tr>
		                        <th><?php echo $_LANG['client_area']['domain']; ?></th>
		                        <th><?php echo $_LANG['client_area']['manage']; ?></th>
		                      </tr>
		                    </tfoot>
		                    <tbody>
		                      <?php
		                      
		                      if(isset($dom['domain']))	
		                      {
		                          $dom = $dom['domain'];
			                      foreach($dom as $domain)
			                      {?>
			                        <tr>
			                          <td><?php echo $domain; ?> </td>
			                          <td>
			                            <a class="btn btn-info" href="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&domain=<?php echo $domain; ?>"><i class="fa fa-cogs"><b style="font-family: Muli, sans-serif;"> <?php echo $_LANG['client_area']['manage']; ?></b></i></a>
			                          </td>
			                        </tr>
			                      <?php
			                      }
			                     } else {
			                        ?>
			                        <tr>
			                          <td><?php echo $dom; ?> </td>
			                          <td>
			                            <a class="btn btn-info" href="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&domain=<?php echo $dom; ?>"><i class="fa fa-cogs"><b style="font-family: Muli, sans-serif;"> <?php echo $_LANG['client_area']['manage']; ?></b></i></a>
			                          </td>
			                        </tr>
			                        <?php
			                     }?>
		                    </tbody>
		                  </table>
		              </div>
		          <?php } else { echo '<br>You didn\'t registered domains trough NameSilo'; } ?>
					</div>
			</div>
		</div>
	</div>
	<?php }
	else if(isset($_GET['action']) && $_GET['action'] == 'viewinfo') {
	?>
	<div class="row">
      <div class="col-lg-12">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">View NameSilo Informations</h6>
          </div>
          <div class="card-body">
					<?php 
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
					display_messages(); ?>
					<b style="font-family: Muli, sans-serif;"> First default nameserver, second default nameserver:</b> <?php echo $nsr_ns1.', '.$nsr_ns2;?><br>
					<b style="font-family: Muli, sans-serif;">  Reseller API key:</b> <button type="button" class="btn btn-secondary" data-toggle="tooltip" data-placement="top" id="nsrapishow" onmouseover="info('show')" onmouseout="info('hide');">HOVER ME</button><br>
					<br>
					<script>
						function info(action)
						{
							if(action == 'show')
							{
								document.getElementById('nsrapishow').innerHTML = `<?php echo $nsr_apikey; ?>`;
							}
							else
							{
								document.getElementById('nsrapishow').innerHTML = `HOVER ME`;
							}
						}
					</script>
					<b style="font-family: Muli, sans-serif;"> NameSilo Account Balance:</b> <?php $bal = nameSilo_call($nsr_apikey, 'getAccountBalance', 1, true); if(is_array($bal)) { echo '$'.$bal['reply']['balance'];  } else { echo $bal; }?><br>
					<b style="font-family: Muli, sans-serif;"> NameSilo Registered Domains:</b> <?php echo nameSilo_call($nsr_apikey, 'getNumberOfDomains', 1, true); ?><br>
          </div>
        </div>
      </div>
    </div>
	<?php }
	else if(isset($_GET['action']) && $_GET['action'] == 'registerdomain') {
	?>
	<div class="row">
      <div class="col-lg-12">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Register a NameSilo Domain</h6>
          </div>
          <div class="card-body">
              <?php
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
              if(isset($_GET['process']))
              {
                  $response = nameSilo_call($nsr_apikey, 'registerDomain', 1, true, $_POST);
                 if($response['reply']['detail'] == 'success')
                 {
                    echo $response['reply']['message'];
                 }
                 else
                 {
                     echo 'API Warning: '.print_r($response, 1);
                 }
              }
              ?>
            <form method="post" action="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&action=registerdomain&process=register">
                    <p>Domain name: </p>
                    <div class="input-group mb-4 border">
                        <input type="text" placeholder="Enter domain name" name="reg_name" aria-describedby="button-addon3" class="form-control border-0">
                    </div>
                    <p>Domain registration years: </p>
                    <div class="input-group mb-4 border">
                        <select class="form-control" name="reg_years" placeholder="Years of registration">
                            <option value="1" selected>1 Year</option>
                            <option value="2">2 Years</option>
                            <option value="3">3 Years</option>
                            <option value="4">4 Years</option>
                            <option value="5">5 Years</option>
                            <option value="6">6 Years</option>
                            <option value="7">7 Years</option>
                            <option value="8">8 Years</option>
                            <option value="9">9 Years</option>
                            <option value="10">10 Years</option>
                        </select>
                    </div>
                    <p>WHOIS privacy: </p>
                    <div class="input-group mb-4 border">
                        <select class="form-control" name="reg_privacy" placeholder="WHOIS privacy">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <p>Auto renew: </p>
                     <div class="input-group mb-4 border">
                        <select class="form-control" name="reg_auto_renew" placeholder="Auto-renew">
                            <option value="1">Yes</option>
                            <option value="0" selected>No</option>
                        </select>
                    </div>
                    <p>First nameserver: </p>
                    <div class="input-group mb-4 border">
                        <input type="text" placeholder="Enter first nameserver" name="reg_ns1" value="<?php echo $nsr_ns1; ?>" aria-describedby="button-addon3" class="form-control border-0">
                    </div>
                    <p>Second nameserver: </p>
                    <div class="input-group mb-4 border">
                        <input type="text" placeholder="Enter second nameserver" name="reg_ns2" value="<?php echo $nsr_ns2; ?>" aria-describedby="button-addon3" class="form-control border-0">
                    </div>
                    <button type="submit" class="btn btn-info">Register domain</button>
            </form><br>
          </div>
        </div>
      </div>
    </div>
	<?php }
	else if(isset($_GET['domain'])) {
	    $domain = $_GET['domain'];
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
					if(isset($_GET['process']) && $_GET['process'] == 'changens')
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
					if(isset($_GET['process']) && $_GET['process'] == 'setepp')
					{
					    $responseepp = nameSilo_call($nsr_apikey, 'transferUpdateChangeEPPCode', 1, true, $_POST, $domain);
                         if($responsens['reply']['detail'] == 'success')
                         {
                            echo 'EPP Transfer Auth Code set to <b>'.$_POST['epp'].'</b>.';
                         }
                         else
                         {
                             echo 'API Warning: '.print_r($responseepp, 1);
                         }
					}
					if(isset($_GET['process']) && $_GET['process'] == 'unlock')
					{
					    $responseepp = nameSilo_call($nsr_apikey, 'domainUnlock', 1, true, null, $domain);
                         if($responsens['reply']['detail'] == 'success')
                         {
                            echo 'The domain has been unlocked.';
                         }
                         else
                         {
                             echo 'API Warning: '.print_r($responseepp, 1);
                         }
					}
					if(isset($_GET['process']) && $_GET['process'] == 'lock')
					{
					    $responseepp = nameSilo_call($nsr_apikey, 'domainLock', 1, true, null, $domain);
                         if($responsens['reply']['detail'] == 'success')
                         {
                            echo 'The domain has been locked.';
                         }
                         else
                         {
                             echo 'API Warning: '.print_r($responseepp, 1);
                         }
					}
		?>
              	<div class="row">
		          <div class="col-lg-12">
		            <div class="card shadow mb-4">
		              <div class="card-header py-3">
		                <h6 class="m-0 font-weight-bold text-primary">Manage domain <?php echo $domain; ?></h6>
		              </div>
		              <div class="card-body">
		              	<p>Domain name: <?php echo $domain; ?></p>
		              	<p>Registration date: <?php echo  $response['reply']['created']; ?></p>
		              	<p>Expiration date: <?php echo $response['reply']['expires']; ?></p>
		              	<p>Status: <?php echo $response['reply']['status']; ?></p>
		              	<p>Locked: <?php echo $response['reply']['locked']; ?></p>
		              	<p>WHOIS protection: <?php echo  $response['reply']['private']; ?></p>
		              	<p>Traffic type: <?php echo  $response['reply']['traffic_type']; ?></p>
		              	<p>Email verification required: <?php echo  $response['reply']['email_verification_required']; ?></p>
		              	<p>Portfolio: <?php echo  $response['reply']['portfolio']; ?></p>
		              	<p>Forward URL: <?php echo  $response['reply']['forward_url']; ?></p>
		              	<p>Forward type: <?php echo  $response['reply']['forward_type']; ?></p>
		              	<p>Nameservers: <?php $nsnumber = 0; foreach($response['reply']['nameservers']['nameserver'] as $ns) {  if($nsnumber != 0) { echo ', '; } $nsnumber += 1; echo $ns; } ?></p><br>
		              	 <form method="post" action="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&domain=<?php echo $domain; ?>&process=changens">
		              	     <p>Nameserver 1: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver" name="ns1" value="<?php if(isset($response['reply']['nameservers']['nameserver'][0])) echo $response['reply']['nameservers']['nameserver'][0]; ?>" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 2: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver" name="ns2"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][1])) echo $response['reply']['nameservers']['nameserver'][1]; ?>" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 3: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][2])) echo $response['reply']['nameservers']['nameserver'][2]; ?>" name="ns3" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 4: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][3])) echo $response['reply']['nameservers']['nameserver'][3]; ?>" name="ns4" aria-describedby="button-addon3" class="form-control border-0">
                            </div><p>Nameserver 5: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][4])) echo $response['reply']['nameservers']['nameserver'][4]; ?>" name="ns5" aria-describedby="button-addon3" class="form-control border-0">
                            </div><p>Nameserver 6: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][5])) echo $response['reply']['nameservers']['nameserver'][5]; ?>" name="ns6" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 7: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][6])) echo $response['reply']['nameservers']['nameserver'][6]; ?>" name="ns7" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 8: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][7])) echo $response['reply']['nameservers']['nameserver'][7]; ?>" name="ns8" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 9: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][8])) echo $response['reply']['nameservers']['nameserver'][8]; ?>" name="ns9" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 10: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][9])) echo $response['reply']['nameservers']['nameserver'][9]; ?>" name="ns10" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 11: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][11])) echo $response['reply']['nameservers']['nameserver'][11]; ?>" name="ns11" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 12: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][11])) echo $response['reply']['nameservers']['nameserver'][11]; ?>" name="ns12" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <p>Nameserver 13: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter nameserver"  value="<?php if(isset($response['reply']['nameservers']['nameserver'][12])) echo $response['reply']['nameservers']['nameserver'][12]; ?>" name="ns13" aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                            <button type="submit" class="btn btn-info">SET NAMESERVERS</button>
		              	     </form>
		              	     <form method="post" action="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&domain=<?php echo $domain; ?>&process=setepp">
		              	     <p>Set EPP code: </p>
                            <div class="input-group mb-4 border">
                                <input type="text" placeholder="Enter new EPP code" name="epp"  aria-describedby="button-addon3" class="form-control border-0">
                            </div>
                             <button type="submit" class="btn btn-info">SET EPP CODE</button>
                             </form><br>
                             <?php if($response['reply']['locked'] == 'Yes')
                             { ?>
                                 <a href="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&domain=<?php echo $domain; ?>&process=unlock" class="btn btn-info">Unlock domain</a>
                             <?php }
                             else
                             { ?>
                                 <a href="client_area?admin_area=1&addon_use=NameSilo_Reseller&manage=1&domain=<?php echo $domain; ?>&process=lock" class="btn btn-danger">Lock domain</a>
                             <?php } ?>
		              </div>
		          </div>
		      </div>
		  </div> <?php }
}