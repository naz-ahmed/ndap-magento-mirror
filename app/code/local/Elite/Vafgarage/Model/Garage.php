<?php     //var/www/html/magento_staging/app/code/local/Elite/Vafgarage/Model



class Elite_Vafgarage_Model_Garage {

    protected $vehicles = array();

    function addVehicle($vehicle) {
        if ($this->hasVehicle($vehicle)) {        
            //return false;
            foreach ($this->vehicles() as $key => $eachVehicle) {   //we did this to remove all other vehicles when added a new vehicle 
                unset($this->vehicles[$key]);
            }   
        } 
        $this->vehicles[] = $vehicle;
    }

    function removeVehicle($vehicle) {
        foreach ($this->vehicles() as $key => $eachVehicle) {
            if ($vehicle === $eachVehicle) {
                unset($this->vehicles[$key]);
            }
        }
    }

    function hasVehicle($vehicle) {
        foreach ($this->vehicles() as $eachVehicle) {
            if ($vehicle === $eachVehicle) {
                return true;
            }
        }
        return false;
    }

    function vehicles() {
        return $this->vehicles;
    }

}