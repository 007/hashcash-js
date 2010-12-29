
// hash function abstraction:
// always call hc_HashFunc, and the behind-the-scenes
// hash function used can be changed easily
// currently crc32 for speed/convenience,
// needs to be defined in its native script
// include the hash script before this one
// make sure your hash function returns a hex string
// case does not matter, upper or lower for A..F

var g_ProcessFlag = 0;

var g_PageContract = 0;
var g_GenSize = 0;

var g_Col_Hash = '';
var g_PSBits = '';


function hc_SetFormData(x, y)
{
	var z = document.getElementById(x);
	if(z) z.value = y;
}

function hc_GetFormData(x)
{
	var z = document.getElementById(x);
	if(z)
		return z.value;
	else
		return '';
}

// convert hex numbers to binary strings
function hc_HexInBin(x)
{
	var ret = '';
	switch(x.toUpperCase())
	{	case '0': ret = '0000'; break; case '1': ret = '0001'; break;
		case '2': ret = '0010'; break; case '3': ret = '0011'; break;
		case '4': ret = '0100'; break; case '5': ret = '0101'; break;
		case '6': ret = '0110'; break; case '7': ret = '0111'; break;
		case '8': ret = '1000'; break; case '9': ret = '1001'; break;
		case 'A': ret = '1010'; break; case 'B': ret = '1011'; break;
		case 'C': ret = '1100'; break; case 'D': ret = '1101'; break;
		case 'E': ret = '1110'; break; case 'F': ret = '1111'; break;
		default : ret = '0000'; }
	return ret;
}

function hc_ExtractBits(hex_string, num_bits)
{
	var bit_string = "";
	var num_chars = Math.ceil(num_bits / 4);
	for(var i = 0; i < num_chars; i++)
		bit_string = bit_string + "" + hc_HexInBin(hex_string.charAt(i));

	bit_string = bit_string.substr(0, num_bits);
	return bit_string;
}

function hc_CheckContract(pg_contract, pg_sbits, col_string)
{
	// check that pContract bits of pStamp and cHash match

	var col_hash = hc_HashFunc(col_string);
	var check_bits = hc_ExtractBits(col_hash, pg_contract);
	return (check_bits == pg_sbits);
}

function hc_GenChars(x)
{
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	var randomstring = '';
	for (var i = 0; i < x; i++)
		randomstring += chars.substr(Math.floor(Math.random() * chars.length), 1);
	return randomstring;
}

function hc_SpendHash()
{
	if(g_ProcessFlag == 0)
	{
		//	testdat = random_chars(8)
		var Collision = hc_GenChars(g_GenSize);

		var looper = 1;

		while(!hc_CheckContract(g_PageContract, g_PSBits, Collision))
		{
			Collision = hc_GenChars(g_GenSize);
			looper++;
		}

		hc_SetFormData('hc_collision', Collision);
	}

	return true;
}

function hc_SetupTimeout()
{
	alert("we got the setup timed out!");
	//	retrieve stamp
	//	retrieve contract
	var PageStamp = hc_GetFormData('hc_stamp');
	g_PageContract = hc_GetFormData('hc_contract');
	g_GenSize = hc_GetFormData('hc_collision');
	
	g_PSBits = hc_ExtractBits(PageStamp, g_PageContract);

	if(!(g_GenSize > 1)) g_GenSize = 8;
	
	setTimeout("hc_TimeoutHash()",10);
}

function hc_TimeoutHash()
{

	//	testdat = random_chars(8)
	var Collision = hc_GenChars(g_GenSize);

	if(!hc_CheckContract(g_PageContract, g_PSBits, Collision))
		setTimeout("hc_TimeoutHash()",10);
	else
	{
		alert("found a value!");
		hc_SetFormData('hc_collision', Collision);
		g_ProcessFlag = 1;
	}
}

function addLoadEvent(func)
{
	var oldonload = window.onload;
	if(typeof window.onload != 'function')
	{
		window.onload = func;
	}
	else
	{
		window.onload = function()
		{
			oldonload();
			func();
		}
	}
}

function addLoadEventParm(func, parm)
{
	var oldonload = window.onload;
	if(typeof window.onload != 'function')
	{
		window.onload = function() { func(parm); };
	}
	else
	{
		window.onload = function()
		{
			oldonload();
			func(parm);
		}
	}
}

addLoadEvent(hc_SetupTimeout);
// addLoadEventParm(hc_SetupTimeout, parm);