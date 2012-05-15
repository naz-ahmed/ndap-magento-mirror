<?php
class paOrder{
    function paOrder(){$this->PoNumber = '';}
    function setUser($userName,$password){$this->userName=$userName;$this->password=$password;}
    function setPoNumber($PoNumber){$this->PoNumber=$PoNumber;}
    function sendRequest()
    {
        $order = array(
               'userName'=>$this->userName,
               'userPass'=>$this->password,
               'PoNumber'=>$this->PoNumber
               );
        $handle = fopen("http://eorders.panetny.com/api/checkOrderStatus.psp?orderData=".urlencode(json_encode($order)), "r");
        $contents='';
        while (!feof($handle)) {$contents .= fread($handle, 8192);}
        fclose($handle);
        return $contents;
    }
}
?>
