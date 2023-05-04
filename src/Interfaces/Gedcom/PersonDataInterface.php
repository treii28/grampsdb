<?php

namespace Treii28\Grampsdb\Interfaces\Gedcom;

//use Treii28\Grampsdb\Interfaces\GedcomDataInterface;

interface PersonDataInterface
{
    public function getAll();
    public function getById($id,bool $withMedia=false);
    public function getByHandle($handle,bool $withMedia=false);
}