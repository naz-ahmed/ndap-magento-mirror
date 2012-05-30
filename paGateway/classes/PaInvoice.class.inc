<?php



require_once 'std.table.class.inc';
class PaInvoice extends Default_Table
{

	var $tablename = 'pa_invoice';         // table name
	var $dbname = 'ETL_Lookup';           // database name

    // additional class variables go here
    function PaInvoice ()
    {
        // $this->tablename       = 'pa_invoice';
        //  $this->dbname          = 'ETL_Lookup';
        $this->rows_per_page   = 15;
        $this->fieldlist       = array("xqty", "xprice", "xmpline", "xline", "xbran", "xfreight", "xcore", "xord", "mhind", "xdate", "xinvtotal", "xinv", "xourpo", "xsku" );
         //$this->fieldlist['column1'] = array('pkey' => 'y');
        //et cetera ...
				
    } // end class constructor


	//function getOrderByOrderId()


} // end class












?>