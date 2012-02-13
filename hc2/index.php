<?php


$hc2Server = '192.168.1.230';	// Change to IP address of your HC2
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
		case 'hc2_set_module_power':
			$hc2->SetModulePower($_GET['id'],$_GET['value']);
			break;	
			
	}
}

include 'tpl/footer.tpl.php';

if($hc2->connected == TRUE) {
	$hc2->Disconnect();
}



