<?php

// opendmarc-dashboard - A PHP based viewer of information gathered via
// opendmarc on your systems.  
// Copyright (C) 2016 TechSneeze.com and John Bieling
//
// Available at:
// https://github.com/techsneeze/opendmarc-dashboard
//
// This program is free software: you can redistribute it and/or modify it
// under the terms of the GNU General Public License as published by the Free
// Software Foundation, either version 3 of the License, or (at your option)
// any later version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of  MERCHANTABILITY or
// FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
// more details.
//
// You should have received a copy of the GNU General Public License along with
// this program.  If not, see <http://www.gnu.org/licenses/>.
//
//####################################################################
//### configuration ##################################################
//####################################################################

// Copy opendmarc-dashboard-config.php.sample to 
// opendmarc-dashboard-config.php and edit with the appropriate info
// for your database authentication and location.

//####################################################################
//### functions ######################################################
//####################################################################

function searcharray($value, $key, $array) {
   foreach ($array as $k => $val) {
       if ($val[$key] == $value) {
           return $k;
       }
   }
   return null;
}

function format_date($date, $format) {
	$answer = date($format, strtotime($date));
	return $answer;
};

function tmpl_reportList($allowed_reports, $arrayreport, $arrayIPs, $arraydomains, $dispositions, $dmarcpolicy, $aligned) {
	$reportlist[] = "";
	$reportlist[] = "<!-- Start of report list -->";

	$reportlist[] = "<h1>OpenDMARC Dashboard</h1>";
	$reportlist[] = "<table class='reportlist'>";
	$reportlist[] = "  <thead>";
	$reportlist[] = "    <tr>";
	$reportlist[] = "      <th>Date</th>";
	$reportlist[] = "      <th>Message ID</th>";
	$reportlist[] = "      <th>Reporter</th>";
	$reportlist[] = "      <th>Policy</th>";
	$reportlist[] = "      <th>Disposition</th>";
	$reportlist[] = "      <th>IP</th>";
	$reportlist[] = "      <th>Envelope Domain</th>";
	$reportlist[] = "      <th>From Domain</th>";
	$reportlist[] = "      <th>DKIM Alignment</th>";
	$reportlist[] = "      <th>SPF Alignment</th>";
	$reportlist[] = "    </tr>";
	$reportlist[] = "  </thead>";

	$reportlist[] = "  <tbody>";

	foreach ($allowed_reports[BySerial] as $row) {

		$date_output_format = "r";
		$reportlist[] =  "    <tr>";
		$reportlist[] =  "      <td class='center'>". $row['date']. "</td>";
		$reportlist[] =  "      <td class='center'>". $row['jobid']. "</td>";
		$reportlist[] =  "      <td class='center'>". $arrayreport[searcharray(($row['reporter']), 'id', $arrayreport)]['name']. "</td>";
		$reportlist[] =  "      <td class='center'>". $dmarcpolicy[searcharray(($row['policy']), '0', $dmarcpolicy)]['1']. "</td>";
		$reportlist[] =  "      <td class='center'>". $dispositions[searcharray(($row['disp']), '0', $dispositions)]['1']. "</td>";
		$reportlist[] =  "      <td class='center'>". $arrayIPs[searcharray(($row['ip']), 'id', $arrayIPs)]['addr']. "</td>";
		$reportlist[] =  "      <td class='center'>". $arraydomains[searcharray(($row['env_domain']), 'id', $arraydomains)]['name']. "</td>";
		$reportlist[] =  "      <td class='center'>". $arraydomains[searcharray(($row['from_domain']), 'id', $arraydomains)]['name']. "</td>";
		$reportlist[] =  "      <td class='center'>". $aligned[searcharray(($row['align_dkim']), '0', $aligned)]['1']. "</td>";
		$reportlist[] =  "      <td class='center'>". $aligned[searcharray(($row['align_spf']), '0', $aligned)]['1']. "</td>";
		$reportlist[] =  "    </tr>";
	}
	$reportlist[] =  "  </tbody>";

	$reportlist[] =  "</table>";

	$reportlist[] = "<!-- End of report list -->";
	$reportlist[] = "";

	#indent generated html by 2 extra spaces
	return implode("\n  ",$reportlist);
}

