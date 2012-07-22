<?php



function verifyX500Cookie() {
	
	$X500HOSTNAME = "x500.umn.edu";  // The x500 server to connect to
	$X500PORT = 87;
	$OPENSSL = "openssl s_client -connect ".$X500HOSTNAME.":".$X500PORT." -quiet";
	$OPENSSL = "openssl s_client -connect ".$X500HOSTNAME.":".$X500PORT."";
	$thisIP = $_SERVER["REMOTE_ADDR"];
	$x500ReturnArray = array();
	global $_COOKIE;
	$cookie = $_COOKIE["umnAuthV2"];

	$fp = stream_socket_client ("ssl://".$X500HOSTNAME.":".$X500PORT, $errno, $errstr, 30);

  if (!$fp) {
      echo "$errstr ($errno)<br>\n";
  } else {
      $buffer = '';
      if (stream_set_blocking($fp, TRUE) ) {
          fwrite ($fp, "WEBCOOKIE\t".$cookie."\n");
          while (!feof($fp)) {
              $buffer .= @fgets($fp);
          }
      }
      fclose ($fp);
	}
	$cookieBits = mb_split("\|", $buffer);

	if(stristr($cookieBits[0], "OK")) {
		$returnArray["status"] = true;
		$returnArray["user"] = $cookieBits[3];
	}
	else {
		$returnArray["status"] = false;
	}

	return $returnArray;

	
	
}




?>