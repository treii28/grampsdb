<?php

// todo add keys to sub types under blob_data

namespace Treii28\Grampsdb;

use Illuminate\Support\Facades\DB;

class GrampsdbHelper
{
    // <editor-fold desc="utility functions">

    /**
     * @var string $dbConn
     */
    protected static $dbConn = 'grampsdb';

    /**
     * characters special to a url that can be used in urlEncode
     * @var string[] $url_special
     * @see urlEncode()
     */
    protected static $url_special = [
        "/" => '%2F',
        "=" => '%3D',
        "?" => '%3F',
        "%" => '%25',
        "&" => '%26',
    ];

    /**
     * list of characters and replacements which can be used with urlEncode()
     *
     * @var string[] $url_entities
     * @see urlEncode()
     */
    protected static $url_entities = [
        ' ' => '%20',
        '!' => '%21',
        '"' => '%22',
        "#" => '%23',
        "$" => '%24',
        "'" => '%27',
        "(" => '%28',
        ")" => '%29',
        '*' => '%2A',
        "+" => '%2B',
        "," => '%2C',
        ":" => '%3A',
        ";" => '%3B',
        "@" => '%40',
        "[" => '%5B',
        "]" => '%5D'
    ];

    /**
     * @param null|string $path
     * @return string|null
     * @throws \Exception if unable to find a valid python executable
     */
    private static function getPythonExecutable($path = null)
    {
        $pyExe = null;

        if (is_file($path) && is_executable($path)) { // use specified if available
            $pyExe = $path;
        } else if (function_exists('env')) { // use env if available
            $pyExe = env('PYTHON_EXE');
        } else if (is_file('/usr/bin/which') && is_executable('/usr/bin/which')) {
            // see if it is in the unix path
            $pyExe = exec('/usr/bin/which python');
        }

        // did we find something useable?
        if (!is_file($pyExe) && is_executable($pyExe))
            throw new \Exception("python executable not found!");

        return $pyExe;
    }

    /**
     * assign the configuration key for the grampsdb
     *
     * @param string $connName
     */
    public static function setDbConnection($connName = 'grampsdb')
    {
        static::$dbConn = $connName;
    }

    /**
     * get the current DB::connection for the gramps database
     *
     * @param string|null $connName
     * @return \Illuminate\Database\ConnectionInterface
     */
    public static function getDbHandle($connName = null)
    {
        if (empty($connName)) $connName = self::$dbConn;
        return DB::connection($connName);
    }

    /**
     * call either a pyinstaller binary or python script with raw blob data to be unpickled
     *
     * @param string $b binary data of blob
     * @return false|mixed
     */
    public static function unpickle($b)
    {
        $cmd = realpath(__DIR__ . "/../bin/unpickle");
        // see if an environment unpickle binary has been specified
        if (function_exists('env') && is_file(env('UNPICKLE_BINARY')) && is_executable(env('UNPICKLE_BINARY')))
            $cmd = base_path(env('UNPICKLE_BINARY'));
        if (!(is_file($cmd) && is_executable($cmd))) { // make sure unpickle cmd exists
            // try to see if the python script exists if no binary does
            if (is_file($cmd . ".py")) {
                $cmd = sprintf("%s %s.py", self::getPythonExecutable(), $cmd);
            } else
                return self::unpyckle($b); // try direct python call
        }

        // use proc_open to execute python code using raw binary data from stdin
        $descriptorspec = [
            ["pipe", "r"],  // stdin is a pipe that the child will read from
            ["pipe", "w"],  // stdout is a pipe that the child will write to
            ["pipe", "w"]   // stderr is a file to write to
        ];

        $cwd = dirname($cmd);
        $env = [];

        $process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);

