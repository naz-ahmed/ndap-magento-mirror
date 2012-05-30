<?php



class Default_Table
{
	var $tablename;         // table name
	var $dbname;            // database name
	var $rows_per_page;     // used in pagination
	var $pageno;            // current page number
	var $lastpage;          // highest page number
	var $fieldlist;         // list of fields in this table
	var $data_array;        // data from the database
	var $errors;            // array of error messages
	
	var $download_filename;             // file to be downloaded
	var $download_mode;                 // 'inline' or null
	
	var $delete_count;                  // count of records deleted
	var $insert_count;                  // count of records inserted
	var $update_count;                  // count of records updated
	var $unchange_count;                // count of records unchanged
	
  
	// the following are used to construct SQL queries
	var $default_orderby = null;        // default for table, may be overridden by $sql_orderby
	var $default_orderby_task = null;   // default for task, may be overridden by $sql_orderby
	var $sql_from;
	var $sql_groupby;
	var $sql_having;
	var $sql_no_foreign_db = false;     // if TRUE _sqlProcessJoin() method will skip tables in other databases
	var $sql_orderby;                   // sort field
	var $sql_orderby_seq;               // 'asc' or 'desc'
	var $sql_orderby_table;             // tablename qualifier for optional sort criteria
	var $sql_search;                    // optional search criteria from a search screen (modifiable)
	var $sql_search_orig;               // original search criteria (unmodified)
	var $sql_search_table;              // tablename qualifier for optional search criteria
	var $sql_select;                    // fields to be selected
	var $sql_selection;                 // selection passed down from previous task
	var $sql_union;                     // optional UNION clause
	var $sql_where;                     // additional selection criteria
	var $where;                         // passed from parent form
  	var $orderby;					 // passed from parent form
  	var $group;
  
	function Default_Table ()
	{
		$this->tablename       = 'default';
		$this->dbname          = 'default';
		$this->rows_per_page   = 10;
		
		$this->fieldlist = array('column1', 'column2', 'column3');
		$this->fieldlist['column1'] = array('pkey' => 'y');
	} 
  
