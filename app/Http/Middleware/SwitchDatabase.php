<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SwitchDatabase
{
    /**
     * Prefix companycode yang pakai DB test
     */
    private const TEST_PREFIX = 'TST';

    public function handle(Request $request, Closure $next)
    {
        $companyCode = session('companycode', '');

        if (str_starts_with($companyCode, self::TEST_PREFIX)) {
            Config::set('database.default', 'mariadb_test');
            DB::purge('mariadb_test');
            DB::reconnect('mariadb_test');
        }

        return $next($request);
    }
}