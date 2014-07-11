<?php

# Required File Includes
include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewaymodule = "Zaakpay"; 

$GATEWAY = getGatewayVariables($gatewaymodule);
$secret_key = $GATEWAY['secretkey']; 
if (!$GATEWAY["type"]) die("Module Not Activated"); # Checks gateway module is active before accepting callback

# Get Returned Variables
$orderId = $_POST["orderId"];
$res_code = $_POST["responseCode"];
$res_desc = $_POST["responseDescription"];
$recv_checksum = $_POST["checksum"];
$transid = $_POST["orderId"];
$amount = $_POST["amount"]) / 100;


#Zaakpay Response Checksum part

$all = ("'". $orderId ."''". $res_code ."''". $res_desc."'");


$hash = hash_hmac('sha256', $all , $secret_key);

foreach($_POST as $key => $value)
{
 if($hash != $recv_checksum)
	  {
		  
		if($key == "responseCode")
		{
			echo '<br><tr><td width="50%" align="center" valign="middle">'.$key.'</td>
						<td width="50%" align="center" valign="middle"><font color=Red>***</font></td></tr><br>';
		}
		else if($key == "responseDescription")
		{
			echo '<tr><td width="50%" align="center" valign="middle">'.$key.'</td> 
						<td width="50%" align="center" valign="middle"><font color=Red>This response is compromised. The Transaction might have been Successfull</font></td></tr><br>';
		}
		else
		{
			echo '<tr><td width="50%" align="center" valign="middle">'.$key.'</td> 
						<td width="50%" align="center" valign="middle">'.$value.'</td></tr><br>';
		}
	  }
	  else
	  {
		  echo '<tr><td width="50%" align="center" valign="middle">'.$key.'</td>
					<td width="50%" align="center" valign="middle">'.$value.'</td></tr><br>';
	  
	  }
	  
	  }
		 if($hash == $recv_checksum)
		 {
			 //confirm
			 echo '<tr><td width="50%" align="center" valign="middle">Checksum Verified </td> 
			 				<td width="50%" align="center" valign="middle"><font color=Blue>Yes</font></td></tr><br>';
			 
		 }
		 else
		 {
			 echo '<tr><td width="50%" align="center" valign="middle">Checksum Verified </td> 
			 			<td width="50%" align="center" valign="middle"><font color=Red>No</font></td></tr><br>';
			
			 
		 }


$orderId = checkCbInvoiceID($orderId, $GATEWAY["name"]); 			# Checks Order Id is a valid order number or ends processing

checkCbTransID($transid); # Checks transaction number isn't already in the database and ends processing if it does

if ($res_code=="100") {	
    # Successful
    addInvoicePayment($orderId, $transid, $amount, $res_code, $res_desc, $gatewaymodule); # Apply Payment to Invoice: orderid, response code, response description, modulename
	logTransaction($GATEWAY["name"], $_POST, "Successful"); 			# Save to Gateway Log: name, data array, status
} else {
	# Unsuccessful
    logTransaction($GATEWAY["name"], $_POST, "Unsuccessful"); 		# Save to Gateway Log: name, data array, status
}
$filename = $request_params['udf1'] . '/viewinvoice.php?id=' . $orderId;
HEADER("location:$filename");
?>