	function getData ($where=null, $groupby=null, $orderby=null)
	{
		 $this->data_array = array();
	      $pageno          = $this->pageno;
	      $rows_per_page   = $this->rows_per_page;
	      $this->numrows   = 0;
	      $this->lastpage  = 0;
		
		global $dbconnect, $query;
      	$dbconnect = db_connect($this->dbname) or trigger_error("SQL", E_USER_ERROR);
      	
      	//formats for $where
      	/*
      	column1='value'
		column1='value' AND column2='value'
		(column1='value' AND column2='value') OR (column1='value' AND column2='value')
		*/
		
		if (empty($where)) 
		{
			$where_str = NULL;
		} 
		else 
		{
		   	$where_str = "WHERE $where";
		} // if
		
		//count the number of rows which satisfy the current selection criteria
		$query = "SELECT count(*) FROM $this->tablename $where_str";
		$result = mysql_query($query, $dbconnect) or trigger_error("SQL", E_USER_ERROR);
		$query_data = mysql_fetch_row($result);
		$this->numrows = $query_data[0];
		
		//if there are no rows, exit
		if ($this->numrows <= 0) 
		{
			$this->pageno = 0;
			return;
      	} // if
      	
      	//removing the paging logic for now
      	
      	//calculate how many pages it'll take to display
		if ($rows_per_page > 0) 
		{
			$this->lastpage = ceil($this->numrows/$rows_per_page);
		} 
		else 
		{
			$this->lastpage = 1;
		} // if
      
      	//make sure requested page number is in range
      	if ($pageno == '' OR $pageno <= '1') 
      	{
         		$pageno = 1;
      	} 
      	elseif ($pageno > $this->lastpage) 
      	{
         		$pageno = $this->lastpage;
      	} // if
      	$this->pageno = $pageno;
      	
      	//construct the LIMIT clause to retrieve only those rows which fall within the specified page number
      	if ($rows_per_page > 0) 
      	{
         		$limit_str = 'LIMIT ' .($pageno - 1) * $rows_per_page .',' .$rows_per_page;
      	} else 
      	{
         		$limit_str = NULL;
      	} // if
      	
      	if(empty($groupby))
      	{
      		$groupby_str = NULL;
      	}
      	else
      	{
      		$groupby_str = ' GROUP BY '.$groupby;
      	}
      	
      	if(empty($orderby))
      	{
      		$orderby_str = NULL;
      		echo "foo";
      	}
      	else
      	{
      		$orderby_str = ' ORDER BY '.$orderby;
      	}
      	
      	
      	//build query string and run it
      	//$query = "SELECT * FROM $this->tablename $where_str $limit_str";
      	//$query = "SELECT ".implode(",",$this->fieldlist)." FROM $this->tablename $where_str "."GROUP BY xline, mhind, xourpo, xsku, xmpline, xinv $orderby_str";
      	$query = "SELECT ".implode(",",$this->fieldlist)." FROM $this->tablename $where_str $groupby_str $orderby_str";
      	echo $query; 
      	echo "\r\n".$groupby_str;
      	$result = mysql_query($query, $dbconnect) or trigger_error("SQL", E_USER_ERROR);
      	
      	//dump data into assoc array
      	while ($row = mysql_fetch_assoc($result)) 
      	{
         		$this->data_array[] = $row;
      	} // while
      	
      	mysql_free_result($result);
   
      	return $this->data_array;
      	
	} // constructor
	
	

	
	function getErrors ()
	// return array of error messages
	{
		$errors = $this->errors;
		$this->errors = array();
		
		if (!is_array($errors)) {
		  // convert string into an array
		  $errors = (array)$errors;
		} // if
		
		return $errors;
	
	} // getErrors
  
  
	function insertRecord ($fieldarray)
   	{
     	$this->errors = array();
     	
     	global $dbconnect, $query;
      	$dbconnect = db_connect($this->dbname) or trigger_error("SQL", E_USER_ERROR);
      
      	$fieldlist = $this->fieldlist;
		foreach ($fieldarray as $field => $fieldvalue) 
		{
			if (!in_array($field, $fieldlist)) 
			{
		  		unset ($fieldarray[$field]);
			} // if
		}
		
		$query = "INSERT INTO $this->tablename SET ";
		foreach ($fieldarray as $item => $value) 
		{
			$query .= "$item='$value', ";
		} // foreach
		
		 $query = rtrim($query, ', ');
		 
		 $result = @mysql_query($query, $dbconnect);
      
		if (mysql_errno() <> 0) 
		{
			if (mysql_errno() == 1062) 
			{
				$this->errors[] = "A record already exists with this ID.";
			} 
			else 
			{
				trigger_error("SQL", E_USER_ERROR);
			} // if
		} // ifx_affected_rows()
      
      	return;
      
      }// insertRecord
      
	function updateRecord ($fieldarray)
	{
		$this->errors = array();
		
		 global $dbconnect, $query;
      	$dbconnect = db_connect($this->dbname) or trigger_error("SQL", E_USER_ERROR);
      	
		$fieldlist = $this->fieldlist;
		foreach ($fieldarray as $field => $fieldvalue) 
		{
			if (!in_array($field, $fieldlist)) 
			{
				unset ($fieldarray[$field]);
			} // if
		} // foreach
		
		$where  = NULL;
		 $update = NULL;
		 foreach ($fieldarray as $item => $value) 
		 {
		    if (isset($fieldlist[$item]['pkey'])) 
		    {
		       $where .= "$item='$value' AND ";
		    } else 
		    {
		       $update .= "$item='$value', ";
		    } // if
		 } // foreach
		 
		 $where  = rtrim($where, ' AND ');
      	$update = rtrim($update, ', ');
      	
      	$query = "UPDATE $this->tablename SET $update WHERE $where";
      	$result = mysql_query($query, $dbconnect) or trigger_error("SQL", E_USER_ERROR);
      
      	return;
		
	}//updateRecord
	
	
	function deleteRecord ($fieldarray)
	{
		$this->errors = array();
		global $dbconnect, $query;
      	$dbconnect = db_connect($this->dbname) or trigger_error("SQL", E_USER_ERROR);
      	
      	$fieldlist = $this->fieldlist;
		$where  = NULL;
		foreach ($fieldarray as $item => $value) 
		{
			if (isset($fieldlist[$item]['pkey'])) 
			{
		  		$where .= "$item='$value' AND ";
			} // if
		} // foreach
		
		 $where  = rtrim($where, ' AND ');
		 $query = "DELETE FROM $this->tablename WHERE $where";
		 $result = mysql_query($query, $dbconnect) or trigger_error("SQL", E_USER_ERROR);
		return;
		
	}//deleteRecord

  
}













?>

