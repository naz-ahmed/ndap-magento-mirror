<?php

/*
$string = "hello world";

echo preg_match('/hello/', $test_string); 

*/

$pattern = '/[^.]+\.[^.]+$/';

// get host name from URL
preg_match('@^(?:http://)?([^/]+)@i',
    "http://www.php.net/index.html", $matches);
$host = $matches[1];

// get last two segments of host name
preg_match($pattern, $host, $matches);
echo "domain name is: {$matches[0]}\n";





//fedex 15 characters
$fedex1 = '/^[0-9]{15}$/';

$num = "439230031461237";

preg_match($fedex1, $num, $matches );
echo "matched: : {$matches[0]}\n";


//fedex 2day
$fedex2 = '/^[0-9]{12}$/';

$num= "479316245275";

preg_match($fedex2, $num, $matches );
echo "matched: : {$matches[0]}\n";


$fedex = '/^[0-9]{12}?$|^[0-9]{15}$/';
//$num= "479316245275";
$num = "439230031461237";
$num = "230014970309444";

preg_match($fedex, $num, $matches );
echo "fedex matched: : {$matches[0]}\n";

$usps1 = '/^[0-9]{22}$/';

$num = "9405510200882395310676";



preg_match($usps1, $num, $matches );
echo "matched: : {$matches[0]}\n";

$usps2 = '/^CJ[0-9]{9}US/';  //priority mail international
$num = "CJ221020023US";

preg_match($usps2, $num, $matches );
echo "matched: : {$matches[0]}\n";

//express mail international

//$usps3 = '/^(EC|CJ)[0-9]{9}US/';
$usps3 = '/^EC[0-9]{9}US?$|^CJ[0-9]{9}US/';
$usps4 = '/^(EA|EC|CP|RA|CJ)[0-9]{9}[A-Z]{2}?$|^94[0-9]{20}/';
$num = "EC691036825US";
//$num = "9405510200882395310676";

preg_match($usps4, $num, $matches );
echo "usps4 matched: : {$matches[0]}\n";


//ooh, we get UPS
$ups1 = '/[0-9A-Z]{2} ?[0-9A-Z]{4} ?[0-9A-Z]{3} ?[0-9A-Z]{8}/';

$num = "1Z6R599Y0398743361";
$num = "1Z766Y250344671182";
//$num = "EC691036825US"; /// this is actually usps - doesn't match :)

preg_match($ups1, $num, $matches );
echo "matched: : {$matches[0]}\n";





function find_carrier($track)
{
	$ups = '/[0-9A-Z]{2} ?[0-9A-Z]{4} ?[0-9A-Z]{3} ?[0-9A-Z]{8}/';
	$usps = '/^(EA|EC|CP|RA|CJ)[0-9]{9}[A-Z]{2}?$|^94[0-9]{20}/';
	$fedex = '/^[0-9]{12}?$|^[0-9]{15}$/';
	$carrier = "";

	if(preg_match($usps,$track))
	{
		$carrier = "usps";
	}
	else if(preg_match($ups,$track))
	{
		$carrier = "ups";
	}
	else if(preg_match($fedex,$track))
	{
		$carrier = "fedex";
	}

	return $carrier;
}


echo "carrier is ".find_carrier('EC691036825US')."\n";  //usps
echo "carrier is ".find_carrier('CJ221020023US')."\n";  //usps
echo "carrier is ".find_carrier('1Z6R599Y0398743361')."\n";   //ups
echo "carrier is ".find_carrier('439230031461237')."\n";   //fedex
echo "carrier is ".find_carrier('230014970309444')."\n";







?>