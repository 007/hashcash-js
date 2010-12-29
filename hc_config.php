<?php
// user-configurable random string
$hc_salt = "ThIsIsAtEsT";

// number of bits to collide
$hc_contract = 12;

// maximum length of data to hash
// client can generate 1..$maxcoll characters of data
$hc_maxcoll = 8;

// tolerance, in minutes between stamp generation and expiration
// don't make this too high, CheckPostage() has to calculate $tolerance different hashes
$hc_tolerance = 2;

// size of our hash function output
// in hex numbers - 0x12345 is 5, 0xabc is 3
$hc_stampsize = 8;
?>