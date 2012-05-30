<?php



require_once 'std.table.class.inc';
class PaInvoiceByPOandInv extends Default_Table
{

	var $tablename = 'pa_invoice';         // table name
	var $dbname = 'ETL_Lookup';           // database name

    // additional class variables go here
    function PaInvoiceByPOandInv ()
    {
        // $this->tablename       = 'pa_invoice';
        //  $this->dbname          = 'ETL_Lookup';
        $this->rows_per_page   = 15;
        $this->fieldlist       = array("xqty", "IF(mhind = 'C', SUM(xprice) * -1, SUM(xprice) )  as Cost", "xmpline", "xline", "xbran", "xfreight", "IF(mhind = 'C', SUM(xcore) * -1, SUM(xcore)) as 'core cost'", "xord", "mhind", "xdate", "IF(mhind = 'C', SUM(xinvtotal) * -1, SUM(xinvtotal) ) as Total", "xinv", "xourpo", "xsku" );

         //$this->fieldlist['column1'] = array('pkey' => 'y');
        //et cetera ...
				
    } // end class constructor


	//function getOrderByOrderId()




} // end class












?>