function tmpl_page ($body) {
	$html = array();

	$html[] = "<!DOCTYPE html>";
	$html[] = "<html>";
	$html[] = "  <head>";
	$html[] = "    <title>OpenDMARC Dashboard</title>";
	$html[] = "    <link rel='stylesheet' href='default.css'>";
	$html[] = "  </head>";

	$html[] = "  <body>";

	$html[] = $body;

	$html[] = "  <div class='footer'>Brought to you by <a href='http://www.techsneeze.com'>TechSneeze.com</a> - <a href='mailto:dave@techsneeze.com'>dave@techsneeze.com</a></div>";
	$html[] = "  </body>";
	$html[] = "</html>";

	return implode("\n",$html);
}



//####################################################################
//### main ###########################################################
//####################################################################

// The file is expected to be in the same folder as this script, and it
// must exist.
include "opendmarc-dashboard-config.php";


$aligned = array (
	array("4","yes"),
	array("5","no")
);

$dispositions = array (
	array("0","reject"),
	array("1","reject"),
	array("2","none"),
	array("3","quarantine")
);

$dmarcpolicy = array (
	array("14","unkown"),
	array("15","pass"),
	array("16","reject"),
	array("17","quarantine"),
	array("18","none")
);


// Make a MySQL Connection using mysqli
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
	echo "Error: Failed to make a MySQL connection, here is why: \n";
	echo "Errno: " . $mysqli->connect_errno . "\n";
	echo "Error: " . $mysqli->connect_error . "\n";
	exit;
}

define("BySerial", 1);
define("ByDomain", 2);
define("ByOrganisation", 3);

// Get allowed reports and cache them - using serial as key
$allowed_reports = array();

// Set default value of how many entries to limit the query to
$limit = 90;

// Check if a limit has been passed via URL, and change to that if present
if (isset($_GET["limit"])){
	$limit = $_GET["limit"];
};
$sql = "SELECT * from messages ORDER BY date DESC LIMIT $limit;";
$query = $mysqli->query($sql) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($row = $query->fetch_assoc()) {
	//todo: check ACL if this row is allowed
	if (true) {
		//add data by serial
		$allowed_reports[BySerial][$row['id']] = $row;
		//make a list of serials by domain and by organisation
		$allowed_reports[ByDomain][$row['reporter']][] = $row['reporter'];
		$allowed_reports[ByOrganisation][$row['id']][] = $row['id'];
	}
}

$arrayreport = array();

$reporters = "SELECT * from reporters";
$queryrep = $mysqli->query($reporters) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($fetchrep = $queryrep->fetch_assoc()) {
        $arrayreport[] = $fetchrep;
}
//$blah='1';
//var_dump($arrayreport['id'== $blah]['name']);

$arraydomains= array();

$domains = "SELECT * from domains";
$querydomains = $mysqli->query($domains) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($fetchdomains = $querydomains->fetch_assoc()) {
        $arraydomains[] = $fetchdomains;
}
$blah='5';
//var_dump($arraydomains[searcharray($blah, 'id', $arraydomains)]['name']);

$arrayIPs= array();

$ipaddr = "SELECT * from ipaddr";
$queryips = $mysqli->query($ipaddr) or die("Query failed: ".$mysqli->error." (Error #" .$mysqli->errno.")");
while($fetchips = $queryips->fetch_assoc()) {
        $arrayIPs[] = $fetchips;
}
$blah='5';
//var_dump($arrayIPs[searcharray($blah, 'id', $arrayIPs)]['addr']);

// Generate Page with report list and report data (if a report is selected).
echo tmpl_page( ""
	.tmpl_reportList($allowed_reports, $arrayreport, $arrayIPs, $arraydomains, $dispositions, $dmarcpolicy, $aligned)
);

?>
