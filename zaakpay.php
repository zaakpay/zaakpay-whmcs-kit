<?php

function zaakpay_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"Zaakpay"),
     "merchantid" => array("FriendlyName" => "Merchant ID", "Type" => "text", "Size" => "50", "Description" => "Enter your Zaakpay Merchant Id here ..", ),
	 "secretkey" => array("FriendlyName" => "Secret Key", "Type" => "text", "Size" => "50", "Description" => "Enter your Zaakpay Secret Key here ..", ),
     "mode" => array("FriendlyName" => "Zaakpay Mode", "Type" => "dropdown", "Options" => "0,1", "Description" => "(0 - Test Mode , 1 - Live Mode)", ),
    );
	return $configarray;
}

function zaakpay_link($params) {
	
	

	# Gateway Specific Variables
	//$gatewayusername = $params['username'];
	$merchantId = $params['merchantid'];
	$mode = $params['mode'];
	$secret_key = $params['secretkey'];
	$txntype = 1;
	$purpose = 1;
	$zpayoption = 1;
	$date = date('Y-m-d');
	
	# Invoice Variables
	$orderId = $params['invoiceid'];
	$description = "Zaakpay subscription fee";	//$params['description'];
    $amt = $params['amount'];					 # Format: ##.##
	$amount = intval($amt * 100); 				//amount should be in paisa
    $currency = $params['currency']; 			# Currency Code

	# Client Variables
	$firstname = $params['clientdetails']['firstname'];
	$lastname = $params['clientdetails']['lastname'];
	$email = $params['clientdetails']['email'];
	$address1 = $params['clientdetails']['address1'];
	$address2 = $params['clientdetails']['address2'];
	$address = $address1.$address2;
	$address = $address;
	$city = $params['clientdetails']['city'];
	$state = $params['clientdetails']['state'];
	$pincode = $params['clientdetails']['postcode'];
	$country = $params['clientdetails']['country'];
	$telephone = $params['clientdetails']['phonenumber'];
	

	# System Variables
	$companyname = $params['companyname'];
	$systemurl = $params['systemurl'];
	$currency = $params['currency'];
	
	
	$ipaddress = "127.0.0.1";			// Merchant Ip Address
	
	# Zaakpay Checksum Part
	
	$post_variables = Array(
"merchantIdentifier" 				=> $merchantId,
"orderId" 							=> $orderId,
//"returnUrl"						=> "", 				//Optional
"buyerEmail"						=> $email,
"buyerFirstName"					=> $firstname,
"buyerLastName"						=> $lastname,
"buyerAddress"  					=> $address,
"buyerCity"							=> $city,
"buyerState"   					 	=> $state,
"buyerCountry"  					=> $country,
"buyerPincode"						=> $pincode,
"buyerPhoneNumber"					=> $telephone,
"txnType"							=> $txntype,
"zpPayOption"						=> $zpayoption,
"mode"								=> $mode,
"currency"							=> $currency,
"amount" 							=> $amount,     
"merchantIpAddress" 				=> $ipaddress,
"purpose"							=> $purpose, 
"productDescription"				=> $description,
//"ShipToAddress"				=> "",
//"ShipToCity"					=> "",					//Optional fields
//"ShipToState"					=> "",
//"ShipToCountry"				=> "",
//"ShipToPincode"				=> "",
//"ShipToPhoneNumber"			=> "",
//"ShipToFirstname"				=> "",
//"ShipToLastname"				=> "",
"txnDate" 							=> $date,

);
	function sanitizedParam($param) {
		$pattern[0] = "%,%";
	        $pattern[1] = "%#%";
	        $pattern[2] = "%\(%";
       		$pattern[3] = "%\)%";
	        $pattern[4] = "%\{%";
	        $pattern[5] = "%\}%";
	        $pattern[6] = "%<%";
	        $pattern[7] = "%>%";
	        $pattern[8] = "%`%";
	        $pattern[9] = "%!%";
	        $pattern[10] = "%\\$%";
	        $pattern[11] = "%\%%";
	        $pattern[12] = "%\^%";
	        $pattern[13] = "%=%";
	        $pattern[14] = "%\+%";
	        $pattern[15] = "%\|%";
	        $pattern[16] = "%\\\%";
	        $pattern[17] = "%:%";
	        $pattern[18] = "%'%";
	        $pattern[19] = "%\"%";
	        $pattern[20] = "%;%";
	        $pattern[21] = "%~%";
	        $pattern[22] = "%\[%";
	        $pattern[23] = "%\]%";
	        $pattern[24] = "%\*%";
	        $pattern[25] = "%&%";
        	$sanitizedParam = preg_replace($pattern, "", $param);
		return $sanitizedParam;
	}

	function sanitizedURL($param) {
		$pattern[0] = "%,%";
	        $pattern[1] = "%\(%";
       		$pattern[2] = "%\)%";
	        $pattern[3] = "%\{%";
	        $pattern[4] = "%\}%";
	        $pattern[5] = "%<%";
	        $pattern[6] = "%>%";
	        $pattern[7] = "%`%";
	        $pattern[8] = "%!%";
	        $pattern[9] = "%\\$%";
	        $pattern[10] = "%\%%";
	        $pattern[11] = "%\^%";
	        $pattern[12] = "%\+%";
	        $pattern[13] = "%\|%";
	        $pattern[14] = "%\\\%";
	        $pattern[15] = "%'%";
	        $pattern[16] = "%\"%";
	        $pattern[17] = "%;%";
	        $pattern[18] = "%~%";
	        $pattern[19] = "%\[%";
	        $pattern[20] = "%\]%";
	        $pattern[21] = "%\*%";
        	$sanitizedParam = preg_replace($pattern, "", $param);
		return $sanitizedParam;
	}
	function calculateChecksum($secret_key, $all) {
		
		$hash = hash_hmac('sha256', $all , $secret_key);
		$checksum = $hash;
		
		return $checksum;
	}
	
	$all = '';
		foreach($post_variables as $name => $value)	{
			if($name != 'checksum') {
				$all .= "'";
				if ($name == 'returnUrl') {
					$all .= sanitizedURL($value);
				} else {				
					
					$all .= sanitizedParam($value);
				}
				$all .= "'";
			}
		}

	$checksum = calculateChecksum($secret_key,$all);
	
		
	# Code submit to Zaakpay ...

	$code = '<form method = "post" action = "https://api.zaakpay.com/transact" >
<input type="hidden" name="merchantIdentifier" value="'.$merchantId.'" />
<input type="hidden" name="orderId" value="'.$orderId.'" />
<input type="hidden" name="buyerEmail" value="'.$email.'" />
<input type="hidden" name="buyerFirstName" value="'.$firstname.'" />
<input type="hidden" name="buyerLastName" value="'.$lastname.'" />
<input type="hidden" name="buyerAddress" value="'.$address.'" />
<input type="hidden" name="buyerCity" value="'.$city.'" />
<input type="hidden" name="buyerState" value="'.$state.'" />
<input type="hidden" name="buyerCountry" value="'.$country.'" />
<input type="hidden" name="buyerPincode" value="'.$pincode.'" />
<input type="hidden" name="buyerPhoneNumber" value="'.$telephone.'" />
<input type="hidden" name="txnType" value="'.$txntype.'" />
<input type="hidden" name="zpPayOption" value="'.$zpayoption.'" />
<input type="hidden" name="mode" value="'.$mode.'" />
<input type="hidden" name="currency" value="'.$currency.'" />
<input type="hidden" name="amount" value="'.$amount.'" />
<input type="hidden" name="merchantIpAddress" value="'.$ipaddress.'" />
<input type="hidden" name="purpose" value="'.$purpose.'" />
<input type="hidden" name="productDescription" value="'.$description.'" />

<!-- Optional <input type="hidden" name="productDescription1" value="" />
<input type="hidden" name="productDescription2" value="" />
<input type="hidden" name="productDescription3" value="" />
<input type="hidden" name="productDescription4" value="" /> -->

<!-- Optional Fields <input type="hidden" name="ShipToAddress" value=""/>
<input type="hidden" name="ShipToCity" value=""/>
<input type="hidden" name="ShipToState" value=""/>
<input type="hidden" name="ShipToPinCode" value=""/>
<input type="hidden" name="ShipToPhoneNumber" value=""/>
<input type="hidden" name="ShipToFirstName" value=""/>
<input type="hidden" name="ShipToLastName" value=""/>
<input type="hidden" name="ShipToAddress" value=""/> -->


<input type="hidden" name="txnDate" value="'.$date.'" />
<input type="hidden" name="checksum" value="'.$checksum.'" />

<input type="submit" value="Proceed to Pay Now" />
</form>';

	return $code;
}



?>