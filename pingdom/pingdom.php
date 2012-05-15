<?PHP
/**
 * pingdom.php
 *
 * This application will check your server swap, hard drive, cpu, and MySQL conditions.
 * It will then generate an appropriate XML file for a Pingdom HTTP Custom check.
 *
 * If any usage is above your preset thresholds, then a down message will be returned,
 * indicating that your server may be under more load than usual, hopefully, providing
 * a bit of advanced notice before a true failure due to lack of resources
 *
 * @author Jon Stacey
 * @version 1.1
 * @website http://jonsview.com
 * 
 **/

// *******************************
// Configure thresholds
// *******************************

$SWAP_THRESHOLD = 256; 			// The amount of swap usage (mb)
$HD_THRESHOLD 	= 90; 			// The percentage of space used
$HD_MOUNT 		= array('/dev/xvda1', '/dev/xvdf ', '/dev/xvdg ', '/dev/xvdh');	// The filesystem mount to check
$CPU_THRESHOLD 	= 4.00;			// The 5 minute CPU load average threshold

// *******************************
// MySQL server configuration
// *******************************

$DB_HOST 	 = 'ndap01.c2lug8itjgui.us-east-1.rds.amazonaws.com';	// Database host
$DB_USERNAME = 'magento'; 	// Database username
$DB_PASSWORD = 'C3HKMVYD7DQJsSVY';	// Database password

// End configurations. You probably won't need to make any changes beyond this point.

// Get start time for the execution timer
// Execution time code from http://www.developerfusion.com
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;


$status = 'OK'; // Assume OK unless something is over the threshold

// *******************************
// Swap Usage
// *******************************

$swapUsage = `free -m |grep Swap|perl -pe 's/Swap:\s+\S+\s+(\S+).*/$1/'`;

if ($swapUsage > $SWAP_THRESHOLD)
	$status = 'SWAP OVER THRESHOLD';

// *******************************
// Filesystem usage
// *******************************

$i=0;
foreach ($HD_MOUNT as $hdUsage){
$hdUsage = `df -h | grep '$hdUsage' | awk '{print $5}' | perl -pe 's/%//'`;

if ($hdUsage > $HD_THRESHOLD)
	$status = 'FILESYSTEM '.$HD_MOUNT[$i].' OVER THRESHOLD';
	$i++;
}

// *******************************
// CPU usage
// *******************************

$cpuUsage = `uptime | awk '{print $10}' | perl -pe 's/,//'`;

if ($cpuUsage > $CPU_THRESHOLD)
	$status = 'CPU OVER THRESHOLD';
	
// *******************************
// MySQL
// *******************************
	
// Check MySQL using the provided connection information
if (!$rh = mysql_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD))
  $status  = 'MYSQL DOWN';

// *******************************
// Return XML response for Pingdom
// *******************************
// If this script responds then obviously HTTP services is obviously working ... so we can just return the status of MySQL
// I'll just print the XML directly instead of going through the hassle of learning PHP's XML functions

// Get end time and calculate execution time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = round(($endtime - $starttime) * 1000, 3); // Time in milliseconds

// Print the XML response
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>

<pingdom_http_custom_check>
	<status>$status</status>
	<response_time>$totaltime</response_time>
</pingdom_http_custom_check>"


?>
