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
if(! function_Exists('get_python_executable')) {
    function get_python_executable(string $path=null): string
    {
        $pyExe = null;

        if (is_file($path) && is_executable($path)) { // use specified if available
            $pyExe = $path;
        } else if (function_exists('env') && !empty(env('PYTHON_EXE'))) { // use env if available
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
}
if(! function_exists('unpyckle')) {
    function unpyckle(mixed $blob): object
    {
        $bblob = base64_encode($blob);
        $cmd = sprintf("import pickle; import base64; import json; print(json.dumps(pickle.loads(base64.b64decode('%s'))))", $bblob);
        //file_put_contents(base_path("tests")."/blob.out", $blob);
        $pcmd = sprintf("%s -c \"%s\"", get_python_executable(), $cmd);
        $result = exec($pcmd);
        $resdec = json_decode($result);
        return $resdec;
    }
}
if(! function_exists('unpickle')) {
    /**
     * call either a pyinstaller binary or python script with raw blob data to be unpickled
     *
     * @param string $b binary data of blob
     * @return false|mixed
     */
    function unpickle(mixed $b): ?object
    {
        $cmd = realpath(__DIR__ . "/../bin/unpickle");
        // see if an environment unpickle binary has been specified
        if (function_exists('env') && is_file(env('UNPICKLE_BINARY')) && is_executable(env('UNPICKLE_BINARY')))
            $cmd = base_path(env('UNPICKLE_BINARY'));
        if (!(is_file($cmd) && is_executable($cmd))) { // make sure unpickle cmd exists
            // try to see if the python script exists if no binary does
            if (is_file($cmd . ".py")) {
                $cmd = sprintf("%s %s.py", get_python_executable(), $cmd);
            } else
                return unpyckle($b); // try direct python call
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

            if (is_json_string($output))
                return json_decode($output);
            else
                return null;
        }
        return null;
    }
}
if(! function_exists('utf8ize')) {
    function utf8ize(mixed $mixed): mixed
    {
        if (is_object($mixed))
            $mixed = (array)$mixed;
        if (is_array($mixed)) {
            // make sure any blob data has already been unpickled to an array
            if (array_key_exists('blob_data', $mixed) && !is_array($mixed['blob_data']))
                $mixed['blob_data'] = unpyckle($mixed['blob_data']);
            foreach ($mixed as $key => $value) {
                $mixed[$key] = utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }
        return $mixed;
    }
}
if(! function_exists('is_associative_array')) {
    function is_associative_array(mixed $arr): bool
    {
        if (!is_array($arr)) return false;
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
if(! function_exists('is_json_string')) {
    function is_json_string(mixed $str): bool
    {
        if (is_string($str) && !empty($str)) {
            json_decode($str);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return false;    }
}
if(! function_exists('is_binary_data')) {
    function is_binary_data($value): bool
    {
        return false === mb_detect_encoding((string)$value, null, true);
    }
}

if(! defined('URL_SPECIAL')) {
    /**
     * characters special to a url that can be used in urlEncode
     * @see urlEncode()
     */
    define('URL_SPECIAL', [
        "/" => '%2F',
        "=" => '%3D',
        "?" => '%3F',
        "%" => '%25',
        "&" => '%26',
    ]);
}
if(! defined('URL_ENTITIES')) {
    /**
     * list of characters and replacements which can be used with urlEncode()
     * @see urlEncode()
     */
    define('URL_ENTITIES', [
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
    ]);
}
if(! function_exists('url_encode_string')) {
    function url_encode_string(string $string, $entities=null)
    {
        if (is_null($entities))
            $entities = URL_ENTITIES;

        if (!is_associative_array($entities))
            throw new \Exception("invalid replacement list");

        return str_replace(array_keys($entities), array_values($entities), $string);
    }
}
if(! function_exists('get_url_from_aws_bucket')) {
    function get_url_from_aws_bucket(string $filename, string $path=null, string $bucket=null, $region=null): string
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
        $fullpath = url_encode_string((empty($path) ? '' : $path . "/") . $filename);

        return sprintf('https://%s.s3.%s.amazonaws.com/%s', $bucket, $region, $fullpath);
    }
}