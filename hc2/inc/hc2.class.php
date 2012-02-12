<?php

define("HC2_CMD_FIBARO",		"FIBARO\n");
define("HC2_CMD_EVENT1003",		"EVENT -1003 Event #-1003\n");

define("HC2_RES_YES",		"YES");
define("HC2_RES_ERR",		"ERROR");
define("HC2_RES_OK",		"OK");


class hc2 {
	var $host;
	var $port;

	var $hc2_sock = NULL;
	var $connected = FALSE;
	
	var $debugging	= TRUE;
	var $errStr	= "";

	var $command_queue;
	private static $hc2_frame_cmd_dimmer_on = Array("MESSAGE 82            ",
                                "\x00",
                                "\xd2\x04",
                                "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                                "\x01\x00\x00\x00",
                                "\x01\x00\x00\x00",
                                "\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                                "\x01\x04\x00\x00\x00\x00\x00\x00",
                                "\x01\x00\x00\x00\x00\x00",
                                "\x32\x30\x00",
                                "\x02\x00\x00\x00",
                                "\x01\x00\x00\x00",
                                "<ID>",		// ID 
                                "\x00",
                                "\x65\x00\x00\x00",
                                "<Value>",		// Value
                                "\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                                "\x85\x1a\x00\x00");
	
	private static $hc2_frame_cmd_dimmer_off = Array("MESSAGE 81            ",
                                "\x00",
                                "\xd2\x04",
                                "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                                "\x01\x00\x00\x00",
                                "\x01\x00\x00\x00",
                                "\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                                "\x01\x04\x00\x00\x00\x00\x00\x00",
                                "\x01\x00\x00\x00\x00\x00",
                                "\x32\x30\x00",
                                "\x02\x00\x00\x00",
                                "\x01\x00\x00\x00",
                                "<ID>",		// ID
                                "\x00",
                                "\x65\x00\x00\x00",
                                "0",		// Value = 0
                                "\x00\x00\x00\x00\x00\x00\x00\x00\x00",
                                "\x85\x1a\x00\x00");



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
                        fputs($this->hc2_sock,$frame);
                        if ( $this->debugging ) echo "hc2->SendFrame() \n";
                }
                return;
        }


	function DimmerOn($id,$value)   {
		$frame="";
		$f = self::$hc2_frame_cmd_dimmer_on;
		$f[12] = $id;
		$f[15] = $value;
		foreach($f as $c) {
			$frame .= $c;
		}
		$this->SendFrame($frame);
                return;
        }
	function DimmerOff($id)   {
                $frame="";
                $f = self::$hc2_frame_cmd_dimmer_off;
                $f[12] = $id;
                foreach($f as $c) {
                        $frame .= $c;
                }
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



