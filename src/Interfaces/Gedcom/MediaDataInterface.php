<?php

namespace Treii28\Grampsdb\Interfaces\Gedcom;

//use Treii28\Grampsdb\Interfaces\GedcomDataInterface;

interface MediaDataInterface
{
    public function getAll();
    public function getById($id);
    public function getByHandle($handle);
    public function getByPersonId($id);
    public function getByPersonHandle($handle);
}