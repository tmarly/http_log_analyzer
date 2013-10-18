<?php
require_once('config.inc.php');

class LogAnalyzer {
	/** Number of seconds used to represent one bar of the histo */
	private $histo_period;

	/** histogram of requests */
	private $requests_histo = array();
	/** url list, on the form $url => nb requests */
	private $requests_list = array();
	/** total number of requests */
	private $requests_total = 0;

	/** histogram of transferred bytes */
	private $bytes_histo = array();
	/** url list, on the form $url => nb bytes (Mo) */
	private $bytes_list = array();
	/** total number of transferred bytes (Mo) */
	private $bytes_total = 0;

	/** histogram of requests 404 */
	private $requests404_histo = array();
	/** url list, on the form $url_404 => nb requests */
	private $requests404_list = array();
	/** total number of requests 404 */
	private $requests404_total = 0;

	/** histogram of time consumption */
	private $speed_histo = array();
	/** url list, on the form $url => speed */
	private $speed_list = array();
	/** total consummed time */
	private $speed_total = 0;

	/** list of IPs */
	private $ip_list = array();

	/** list of User Agents */
	private $ua_list = array();

	/** counter used to give a new id for each new graph */
	private static $nb_histo_displayed = 0;

	/**
	 * Open the given log file and store data in memory
	 * @param $filename Absolute file path.
	 * @param $histo_dela In seconds.
	 * @param $url_filter regexp, or false if no regexp.
	 */
	public function __construct($filename,$url_filter, $log_filter, $histo_period) {
		global $config;

		// make a PECL regexp
		if ($url_filter !== false) {
			$url_filter = '/' . $url_filter . '/';
		}
		if ($log_filter !== false) {
			$log_filter = '/' . $log_filter . '/';
		}

		// Convert string => int ! and store it for later
		$histo_period = intval($histo_period);
		$this->histo_period = $histo_period;

		// Read the file
		$fp = fopen($filename, 'r');
		if ($fp == false) {
			throw new Exception('Can not open ' . $filename . ' (check: does the file exists ? is the file readable by http server ? is the php directive open_basedir or safe_mode ok ? A solution for the latest case is to move the log file in another directory)');
		}
		$index_min = PHP_INT_MAX;
		$index_max = 0;
		$datetime_format = $config['log_format']['date_format'] . ' ' . $config['log_format']['time_format'] . ' ' . $config['log_format']['tz_format'];
		$i_max = max($config['log_format']['url_index'], $config['log_format']['date_index'], $config['log_format']['time_index'], $config['log_format']['tz_index'], $config['log_format']['bytes_index'], $config['log_format']['status_index']);
		while (($log = fgets($fp)) !== false) {
			if ($log_filter !== false && preg_match($log_filter, $log) == 0) {
				continue;
			}
			$matches = array();
			$regex = $config['log_format']['regexp'];
			preg_match($regex ,$log, $matches);

			// is it an interesting log ?
			$nb_matches = count($matches);
			if ($nb_matches > $i_max && ($url_filter == false || preg_match($url_filter, $matches[$config['log_format']['url_index']]) > 0)) {
		
				// compute the timestamp
				$timestamp = DateTime::createFromFormat($datetime_format, $matches[$config['log_format']['date_index']] . " " . $matches[$config['log_format']['time_index']] . " " . $matches[$config['log_format']['tz_index']])->getTimestamp();

				// Compute the column (bar) number
				$index = floor($timestamp / $histo_period) * $histo_period;
				$index_min = min($index_min, $index);
				$index_max = max($index_max, $index);
				
				// Ok, now we store all data
				$url = $matches[$config['log_format']['url_index']];
				
				// Requests
				$this->requests_histo[$index]++;
				$this->requests_list[$url]++;
				$this->requests_total++;

				// Bytes
				if ($config['log_format']['bytes_index'] >= 0) {
					$bytes = intval($matches[$config['log_format']['bytes_index']]);
					$this->bytes_histo[$index] += $bytes;
					$this->bytes_list[$url] += $bytes;
					$this->bytes_total += $bytes;
				}

				// 404
				if ($matches[$config['log_format']['status_index']] == '404') {
					$this->requests404_histo[$index]++;
					$this->requests404_list[$url]++;
					$this->requests404_total++;
				}

				// IPs
				$ip = $matches[$config['log_format']['ip_index']];
				if (!isset($this->ip_list[$ip])) {
					$this->ip_list[$ip] = 0;
				}
				$this->ip_list[$ip]++;

				// User Agents
				$ua = $matches[$config['log_format']['ua_index']];
				if (!isset($this->ua_list[$ua])) {
					$this->ua_list[$ua] = 0;
				}
				$this->ua_list[$ua]++;


				// security
				if (count($this->requests_histo) > $config['nb_bars_max']) {
					throw new Exception("Too many columns in the graph, please increase the Histogram Period");
				}
			} 
//else {
//	echo "<pre>";
//	var_dump($nb_matches);
//	var_dump($log);
//	echo "</pre><br/>";
//}
	    }
		fclose($fp);

	    // Make sure a value is defined for every bar, and optionnaly change units
	    if ($this->requests_total > 0) {
		    for ($index = $index_min; $index <= $index_max; $index += $histo_period) {

		    	// Requests
		    	if (!isset($this->requests_histo[$index])) {
		    		$this->requests_histo[$index] = 0;
		    	}

		    	// Volume
		    	if (!isset($this->bytes_histo[$index])) {
		    		$this->bytes_histo[$index] = 0;
		    	} else {
		    		// Change unit: byte => Mo
		    		$this->bytes_histo[$index] = round($this->bytes_histo[$index] / 1024 / 1024);
		    	}

		    	// 404
		    	if (!isset($this->requests404_histo[$index])) {
		    		$this->requests404_histo[$index] = 0;
		    	}

		    	// Speed
		    	if (!isset($this->speed_histo[$index])) {
		    		$this->speed_histo[$index] = 0;
		    	}
		    }
		}
		// sort histo by timestamp
		ksort($this->requests_histo);
		ksort($this->bytes_histo);
		ksort($this->requests404_histo);
		ksort($this->speed_histo);

		// Keep only the N top values for requests
		arsort($this->requests_list);
		$this->requests_list = array_slice($this->requests_list, 0, $config['nb_top_results'], true);

		// Keep only the N top values for volume
		arsort($this->bytes_list);
		$this->bytes_list = array_slice($this->bytes_list, 0, $config['nb_top_results'], true);
		$this->bytes_total = round($this->bytes_total / 1024 / 1024); // byte => Mo
		foreach($this->bytes_list as $url => $bytes) {
			$this->bytes_list[$url] = round($bytes / 1024 / 1024);
		}

		// Keep only the N top values for 404
		arsort($this->requests404_list);
		$this->requests404_list = array_slice($this->requests404_list, 0, $config['nb_top_results'], true);

		// Keep only the N top values for IPs
		arsort($this->ip_list, SORT_NUMERIC);
		$this->ip_list = array_slice($this->ip_list, 0, $config['nb_top_results'], true);

		// Keep only the N top values for User Agetns
		arsort($this->ua_list, SORT_NUMERIC);
		$this->ua_list = array_slice($this->ua_list, 0, $config['nb_top_results'], true);

	}

