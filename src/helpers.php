<?php


if(!defined('GRAMPSDBROOT')) {
    define('GRAMPSDBROOT', realpath(__DIR__ . DIRECTORY_SEPARATOR .'..'));
}

if (! function_exists('grampsdb_laravel_path')) {
    function grampsdb_laravel_path($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, $path);
    }
}

if(! function_exists('grampsdb_laravel_asset')) {
    function grampsdb_laravel_asset($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'assets', $path);
    }
}
if(! function_exists('grampsdb_laravel_config')) {
    function grampsdb_laravel_config($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'config', $path);
    }
}

if(! function_exists('grampsdb_laravel_database')) {
    function grampsdb_laravel_database($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'database', $path);
    }
}
if(! function_exists('grampsdb_laravel_factories')) {
    function grampsdb_laravel_factories($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'database', 'factories', $path);
    }
}
if(! function_exists('grampsdb_laravel_migrations')) {
    function grampsdb_laravel_migrations($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'database', 'migrations', $path);
    }
}
if(! function_exists('grampsdb_laravel_seeders')) {
    function grampsdb_laravel_seeders($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'database', 'seeders', $path);
    }
}
if(! function_exists('grampsdb_laravel_seeds')) {
    function grampsdb_laravel_seeds($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'database', 'seeds', $path);
    }
}


if(! function_exists('grampsdb_laravel_resources')) {
    function grampsdb_laravel_resources($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'resources', $path);
    }
}
if(! function_exists('grampsdb_laravel_css')) {
    function grampsdb_laravel_css($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'resources', 'css', $path);
    }
}
if(! function_exists('grampsdb_laravel_fonts')) {
    function grampsdb_laravel_fonts($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'resources', 'fonts', $path);
    }
}
if(! function_exists('grampsdb_laravel_js')) {
    function grampsdb_laravel_js($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'resources', 'js', $path);
    }
}
if(! function_exists('grampsdb_laravel_lang')) {
    function grampsdb_laravel_lang($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'resources', 'lang', $path);
    }
}
if(! function_exists('grampsdb_laravel_views')) {
    function grampsdb_laravel_views($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'resources', 'views', $path);
    }
}

if(! function_exists('grampsdb_laravel_public')) {
    function grampsdb_laravel_public($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'public', $path);
    }
}
if(! function_exists('grampsdb_laravel_routes')) {
    function grampsdb_laravel_routes($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'routes', $path);
    }
}
if(! function_exists('grampsdb_laravel_stubs')) {
    function grampsdb_laravel_stubs($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'stubs', $path);
    }
}

if(! function_exists('grampsdb_laravel_models')) {
    function grampsdb_laravel_models($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'src', 'Models', $path);
    }
}
if(! function_exists('grampsdb_laravel_controllers')) {
    function grampsdb_laravel_controllers($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'src', 'Http', 'Controllers', $path);
    }
}
if(! function_exists('grampsdb_laravel_middleware')) {
    function grampsdb_laravel_middleware($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'src', 'Http', 'Middleware', $path);
    }
}
if(! function_exists('grampsdb_laravel_requests')) {
    function grampsdb_laravel_requests($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'src', 'Http', 'Requests', $path);
    }
}
if(! function_exists('grampsdb_laravel_services')) {
    function grampsdb_laravel_services($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'src', 'Services', $path);
    }
}
if(! function_exists('grampsdb_laravel_providers')) {
    function grampsdb_laravel_providers($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'src', 'Providers', $path);
    }
}
if(! function_exists('grampsdb_laravel_helpers')) {
    function grampsdb_laravel_helpers($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'src', 'helpers', $path);
    }
}

if(! function_exists('grampsdb_laravel_storage')) {
    function grampsdb_laravel_storage($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'storage', $path);
    }
}
if(! function_exists('grampsdb_laravel_tests')) {
    function grampsdb_laravel_tests($path = '')
    {
        return \Illuminate\Filesystem\join_paths(GRAMPSDBROOT, 'tests', $path);
    }
}

