<?php


$hc2Server = '192.168.1.230';	// Change to own IP address
$hc2Port = '4420';

include 'inc/hc2.class.php';

$hc2 = new hc2($hc2Server, $hc2Port);

include 'tpl/header.tpl.php';

if($hc2->connected == FALSE) {
	echo "Error: " .$hc2->errStr;
} else {
	$hc2->SendEvent1003();
	$hc2->SendFibaro();

	switch($_GET['command']) {
		case 'dimmer_on':
			$hc2->DimmerOn($_GET['id'],$_GET['value']);
			break;	
		case 'dimmer_off':
			$hc2->DimmerOff($_GET['id']);
			break;
			
	}
}

include 'tpl/footer.tpl.php';

if($hc2->connected == TRUE) {
	$hc2->Disconnect();
}



