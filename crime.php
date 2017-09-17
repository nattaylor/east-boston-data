<?php
/**
column names must be UPPERCASE and doublequoted; values must be single quoted

areas:
* The Heights
* Lower Heights
* Eagle Hill
* Maverick Landing
* Jefferies Point

7	Orient Heights
8	Orient Heights
9	Orient Heights
10	Orient Heights
11	Lower Heights
12	Lower Heights
13	Lower Heights
14	Lower Heights
15	Lower Heights
16	Eagle Hill
17	Eagle Hill
18	Eagle Hill
19	Eagle Hill
20	Eagle Hill
21	Eagle Hill
22	Eagle Hill
23	Eagle Hill
24	Eagle Hill
25	Eagle Hill
26	Eagle Hill
27	Eagle Hill
28	Maverick Landing
29	Jeffries Point
30	Jeffries Point
31	Jeffries Point
32	Jeffries Point
33	Jeffries Point
34	Jeffries Point
35	Eagle Hill
36	Lower Heights
629	Eagle Hill
765	Eagle Hill
824	Eagle Hill
902	Jeffries Point
907	Maverick Landing
942	Orient Heights

*/
date_default_timezone_set('EST');

const DATABASE = "12cb3883-56f5-47de-afa5-3b1cf61b257b";
const ENDPOINT = "https://data.boston.gov/api/action/datastore_search_sql?sql=";
$sql=<<<SQL
SELECT
	"OCCURRED_ON_DATE",
	CASE
		WHEN "REPORTING_AREA"='1' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='2' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='3' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='4' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='5' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='6' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='7' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='8' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='9' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='10' THEN 'Orient Heights'
		WHEN "REPORTING_AREA"='11' THEN 'Lower Heights'
		WHEN "REPORTING_AREA"='12' THEN 'Lower Heights'
		WHEN "REPORTING_AREA"='13' THEN 'Lower Heights'
		WHEN "REPORTING_AREA"='14' THEN 'Lower Heights'
		WHEN "REPORTING_AREA"='15' THEN 'Lower Heights'
		WHEN "REPORTING_AREA"='16' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='17' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='18' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='19' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='20' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='21' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='22' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='23' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='24' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='25' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='26' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='27' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='28' THEN 'Maverick Landing'
		WHEN "REPORTING_AREA"='29' THEN 'Jeffries Point'
		WHEN "REPORTING_AREA"='30' THEN 'Jeffries Point'
		WHEN "REPORTING_AREA"='31' THEN 'Jeffries Point'
		WHEN "REPORTING_AREA"='32' THEN 'Jeffries Point'
		WHEN "REPORTING_AREA"='33' THEN 'Jeffries Point'
		WHEN "REPORTING_AREA"='34' THEN 'Jeffries Point'
		WHEN "REPORTING_AREA"='35' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='36' THEN 'Lower Heights'
		WHEN "REPORTING_AREA"='629' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='765' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='824' THEN 'Eagle Hill'
		WHEN "REPORTING_AREA"='902' THEN 'Jeffries Point'
		WHEN "REPORTING_AREA"='907' THEN 'Maverick Landing'
		WHEN "REPORTING_AREA"='942' THEN 'Orient Heights'
		ELSE 'OTHER'
	END AREA,
	"OFFENSE_CODE_GROUP",
	"STREET"
FROM "12cb3883-56f5-47de-afa5-3b1cf61b257b"
WHERE "DISTRICT"='A7'
	AND "OCCURRED_ON_DATE">='%s'
ORDER BY AREA, "OCCURRED_ON_DATE" ASC
SQL;


$date = strftime('%Y-%m-%d',(strtotime('8 days ago')));

$results = json_decode(file_get_contents(ENDPOINT.rawurlencode(sprintf($sql,$date))));

$bucketed_results = array();

foreach($results->result->records as $record) {
	$area = $record->area;
	$bucketed_results[$area][]=$record;
}

$html = "";
foreach($bucketed_results as $area => $results) {
	$html .= "<h2>$area</h2>";
	foreach($results as $record) {
		$time = strftime('%a %m/%d at %k:%M %p',(strtotime($record->OCCURRED_ON_DATE)));
		$street = ucwords(strtolower($record->STREET));
		$html .= "<p>On {$time} a {$record->OFFENSE_CODE_GROUP} occurred on {$street}</p>";
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<title>East Boston Crime Incidents</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1>East Boston Crime Incidents</h1>
<h2>Seven Day Period Starting <?php echo $date; ?></h2>
<p>Crime Indicdents in East Boston (A7) sourced from <a href="https://data.boston.gov/dataset/crime-incident-reports-august-2015-to-date-source-new-system">boston.gov</a>.  BPD reporting "REPORTING AREA" is decoded into neighborhoods with somehwat dubious boundaries.</p>
<?php echo $html; ?>
</body>
</html>