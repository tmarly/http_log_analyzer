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
		<input type='text' class='span5' name='logpath' value='<?php echo $_GET['logpath']; ?>' />
		<span class="help-block">Example: /var/log/apache2/access.log</span>
		<label>URL Filter (regexp)</label>
		<input type='text' class='span5' name='urlfilter' value='<?php echo $_GET['urlfilter'] ?>' />
		<span class="help-block">Example: \.jpg$</span>
		<label>Log Filter (regexp)</label>
		<input type='text' class='span5' name='logfilter' value='<?php echo $_GET['logfilter'] ?>' />
		<span class="help-block">Example: Mozilla</span>
		<label>Start date</label>
		<input type='text' class='span5' name='start_date' value='<?php echo $_GET['start_date']  ?>' /> 
		<span class='help-block'>Example: 22/11/2013 08:00</span>
		<label>End date</label>
		<input type='text' class='span5' name='end_date' value='<?php echo $_GET['end_date']  ?>' /> 
		<span class='help-block'>Example: 22/11/2013 20:00</span>
		<label>Histogram period</label>
		<input type='text' class='span5' name='histo_period' value='<?php echo $_GET['histo_period'] ? $_GET['histo_period'] : '3600' ?>' /> sec.
		<span class='help-block'></span>
		<button type='submit' class='btn'>Analyze</button>
	</form>
<?php
if (isset($_GET['logpath'])) {
	$url_filter = isset($_GET['urlfilter']) ? $_GET['urlfilter'] : '';
	if (trim($url_filter) == '') {
		$url_filter = false;
	}
	$log_filter = isset($_GET['logfilter']) ? $_GET['logfilter'] : '';
	if (trim($log_filter) == '') {
		$log_filter = false;
	}
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
		$log = new LogAnalyzer($_GET['logpath'], $url_filter, $log_filter, $start_date, $end_date, $_GET['histo_period']);
		include('lib/_log_results.php');
	} catch (Exception $e) {
	   echo "<div class='alert alert-error'> Error: " . $e->getMessage() . "</div>";
	}
}
 
?>
</div>
</body>
</html>
