<?php      ///var/www/html/magento/fitter                                       
/* PHP notices throw, bloating error log 
2012-05-01T21:21:58+00:00 ERR (3): Notice: Undefined index: func  in /var/www/html/magento/fitter/dropdown.php on line 25
2012-05-01T21:21:58+00:00 ERR (3): Notice: Undefined index: func  in /var/www/html/magento/fitter/dropdown.php on line 72
*/
  //**************************************
  
//     Page load dropdown results     //
//**************************************
//include_once('dbconnect.php');
$inApp = "yessir!";
include_once('dbconnect.php');
function getTierOne()
{
    $result = mysql_query("SELECT * FROM elite_level_year order by title DESC")
    or die(mysql_error());
      while($tier = mysql_fetch_array( $result ))
        {
           echo '<option value="'.$tier['id'].'">'.$tier['title'].'</option>';
        }
}

function drop_1($drop_var)
{

    if (!is_numeric ($drop_var)){exit;}  

    include_once('dbconnect.php');
 
    $result = mysql_query("SELECT * FROM elite_level_make WHERE year_id=$drop_var ORDER BY title ASC") // asc / desc
    or die(mysql_error());
    echo '<select name="make" id="drop_2">
          <option value=" " disabled="disabled" selected="selected">Select Make</option>';
           while($drop_2 = mysql_fetch_array( $result ))
            {
              echo '<option value="'.$drop_2['id'].'">'.$drop_2['title'].'</option>';
            }
    echo '</select>';
    echo "<script type=\"text/javascript\">
          jQuery('#cpkBtn').hide('fast');
          jQuery('#wait_2').show();
          jQuery('#result_2').hide();
          
          jQuery('#drop_2').change(function(){
                                         jQuery('#cpkBtn').hide('fast');
                                         jQuery('#wait_2').show();
                                         jQuery('#result_2').hide();
                                         jQuery.get(\"/fitter/dropdown.php\", {
                                                                    func: \"drop_2\",
                                                                    drop_var: jQuery('#drop_2').val(),
                                                                    drop_var2: jQuery('#drop_1').val()
                                                                                            }, function(response){
                                                                                                                    jQuery('#result_2').fadeOut();
                                                                                                                    setTimeout(\"finishAjax2('result_2', '\"+escape(response)+\"')\", 400);
                                         
                                                                                                                  });
                                         return false;
                                         });
                                        

</script>";  
}

function drop_2($drop_var)
{
    if (!is_numeric ($drop_var)){exit;}
    
    include_once('dbconnect.php');
    $result = mysql_query("SELECT * FROM elite_level_model WHERE make_id=$drop_var ORDER BY title asc")
    or die(mysql_error());
    echo '<select name="model" id="drop_3">
          <option value=" " disabled="disabled" selected="selected">Select Model</option>';
           while($drop_3 = mysql_fetch_array( $result ))
            {
              echo '<option value="'.$drop_3['id'].'">'.$drop_3['title'].'</option>';
            }
    echo '</select>';
    
        echo "<script type=\"text/javascript\"> 
        
       
        jQuery('#drop_3').change(function() {
            
            jQuery('#cpkBtn').show('fast');
            });
            
        </script>"; 
}

function getTierOne_footer($pickedYear,$pickedYearName)
{
    $result = mysql_query("SELECT * FROM elite_level_year order by title DESC")
    or die(mysql_error());
    
    
            
      while($tier = mysql_fetch_array( $result ))
        { 
            if ( $tier['id'] == $pickedYear){
                echo '<option  value="'.$pickedYear.'" selected="selected">'.$pickedYearName.'</option>'; 
                }else{
                        echo '<option value="'.$tier['id'].'">'.$tier['title'].'</option>';
                }
        }
}

function drop_1_footer($pickedYear,$pickedMake,$pickedMakeName)
{

    if (!is_numeric ($pickedYear)){exit;}  

    include_once('dbconnect.php');
 
    $result = mysql_query("SELECT * FROM elite_level_make WHERE year_id=$pickedYear ORDER BY title ASC") // asc / desc
    or die(mysql_error());
 
           while($drop_2 = mysql_fetch_array( $result ))
            {
              if ( $drop_2['id'] == $pickedMake){
                echo '<option  value="'.$pickedMake.'" selected="selected">'.$pickedMakeName.'</option>'; 
                }else{
                        echo '<option value="'.$drop_2['id'].'">'.$drop_2['title'].'</option>';
                }
            }
    echo '</select>';
    echo "<script type=\"text/javascript\">
          jQuery('#drop_2').change(function(){
                                         jQuery('#cpkBtn').hide('fast');
                                         jQuery('#wait_2').show();
                                         jQuery('#result_2').hide();
                                         jQuery.get(\"/fitter/dropdown.php\", {
                                                                    func: \"drop_2\",
                                                                    drop_var: jQuery('#drop_2').val(),
                                                                    drop_var2: jQuery('#drop_1').val()
                                                                                            }, function(response){
                                                                                                                    jQuery('#result_2').fadeOut();
                                                                                                                    setTimeout(\"finishAjax2('result_2', '\"+escape(response)+\"')\", 400);
                                         
                                                                                                                  });
                                         return false;
                                         });
                                        

</script>";  
}

function drop_2_footer($pickedMake,$pickedModel,$pickedModelName)
{
    if (!is_numeric ($pickedMake)){exit;}
    
    include_once('dbconnect.php');
    $result = mysql_query("SELECT * FROM elite_level_model WHERE make_id=$pickedMake ORDER BY title asc")
    or die(mysql_error());
           while($drop_3 = mysql_fetch_array( $result ))
            {
            if ( $drop_3['id'] == $pickedModel){
                echo '<option  value="'.$pickedModel.'" selected="selected">'.$pickedModelName.'</option>'; 
                }else{
                        echo '<option value="'.$drop_3['id'].'">'.$drop_3['title'].'</option>';
                }
            }

}

//**************************************
//     First selection results     //
//**************************************

if (isset($_GET['func'])){ 
    if($_GET['func'] == "drop_1") {
       drop_1($_GET['drop_var']);
       }


//**************************************
//     Second selection results     //
//**************************************
//$result = mysql_query("SELECT * FROM elite_level_model WHERE make_id=$drop_var ORDER BY title desc")    

    if($_GET['func'] == "drop_2") {
       drop_2($_GET['drop_var']);
       }
}

                                                               
