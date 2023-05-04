<?php

namespace Treii28\Grampsdb\Interfaces\Gedcom;

interface ReferenceDataInterface
{
    public function getAll($withSubs=false);
    public function findRefs($objHandle=null,$objClass=null,$refHandle=null,$refClass=null,$withSubs=false);
    public function getByObjHandle($objHandle,$withSubs=false);
    public function getByObjClass($objClass,$withSubs=false);
    public function getByRefHandle($refHandle,$withSubs=false);
    public function getByRefClass($refClass,$withSubs=false);
}