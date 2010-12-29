<?php
include "hc_config.php";

  //////////////////////
 // helper functions //
//////////////////////

function DEBUG_OUT($x) { echo "<pre>$x</pre>\n"; }

// define generic hash function (currently md5)
function hc_HashFunc($x) { return sprintf("%08x", crc32($x)); }

// convert hex numbers to decimal
function hc_HexInDec($x)
{
	if(($x >= "0") && ($x <= "9"))
		$ret = $x * 1;
	else
		switch($x)
	{
		case "A": $ret = 10;
		case "B": $ret = 11;
		case "C": $ret = 12;
		case "D": $ret = 13;
		case "E": $ret = 14;
		case "F": $ret = 15;
		default: $ret = 0;
	}
}

// convert hex numbers to binary strings
function hc_HexInBin($x)
{
	switch($x)
	{
		case '0': $ret = '0000'; break;
		case '1': $ret = '0001'; break;
		case '2': $ret = '0010'; break;
		case '3': $ret = '0011'; break;
		case '4': $ret = '0100'; break;
		case '5': $ret = '0101'; break;
		case '6': $ret = '0110'; break;
		case '7': $ret = '0111'; break;
		case '8': $ret = '1000'; break;
		case '9': $ret = '1001'; break;
		case 'A': $ret = '1010'; break;
		case 'B': $ret = '1011'; break;
		case 'C': $ret = '1100'; break;
		case 'D': $ret = '1101'; break;
		case 'E': $ret = '1110'; break;
		case 'F': $ret = '1111'; break;
		default: $ret = '0000';
	}
//	DEBUG_OUT("ret = " . $ret);
	return $ret;
}

function hc_ExtractBits($hex_string, $num_bits)
{
	$bit_string = "";
	$num_chars = ceil($num_bits / 4);
	for($i = 0; $i < $num_chars; $i++)
		$bit_string .= hc_HexInBin(substr($hex_string, $i, 1));

//	DEBUG_OUT("requested $num_bits from $hex_string, returned $bit_string as " . substr($bit_string, 0, $num_bits));
	return substr($bit_string, 0, $num_bits);
}



  /////////////////////////////
 // stamp creation function //
/////////////////////////////

// generate a stamp
function hc_CreateStamp()
{
	global $hc_salt, $hc_contract, $hc_maxcoll;
	$ip = $_SERVER['REMOTE_ADDR'];
	$now = intval(time() / 60);

	// create stamp
	// stamp = hash of time (in minutes) . user ip . salt value
	$stamp = hc_HashFunc($now . $ip . $hc_salt);

	//embed stamp in page
	echo "<input type=\"hidden\" name=\"hc_stamp\" id=\"hc_stamp\" value=\"" . $stamp . "\" />\n";
	echo "<input type=\"hidden\" name=\"hc_contract\" id=\"hc_contract\" value=\"" . $hc_contract . "\" />\n";
	echo "<input type=\"hidden\" name=\"hc_collision\" id=\"hc_collision\" value=\"" . $hc_maxcoll . "\" />\n";
}


  //////////////////////////////
 // stamp checking functions //
//////////////////////////////

// hc_CheckExpiration - true = valid, false = expired
function hc_CheckExpiration($a_stamp)
{
	global $hc_salt, $hc_tolerance;

	$expired = true;
	$tempnow = intval(time() / 60);
	$ip = $_SERVER['REMOTE_ADDR'];

	// gen hashes for $tempnow ... $tempnow - $tolerance
	for($i = 0; $i < $hc_tolerance; $i++)
	{
//		DEBUG_OUT("checking $a_stamp versus " . hc_HashFunc(($tempnow - $i) . $ip . $hc_salt));
		if($a_stamp === hc_HashFunc(($tempnow - $i) . $ip . $hc_salt))
		{
//			DEBUG_OUT("stamp matched at T-Minus-" . $i);
			$expired = false;
			break;
		}
	}

	return !($expired);
}

// check for collision of $stamp_contract bits for $stamp and $collision
function hc_CheckContract($stamp, $collision, $stamp_contract)
{
	if($stamp_contract >= 32)
		return false;

	// get hash of $collision to compare to $stamp
	$maybe_sum = hc_HashFunc($collision);
//	DEBUG_OUT("checking contract of $stamp versus $maybe_sum for $stamp_contract bits");

	$partone = hc_ExtractBits($stamp, $stamp_contract);
	$parttwo = hc_ExtractBits($maybe_sum, $stamp_contract);
//	DEBUG_OUT("checking $stamp_contract bits for $partone versus $parttwo");

	return (strcmp($partone, $parttwo) == 0);
}

// check a stamp
// checks validity, expiration, and contract obligations for a stamp
function hc_CheckStamp()
{
	global $hc_contract, $hc_maxcoll, $hc_stampsize;
	$validstamp = true;

// used for debugging
/*
	echo "<pre>";
	print_r($_POST);
	echo "</pre>";
*/
	// get stamp from input
	// todo: use is_notnull?
	$stamp = $_POST['hc_stamp'];
	$client_con = $_POST['hc_contract'];
	$collision = $_POST['hc_collision'];

//	DEBUG_OUT("got variables!");
//	DEBUG_OUT("stamp: $stamp");
//	DEBUG_OUT("hc_contract: $client_con");
//	DEBUG_OUT("collision text: $collision");

//	DEBUG_OUT("before all checks, valid stamp is $validstamp");


	// optimized, fastest-test-first order

	if($client_con != $hc_contract) $validstamp = false;                   // valid contract?
//	DEBUG_OUT("contract comparison: $client_con and $hc_contract : $validstamp");

	if($validstamp) if(strlen($stamp) != $hc_stampsize) $validstamp = false;       // valid stamp?
//	DEBUG_OUT("stamp size: " . strlen($stamp) . " and $hc_stampsize : $validstamp");

	if($validstamp) if(strlen($collision) > $hc_maxcoll) $validstamp = false;    // valid collision?
//	DEBUG_OUT("collision size " . strlen($collision) . " <= $hc_maxcoll : $validstamp");

	if($validstamp) $validstamp = hc_CheckExpiration($stamp);           // stamp expired?
//	DEBUG_OUT("checked expiration: $validstamp");


	if($validstamp) $validstamp = hc_CheckContract($stamp, $collision, $contract); // collision meets contract?
//	DEBUG_OUT("FINAL checked contract: $validstamp");

	return $validstamp;
}
?>