<?php

namespace Treii28\Grampsdb\Interfaces;

interface GedcomDataInterface
{
    public function getAll();
    public function getById($id);
    //public function deleteById($id);
    //public function create(array $details);
    //public function update($id, array $newDetails);
}