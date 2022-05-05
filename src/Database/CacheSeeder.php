<?php

namespace Treii28\Grampsdb\Database;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Treii28\Grampsdb\GrampsdbHelper;

class CacheSeeder extends \Illuminate\Database\Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $gdbName = Config::get('grampsdb.database.default');
        $gdb = DB::connection($gdbName);

        $blob_tables = [
            'citation',
            'event',
            'family',
            'media',
            'note',
            'person',
            'place',
            'repository',
            'source'
        ];
        foreach($blob_tables as $t) {
            printf("caching %s blob data: ".PHP_EOL, ucfirst($t));
            $d = $gdb->table($t)->get();
            $dc = $d->count();
            $c = 0;
            foreach($d as $r) {
                if(property_exists($r, 'blob_data')) {
                    printf("\r                    \r%d of %d", $c++, $dc);
                    GrampsdbHelper::unpickleCached($r->blob_data, ucfirst($t), $r->gramps_id);
                }
            }
            echo PHP_EOL;
        }
    }
}