	/**
	 * Display a graph 
	 * (@TODO in fact, has nothing to do in this class I guess :)
	 * @param $histo The histogram (such as $this->getRequestsHisto())
	 * @param $legend The legend to display
	 * @param $div_name The div id that muse contain by the graph
	 * @param $color
	 */
	public function addHistoJS($histo, $legend, $div_name, $color) {
		$nb_histo_displayed++;

		// No data => no graph !
		if (count($histo) == 0) {
			echo "No data";
			return;
		}

		// The time format is not the same depending on the scale
		$data_js = '[[\'Time\', \'' . $legend . '\']';
		if ($this->histo_period < 60) {
			$format = "H:i:s";
		} else if ($this->histo_period < 3600) {
			$format = "H:i";
		} else {
			$format = "H:i";
		}

		// Construct a string containing a javascript declaration of the array contianing data
		foreach($histo as $timestamp => $column) {
			$data_js .= ', [\'' . date($format, $timestamp) . '\', ' . $column . ']';
		}
		$data_js .= ']';

		// Ok, time to display data !
		?>
			<script type="text/javascript">
		      google.setOnLoadCallback(drawHisto<?php echo LogAnalyzer::$nb_histo_displayed; ?>);
		      function drawHisto<?php echo LogAnalyzer::$nb_histo_displayed; ?>() {
		        var data = google.visualization.arrayToDataTable(<?php echo $data_js; ?>);	
		        var chart = new google.visualization.ColumnChart(document.getElementById('<?php echo $div_name; ?>'));
				var options = {
		          colors: ['<?php echo $color; ?>']
		        };
		                chart.draw(data, options);
		       }
		   </script>
        <?php

        // Over !
	}

