# Hashcash for PHP/JavaScript forms

Hashcash works by requiring the client to perform a complex calculation before the server will accept its input. Although the calculation may take the client several seconds to process, the server can check the results almost instantly.

The process is easiest to explain using square roots. Given the number 332,929, it takes about 36 steps for a computer to find its square root of 577. However, once the root is found, it only takes one step to test if the result is correct — check to see that 577 * 577 = 332,929. Hashcash doesn’t use square roots for its calculations, but it does work by the same principle.

In a Hashcash transaction, the server generates a string called a stamp that contains encoded information about the client. Specifically, it includes the current time at the server, and the IP address from which the client is accessing the server. The data also includes a salt value (described below), and is encoded using a hash function (in this case, CRC32).

Salt values, commonly used in encryption functions, are used to increase the amount of processing that an attacker would have to expend to "crack" some data. They are simply random data that is added to the information being encrypted to make reversing the process harder.

Without salts, an attacker will know their IP address and can calculate a large number of stamps before launching an attack.

With the addition of the salt value, the attacker would not have the information necessary to calculate correct stamp values, even if they could correctly identify the server time and their own IP address. Thusly, every time the server generates a new stamp, the attacker is forced to start over.

If the same salt value is used in two different implementations, the hash values generated will match between the two. An attacker who saw that two different sites that use this protection generated similar stamps, they would only have to expend effort to bypass one of the sites in order to gain access to both. If, instead, the two sites used different salts, the attacker would have to expend twice the effort to access both sites. With this property in mind, the salt values should be changed to a different random string for each client this script is installed upon.

## Step-by-step

There are two parties to a hashcash transaction, client and server.

*   Step 1: The client requests a web page from the server. It can be any page that submits data with a form, or it can be a page with several forms included within.
*   Step 2: The server calculates a stamp based on the client’s IP address, the current time on the server, and a user-defined salt value. The stamp is then scrambled using the function hc_HashFunc for security, and is embedded invisibly in the form.
*   Step 3a: The client fills out the form and clicks the submit button.
*   Step 3b: Before the client’s computer actually submits the data to the server, it gets the invisible stamp from the page, calculates a collision string and adds the string into the form.
*   Step 4a: Before the server accepts the data from the client — blog comments, auction postings, etc. — it checks to see that there is a stamp on the page.
*   Step 4b: If there is a stamp, the server checks to see that it is valid — it has been posted before the stamp expires, and that the calculated string and the stamp match up.
*   Step 4c: If the stamp check fails or if there is no stamp, the server returns an error, and can either ignore the submitted data, request that the user try again, or present some alternate way for the client to authenticate itself — a captcha or log in mechanism.
*   Step 4d: If the stamp check succeeds, the server accepts the data and processes it as appropriate.

### Hc config file — 4 params

TODO: what is this?

## JAVASCRIPT DOCUMENTATION

hc_HashFunc should be defined in its own script, wherever the function itself is created. It should take one parameter (a string) and return a hex string as its output. In the current implementation I use CRC32, but any hash function that has both a JavaScript and a server-side implementation that return the same results for the same input data will work. MD5 was tested, but was too slow for the client side. Depending on your exact circumstances you may want to use some other function, but the included version should work for most implementations.

    // set form element x to value y
    
    function hc_SetFormData(x, y)
    
    
    // return value of form element x
    
    function hc_GetFormData(x)
    
    
    // convert hex numbers to binary strings
    
    function hc_HexInBin(x)
    
    
    // returns a string of num_bits from hex_string
    
    function hc_ExtractBits(hex_string, num_bits)
    
    
    // checks col_string vs. pg_sbits for pg_contract bits
    
    function hc_CheckContract(pg_contract, pg_sbits, col_string)
    
    
    // generate a random string of length x using [0-9A-Za-z]
    
    function hc_GenChars(x)
    
    
    // extracts data from page to calculate hash collision
    
    function hc_SpendHash()
    

## PHP DOCUMENTATION

    // define generic hash function (currently crc32)
    
    function hc_HashFunc($x) { return sprintf("%08x", crc32($x)); }
    
    
    // convert hex numbers to binary strings
    
    function hc_HexInBin($x)
    
    
    // get the first $num_bits bits of $hex_string
    
    function hc_ExtractBits($hex_string, $num_bits)
    
    
    // generate a stamp based on IP, time and salt
    
    // embeds hc_stamp, hc_contract and hc_collision in the form
    
    function hc_CreateStamp()
    
    
    // hc_CheckExpiration - true = valid, false = expired
    
    function hc_CheckExpiration($a_stamp)
    
    
    // check for collision of $stamp_contract bits for $stamp and $collision
    
    function hc_CheckContract($stamp, $collision, $stamp_contract)
    
    
    // checks validity, expiration, and contract obligations for a stamp
    
    function hc_CheckStamp()
    

## Still ToDo

*   Make hashcash javascript functions run in the background 
    *   As-is, they run only onsubmit, they should take advantage of the time that a user needs to fill out a form. Instead of letting the user fill out a form for 3 minutes and then wait another 10 seconds to calculate the stamp, the JavaScript should start when the page loads and be finished before the user is done with their input.
*   Make hashcash javascript function run on page load 
    *   The interface should be as simple as possible, requiring at most one line per form. If it can be included automatically using something like addLoadEvent, that would be ideal.
*   Complete documentation 
    *   There should be more information on how to implement this script, including a step-by-step tutorial. This should be done last, so that the onload and background functionality described above can be included.

