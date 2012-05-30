<?php
class invoiceReport{
    function invoiceReport(){}
    function setUser($userName,$password){$this->userName=$userName;$this->password=$password;}
    function setAction($x){$this->action=$x;}
    function setStartDate($x){$this->startDate=$x;}
    function setEndDate($x){$this->endDate=$x;}
    function sendRequest()
    {
        $req = array(
               'userName'=>$this->userName,
               'userPass'=>$this->password,
               'action'=>$this->action,
               'startDate'=>$this->startDate,
               'endDate'=>$this->endDate
               );
        $handle = fopen("http://eorders.panetny.com/api/invoiceReport.psp?reqData=".urlencode(json_encode($req)), "r");
        $contents='';
        while (!feof($handle)) {$contents .= fread($handle, 8192);}
        fclose($handle);
        return $contents;
    }
}
?>

