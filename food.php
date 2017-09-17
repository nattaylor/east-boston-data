<?php
/**
* Do the geometry in PHP if SQL fails: https://gist.github.com/vzool/e5ee5fab6608c7a9e82e2c4b800a86e3
* //TODO: Add in ZIP filter for quary speed
https://www.keene.edu/campus/maps/tool/

* Wasted ~2 hours before realizing that `::float` fails on empty string!

https://www.cityofboston.gov/assessing/search/?q=0105607000

*/
date_default_timezone_set('UTC');
setlocale(LC_MONETARY, 'en_US.UTF-8');

const DATABASE = "6ddcd912-32a0-43df-9908-63574f8c7e77";
const ENDPOINT = "https://data.boston.gov/api/action/datastore_search_sql?sql=";
$sql=<<<SQL
SELECT
	*,
	CASE
		WHEN POINT(trim(both '()' from split_part("Location", ', ', 1))::float, trim(both '()' from split_part("Location", ', ', 2))::float) <@ '((42.3640299, -71.0430908),(42.3605259, -71.0309672),(42.3647116, -71.0265899),(42.3680251, -71.0329628),(42.3703238, -71.0309672),(42.3727493, -71.0349154),(42.3640299,-71.0430908))'::path THEN 'JEFFRIES_POINT'
		WHEN POINT(trim(both '()' from split_part("Location", ', ', 1))::float, trim(both '()' from split_part("Location", ', ', 2))::float) <@ '((42.3654568, -71.0420394), (42.3727810, -71.0348296), (42.3762843, -71.0418892), (42.3705616, -71.0482192))'::path THEN 'MAVERICK_LANDING'
		WHEN POINT(trim(both '()' from split_part("Location", ', ', 1))::float, trim(both '()' from split_part("Location", ', ', 2))::float) <@ '((42.3726542, -71.0344005), (42.3726700, -71.0343575), (42.3791533, -71.0244012), (42.3840669, -71.0275555), (42.3847009, -71.0431767), (42.3768866, -71.0433054))'::path THEN 'EAGLE_HILL'
		WHEN POINT(trim(both '()' from split_part("Location", ', ', 1))::float, trim(both '()' from split_part("Location", ', ', 2))::float) <@ '((42.3837816, -71.0277915), (42.3765220, -71.0208178), (42.3809761, -70.9965491), (42.3894872, -70.9948325), (42.3904222, -70.9964848), (42.3866345, -71.0065699), (42.3888057, -71.0103035), (42.3922447, -71.0147452), (42.3839084, -71.0272765))'::path THEN 'LOWER_HEIGHTS'
		WHEN POINT(trim(both '()' from split_part("Location", ', ', 1))::float, trim(both '()' from split_part("Location", ', ', 2))::float) <@ '((42.3866345, -71.0067415), (42.3879499, -71.0021925), (42.3889167, -70.9994888), (42.3904698, -70.9978795), (42.3931956, -71.0040379), (42.3941464, -71.0106254), (42.3914523, -71.0131574), (42.3887740, -71.0102606), (42.3870465, -71.0080290))'::path THEN 'ORIENT_HEIGHTS'
		ELSE 'OTHER'
	END "Neighborhood"
FROM "f1e13724-284d-478c-b8bc-ef042aa5b70b"
WHERE
	"ZIP" = '02128'
	AND "Location" != ''
	AND "Address" NOT ILIKE '%LOGAN%'
	AND "Address" NOT ILIKE '%TERMINAL%'
	AND "_id" NOT IN (2606, 2749, 2522, 2186, 2185, 2102, 1299, 1261, 482, 826, 830, 456)
SQL;


$date = strftime('%Y-%m-%d',(strtotime('90 days ago')));

$results = json_decode(file_get_contents(ENDPOINT.rawurlencode($sql)));

//print_R($results->result->records);

$table = <<<HTML
<table>
<thead>
	<tr>
		<th>Search</th>
		<th>businessName</th>
		<th>Neighborhood</th>
		<th>ADDRESS</th>
		<th>DESCRIPTION</th>
	</tr>
</thead>
<tbody>
HTML;

foreach($results->result->records as $record) {
	$table .= "
	<tr>
		<td>
		<a href=\"https://www.google.com/maps/search/{$record->businessName}\" title=\"Search on Google Maps\">G</a>
		<a href=\"https://www.facebook.com/search/pages/?q={$record->businessName}\" title=\"Search on Facebook\">F</a>
		<a href=\"https://www.yelp.com/search?find_loc=Boston%2C+MA&ns=1&find_desc={$record->businessName}\ title=\"Search on Yelp\">Y</a>
		</td>
		<td>{$record->businessName}</td>
		<td>{$record->Neighborhood}</td>
		<td>{$record->Address}</td>
		<td>{$record->DESCRIPT}</td>
	</tr>";
}

$table .= "</tbody></table>";

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<title>East Boston Food Establishments</title>
	<style>
		tr:nth-child(even) {background: #EFEFEF}
		tr td:nth-child(3), tr td:nth-child(4) {white-space: nowrap;}
	</style>
</head>
<body>
<h1>East Boston Food Establishments</h1>
<p>Active food establishments sourced from <a href="https://data.boston.gov/dataset/active-food-establishment-licenses">data.boston.gov</a>.  Neighborhood boundaries are drawn arbitrarily; "OTHER" indicates a missing lat-long.</p>
%s
</body>
</html>
HTML;

echo sprintf($html, $table);
