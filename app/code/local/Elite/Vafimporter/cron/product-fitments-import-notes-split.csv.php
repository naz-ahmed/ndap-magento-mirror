<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

echo "Starting Vehicle Fits Split CSV Product Fitments Import \n";

echo "Spliting product-fitments-import-notes.csv.new into smaller files \n";
exec('split -l 1000000 product-fitments-import-notes.csv.new');

echo "Insering a CSV header into the smaller files \n";
exec("sed -i '1i sku,year,make,model,note_message' x*");

foreach (glob("x*") as $filename) {
	rename("$filename", "product-fitments-import.csv");
	echo "Renamed $filename to product-fitments-import.csv \n";
	
	echo "Starting import of $filename \n";
	exec ("php product-fitments-import.csv.php > error_log");
    echo "Finished import of $filename \n";
}
echo "Finished Vehicle Fits Split CSV Product Fitments Import \n";
?>
