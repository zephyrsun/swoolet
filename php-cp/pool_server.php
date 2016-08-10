<?php
// ps -ef | grep pool_| awk '{print $2}'| awk '{system ("kill -9 "$0)}'
ini_set('memory_limit', '2048M');
ini_set("display_errors", "On");

if (!$conf = &$argv[1]) {
    echo 'Please input INI' . PHP_EOL;
    return;
}

$conf = __DIR__ . DIRECTORY_SEPARATOR . $conf;

$cmd = $argv[2];
if (!$cmd) {
    echo 'Usage: pool_server {start|stop|restart}' . PHP_EOL;
    return;
}

if (($conf_arr = parse_ini_file($conf, true)) === false)//for stop && reload && test ini
{
    echo 'bad ini file' . PHP_EOL;
    return;
}

switch ($cmd) {
    case "start":
        pool_server_create($conf);
        break;
    case "reload":
        pool_server_reload((int)file_get_contents("/var/run/con_pool_.pid"));
        echo "Tips: The reload can only modify 'pool_min','pool_max','recycle_num' and 'idel_time'\n";
        die;
        break;
    case "stop":
        pool_server_shutdown((int)file_get_contents("/var/run/con_pool_.pid"));
        break;
    case "restart":
        pool_server_shutdown((int)file_get_contents("/var/run/con_pool_.pid"));
        sleep(1);
        pool_server_create($conf);
        break;
    default:
        break;
}