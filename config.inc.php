<?php

/*
 * HTTP Log Format Definition
 */
//$config['log_format']['regexp'] = '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+)/';
$config['log_format']['regexp'] =   '/^(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\S+) (.*)/';
$config['log_format']['ip_index'] = 1;
$config['log_format']['ua_index'] = 13;
$config['log_format']['date_index'] = 4;
$config['log_format']['date_format'] = 'd/M/Y';
$config['log_format']['time_index'] = 5;
$config['log_format']['time_format'] = 'H:i:s';
$config['log_format']['tz_index'] = 6;
$config['log_format']['tz_format'] = 'O';
$config['log_format']['url_index'] = 8;
$config['log_format']['bytes_index'] = 11;
$config['log_format']['status_index'] = 10;
$config['log_format']['method_index'] = 7;

// Maximum number of histogram bars allowed
$config['nb_bars_max'] = 1000;

// Number of rows in the detail table
$config['nb_top_results'] = 200;