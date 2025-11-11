<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    public function show(?string $connection = null)
    {
        $conn = $connection ?: config('database.default');

        // MySQL style introspection
        $tableRows = DB::connection($conn)->select('SHOW TABLES');

        // get column name of tables list (depends on DB name)
        $first = (array) ($tableRows[0] ?? []);
        $tables = [];
        if ($first) {
            $key = array_keys($first)[0];
            foreach ($tableRows as $r) {
                $tables[] = $r->$key;
            }
        }

        $schema = [];
        foreach ($tables as $t) {
            $cols = DB::connection($conn)->select("SHOW COLUMNS FROM `$t`");
            $schema[$t] = array_map(function ($c) {
                return [
                    'name' => $c->Field,
                    'type' => $c->Type,
                    'null' => $c->Null,
                    'key'  => $c->Key,
                    'default' => $c->Default,
                    'extra' => $c->Extra,
                ];
            }, $cols);
        }

        return view('services.database_show', [
            'connection' => $conn,
            'schema' => $schema,
        ]);
    }
}
