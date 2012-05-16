<?php
$time_start = microtime(true);

mysql_connect('ndap01.c2lug8itjgui.us-east-1.rds.amazonaws.com', 'magento', 'C3HKMVYD7DQJsSVY') or die(mysql_error());
mysql_select_db('ETL_Data');
$query = 'select
        sku_m as sku,
        TRIM(bv.YearID) as `year`, TRIM(vcma.MakeName) as make, TRIM(vcmo.ModelName)
        as model
		, TRIM(sm.submodelName) as submodel 
		, CONCAT (REPLACE( TRIM(eb.BlockType),\'-\',\'\' )
		, REPLACE( TRIM(eb.Cylinders), \'-\',\'\' ), \' \'
		, REPLACE( concat(TRIM(eb.Liter),\'L\'),\'-L\', \'\'),\' \'
		, REPLACE( concat(TRIM(eb.CC),\'cc\'),\'-cc\', \'\') ) as `engine`
        FROM ETL_Data.parts p
        INNER JOIN ETL_Data.fitments f ON f.PartId = p.PartId
        INNER JOIN VCdb.basevehicle bv on bv.BaseVehicleId = f.BaseVehicleId
        INNER JOIN VCdb.make vcma ON vcma.MakeID = bv.MakeID
        INNER JOIN VCdb.model vcmo ON vcmo.ModelID = bv.ModelID
		INNER JOIN VCdb.vehicle v ON bv.BaseVehicleID = v.BaseVehicleID
	   INNER JOIN VCdb.submodel sm ON sm.SubmodelID = v.SubmodelID
	   INNER JOIN VCdb.vehicletoengineconfig vtec ON v.VehicleID = vtec.vehicleId
	   INNER JOIN VCdb.engineconfig ec ON vtec.EngineConfigID = ec.EngineConfigID
	   INNER JOIN VCdb.enginebase eb ON eb.EngineBaseID = ec.EngineBaseID
        GROUP BY sku_m, year, make, model, submodel, `engine`
        ';
$result = mysql_query($query) or die(mysql_error());

$fp = fopen('product-fitments-import.csv.new', 'w');
fwrite ($fp, "sku,year,make,model,submodel,engine\n");
while($row=mysql_fetch_assoc($result))
{
    fwrite ($fp, $row['sku']);
    fwrite ($fp, ",");
    fwrite ($fp, $row['year']);
    fwrite ($fp, ",");
    fwrite ($fp, $row['make']);
    fwrite ($fp, ",");
    fwrite ($fp, $row['model']);
    fwrite ($fp, ",");
    fwrite ($fp, $row['submodel']);
    fwrite ($fp, ",");
    fwrite($fp, $row['engine']);
    fwrite ($fp, "\n");
}

$time_end = microtime(true);
$time = date("H:i:s",$time_end - $time_start);

echo "Yey! I ran in $time";

?>

