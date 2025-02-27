<?php

// todo add keys to sub types under blob_data

namespace Treii28\Grampsdb;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Treii28\Grampsdb\Exceptions\GrampsdbException;
//use Treii28\Grampsdb\Models\Unpicklecache;
use Treii28\Grampsdb\Models\Unpicklecache;

class GrampsdbHelper
{
    // <editor-fold desc="utility functions">

    /**
     * @var string $dbConn
     */
    protected static $dbConn = 'grampsdb';

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
        // get a connName either from value specified, configuration or default
        if (empty($connName)) {
            $confdbname = Config::get('grampsdb.database.default');
            $connName = (empty($confdbname) ? self::$dbConn : $confdbname);
        }

        return DB::connection($connName);
    }

    public static function unpickleCached($b, $dataType, $gramps_id)
    {
        if (!is_binary_data($b))
            throw new GrampsdbException(sprintf("%s - invalid blob_data"));
        $bHash = sha1(base64_encode($b));
        $bch = Unpicklecache::where(['dataType' => $dataType, 'gramps_id' => $gramps_id])->first();

        if (!(($bch instanceof Unpicklecache) && ($bHash == $bch->hash))) {
            $output = unpickle($b);
            if (!($bch instanceof Unpicklecache)) {
                $bch = new Unpicklecache();
                $bch->dataType = $dataType;
                $bch->gramps_id = $gramps_id;
            }
            $bch->hash = $bHash;
            $bch->json = json_encode($output);
            $bch->save();
        }
        return json_decode($bch->json);
    }
    // </editor-fold desc="utility functions">

    // <editor-fold desc="grampsdb record accessors">

    // <editor-fold desc="citation table record accessors">
    /**
     * @param string $chan
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getCitationByHandle($chan, $withSubs = false, $subsRecursive=false)
    {
        $cRec = self::getDbHandle()->table('citation')->where(['handle' => $chan])->first();
        self::prepCitation($cRec);
        if($withSubs == true) {
            if(property_exists($cRec, 'source_handle') && !empty($cRec->source_handle))
                $cRec->source = self::getSourceByHandle($cRec->source_handle, $subsRecursive);
        }
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
    public static function getFamilyByHandle($fhan, $withPersons = false)
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
    public static function getFamilyById($gId, $withPersons = false)
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
    public static function getFamilyByPersonHandle($phan, $withPersons = false)
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
    public static function getMedia($skipPath = true)
    {
        $grampsMedia = [];
        $gMedia = self::getDbHandle()->table('media')->get();
        foreach ($gMedia as $mRec) {
            $gid = $mRec->gramps_id;
            self::prepMedia($mRec, $skipPath);
            $grampsMedia[$gid] = $mRec;
        }
        return $grampsMedia;
    }

    /**
     * @param string $gid
     * @param boolean $skipPath
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getMediaById($gid, $skipPath = true)
    {
        $media = self::getDbHandle()->table('media')->where('gramps_id', $gid)->first();
        self::prepMedia($media, $skipPath);
        return $media;
    }

    /**
     * @param string $ghan
     * @param boolean $skipPath
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getMediaByHandle($ghan, $skipPath = true)
    {
        $media = self::getDbHandle()->table('media')->where('handle', $ghan)->first();
        self::prepMedia($media, $skipPath);
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
    public static function getMediaByPersonHandle($ghan, $skipPath = true)
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
     *
     * @param boolean $withExtra
     * @return array
     */
    public static function getPersons($withMedia = false)
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
    public static function getPersonById($gid, $withMedia = false)
    {
        $pObj = self::getDbHandle()->table('person')->where('gramps_id', $gid)->first();
        self::prepPerson($pObj, $withMedia);
        return $pObj;
    }

    /**
     * get a specific person by their handle, optionally collecting their media as well
     *
     * @param string $pHan
     * @param false $withSubs whether to get all associated media references as a sub-element of the array
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     * @see getMediaByPersonHandle()
     */
    public static function getPersonByHandle($pHan, $withSubs = false, $subRecursive = false)
    {
        $pObj = self::getDbHandle()->table('person')->where('handle', $pHan)->first();
        self::prepPerson($pObj);

        if ($withSubs) {
            foreach([
                        'citations' => "Citation",
                        'events' => "Event",
                        'family' => "Family",
                        'media' => "Media",
                        'note' => "Note"
                    ] as $k => $v) {
                $result = self::getReferences([
                    'obj_class' => 'Person',
                    'obj_handle' => $pHan,
                    'ref_class' => $v
                ], $withSubs, $subRecursive);
                $pObj->$k = $result;
            }
            if(property_exists($pObj, 'family') && is_array($pObj->family)) {
                foreach($pObj->family as $f) {
                    if(property_exists($f, 'ref') && is_object($f->ref)) {
                        if((property_exists($f->ref, 'father_handle') && $f->ref->father_handle == $pHan) || (property_exists($f->ref, 'wife_handle') && $f->ref->wife_handle == $pHan)) {
                            if(property_exists($f->ref, 'type_data') && is_array($f->ref->type_data) && array_key_exists('event_ref_list', $f->ref->type_data) && is_array($f->ref->type_data['event_ref_list'])) {
                                $spEvts = [];
                                foreach($f->ref->type_data['event_ref_list'] as $e) {
                                    array_push($spEvts, GrampsdbHelper::getEventByHandle($e[3]));
                                }
                                if(count($spEvts) > 0)
                                    $pObj->spouse->events = $spEvts;
                            }
                            $pObj->spouse = $f->ref;
                        } elseif(property_exists($f->ref, 'type_data') && is_array($f->ref->type_data) && array_key_exists('child_ref_list', $f->ref->type_data)) {
                            foreach($f->ref->type_data['child_ref_list'] as $c) {
                                if(is_array($c) && $c[3] == $pHan) {
                                    $pObj->parents = $f->ref;
                                }
                            }
                        }
                    }
                }
            }
        }

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

    public static function sortEvents($evtArr)
    {
        $sEvt = [];
        foreach($evtArr as $evt) {
            if(property_exists($evt, 'ref')) {
                if(property_exists($evt->ref, 'type') && property_exists($evt->ref, 'type_data') && ($evt->ref->type === "Event")) {

                    $eGid = $evt->ref->gramps_id;
                    $dateVal = "UNKNOWN"; // todo try to determine why 'residence' come back with date unknown
                    $dStr = $dateVal . '-' . $eGid;
                    if(array_key_exists('date', $evt->ref->type_data) && array_key_exists('type', $evt->ref->type_data)) {
                        if(is_array($evt->ref->type_data['date'])) {
                            // skip events for 'death' and 'living'
                            if (
                                strcmp(strtolower($evt->ref->type_data['type']['type_name']),'death') == 0 &&
                                strcmp(strtolower($evt->ref->type_data['date'][4]), "living") == 0
                            ) {
                                $dateVal = "living";
                            } else {
                                $dDat = $evt->ref->type_data['date'][4];
                                if(preg_match('/^\d{4}$/', $dDat)) {
                                    $dDat = "Jan 01 ".$dDat;
                                }
                                $dStr = date('Ymd', strtotime($dDat)) . '-' . $eGid;
                                $tStr = strtotime($dDat);
                                $dateVal = date('F j, Y', strtotime($dDat));
                            }
                        }
                        $event = [
                            'date' =>  $dateVal,
                            'type' => $evt->ref->type_data['type']['type_name'],
                            'handle' => $evt->ref_handle,
                            'gramps_id' => $evt->ref->gramps_id
                        ];
                    }
                    // todo get date and possibly place from Citation if not filled in correctly in event
                    if(property_exists($evt->ref, 'place') && !empty($evt->ref->place)) {
                        $plObj = null;
                        if(is_object($evt->ref->place))
                            $plObj = $evt->ref->place;
                        if(is_string($evt->ref->place)) {
                            $plObj = self::getPlaceByHandle($evt->ref->place);
                        }
                        if(is_object($plObj) && property_exists($plObj, 'title') && !empty($plObj->title)) {
                            $event['place'] = $plObj->title;
                        }
                    }
                    if(!empty($event['date']) && !empty($event['place']))
                        $sEvt[$dStr] = $event;
                } else
                    throw new GrampsdbException(sprintf("%s - invalid event object", __METHOD__));
            } else
                throw new GrampsdbException(sprintf("%s - invalid event reference object", __METHOD__));
        }
        return $sEvt;
    }

    public static function getPersonWithEventsByHandle($pHan, $withSubs=false, $subRecursive=false)
    {
        $person = self::getPersonByHandle($pHan,$withSubs);
        $person->events = self::getReferences([
            'obj_class' => 'Person',
            'obj_handle' => $pHan,
            'ref_class' => 'Event'
        ], $withSubs, $subRecursive);

        return $person;
    }
    // </editor-fold desc="person table record accessors">

    // <editor-fold desc="place table record accessors">
    public static function getPlaceByHandle($phan)
    {
        $pRec = self::getDbHandle()->table('place')->where(['handle' => $phan])->first();
        self::prepPlace($pRec);
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
    /**
     * @param $search
     * @param boolean $withSubs  defaults to 'false'
     * @param boolean $subsRecursive  defaults to 'false'
     * @return array|\Illuminate\Support\Collection
     */
    public static function getReferences($search, $withSubs=false, $subsRecursive=false)
    {
        $gRefs = [];
        if(is_integer($search)) {
            array_push($gRefs, self::getDbHandle()->table('reference')->find($search));
        } else if(is_associative_array($search)) {
            $where = [];
            if(array_key_exists('obj_class', $search) && is_string($search['obj_class']))
                $where['obj_class'] = ucfirst(strtolower($search['obj_class']));
            if(array_key_exists('obj_handle', $search) && !empty($search['obj_handle']) && preg_match('/^[a-f0-9]+$/', strtolower($search['obj_handle'])))
                $where['obj_handle'] = strtolower($search['obj_handle']);
            if(array_key_exists('ref_class', $search) && is_string($search['ref_class']))
                $where['ref_class'] = ucfirst(strtolower($search['ref_class']));
            if(array_key_exists('ref_handle', $search) && !empty($search['ref_handle']) && preg_match('/^[a-f0-9]+$/', strtolower($search['ref_handle'])))
                $where['ref_handle'] = strtolower($search['ref_handle']);
            if(count($where) > 0) {
                $gRefs = self::getDbHandle()->table('reference')->where($where)->get();
            } else {
                $gRefs = self::getDbHandle()->table('reference')->get();
            }
        } else {
            $gRefs = self::getDbHandle()->table('reference')->get();
        }

        $refArray = [];
        foreach($gRefs as $r) {
            if ($withSubs == true) {
                $r->obj = self::getSubClass($r->obj_class, $r->obj_handle, false, false);
                $r->obj->type = $r->obj_class;
                $r->ref = self::getSubClass($r->ref_class, $r->ref_handle, $withSubs, $subsRecursive);
                $r->ref->type = $r->ref_class;
            }
            array_push($refArray, $r); // 12360 6 F2F1
        }
        return $refArray;
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

    /**
     * generic wrapper to pull 'subclass' data for record types that have a 'handle' pointer to some other record
     *   e.g. can be used to pull persons for family records, or associated records for reference entries
     *
     * @param string $classType
     * @param string $handle
     * @param boolean $withSubs defaults false
     * @param boolean $subsRecursive  defaults to 'false'
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public static function getSubClass($classType, $handle, $withSubs=false, $subsRecursive=false)
    {
        $subClass = null;
        switch ($classType) {
            case 'citation':
            case 'Citation':
                $subClass = self::getCitationByHandle($handle, $withSubs, $subsRecursive);
                break;
            case 'event':
            case 'Event':
                $subClass = self::getEventByHandle($handle, $withSubs, $subsRecursive);
                break;
            case 'family':
            case 'Family':
                $subClass = self::getFamilyByHandle($handle, $withSubs, $subsRecursive);
                break;
            case 'media':
            case 'Media':
                $subClass = self::getMediaByHandle($handle);
                break;
            case 'note':
            case 'Note':
                $subClass = self::getNoteByHandle($handle, $withSubs, $subsRecursive);
                break;
            case 'Person':
                $subClass = self::getPersonByHandle($handle, $withSubs, $subsRecursive);
                break;
            case 'place':
            case 'Place':
                $subClass = self::getPlaceByHandle($handle, $withSubs, $subsRecursive);
                break;
            case 'repository':
            case 'Repository':
                $subClass = self::getRepositoryByHandle($handle, $withSubs, $subsRecursive);
                break;
            case 'source':
            case 'Source':
                $subClass = self::getSourceByHandle($handle, $withSubs, $subsRecursive);
                break;
            default:
                break;
        }
        return $subClass;
    }

    /**
     * @param string $pid
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
    /**
     * @param string $shan
     * @return object|null
     */
    public static function getSourceByHandle($shan, $withSubs=false, $subsRecursive=false)
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
    /*
     *  'prep' prefixed methods handle the mapping of blob data
     * (adding relevant keys and TYPE values)
     */
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
            $blob_data = self::unpickleCached($cObj->blob_data,'Citation',$cObj->gramps_id);
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
            'date'           => $data[3],
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
            $blob_data = self::unpickleCached($eObj->blob_data,'Event',$eObj->gramps_id);
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
     * @param object $fObj
     * @param boolean $withPersons
     */
    private static function prepFamily(&$fObj, $withPersons=false)
    {
        // decode blob_data if any
        if (property_exists($fObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickleCached($fObj->blob_data,'Family',$fObj->gramps_id);
            if ($blob_data != false) {
                $fObj->type_data = self::mapFamilyData($blob_data);
                unset($fObj->blob_data);
            }
        }
        if ($withPersons == true) {
            if (!empty($fObj->father_handle))
                $fObj->father = self::getPersonByHandle($fObj->father_handle);
            if (!empty($fObj->mother_handle))
                $fObj->mother = self::getPersonByHandle($fObj->mother_handle);
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
            $blob_data = unpyckle($mObj->blob_data);
            $mObj->type_data = self::mapMediaData($blob_data);
            unset($mObj->blob_data);
        }
        // uncomment for just filename
        //$mRec->filename = preg_replace('|.*'.DIRECTORY_SEPARATOR.'([^'.DIRECTORY_SEPARATOR.']+)$|', "$1", $mRec->path);
        // insert relative url from site root for this server
        //$mRec->url = preg_replace('|.*'.DIRECTORY_SEPARATOR.'([^'.DIRECTORY_SEPARATOR.']+)$|', '/'.$mPath."/$1", $mRec->path);
        $media_path = (function_exists('env') ? env('GEDCOM_MEDIA_PATH', 'gedcomx/media') : 'gedcomx/media');
        $mObj->url = get_url_from_aws_bucket(basename($mObj->path), $media_path);
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
            $blob_data = self::unpickleCached($nObj->blob_data,'Note', $nObj->gramps_id);
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

    private static function prepPerson(&$pObj)
    {
        // decode blob_data if any
        if (property_exists($pObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickleCached($pObj->blob_data, 'Person', $pObj->gramps_id);
            if ($blob_data != false) {
                $pObj->type_data = self::mapPersonData($blob_data);
                unset($pObj->blob_data);
            }
        }
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
     * @param object $lObj
     */
    private static function prepPlace(&$lObj)
    {
        // decode blob_data if any
        if (property_exists($lObj, 'blob_data')) {
            // try different methods to unpickle
            $blob_data = self::unpickleCached($lObj->blob_data, 'Place', $lObj->gramps_id);
            if ($blob_data != false) {
                $lObj->type_data = self::mapPlaceData($blob_data);
                unset($lObj->blob_data);
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
            $blob_data = self::unpickleCached($rObj->blob_data, 'Repository', $rObj->gramps_id);
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
            $blob_data = self::unpickleCached($sObj->blob_data,'Source', $sObj->gramps_id);
            if ($blob_data != false) {
                $sObj->type_data = self::mapSourceData($blob_data);
                unset($sObj->blob_data);
            }
        }
    }
    // </editor-fold desc="source blob handler">
    // </editor-fold desc="blob_data handlers">
}