	/**
	 * Display a detailed table
	 * (@TODO in fact, has nothing to do in this class I guess :)
	 */
	public function displayTable($array, $total, $legend) {
		echo '<table class="table  table-bordered table-striped table-condensed"><thead><tr><th>URL</th><th>' . htmlentities($legend) . '</th></thead><tbody>';
		foreach($array as $url => $nb) {
			$drill_down_url = $_SERVER['PHP_SELF'] . '?logpath=' . urlencode($_GET['logpath']) . '&urlfilter=' . urlencode('^' . preg_quote($url, '/') . '$') . '&histo_period=' . $_GET['histo_period'];
			echo '<tr><td><a title="drill down" href="' . $drill_down_url . '">' . $url . '</a></td><td class="occurences">' . $nb . '</td></tr>';
		}
		echo '<tr><th>Total</th><th class="occurences">' . $total . '</th></tr>';
		echo '</tbody></table>';
	}

	/**
	 *  @return histogram of requests
	 */
	public function getRequestsHisto() {
		return $this->requests_histo;
	}

	/**
	 * @return url list, on the form $url => nb requests 
	 */
	public function getRequestsList() {
		return $this->requests_list;
	}

	/**
	 * @return total number of requests 
	 */
	public function getRequestsTotal() {
		return $this->requests_total;
	}

	/**
	 *  @return histogram of transferred bytes
	 */
	public function getBytesHisto() {
		return $this->bytes_histo;
	}

	/**
	 * @return url list, on the form $url => nb bytes (Mo)
	 */
	public function getBytesList() {
		return $this->bytes_list;
	}

	/**
	 * @return total number of transferred bytes (Mo)
	 */
	public function getBytesTotal() {
		return $this->bytes_total;
	}

	/**
	 *  @return histogram of requests 404
	 */
	public function getRequests404Histo() {
		return $this->requests404_histo;
	}

	/**
	 * @return url list, on the form $url_404 => nb requests 
	 */
	public function getRequests404List() {
		return $this->requests404_list;
	}

	/**
	 * @return total number of requests 404
	 */
	public function getRequests404Total() {
		return $this->requests404_total;
	}

	/**
	 *  @return histogram of time consumption
	 */
	public function getSpeedHisto() {
		return $this->speed_histo;
	}

	/**
	 * @return url list, on the form $url => consummed time
	 */
	public function getSpeedList() {
		return $this->speed_list;
	}

	/**
	 * @return total consummed time
	 */
	public function getSpeedTotal() {
		return $this->speed_total;
	}

	/**
	 * @return the ip list
	 */
	public function getIpList() {
		return $this->ip_list;
	}

	/**
	 * @return the user agent list
	 */
	public function getUaList() {
		return $this->ua_list;
	}
}
