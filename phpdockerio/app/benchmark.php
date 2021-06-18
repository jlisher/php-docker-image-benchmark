<?php
/**
 * PHP Script to benchmark PHP and MySQL-Server.
 * inspired by / thanks to:
 * - www.php-benchmark-script.com (Alessandro Torrisi)
 * - www.webdesign-informatik.de
 *
 * @license MIT
 * @noinspection HtmlDeprecatedAttribute
 * @noinspection PhpExpressionResultUnusedInspection
 * @noinspection LoopWhichDoesNotLoopInspection
 * @noinspection MissingOrEmptyGroupStatementInspection
 * @noinspection PhpStatementHasEmptyBodyInspection
 * @noinspection PhpUndefinedFunctionInspection
 * @noinspection NestedPositiveIfStatementsInspection
 * @noinspection UnnecessarySemicolonInspection
 * @noinspection PhpUnnecessarySemicolonInspection
 * @noinspection PhpUnusedLocalVariableInspection
 * @noinspection PhpIncludeInspection
 */

// -----------------------------------------------------------------------------
// Setup
// -----------------------------------------------------------------------------
set_time_limit(120); // 2 minutes
if (file_exists(__DIR__.'/environment.php')) {
    include_once __DIR__.'/environment.php';
}

$options = [];

if (!empty($_ENV['HOSTNAME'])) {
    $_SERVER['SERVER_NAME'] = $_ENV['HOSTNAME'];
}

if (!empty(getenv('HOSTNAME'))) {
    $_SERVER['SERVER_NAME'] = getenv('HOSTNAME');
}

// Show or hide the server name and IP address
$showServerName = true;

// Optional: mysql performance test
// $options['db.host'] = 'mysql';
// $options['db.user'] = 'user';
// $options['db.pw'] = 'pass';
// $options['db.name'] = 'data';

// -----------------------------------------------------------------------------
// Main
// -----------------------------------------------------------------------------
// check performance
$benchmarkResult = test_benchmark($options);

