<?php

namespace Treii28\Grampsdb\Models\Helpers;

class GedcomxHelper extends \Gedcomx\Gedcomx
{
    private $__gedcomFile = 'gedcomx.xml';

    /**
     * @param $inputFile
     * @param string $outputFile
     * @param string $converterJar
     * @param string $jre
     * @return bool
     * @throws \Exception
     */
    public static function convertGedToGedx($inputFile, $outputFile = "gedcomx.xml", $converterJar = "gedcomx-converter.jar", $jre = 'java')
    {
        if (($jre !== "java") && !file_exists($jre))
            throw new \Exception("java executable not found");
        if (!file_exists($converterJar))
            throw new \Exception("converter jar file not found");
        if (!file_exists($inputFile))
            throw new \Exception("file does not exist");
        $cmd = sprintf("%s -jar %s -i %s -o %s", $jre, $converterJar, $inputFile, $outputFile);
        $res = exec($cmd);
        return true;
    }

    public function __construct($f = null)
    {
        $x = null;

        if ($f instanceof \XMLReader)
            $x = $f;
        elseif (is_string($f) && !empty($f) && file_exists($f)) {
            $this->__gedcomFile = realpath($f);
        } else {
            $gdf = $this->__gedcomFile;
            $rpGdf = realpath($gdf);
            if ($rpGdf)
                $this->__gedcomFile = $gdf;
        }
        if (!empty($this->__gedcomFile) && file_exists($this->__gedcomFile)) {
            $x = new \XMLReader();
            $x->open($this->__gedcomFile);
        }

        if ($x instanceof \XMLReader)
            parent::__construct($x);
        else
            throw new \Exception("unable to locate valid gedcom file");
    }
}