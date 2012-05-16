<?php

class Elite_Vaf_Model_Merge extends Elite_Vaf_Model_Base
{

    protected $slaveLevels;
    protected $masterLevel;
    protected $operating_grain;

    /**
     * @param aray $slaveLevels - Ex. array('year'=>$year1,'year'=>$year2);
     * @param array $masterLevel - Ex. array('year'=>$year2);
     */
    function __construct($slaveLevels, $masterLevel)
    {
        $this->slaveLevels = $slaveLevels;
        $this->masterLevel = $masterLevel;
    }

    function execute()
    {
        $this->getReadAdapter()->beginTransaction();

        try
        {
            $master_level_type = current($this->masterLevel);

            $this->setMasterVehicle();
            $master_vehicle = $this->masterVehicle();

            $this->operating_grain = $master_level_type;

            $this->ensureSameGrain();

            $slaveVehicles = $this->slaveVehicles();

            $this->ensureCompatible($slaveVehicles, $master_vehicle);

            foreach ($slaveVehicles as $slaveVehicle)
            {
                if ($this->equalsAboveOperatingGrain($slaveVehicle, $master_vehicle))
                {
                    continue;
                }

                $this->merge_vehicle($slaveVehicle, $master_vehicle);
                $this->unlinkSlaves($slaveVehicle, $master_vehicle);
            }
        } catch (Exception $e)
        {
            $this->getReadAdapter()->rollback();
            throw $e;
        }
        $this->getReadAdapter()->commit();
    }

    function ensureCompatible($slaveVehicles, $masterVehicle)
    {
        if (file_exists(ELITE_PATH . '/Vafwheel'))
        {
            $wheelValidator = new Elite_Vafwheel_Model_MergeValidator;
            $wheelValidator->ensureCompatible($slaveVehicles, $masterVehicle);
        }

        if (file_exists(ELITE_PATH . '/Vaftire'))
        {
            $tireValidator = new Elite_Vaftire_Model_MergeValidator;
            $tireValidator->ensureCompatible($slaveVehicles, $masterVehicle);
        }

        if (file_exists(ELITE_PATH . '/Vafpaint'))
        {
            $paintValidator = new Elite_Vafpaint_Model_MergeValidator;
            $paintValidator->ensureCompatible($slaveVehicles, $masterVehicle);
        }
    }

    function operatingGrain()
    {
        return $this->operating_grain;
    }

    function setMasterVehicle()
    {
        $this->master_vehicle = next($this->masterLevel);
    }

    function masterVehicle()
    {
        return $this->master_vehicle;
    }

    function slaveVehicles()
    {
        $slaveVehicles = array();

        foreach ($this->slaveLevels as $levelsToBeMergedArray)
        {
            $vehicle_object = next($levelsToBeMergedArray);

            $levelIds = $vehicle_object->levelIdsTruncateAfter($this->operatingGrain());
            $slaveVehicles = array_merge($slaveVehicles, $this->vehicleFinder()->findByLevelIds($levelIds));
        }

        foreach ($slaveVehicles as $slaveVehicle)
        {
            $slaveVehicle->toValueArray();
        }
        return $slaveVehicles;
    }

    function ensureSameGrain()
    {
        $last_level_type = '';
        $i = 0;
        foreach ($this->slaveLevels as $levelsToBeMergedArray)
        {
            $level_type = current($levelsToBeMergedArray);
            if ($last_level_type != $level_type && $i)
            {
                throw new Elite_Vaf_Model_Vehicle_Finder_Exception_DifferingGrain('slave levels should all be at same grain to merge');
            }
            $last_level_type = $level_type;
            $i++;
        }

        if ($last_level_type != $this->operatingGrain())
        {
            throw new Elite_Vaf_Model_Vehicle_Finder_Exception_DifferingGrain('master level must be at same grain as slave levels');
        }
    }

    function unlinkSlaves($slaveVehicle, $master_vehicle)
    {
        if (!$this->equalsAboveOperatingGrain($slaveVehicle, $master_vehicle))
        {
            $params = $slaveVehicle->levelIdsTruncateAfter($this->operatingGrain());
            $unlinkTarget = $this->vehicleFinder()->findOneByLevelIds($params, Elite_Vaf_Model_Vehicle_Finder::EXACT_ONLY);
            if ($unlinkTarget)
            {
                $unlinkTarget->unlink();
            }
        }
    }

    function equalsAboveOperatingGrain($vehicle1, $vehicle2)
    {
        return $vehicle1->levelIdsTruncateAfter($this->operatingGrain()) == $vehicle2->levelIdsTruncateAfter($this->operatingGrain());
    }

    function merge_vehicle($slave_vehicle, $master_vehicle)
    {
        $titles = $slave_vehicle->toTitleArray();
        $levelsToReplace = $this->getSchema()->getPrevLevelsIncluding($this->operatingGrain());
        foreach ($levelsToReplace as $levelToReplace)
        {
            $titles[$levelToReplace] = $master_vehicle->getLevel($levelToReplace)->getTitle();
        }
        $new_vehicle = Elite_Vaf_Model_Vehicle::create($this->getSchema(), $titles);
        $new_vehicle->save();

        $this->mergeFitments($slave_vehicle, $new_vehicle);
    }

}