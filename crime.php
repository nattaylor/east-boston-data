<?php
/**
* column names must be UPPERCASE and doublequoted; values must be single quoted
*
* areas:
* * The Heights
* * Lower Heights
* * Eagle Hill
* * Maverick Landing
* * Jefferies Point
* 
* 7	Orient Heights
* 8	Orient Heights
* 9	Orient Heights
* 10	Orient Heights
* 11	Lower Heights
* 12	Lower Heights
* 13	Lower Heights
* 14	Lower Heights
* 15	Lower Heights
* 16	Eagle Hill
* 17	Eagle Hill
* 18	Eagle Hill
* 19	Eagle Hill
* 20	Eagle Hill
* 21	Eagle Hill
* 22	Eagle Hill
* 23	Eagle Hill
* 24	Eagle Hill
* 25	Eagle Hill
* 26	Eagle Hill
* 27	Eagle Hill
* 28	Maverick Landing
* 29	Jeffries Point
* 30	Jeffries Point
* 31	Jeffries Point
* 32	Jeffries Point
* 33	Jeffries Point
* 34	Jeffries Point
* 35	Eagle Hill
* 36	Lower Heights
* 629	Eagle Hill
* 765	Eagle Hill
* 824	Eagle Hill
* 902	Jeffries Point
* 907	Maverick Landing
* 942	Orient Heights
* 
*/
date_default_timezone_set('EST');

const DATABASE = "12cb3883-56f5-47de-afa5-3b1cf61b257b";
const ENDPOINT = "https://data.boston.gov/api/action/datastore_search_sql?sql=";
$sql=<<<SQL
SELECT
	"OCCURRED_ON_DATE",
	CASE
		WHEN "REPORTING_AREA" IN ('1','2','3','4','5','6','7','8','9','10','942') THEN 'Orient Heights'
		WHEN "REPORTING_AREA" IN ('11','12','13','14','15','36') THEN 'Lower Heights'
		WHEN "REPORTING_AREA" IN ('16','17','18','19','20','21','22','23','24','25','26','27','35','629','765','824') THEN 'Eagle Hill'
		WHEN "REPORTING_AREA" IN ('28','907') THEN 'Maverick'
		WHEN "REPORTING_AREA" IN ('29','30','31','32','33','34','942') THEN 'Jeffries Point'
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