<?php
$time_start = microtime(true);

mysql_connect('ndap01.c2lug8itjgui.us-east-1.rds.amazonaws.com', 'magento', 'C3HKMVYD7DQJsSVY') or die(mysql_error());
mysql_select_db('ETL_Data');

//select all linecodes
$sqlLinecodeIds = "SELECT LineCodeId FROM linecodes";
//DEBUG
// $sqlLinecodeIds .= " WHERE LineCodeId in (49,53)"; 

$lc_result = mysql_query($sqlLinecodeIds) or die(mysql_error());


while($lrow = mysql_fetch_assoc($lc_result))
{
	$lineCodeId = $lrow["LineCodeId"];
	
	$query = '
	   INSERT INTO ETL_Data.export_import_notes (sku,year,make,model,submodel,engine,notes) 
	   select 
        sku_m as sku,
        TRIM(bv.YearID) as `year`, TRIM(vcma.MakeName) as make, TRIM(vcmo.ModelName) as model
		, TRIM(sm.submodelName) as submodel 
		, CONCAT (REPLACE( TRIM(eb.BlockType),\'-\',\'\' )
		, REPLACE( TRIM(eb.Cylinders), \'-\',\'\' ), \' \'
		, REPLACE( concat(TRIM(eb.Liter),\'L\'),\'-L\', \'\'),\' \'
		, REPLACE( concat(TRIM(eb.CC),\'cc\'),\'-cc\', \'\') ) as `engine` 
		, GROUP_CONCAT(DISTINCT n.NoteText SEPARATOR \' | \') as note_message
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
	  LEFT JOIN ETL_Data.notes n ON n.FitmentId = f.fitmentId
	   WHERE p.Price > 0
	   AND p.QtyInStock > 0
	   AND p.LineCodeId = '.$lineCodeId.'
	    GROUP BY sku_m, year, make, model, submodel, `engine`
        ';
	$result = mysql_query($query) or die(mysql_error());
	
	/*
	$fp = fopen('product-fitments-import-notes.csv.new', 'w');
	fwrite ($fp, "sku,year,make,model,submodel,engine,notes\n");
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
	    fwrite ($fp, $row['engine']);
	    fwrite ($fp, ",");
	    $chars = array("\"", ",", ";", "/", "\\");
	    $rpl_notes = str_replace($chars, "", $row['notes']);
	    fwrite ($fp, $rpl_notes);
	    fwrite ($fp, "\n");
	}
	*/
}




$time_end = microtime(true);
$time = date("H:i:s",$time_end - $time_start);

echo "Yey! I ran in $time";

?>

