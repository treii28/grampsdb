<?php

namespace Treii28\Grampsdb;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GrampsdbHelperTest extends TestCase
{
    public function testHelper()
    {
        $db = DB::connection('grampsdb');
        $this->assertInstanceOf('Illuminate\Database\Connection', $db);
        /** @var \stdClass $record */
        $record = $db->table('person')
            ->select(['*'])
            ->where('given_name', 'Scott Webster')
            ->first();
        $this->assertIsObject($record);
        $blob = $record->blob_data;
        $data = unpickle($blob); // now in helper
        $this->assertIsArray($data);
    }
}
