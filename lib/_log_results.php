<div class="subnav">
	<ul class="nav nav-pills">
		<li><a href='#report-requests'>Requests</a></li>
		<li><a href='#report-volume'>Volume</a></li>
		<li><a href='#report-requests404'>Requests 404</a></li>
		<li><a href='#report-ip'>IP</a></li>
		<li><a href='#report-ua'>User Agents</a></li>
		<li><a href='#report-suspicious-ua'>Suspicious User Agents</a></li>
	</ul>
</div>

<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
</script>

<section id='report-requests'>
	<div class="page-header"><h1>Requests</h1></div>
	<div id="histo_requests" class="container"></div>
	<?php $log->addHistoJS($log->getRequestsHisto(), 'Requests', 'histo_requests', '#0000ff'); ?>
	<?php $log->displayTable($log->getRequestsList(), $log->getRequestsTotal(), 'Nb requests'); ?>
</section>

<section id='report-requests404'>
	<div class="page-header"><h1>Pages not found (404)</h1></div>
	<div id="histo_404" class="container"></div>
	<?php $log->addHistoJS($log->getRequests404Histo(), 'Requests', 'histo_404', '#ff0000'); ?>
	<?php $log->displayTable($log->getRequests404List(), $log->getRequests404Total(), 'Nb requests'); ?>
</section>

<section id='report-volume'>
	<div class="page-header"><h1>Volume</h1></div>
	<div id="histo_bytes" class="container"></div>
	<?php $log->addHistoJS($log->getBytesHisto(), 'Data (Mo)', 'histo_bytes', '#FFA500'); ?>
	<?php $log->displayTable($log->getBytesList(), $log->getBytesTotal(), 'Data (Mo)'); ?>
</section>

<section id='report-ip'>
	<div class="page-header"><h1>IPs</h1></div>
	<?php $log->displayTable($log->getIpList(), $log->getRequestsTotal(), 'Nb requests'); ?>
</section>

<section id='report-ua'>
	<div class="page-header"><h1>User Agents</h1></div>
	<?php $log->displayTable($log->getUaList(), $log->getRequestsTotal(), 'Nb requests'); ?>
</section>

<section id='report-suspicious-ua'>
	<div class="page-header"><h1>Suspicious User Agents</h1></div>
	<?php $log->displayTable($log->getSuspiciousUaList(), $log->getRequestsTotal(), 'Nb requests'); ?>
</section>
