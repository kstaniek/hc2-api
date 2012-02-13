<?php

define("HC2_CMD_FIBARO",		"FIBARO\n");						// Sounds like hello/wakeup command ;-)
define("HC2_CMD_EVENT1003",		"EVENT -1003 Event #-1003\n");				// WTF is this?
define("HC2_CMD_SET_MODULE_POWER",	"vvvvvvvvvvvvvvvvvvvvvvvvva*xvvvva*xvva*xvvvvvv");	// Frame format for dimmer and on/off

define("HC2_RES_YES",		"YES");
define("HC2_RES_ERR",		"ERROR");
define("HC2_RES_OK",		"OK");


class hc2 {
	var $host;
	var $port;

	var $hc2_sock = NULL;
	var $connected = FALSE;
	
	var $debugging	= FALSE;	// Change to TRUE for debugging
	var $errStr	= "";


	function hc2($srv,$port) {
		$this->host = $srv;
		$this->port = $port;

		$resp = $this->Connect();
		if ( is_null($resp) ) {
			$this->errStr = "Could not connect";
			return;
		}
		return;

	}

	function Connect() {
		if ( $this->debugging ) echo "hc2->Connect() / host: ".$this->host.", port: ".$this->port."<br>";
		$this->hc2_sock = fsockopen($this->host,$this->port,$errNo,$errStr);

		if (!$this->hc2_sock) {
			$this->errStr = "Socket Error: $errStr ($errNo)";
			return NULL;
		} else {
			$response="";
			fputs($this->hc2_sock,HC2_CMD_FIBARO);
			while(!feof($this->hc2_sock)) {
				$response =  fgets($this->hc2_sock,1024);
				if (strncmp(HC2_RES_YES,$response,strlen(HC2_RES_YES)) == 0) {
					$this->connected = TRUE;
					return $response;
					break;
				}
				if (strncmp(HC2_RES_ERR,$response,strlen(HC2_RES_ERR)) == 0) {
					$this->errStr = "Server responded with: $response";
					return NULL;
				}
			}
			// Generic response
			$this->errStr = "Connection not available";
			return NULL;
		}
	}
	function SendEvent1003() {	// No clue why it's needed
		if ( $this->debugging ) echo "hc2->Event1003() \n";
		if ( ! $this->connected ) {
			echo "hc2->Event1003() / Error: Not connected\n";
		} else {
			$response="";
                        fputs($this->hc2_sock,HC2_CMD_EVENT1003);
                        while(!feof($this->hc2_sock)) {
                                $response =  fgets($this->hc2_sock,1024);
                                if (strncmp(HC2_RES_OK,$response,strlen(HC2_RES_OK)) == 0) {
                                        break;
                                }
                                if (strncmp(HC2_RES_ERR,$response,strlen(HC2_RES_ERR)) == 0) {
					list ( $junk, $errTmp ) = explode(HC2_RES_ERR . " ",$response );
					$this->errStr = strtok($errTmp,"\n");
				}

				if ( strlen($this->errStr) > 0 ) {
					return NULL;
				}

				// Build the response string
				$respStr .= $response;
			}
			$respStr .= $response;
			if ( $this->debugging ) echo "hc2->SendEvent1003() / response: '".$respStr."'\n";
		}
		return $respStr;
	}
	function SendFibaro()	{
		if ( $this->debugging ) echo "hc2->SendFibaro() \n";
                if ( ! $this->connected ) {
                        echo "hc2->SendFibaro() / Error: Not connected\n";
                } else {
                        $response="";
                        fputs($this->hc2_sock,HC2_CMD_FIBARO);
                        while(!feof($this->hc2_sock)) {
                                $response =  fgets($this->hc2_sock,1024);
				if (strncmp(HC2_RES_YES,$response,strlen(HC2_RES_YES)) == 0) {
                                        break;
                                }
                                if (strncmp(HC2_RES_ERR,$response,strlen(HC2_RES_ERR)) == 0) {
                                        list ( $junk, $errTmp ) = explode(HC2_RES_ERR . " ",$response );
                                        $this->errStr = strtok($errTmp,"\n");
                                }

                                if ( strlen($this->errStr) > 0 ) {
                                        return NULL;
                                }

                                // Build the response string
                                $respStr .= $response;
                        }
                        $respStr .= $response;
                        if ( $this->debugging ) echo "hc2->SendFibaro() / response: '".$respStr."'\n";
                }
                return $respStr;
        }


	function SendFrame($frame)	{
		if ( $this->debugging ) echo "hc2->SendFrame() / arg: '".$frame."'\n";
                if ( ! $this->connected ) {
                        echo "hc2->SendFrame() / Error: Not connected\n";
                } else {
			$message = "MESSAGE ".strlen($frame);
			$message = pack("A22x",$message).$frame;
                        fputs($this->hc2_sock,$message);
                        if ( $this->debugging ) echo "hc2->SendFrame() \n";
                }
                return;
        }


	function SetModulePower($id,$value)   {
		$frame=pack(HC2_CMD_SET_MODULE_POWER,
			1234,0,0,0,0,0,0,0,1,0,1,0,1,0,0,0,0,0,260,0,0,0,1,0,0,
			"20",2,0,1,0,$id,101,0,$value,0,0,0,0,6789,0);
		$this->SendFrame($frame);
                return;
        }


	function Disconnect() {
		if ( $this->debugging ) echo "hc2->Disconnect()<br>";
		fclose($this->hc2_sock);

		$this->connected = FALSE;
		unset($this->errStr);
		unset($this->hc2_sock);
	}
		
		
}

?>	