        if (is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // 2 => readable handle connected to child stderr

            fwrite($pipes[0], $b);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $return_value = proc_close($process);

            if (self::isJson($output))
                return json_decode($output);
            else
                return false;
        }
        return false;
    }

    /**
     * use a python exec call to 'unpickle' the blob_data
     *   to get the binary blob into a command line argument, base64 encode it
     *   to get the data back out of python, json serialize it
     * @param string $blob binary blob data
     * @return mixed
     */
    public static function unpyckle($blob)
    {
        $bblob = base64_encode($blob);
        $cmd = sprintf("import pickle; import base64; import json; print(json.dumps(pickle.loads(base64.b64decode('%s'))))", $bblob);
        //file_put_contents(base_path("tests")."/blob.out", $blob);
        $pcmd = sprintf("%s -c \"%s\"", self::getPythonExecutable(), $cmd);
        $result = exec($pcmd);
        $resdec = json_decode($result);
        return $resdec;
    }

    /**
     * Use it for json_encode some corrupt UTF-8 chars
     * useful for = malformed utf-8 characters possibly incorrectly encoded by json_encode
     * @param $mixed
     * @return array|false|mixed|string|string[]|null
     */
    public static function utf8ize($mixed)
    {
        if (is_object($mixed))
            $mixed = (array)$mixed;
        if (is_array($mixed)) {
            // make sure any blob data has already been unpickled to an array
            if (array_key_exists('blob_data', $mixed) && !is_array($mixed['blob_data']))
                $mixed['blob_data'] = self::unpyckle($mixed['blob_data']);
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }

    /**
     * test an array to see if it appears to be an associative array
     *
     * @param mixed $arr
     * @return bool
     * @static
     */
    public static function isAssoc($arr)
    {
        if (!is_array($arr)) return false;
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * try to detect if a string is a json string
     *
     * @param $str
     * @return bool
     */
    public static function isJson($str)
    {
        if (is_string($str) && !empty($str)) {
            json_decode($str);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return false;
    }

    /**
     * custom url encode using specified associative array of replacements
     *   will default to self::$url_entities if no replacement list is given
     * @param string $string
     * @param array $incSpec
     * @return string
     */
    public static function urlEncode($string, $entities = null)
    {
        if (is_null($entities))
            $entities = self::$url_entities;

        if (!self::isAssoc($entities))
            throw new \Exception("invalid replacement list");

        return str_replace(array_keys($entities), array_values($entities), $string);
    }

    /**
     * map a given media file record to point to a URL in an AWS S3 bucket
     *
     * @param string $filename
     * @param null|string $path
     * @param null|string $bucket
     * @param null|string $region
     * @return string
     * @throws \Exception
     */
    public static function getUrlFromAwsBucket($filename, $path = null, $bucket = null, $region = null)
    {
        if (empty($region) && function_exists('env'))
            $region = env('AWS_REGION', 'us-east-1');
        if (empty($bucket) && function_exists('env'))
            $bucket = env('AWS_BUCKET', 'grampsmedia');
        // replace any backslashes and get rid of any leading or trailing slashes or whitespace on path
        if (!empty($path)) {
            // swap backslashes and remove multiple separators
            $path = preg_replace("|[\\\\/]+|", "/", $path);
            // remove any leading path separator
            $path = preg_replace('|[/\s]*$|', '', $path);
            // remove any trailing path separator
            $path = preg_replace('|^[/\s]*|', '', $path);
        }
        // if the (stripped) path is not empty, add a trailing slash to the path
        $fullpath = self::urlEncode((empty($path) ? '' : $path . "/") . $filename);

        return sprintf('https://%s.s3.%s.amazonaws.com/%s', $bucket, $region, $fullpath);
    }
    // </editor-fold desc="utility functions">

    // <editor-fold desc="grampsdb record accessors">

    // <editor-fold desc="citation table record accessors">
    /**
     * @param string $chan
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getCitationByHandle($chan)
    {
        $cRec = self::getDbHandle()->table('citation')->where(['handle' => $chan])->first();
        self::prepCitation($cRec);
        return $cRec;
    }

    /**
     * @param $phan
     * @return array
     */
    public static function getCitationByPersonHandle($phan)
    {
        $citations = [];
        $ctRefs = self::getRefByPersonHandle($phan, 'Citation');

        foreach ($ctRefs as $c) {
            $cRec = self::getCitationByHandle($c->ref_handle);
            $cid = $cRec->gramps_id;
            $citations[$cid] = $cRec;
        }
        return $citations;
    }
    // </editor-fold desc="citation table record accessors">

    // <editor-fold desc="event table record accessors">
    /**
     * @param string $ehan
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getEventByHandle($ehan)
    {
        $eRec = self::getDbHandle()->table('event')->where(['handle' => $ehan])->first();
        self::prepEvent($eRec);
        return $eRec;
    }

    /**
     * @param string $gId
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getEventById($gId)
    {
        $eRec = self::getDbHandle()->table('event')->where(['gramps_id' => $gId])->first();
        self::prepEvent($eRec);
        return $eRec;
    }

    /**
     * get a list of events associated with a given person handle
     *
     * @param string $gphan
     * @return array
     * @throws \Exception
     */
    public static function getEventsByPersonHandle($gphan)
    {
        $events = [];
        $epRefs = self::getRefByPersonHandle($gphan, 'Event');

        foreach ($epRefs as $r) {
            $eRec = self::getEventByHandle($r->ref_handle);
            $eid = $eRec->gramps_id;
            $events[$eid] = $eRec;
        }
        return $events;
    }
    // </editor-fold desc="event table record accessors">

    // <editor-fold desc="family table record accessors">
    /**
     * @param string $fhan
     * @param boolean $withPersons
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getFamilyByHandle($fhan, $withPersons=false)
    {
        $fRec = self::getDbHandle()->table('family')->where(['handle' => $fhan])->first();
        self::prepFamily($fRec, $withPersons);
        return $fRec;
    }

    /**
     * @param string $gId
     * @param boolean $withPersons
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getFamilyById($gId, $withPersons=false)
    {
        $fRec = self::getDbHandle()->table('family')->where(['gramps_id' => $gId])->first();
        self::prepFamily($fRec, $withPersons);
        return $fRec;
    }

    /**
     * @param string $phan
     * @param boolean $withPersons
     * @return array
     */
    public static function getFamilyByPersonHandle($phan, $withPersons=false)
    {
        $family = [];
        $fmRefs = self::getRefByPersonHandle($phan, 'Family');

        foreach ($fmRefs as $f) {
            $fRec = self::getFamilyByHandle($f->ref_handle, $withPersons);
            $fid = $fRec->gramps_id;
            $family[$fid] = $fRec;
        }
        return $family;
    }
    // </editor-fold desc="family table record accessors">

    // <editor-fold desc="media table record accessors">
    /**
     * @param boolean $skipPath
     * @return array
     */
    public static function getMedia($skipPath=true)
    {
        $grampsMedia = [];
        $gMedia = self::getDbHandle()->table('media')->get();
        foreach ($gMedia as $mRec) {
            $gid = $mRec->gramps_id;
            self::prepMedia($mRec,$skipPath);
            $grampsMedia[$gid] = $mRec;
        }
        return $grampsMedia;
    }

    /**
     * @param string $gid
     * @param boolean $skipPath
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getMediaById($gid,$skipPath=true)
    {
        $media = self::getDbHandle()->table('media')->where('gramps_id', $gid)->first();
        self::prepMedia($media,$skipPath);
        return $media;
    }

    /**
     * @param string $ghan
     * @param boolean $skipPath
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getMediaByHandle($ghan,$skipPath=true)
    {
        $media = self::getDbHandle()->table('media')->where('handle', $ghan)->first();
        self::prepMedia($media,$skipPath);
        return $media;
    }

    /**
     * retrieve all the media entries for a person by reference using a gramps_id
     *
     * @param string $gid
     * @return array|false
     * @see getPersonHandleById()
     * @see getMediaByPersonHandle()
     */
    public static function getMediaByPersonId($gid)
    {
        if ($pid = self::getPersonHandleById($gid))
            return self::getMediaByPersonHandle($pid);
        else return false;
    }

    /**
     * get all media associated with a given person by handle
     *
     * @param string $ghan
     * @param bool $skipPath
     * @return array
     * @see getRefByPersonHandle()
     */
    public static function getMediaByPersonHandle($ghan, $skipPath=true)
    {
        $media = [];
        $mpRefs = self::getRefByPersonHandle($ghan, 'Media');
        //$mPath = ((function_exists('env')) ? env('GEDCOM_MEDIA', 'media') : 'media'); // used for local images
        foreach ($mpRefs as $r) {
            $rh = $r->ref_handle;
            $mRec = self::getDbHandle()->table('media')->where(['handle' => $rh])->first();

            $mid = $mRec->gramps_id;
            self::prepMedia($mRec, $skipPath);
            $media[$mid] = $mRec;
        }
        return $media;
    }
    // </editor-fold desc="media table record accessors">

    // <editor-fold desc="note table record accessors">
    public static function getNoteByHandle($nhan)
    {
        $nRec = self::getDbHandle()->table('note')->where(['handle' => $nhan])->first();
        self::prepNote($nRec);
        return $nRec;
    }

    public static function getNoteById($gId)
    {
        $nRec = self::getDbHandle()->table('note')->where(['gramps_id' => $gId])->first();
        self::prepNote($nRec);
        return $nRec;
    }
    // </editor-fold desc="note table record accessors">

    // <editor-fold desc="person table record accessors">
    /**
     *  retrieve a full list of persons from the grampsdb
     * @param boolean $withExtra
     * @return array
     */
    public static function getPersons($withMedia=false)
    {
        $grampsPersons = [];
        $gPersons = self::getDbHandle()->table('person')->get();
        foreach ($gPersons as $pRec) {
            $gid = $pRec->gramps_id;
            self::prepPerson($pRec, $withMedia);
            $grampsPersons[$gid] = $pRec;
        }
        return $grampsPersons;
    }

    /**
     * get a specific person by their gramps_id, optionally collecting their media as well
     *
     * @param string $gid
     * @param false $withMedia optionally get their media
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     * @see getMediaByPersonHandle()
     */
    public static function getPersonById($gid, $withMedia=false)
    {
        $pObj = self::getDbHandle()->table('person')->where('gramps_id', $gid)->first();
        self::prepPerson($pObj, $withMedia);
        return $pObj;
    }

    /**
     * get a specific person by their handle, optionally collecting their media as well
     *
     * @param string $ghan
     * @param false $withMedia whether to get all associated media references as a sub-element of the array
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     * @see getMediaByPersonHandle()
     */
    public static function getPersonByHandle($ghan, $withMedia=false)
    {
        $pObj = self::getDbHandle()->table('person')->where('handle', $ghan)->first();
        self::prepPerson($pObj, $withMedia);

        return $pObj;
    }

    /**
     * get the gramps_id for a person using their handle
     *
     * @param string $ghan
     * @return false|mixed
     * @see getPersonByHandle()
     */
    public static function getPersonIdByHandle($ghan)
    {
        $gPerson = self::getPersonByHandle($ghan);
        if (is_object($gPerson) && property_exists($gPerson, 'gramps_id'))
            return $gPerson->gramps_id;
        else return false;
    }

    /**
     * get the handle for a person using their gramps_id
     *
     * @param string $gid
     * @return false|mixed
     * @see getPersonById()
     */
    public static function getPersonHandleById($gid)
    {
        $gPerson = self::getPersonById($gid);
        if (is_object($gPerson) && property_exists($gPerson, 'handle'))
            return $gPerson->handle;
        else return false;
    }
    // </editor-fold desc="person table record accessors">

    // <editor-fold desc="place table record accessors">
    public static function getPlaceByHandle($phan)
    {
        $pRec = self::getDbHandle()->table('place')->where(['handle' => $phan])->first();
        self::prepNote($pRec);
        return $pRec;
    }

    public static function getPlaceById($gId)
    {
        $pRec = self::getDbHandle()->table('place')->where(['gramps_id' => $gId])->first();
        self::prepNote($pRec);
        return $pRec;
    }
    // </editor-fold desc="place table record accessors">

    // <editor-fold desc="repository table record accessors">
    public static function getRepositoryByHandle($rhan)
    {
        $rRec = self::getDbHandle()->table('repository')->where(['handle' => $rhan])->first();
        self::prepRepository($rRec);
        return $rRec;
    }

    public static function getRepositoryById($gId)
    {
        $rRec = self::getDbHandle()->table('repository')->where(['gramps_id' => $gId])->first();
        self::prepRepository($rRec);
        return $rRec;
    }
    // </editor-fold desc="repository table record accessors">

    // <editor-fold desc="reference table record accessors">
    // todo pick up where we left off and finish this function
    public static function getReferences($withSubs=false)
    {
        $gRefs = self::getDbHandle()->table('reference')->get();
        if ($withSubs) {
            foreach ($gRefs as $k => $ref) {
                switch ($ref->obj_class) {
                    case 'Person':
                    default:
                        break;
                }
            }
        }
    }

    /**
     * get refs by handle optionally specifying the obj_class and/or ref_class
     * @param string $ghan object handle
     * @param null|string $oc object class (e.g. 'Person')
     * @param null|string $rc ref class (e.g. 'Media')
     * @return \Illuminate\Support\Collection
     */
    public static function getRefByHandle($ghan, $oc = null, $rc = null)
    {
        $whereData = ['obj_handle' => $ghan];
        if (!empty($oc)) $whereData['obj_class'] = $oc;
        if (!empty($rc)) $whereData['ref_class'] = $rc;

        $reference = self::getDbHandle()->table('reference')->where($whereData)->get();

        return $reference;
    }

    public static function getSubClass($classType, $handle, $withExtra=false)
    {
        $subClass = null;
        switch ($classType) {
            case 'Citation':
                $subClass = self::getCitationByHandle($handle);
                break;
            case 'Event':
                $subClass = self::getEventByHandle($handle);
                break;
            case 'Family':
                $subClass = self::getFamilyByHandle($handle, $withExtra);
                break;
            case 'Media':
                $subClass = self::getMediaByHandle($handle);
                break;
            case 'Note':
                $subClass = self::getNoteByHandle($handle);
                break;
            case 'Person':
                $subClass = self::getPersonByHandle($handle);
                break;
            case 'Place':
                $subClass = self::getPlaceByHandle($handle);
                break;
            case 'Repository':
                $subClass = self::getRepositoryByHandle($handle);
                break;
            case 'Source':
                $subClass = self::getSourceByHandle($handle);
                break;
            default:
                break;
        }
        return $subClass;
    }

    /**
     * @param $pid
     * @param null|string $rc
     * @return \Illuminate\Support\Collection|void
     * @see getPersonHandleById()
     * @see getRefByPersonHandle()
     */
    public static function getRefByPersonid($pid, $rc = null)
    {
        if ($phan = self::getPersonHandleById($pid))
            return self::getRefByPersonHandle($phan, $rc);
    }

    /**
     * get all person refs by handle, optionally filtering by ref_class
     * @param $ghan  object handle
     * @param null|string $rc ref class (e.g. 'Media')
     * @return \Illuminate\Support\Collection
     */
    public static function getRefByPersonHandle($ghan, $rc = null)
    {
        return self::getRefByHandle($ghan, 'Person', $rc);
    }
    // </editor-fold desc="reference table record accessors">

    // <editor-fold desc="source table record accessors">
    public static function getSourceByHandle($shan)
    {
        $sRec = self::getDbHandle()->table('source')->where(['handle' => $shan])->first();
        self::prepSource($sRec);
        return $sRec;
    }

    public static function getSourceById($gId)
    {
        $sRec = self::getDbHandle()->table('source')->where(['gramps_id' => $gId])->first();
        self::prepNote($sRec);
        return $sRec;
    }
    // </editor-fold desc="source table record accessors">

    // </editor-fold desc="grampsdb record accessors">

    // <editor-fold desc="blob_data handlers">

    // <editor-fold desc="citation blob handler">
    /**
     * map keys into citation type blob_data
     *
     * @param array $data
     * @return array|false
     */
    private static function mapCitationData($data)
    {
        if (!is_array($data) || (count($data) != 12)) return false;
        return [
            'handle'        => $data[0],
            'gramps_id'     => $data[1],
            'date'          => $data[2], // todo convert to php date
            'page'          => $data[3],
            'confidence'    => $data[4],
            'source_handle' => $data[5],
            'note_list'     => $data[6],
            'media_list'    => $data[7],
            'srcattr_list'  => $data[8],
            'change'        => $data[9],
            'tag_list'      => $data[10],
            'private'       => $data[11]
        ];
    }

    /**
     * @param object $cObj
     */
    private static function prepCitation(&$cObj)
    {
        // decode blob_data if any
        if (property_exists($cObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickle($cObj->blob_data);
            if ($blob_data != false) {
                $cObj->type_data = self::mapCitationData($blob_data);
                unset($cObj->blob_data);
            }
        }
    }
    // </editor-fold desc="citation blob handler">

    // <editor-fold desc="event blob handler">
    /**
     * text names associated with event type integer values
     *
     * @var string[]
     * @see https://www.gramps-project.org/docs/gen/gen_lib.html?highlight=eventtype#module-gramps.gen.lib.eventtype
     */
    private static $eventTypes = [
        '-1' => 'UNKNOWN',
        '0'  => 'CUSTOM',
        '1'  => 'MARRIAGE',
        '2'  => 'MARR_SETTL',
        '3'  => 'MARR_LIC',
        '4'  => 'MARR_CONTR',
        '5'  => 'MARR_BANNS',
        '6'  => 'ENGAGEMENT',
        '7'  => 'DIVORCE',
        '8'  => 'DIV_FILING',
        '9'  => 'ANNULMENT',
        '10' => 'MARR_ALT',
        '11' => 'ADOPT',
        '12' => 'BIRTH',
        '13' => 'DEATH',
        '14' => 'ADULT_CHRISTEN',
        '15' => 'BAPTISM',
        '16' => 'BAR_MITZVAH',
        '17' => 'BAS_MITZVAH',
        '18' => 'BLESS',
        '19' => 'BURIAL',
        '20' => 'CAUSE_DEATH',
        '21' => 'CENSUS',
        '22' => 'CHRISTEN',
        '23' => 'CONFIRMATION',
        '24' => 'CREMATION',
        '25' => 'DEGREE',
        '26' => 'EDUCATION',
        '27' => 'ELECTED',
        '28' => 'EMIGRATION',
        '29' => 'FIRST_COMMUN',
        '30' => 'IMMIGRATION',
        '31' => 'GRADUATION',
        '32' => 'MED_INFO',
        '33' => 'MILITARY_SERV',
        '34' => 'NATURALIZATION',
        '35' => 'NOB_TITLE',
        '36' => 'NUM_MARRIAGES',
        '37' => 'OCCUPATION',
        '38' => 'ORDINATION',
        '39' => 'PROBATE',
        '40' => 'PROPERTY',
        '41' => 'RELIGION',
        '42' => 'RESIDENCE',
        '43' => 'RETIREMENT',
        '44' => 'WILL'
    ];

    /**
     * map keys to event type blob_data
     *
     * @param array $data
     * @return array|false
     */
    private static function mapEventData($data)
    {
        if (!is_array($data) || (count($data) != 13)) return false;
        $eventTypeId = $data[2][0];
        $eventTypeName = (empty($data[2][1]) ? (array_key_exists($eventTypeId, self::$eventTypes) ? self::$eventTypes[$eventTypeId] : '') : $data[2][1]);
        return [
            'handle'         => $data[0],
            'gramps_id'      => $data[1],
            'type' => [
                'type_id'   => $eventTypeId,
                'type_name' => $eventTypeName
            ],
            'date'           => $data[3], // todo convert to php date?
            'description'    => $data[4],
            'place'          => $data[5],
            'citation_list'  => $data[6],
            'note_list'      => $data[7],
            'media_list'     => $data[8],
            'attribute_list' => $data[9],
            'change'         => $data[10],
            'tag_list'       => $data[11],
            'private'        => $data[12]
        ];
    }

    /**
     * @param object $eObj
     */
    private static function prepEvent(&$eObj)
    {
        // decode blob_data if any
        if (property_exists($eObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickle($eObj->blob_data);
            if ($blob_data != false) {
                $eObj->type_data = self::mapEventData($blob_data);
                unset($eObj->blob_data);
            }
        }
    }
    // </editor-fold desc="event blob handler">

    // <editor-fold desc="family blob handler">
    /**
     * text names associated with event type integer values
     *
     * @var string[]
     * @see https://www.gramps-project.org/docs/gen/gen_lib.html#module-gramps.gen.lib.familyreltype
     */
    private static $familyTypes = [
        '0' => 'MARRIED',
        '1' => 'UNMARRIED',
        '2' => 'CIVIL_UNION',
        '3' => 'UNKNOWN',
        '4' => 'CUSTOM'
    ];

    /**
     * map keys into family type blob_data
     *
     * @param array $data
     * @return array|false
     */
    private static function mapFamilyData($data)
    {
        if (!is_array($data) || (count($data) != 15)) return false;
        $familyTypeId = $data[5][0];
        $familyTypeName = (empty($data[5][1]) ? (array_key_exists($familyTypeId, self::$familyTypes) ? self::$familyTypes[$familyTypeId] : '') : $data[5][1]);
        return [
            'handle'         => $data[0],
            'gramps_id'      => $data[1],
            'father_handle'  => $data[2],
            'mother_handle'  => $data[3],
            'child_ref_list' => $data[4],
            'type' => [
                'type_id'   => $familyTypeId,
                'type_name' => $familyTypeName
            ],
            'event_ref_list' => $data[6],
            'media_list'     => $data[7],
            'attribute_list' => $data[8],
            'lds_ord_list'   => $data[9],
            'citation_list'  => $data[10],
            'note_list'      => $data[11],
            'change'         => $data[12],
            'tag_list'       => $data[13],
            'private'        => $data[14]
        ];
    }

    /**
     * unpickle blob_data and get person objects if specified
     * note: only populates mother and father if they are previously empty
     *
     * @param object $fRec
     * @param boolean $withPersons
     */
    private static function prepFamily(&$fRec, $withPersons=false)
    {
        // decode blob_data if any
        if (property_exists($fRec, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickle($fRec->blob_data);
            if ($blob_data != false) {
                $fRec->type_data = self::mapFamilyData($blob_data);
                unset($fRec->blob_data);
            }
        }
        if ($withPersons == true) {
            if (!empty($fRec->father_handle))
                $fRec->father = self::getPersonByHandle($fRec->father_handle);
            if (!empty($fRec->mother_handle))
                $fRec->mother = self::getPersonByHandle($fRec->mother_handle);
        }
    }
    // </editor-fold desc="family blob handler">

    // <editor-fold desc="media blob handler">
    /**
     * map keys into media type blob_data
     *
     * @param array $data
     * @return array|false
     */
    private static function mapMediaData($data)
    {
        if (!is_array($data) || (count($data) != 13)) return false;
        return [
            'handle'         => $data[0],
            'gramps_id'      => $data[1],
            'path'           => $data[2],
            'mime'           => $data[3],
            'description'    => $data[4],
            'checksum'       => $data[5],
            'attribute_list' => $data[6],
            'citation_list'  => $data[7],
            'note_list'      => $data[8],
            'change'         => $data[9],
            'date'           => $data[10], // todo convert to php date
            'tag_list'       => $data[11],
            'private'        => $data[12]
        ];
    }

    /**
     * @param object $mObj
     */
    private static function prepMedia(&$mObj, $skipPath=true)
    {
        // decode blob_data if any
        if (property_exists($mObj, 'blob_data')) {
            $blob_data = self::unpyckle($mObj->blob_data);
            $mObj->type_data = self::mapMediaData($blob_data);
            unset($mObj->blob_data);
        }
        // uncomment for just filename
        //$mRec->filename = preg_replace('|.*'.DIRECTORY_SEPARATOR.'([^'.DIRECTORY_SEPARATOR.']+)$|', "$1", $mRec->path);
        // insert relative url from site root for this server
        //$mRec->url = preg_replace('|.*'.DIRECTORY_SEPARATOR.'([^'.DIRECTORY_SEPARATOR.']+)$|', '/'.$mPath."/$1", $mRec->path);
        $media_path = (function_exists('env') ? env('GEDCOM_MEDIA_PATH', 'gedcomx/media') : 'gedcomx/media');
        $mObj->url = self::getUrlFromAwsBucket(basename($mObj->path), $media_path);
        if ($skipPath) unset($mObj->path); // remove path if not relevant to local resources
    }
    // </editor-fold desc="media blob handler">

    // <editor-fold desc="note blob handler">
    /**
     * @var string[] $noteTypes
     * @see https://www.gramps-project.org/docs/gen/gen_lib.html?highlight=notetype#module-gramps.gen.lib.notetype
     */
    private static $noteTypes = [
        '0'  => "CUSTOM",
        '1'  => "GENERAL",
        '2'  => "RESEARCH",
        '3'  => "TRANSCRIPT",
        '4'  => "PERSON",
        '5'  => "ATTRIBUTE",
        '6'  => "ADDRESS",
        '7'  => "ASSOCIATION",
        '8'  => "LDS",
        '9'  => "FAMILY",
        '10' => "EVENT",
        '11' => "EVENTREF",
        '12' => "SOURCE",
        '13' => "SOURCEREF",
        '14' => "PLACE",
        '15' => "REPO",
        '16' => "REPOREF",
        '17' => "MEDIA",
        '18' => "MEDIAREF",
        '19' => "CHILDREF",
        '20' => "PERSONNAME",
        '21' => "SOURCE_TEXT",
        '22' => "CITATION",
        '23' => "REPORT_TEXT",
        '24' => "HTML_CODE",
        '25' => "TODO",
        '26' => "LINK"
    ];

    /**
     * map keys to note type blob_data
     *
     * @param array $data
     * @return array|false
     */
    private static function mapNoteData($data)
    {
        if (!is_array($data) || (count($data) != 8)) return false;
        $noteTypeId = $data[4][0];
        $noteTypeName = (empty($data[4][1]) ? (array_key_exists($noteTypeId, self::$noteTypes) ? self::$noteTypes[$noteTypeId] : '') : $data[4][1]);
        return [
            'handle'    => $data[0],
            'gramps_id' => $data[1],
            'text'      => $data[2],
            'format'    => $data[3],
            'type' => [
                'type_id'   => $noteTypeId,
                'type_name' => $noteTypeName
            ],
            'change'    => $data[5],
            'tag_list'  => $data[6],
            'private'   => $data[7]
        ];
    }

    /**
     * @param object $nObj
     */
    private static function prepNote(&$nObj)
    {
        // decode blob_data if any
        if (property_exists($nObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickle($nObj->blob_data);
            if ($blob_data != false) {
                $nObj->type_data = self::mapNoteData($blob_data);
                unset($nObj->blob_data);
            }
        }
    }
    // </editor-fold desc="note blob handler">

    // <editor-fold desc="person blob handler">
    /**
     * add keys to the array created from blob_data for a person type
     *
     * @param array $data
     * @return array|false
     */
    private static function mapPersonData($data)
    {
        if (!is_array($data) || (count($data) != 21)) return false;
        $genders = ['Female', 'Male', 'Unknown'];
        $genderId = $data[2];
        return [
            'handle'             => $data[0],
            'gramps_id'          => $data[1],
            'gender'             => $genders[$genderId],
            'primary_name'       => $data[3],
            'alternate_names'    => $data[4],
            'death_ref_index'    => $data[5],
            'birth_ref_index'    => $data[6],
            'event_ref_index'    => $data[7],
            'family_list'        => $data[8],
            'parent_family_list' => $data[9],
            'media_list'         => $data[10],
            'address_list'       => $data[11],
            'attribute_list'     => $data[12],
            'urls'               => $data[13],
            'lds_ord_list'       => $data[14],
            'citation_list'      => $data[15],
            'note_list'          => $data[16],
            'change'             => $data[17],
            'tag_list'           => $data[18],
            'private'            => $data[19],
            'person_ref_list'    => $data[20]
        ];
    }

    private static function prepPerson(&$pObj, $withMedia=false)
    {
        // decode blob_data if any
        if (property_exists($pObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickle($pObj->blob_data);
            if ($blob_data != false) {
                $pObj->type_data = self::mapPersonData($blob_data);
                unset($pObj->blob_data);
            }
        }
        if ($withMedia)
            $pObj->media = self::getMediaByPersonHandle($pObj->handle);
    }
    // </editor-fold desc="person blob handler">

    // <editor-fold desc="place blob handler">
    /**
     * @var string[] $placeTypes
     * @see https://www.gramps-project.org/docs/gen/gen_lib.html?highlight=placetype#module-gramps.gen.lib.placetype
     */
    private static $placeTypes = [
        '-1' => "UNKNOWN",
        '0'  => "CUSTOM",
        '1'  => "COUNTRY",
        '2'  => "STATE",
        '3'  => "COUNTY",
        '4'  => "CITY",
        '5'  => "PARISH",
        '6'  => "LOCALITY",
        '7'  => "STREET",
        '8'  => "PROVINCE",
        '9'  => "REGION",
        '10' => "DEPARTMENT",
        '11' => "NEIGHBORHOOD",
        '12' => "DISTRICT",
        '13' => "BOROUGH",
        '14' => "MUNICIPALITY",
        '15' => "TOWN",
        '16' => "VILLAGE",
        '17' => "HAMLET",
        '18' => "FARM",
        '19' => "BUILDING",
        '20' => "NUMBER"
    ];

    /**
     * map keys to place type blob_data
     *
     * @param array $data
     * @return array|false
     */
    private static function mapPlaceData($data)
    {
        if (!is_array($data) || (count($data) != 18)) return false;
        $placeTypeId = $data[8][0];
        $placeTypeName = (empty($data[8][1]) ? (array_key_exists($placeTypeId, self::$placeTypes) ? self::$placeTypes[$placeTypeId] : '') : $data[2][1]);
        return [
            'handle'        => $data[0],
            'gramps_id'     => $data[1],
            'title'         => $data[2],
            'long'          => $data[3],
            'lat'           => $data[4],
            'placeref_list' => $data[5],
            'name'          => $data[6],
            'alt_names'     => $data[7],
            'type' => [
                'type_id'   => $placeTypeId,
                'type_name' => $placeTypeName
            ],
            'code'          => $data[9],
            'alt_loc'       => $data[10],
            'urls'          => $data[11],
            'media_list'    => $data[12],
            'citation_list' => $data[13],
            'note_list'     => $data[14],
            'change'        => $data[15],
            'tag_list'      => $data[16],
            'private'       => $data[17]
        ];
    }

    /**
     * @param object $pObj
     */
    private static function prepPlace(&$pObj)
    {
        // decode blob_data if any
        if (property_exists($pObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickle($pObj->blob_data);
            if ($blob_data != false) {
                $pObj->type_data = self::mapPlaceData($blob_data);
                unset($pObj->blob_data);
            }
        }
    }
    // </editor-fold desc="place blob handler">

    // <editor-fold desc="repository blob handler">
    /**
     * @var string[] $repositoryType
     */
    private static $repositoryType = [
        '-1' => "UNKNOWN",
        '0'  => "CUSTOM",
        '1'  => "LIBRARY",
        '2'  => "CEMETERY",
        '3'  => "CHURCH",
        '4'  => "ARCHIVE",
        '5'  => "ALBUM",
        '6'  => "WEBSITE",
        '7'  => "BOOKSTORE",
        '8'  => "COLLECTION",
        '9'  => "SAFE"
    ];

    /**
     * map keys to note type blob_data
     *
     * @param array $data
     * @return array|false
     */
    private static function mapRepositoryData($data)
    {
        if (!is_array($data) || (count($data) != 10)) return false;
        $repositoryTypeId = $data[2][0];
        $repositoryTypeName = (empty($data[2][1]) ? (array_key_exists($repositoryTypeId, self::$repositoryTypes) ? self::$repositoryTypes[$repositoryTypeId] : '') : $data[2][1]);
        return [
            'handle'       => $data[0],
            'gramps_id'    => $data[1],
            'type' => [
                'type_id'   => $repositoryTypeId,
                'type_name' => $repositoryTypeName
            ],
            'name' => $data[3],
            'note_list'    => $data[4],
            'address_list' => $data[5],
            'urls'         => $data[6],
            'change'       => $data[7],
            'tag_list'     => $data[8],
            'private'      => $data[9]
        ];
    }

    /**
     * @param object $rObj
     */
    private static function prepRepository(&$rObj)
    {
        // decode blob_data if any
        if (property_exists($rObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickle($rObj->blob_data);
            if ($blob_data != false) {
                $rObj->type_data = self::mapRepositoryData($blob_data);
                unset($rObj->blob_data);
            }
        }
    }
    // </editor-fold desc="repository blob handler">

    // <editor-fold desc="source blob handler">
    /**
     * @var string[]
     */
    private static $sourceTypes = [
        '-1' => "UNKNOWN",
        '0'  => "CUSTOM",
        '1'  => "AUDIO",
        '2'  => "BOOK",
        '3'  => "CARD",
        '4'  => "ELECTRONIC",
        '5'  => "FICHE",
        '6'  => "FILM",
        '7'  => "MAGAZINE",
        '8'  => "MANUSCRIPT",
        '9'  => "MAP",
        '10' => "NEWSPAPER",
        '11' => "PHOTO",
        '12' => "TOMBSTONE",
        '13' => "VIDEO"
    ];

    /**
     * @param array $data
     * @return array|false
     */
    private static function mapRepoRef($data) {
        if (!is_array($data) || (count($data) != 5)) return false;
        $sourceTypeId = $data[3][0];
        $sourceTypeName = (empty($data[3][1]) ? (array_key_exists($sourceTypeId, self::$sourceTypes) ? self::$sourceTypes[$sourceTypeId] : '') : $data[3][1]);

        return [
            'note_list'   => $data[0],
            'ref'         => $data[1],
            'call_number' => $data[2],
            'type' => [
                'type_id'   => $sourceTypeId,
                'type_name' => $sourceTypeName
            ],
            'private'     => $data[4],
        ];
    }

    /**
     * map keys to source type blob_data
     *
     * @param array $data
     * @return array|false
     */
    private static function mapSourceData($data)
    {
        if (!is_array($data) || (count($data) != 13)) return false;
        $repoRefList = [];
        $refList = $data[10];
        foreach($refList as $ref)
            array_push($repoRefList, self::mapRepoRef($ref));

        $sourceData = [
            'handle'         => $data[0],
            'gramps_id'      => $data[1],
            'title'          => $data[2],
            'author'         => $data[3],
            'pubinfo'        => $data[4],
            'note_list'      => $data[5],
            'media_list'     => $data[6],
            'abbrev'         => $data[7],
            'change'         => $data[8],
            'attribute_list' => $data[9],
            'reporef_list'   => $repoRefList,
            'tag_list'       => $data[11],
            'private'        => $data[12]
        ];
        return $sourceData;
    }

    /**
     * @param object $sObj
     */
    private static function prepSource(&$sObj)
    {
        // decode blob_data if any
        if (property_exists($sObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickle($sObj->blob_data);
            if ($blob_data != false) {
                $sObj->type_data = self::mapSourceData($blob_data);
                unset($sObj->blob_data);
            }
        }
    }
    // </editor-fold desc="source blob handler">
    // </editor-fold desc="blob_data handlers">
}