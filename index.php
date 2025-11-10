<?php
// Process analysis before displaying the form
$log = null;
$error_message = null;

if (isset($_GET['logpath'])) {
    $url_filter = isset($_GET['urlfilter']) ? $_GET['urlfilter'] : '';
    if (trim($url_filter) == '') {
        $url_filter = false;
    }
    $ua_filter = isset($_GET['ua_filter']) ? $_GET['ua_filter'] : '';
    if (trim($ua_filter) == '') {
        $ua_filter = false;
    }
    $ip_filter = isset($_GET['ip_filter']) ? $_GET['ip_filter'] : '';
    if (trim($ip_filter) == '') {
        $ip_filter = false;
    }
    $url_filter = isset($_GET['urlfilter']) ? $_GET['urlfilter'] : '';
    if (trim($url_filter) == '') {
        $url_filter = false;
    }
	$log_filter = isset($_GET['logfilter']) ? $_GET['logfilter'] : '';
	if (trim($log_filter) == '') {
		$log_filter = false;
	}

	$nodep = isset($_GET['nodep']) ? $_GET['nodep'] == '1' : false;

	$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
	if (trim($start_date) == '') {
		$start_date = false;
	}
	$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
	if (trim($end_date) == '') {
		$end_date = false;
	}

	require_once('lib/lib.inc.php');
	try {
		$log = new LogAnalyzer($_GET['logpath'], $url_filter, $ua_filter, $ip_filter, $log_filter, $nodep, $start_date, $end_date, $_GET['histo_period']);

		// Auto-populate dates if they were empty
		if ($start_date === false && $log->getDateMin() !== null) {
			$_GET['start_date'] = $log->getDateMin();
		}
		if ($end_date === false && $log->getDateMax() !== null) {
			$_GET['end_date'] = $log->getDateMax();
		}
	} catch (Exception $e) {
	   $error_message = $e->getMessage();
	}
}
?>
<html>
<head>
	<title>HTTP Log Analyzer</title>
	<link href="bootstrap/css/bootstrap.css" rel="stylesheet">
	<link href="css/styles.css" rel="stylesheet">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>
<body id='body'>
<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand" href="#">HTTP Log Analyzer</a>
    </div>
  </div>
</div>
<div class="container" id="form-log">
	<form class='well form-vertical'>
		<label>Path Apache Log</label>
		<input type='text' class='span5' name='logpath' value='<?php echo isset($_GET['logpath']) ? $_GET['logpath'] : '' ?>' />
		<span class="help-block">Example: /var/www/apache-histo/logs/access.log</span>
		<label>URL Filter (regexp)</label>
		<input type='text' class='span5' name='urlfilter' value='<?php echo isset($_GET['urlfilter']) ? $_GET['urlfilter'] : '' ?>' />
		<span class="help-block">Example: \.jpg$</span>
		<label>Exclude dependencies</label>
		<input type='checkbox' class='span1' name='nodep' value='1' <?php echo isset($_GET['nodep']) && $_GET['nodep'] == '1' ? 'checked' : '' ?> />
        <label>User Agent</label>
        <input type='text' class='span5' name='ua_filter' value='<?php echo isset($_GET['ua_filter']) ? $_GET['ua_filter'] : '' ?>' />
        <label>IP</label>
        <input type='text' class='span5' name='ip_filter' value='<?php echo isset($_GET['ip_filter']) ? $_GET['ip_filter'] : '' ?>' />
		<label>Log Filter (regexp)</label>
		<input type='text' class='span5' name='logfilter' value='<?php echo isset($_GET['logfilter']) ? $_GET['logfilter'] : '' ?>' />
		<span class="help-block">Example: Mozilla</span>
		<label>Start date</label>
		<input type='text' class='span5' name='start_date' value='<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''  ?>' />
		<span class='help-block'>Example: 22/11/2013 08:00</span>
		<label>End date</label>
		<input type='text' class='span5' name='end_date' value='<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : '' ?>' />
		<span class='help-block'>Example: 22/11/2013 20:00</span>
		<label>Histogram period</label>
		<input type='text' class='span5' name='histo_period' value='<?php echo isset($_GET['histo_period']) ? $_GET['histo_period'] : '3600' ?>' /> sec.
		<span class='help-block'></span>
		<button type='submit' class='btn'>Analyze</button>
	</form>
<?php
if ($error_message !== null) {
   echo "<div class='alert alert-error'> Error: " . $error_message . "</div>";
}

if ($log !== null) {
	include('lib/_log_results.php');
}
?>
</div>
</body>
</html>
