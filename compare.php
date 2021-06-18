<?php

/**
 * @throws \JsonException
 */
function compareHosts(array $hosts, int $rounds = 10, int $precision = 5): array
{
    $results = [];
    $totals = [];
    $averages = [];

    for ($i = 0; $i < $rounds; $i++) {
        // se going to use a round-robin approach.
        foreach ($hosts as $endpoint) {
            $result = runBench($endpoint);

            $server = $result['sysinfo']['server_name'];

            if (!isset($results[$server])) {
                $results[$server] = [];
                $totals[$server] = [
                    'math'        => 0,
                    'string'      => 0,
                    'loops'       => 0,
                    'ifelse'      => 0,
                    'calculation' => 0,
                    'total'       => 0,
                ];
                $averages[$server] = [
                    'math'        => 0,
                    'string'      => 0,
                    'loops'       => 0,
                    'ifelse'      => 0,
                    'calculation' => 0,
                    'total'       => 0,
                ];
            }

            $benchmark = $result['benchmark'];

            $numeric = [
                'math'        => (float)substr($benchmark['math'], 0, -5),
                'string'      => (float)substr($benchmark['string'], 0, -5),
                'loops'       => (float)substr($benchmark['loops'], 0, -5),
                'ifelse'      => (float)substr($benchmark['ifelse'], 0, -5),
                'calculation' => (float)substr($benchmark['calculation'], 0, -5),
                'total'       => (float)substr($benchmark['total'], 0, -5),
            ];

            $results[$server][] = $numeric;

            $totals[$server]['math'] += $numeric['math'];
            $totals[$server]['string'] += $numeric['string'];
            $totals[$server]['loops'] += $numeric['loops'];
            $totals[$server]['ifelse'] += $numeric['ifelse'];
            $totals[$server]['calculation'] += $numeric['calculation'];
            $totals[$server]['total'] += $numeric['total'];
        }
    }

    // calculate the averages
    foreach ($totals as $host => $total) {
        $averages[$host] = [
            'math'        => round($total['math'] / $rounds, $precision),
            'string'      => round($total['string'] / $rounds, $precision),
            'loops'       => round($total['loops'] / $rounds, $precision),
            'ifelse'      => round($total['ifelse'] / $rounds, $precision),
            'calculation' => round($total['calculation'] / $rounds, $precision),
            'total'       => round($total['total'] / $rounds, $precision),
        ];
    }

    return [
        'results'  => $results,
        'totals'   => $totals,
        'averages' => $averages,
    ];
}

/**
 * @throws \JsonException
 */
function runBench(string $endpoint)
{
    $handler = curl_init($endpoint);

    curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($handler);
    curl_close($handler);

    return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
}

/** @noinspection HttpUrlsUsage */
$hosts = [
    'debian'      => 'http://web:80/benchmark.php?json',
    'official'    => 'http://web:81/benchmark.php?json',
    'phpdockerio' => 'http://web:82/benchmark.php?json',
    'nami'        => 'http://web:83/benchmark.php?json',
];

$rounds = 10;
$precision = 5;

/** @noinspection PhpUnhandledExceptionInspection */
$results = compareHosts($hosts, $rounds, $precision);

/** @noinspection PhpUnhandledExceptionInspection */
echo json_encode($results['averages'], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT).PHP_EOL;