// benchmark.php?json
if (isset($_GET['json'])) {
    // Json output
    header('Content-Type', 'application/json');
    try {
        echo json_encode($benchmarkResult, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    } catch (JsonException $e) {
        http_response_code(500);
    }
} else {
    // html output
    print_html_result($benchmarkResult, $showServerName);
}

exit;

// -----------------------------------------------------------------------------
// Benchmark functions
// -----------------------------------------------------------------------------

function test_benchmark(array $settings): array
{
    $result = [];
    $result['version'] = '1.6';
    $result['sysinfo']['time'] = date('Y-m-d H:i:s');
    $result['sysinfo']['php_version'] = PHP_VERSION;
    $result['sysinfo']['platform'] = PHP_OS;
    $result['sysinfo']['server_name'] = $_SERVER['SERVER_NAME'];
    $result['sysinfo']['server_addr'] = $_SERVER['SERVER_ADDR'];
    $result['sysinfo']['xdebug'] = in_array('xdebug', get_loaded_extensions());

    $timeStart = microtime(true);

    test_math($result);
    test_string($result);
    test_loops($result);
    test_ifelse($result);

    $result['benchmark']['calculation'] = timer_diff($timeStart).' sec.';

    if (isset($settings['db.host'])) {
        test_mysql($result, $settings);
    }

    $result['benchmark']['total'] = timer_diff($timeStart).' sec.';

    return $result;
}

function test_math(&$result, $count = 140000)
{
    $timeStart = microtime(true);

    $mathFunctions = ["abs", "acos", "asin", "atan", "bindec", "floor", "exp", "sin", "tan", "pi", "is_finite", "is_nan", "sqrt"];
    foreach ($mathFunctions as $key => $function) {
        if (!function_exists($function)) {
            unset($mathFunctions[$key]);
        }
    }
    for ($i = 0; $i < $count; $i++) {
        foreach ($mathFunctions as $function) {
            $r = $function($i);
        }
    }

    $result['benchmark']['math'] = timer_diff($timeStart).' sec.';
}

function test_string(&$result, $count = 130000)
{
    $timeStart = microtime(true);

    $stringFunctions = ["addslashes", "chunk_split", "metaphone", "strip_tags", "md5", "sha1", "strtoupper", "strtolower", "strrev", "strlen", "soundex", "ord"];
    foreach ($stringFunctions as $key => $function) {
        if (!function_exists($function)) {
            unset($stringFunctions[$key]);
        }
    }
    $string = "the quick brown fox jumps over the lazy dog";
    for ($i = 0; $i < $count; $i++) {
        foreach ($stringFunctions as $function) {
            $r = $function($string);
        }
    }

    $result['benchmark']['string'] = timer_diff($timeStart).' sec.';
}

function test_loops(&$result, $count = 19000000)
{
    $timeStart = microtime(true);
    for ($i = 0; $i < $count; ++$i) {
        ;
    }
    $i = 0;
    while ($i < $count) {
        ++$i;
    }

    $result['benchmark']['loops'] = timer_diff($timeStart).' sec.';
}

function test_ifelse(&$result, $count = 9000000)
{
    $timeStart = microtime(true);

    for ($i = 0; $i < $count; $i++) {
        if ($i === -1) {
        } elseif ($i === -2) {
        } else {
            if ($i === -3) {
            }
        }
    }

    $result['benchmark']['ifelse'] = timer_diff($timeStart).' sec.';
}

function test_mysql(&$result, $settings): array
{
    $timeStart = microtime(true);

    $link = mysqli_connect($settings['db.host'], $settings['db.user'], $settings['db.pw']);
    $result['benchmark']['mysql_connect'] = timer_diff($timeStart).' sec.';

    mysqli_select_db($link, $settings['db.name']);
    $result['benchmark']['mysql_select_db'] = timer_diff($timeStart).' sec.';

    $dbResult = mysqli_query($link, 'SELECT VERSION() as version;');
    $arr_row = mysqli_fetch_array($dbResult);
    $result['sysinfo']['mysql_version'] = $arr_row['version'];
    $result['benchmark']['mysql_query_version'] = timer_diff($timeStart).' sec.';

    $query = "SELECT BENCHMARK(1000000, AES_ENCRYPT('hello', UNHEX('F3229A0B371ED2D9441B830D21A390C3')));";
    mysqli_query($link, $query);
    $result['benchmark']['mysql_query_benchmark'] = timer_diff($timeStart).' sec.';

    mysqli_close($link);

    $result['benchmark']['mysql_total'] = timer_diff($timeStart).' sec.';

    return $result;
}

function timer_diff($timeStart): string
{
    return number_format(microtime(true) - $timeStart, 4);
}

function print_html_result(array $data, bool $showServerName = true)
{
    echo "<!DOCTYPE html>\n<html lang=\"en\"><head><title></title>\n";
    echo "<style>
       table a:link {
        color: #666;
        font-weight: bold;
        text-decoration:none;
    }
    table a:visited {
        color: #999999;
        font-weight:bold;
        text-decoration:none;
    }
    table a:active,
    table a:hover {
        color: #bd5a35;
        text-decoration:underline;
    }
    table {
        font-family:Arial, Helvetica, sans-serif;
        color:#666;
        font-size:12px;
        text-shadow: 1px 1px 0 #fff;
        background:#eaebec;
        margin:20px;
        border:#ccc 1px solid;
        -moz-border-radius:3px;
        -webkit-border-radius:3px;
        border-radius:3px;
        -moz-box-shadow: 0 1px 2px #d1d1d1;
        -webkit-box-shadow: 0 1px 2px #d1d1d1;
        box-shadow: 0 1px 2px #d1d1d1;
    }
    table th {
        padding:8px 15px 8px 8px;
        border-top:1px solid #fafafa;
        border-bottom:1px solid #e0e0e0;
        text-align: left;
        background: #ededed;
        background: -webkit-gradient(linear, left top, left bottom, from(#ededed), to(#ebebeb));
        background: -moz-linear-gradient(top,  #ededed,  #ebebeb);
    }
    table th:first-child {
        text-align: left;
        padding-left:10px;
    }
    table tr:first-child th:first-child {
        -moz-border-radius-topleft:3px;
        -webkit-border-top-left-radius:3px;
        border-top-left-radius:3px;
    }
    table tr:first-child th:last-child {
        -moz-border-radius-topright:3px;
        -webkit-border-top-right-radius:3px;
        border-top-right-radius:3px;
    }
    table tr {
        padding-left:10px;
    }
    table td:first-child {
        text-align: left;
        padding-left:10px;
        border-left: 0;
    }
    table td {
        padding:8px;
        border-top: 1px solid #ffffff;
        border-bottom:1px solid #e0e0e0;
        border-left: 1px solid #e0e0e0;
        background: #fafafa;
        background: -webkit-gradient(linear, left top, left bottom, from(#fbfbfb), to(#fafafa));
        background: -moz-linear-gradient(top,  #fbfbfb,  #fafafa);
    }
    table tr.even td {
        background: #f6f6f6;
        background: -webkit-gradient(linear, left top, left bottom, from(#f8f8f8), to(#f6f6f6));
        background: -moz-linear-gradient(top,  #f8f8f8,  #f6f6f6);
    }
    table tr:last-child td {
        border-bottom:0;
    }
    table tr:last-child td:first-child {
        -moz-border-radius-bottomleft:3px;
        -webkit-border-bottom-left-radius:3px;
        border-bottom-left-radius:3px;
    }
    table tr:last-child td:last-child {
        -moz-border-radius-bottomright:3px;
        -webkit-border-bottom-right-radius:3px;
        border-bottom-right-radius:3px;
    }
    table tr:hover td {
        background: #f2f2f2;
        background: -webkit-gradient(linear, left top, left bottom, from(#f2f2f2), to(#f0f0f0));
        background: -moz-linear-gradient(top,  #f2f2f2,  #f0f0f0);	
    }
    </style>
    </head>
    <body>";

    $result = '<table cellspacing="0">';
    $result .= '<thead><tr><th>System Info</th><th></th></tr></thead>';
    $result .= '<tbody>';
    $result .= '<tr class="even"><td>Version</td><td>'.h($data['version']).'</td></tr>';
    $result .= '<tr class="even"><td>Time</td><td>'.h($data['sysinfo']['time']).'</td></tr>';

    if (!empty($data['sysinfo']['xdebug'])) {
        // You are running the benchmark with xdebug enabled. This has a major impact on runtime performance.
        $result .= '<tr class="even"><td>Xdebug</td><td><span style="color: darkred">'
                   .h('Warning: Xdebug is enabled!')
                   .'</span></td></tr>';
    }

    $result .= '<tr class="even"><td>PHP Version</td><td>'.h($data['sysinfo']['php_version']).'</td></tr>';
    $result .= '<tr class="even"><td>Platform</td><td>'.h($data['sysinfo']['platform']).'</td></tr>';

    if ($showServerName === true) {
        $result .= '<tr class="even"><td>Server name</td><td>'.h($data['sysinfo']['server_name']).'</td></tr>';
        $result .= '<tr class="even"><td>Server address</td><td>'.h($data['sysinfo']['server_addr']).'</td></tr>';
    }

    $result .= '</tbody>';

    $result .= '<thead><tr><th>Benchmark</th><th></th></tr></thead>';
    $result .= '<tbody>';
    $result .= '<tr><td>Math</td><td>'.h($data['benchmark']['math']).'</td></tr>';
    $result .= '<tr><td>String</td><td>'.h($data['benchmark']['string']).'</td></tr>';
    $result .= '<tr><td>Loops</td><td>'.h($data['benchmark']['loops']).'</td></tr>';
    $result .= '<tr><td>If Else</td><td>'.h($data['benchmark']['ifelse']).'</td></tr>';
    $result .= '<tr class="even"><td>Calculation total</td><td>'.h(
            $data['benchmark']['calculation']
        ).'</td></tr>';
    $result .= '</tbody>';

    if (isset($data['sysinfo']['mysql_version'])) {
        $result .= '<thead><tr><th>MySQL</th><th></th></tr></thead>';
        $result .= '<tbody>';
        $result .= '<tr><td>MySQL Version</td><td>'.h($data['sysinfo']['mysql_version']).'</td></tr>';
        $result .= '<tr><td>MySQL Connect</td><td>'.h($data['benchmark']['mysql_connect']).'</td></tr>';
        $result .= '<tr><td>MySQL Select DB</td><td>'.h($data['benchmark']['mysql_select_db']).'</td></tr>';
        $result .= '<tr><td>MySQL Query Version</td><td>'.h($data['benchmark']['mysql_query_version']).'</td></tr>';
        $result .= '<tr><td>MySQL Benchmark</td><td>'.h($data['benchmark']['mysql_query_benchmark']).'</td></tr>';
        $result .= '<tr class="even"><td>MySQL Total</td><td>'.h($data['benchmark']['mysql_total']).'</td></tr>';
        $result .= '</tbody>';
    }

    $result .= '<thead><tr><th>Total</th><th>'.h($data['benchmark']['total']).'</th></tr></thead>';
    $result .= '</table>';

    echo $result;

    echo "\n</body></html>";
}

function h($v): string
{
    return htmlentities($v);
}
