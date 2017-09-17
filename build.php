<?php
/**
* Do the geometry in PHP if SQL fails: https://gist.github.com/vzool/e5ee5fab6608c7a9e82e2c4b800a86e3
* //TODO: Add in ZIP filter for quary speed
https://www.keene.edu/campus/maps/tool/

* Wasted ~2 hours before realizing that `::float` fails on empty string!

https://www.cityofboston.gov/assessing/search/?q=0105607000

[89] => stdClass Object
        (
            [Parcel_ID] => 0105607000
            [Comments] => ;gut out demo to frame  ;trash removal  and cleanup ; complete demolition down to steps on 1 2 3 and basement floor; demo exterior siding
            [Location] => (42.371800000, -71.041690000)
            [PermitNumber] => SF735935
            [PermitTypeDescr] => Short Form Bldg Permit
            [ISSUED_DATE] => 2017-07-26T13:49:58
            [sq_feet] => 0
            [TOTAL_FEES] => 270.00
            [EXPIRATION_DATE] => 2018-01-26 00:00:00
            [WORKTYPE] => INTDEM
            [STATUS] => OPEN
            [ZIP] => 02128
            [APPLICANT] => Noe Hernandez
            [STATE] => MA
            [DECLARED_VALUATION] => 25000.00
            [CITY] => East Boston
            [Neighborhood] => MAVERICK_LANDING
            [DESCRIPTION] => Demolition - Interior
            [_full_text] => '-01':54 '-07':40 '-26':41,55 '-3':46 '-71.041690000':61 '0':3 '00':56,57,58 '0105607000':38 '02128':59 '1':25,45 '2':26 '2017':39 '2018':53 '25000.00':66 '270.00':9 '3':27 '4':62 '42.371800000':60 '42502':1 '49':43 '58':44 'and':17,28 'basement':29 'bldg':7 'boston':37 'cleanup':18 'complete':19 'coppersmith':63 'demo':12,31 'demolition':20,34 'down':21 'east':36 'exterior':32 'fam':47 'floor':30 'form':6 'frame':14 'giovanniello':48 'gut':10 'hernandez':52 'intdem':50 'interior':35 'ma':2 'noe':51 'on':24 'open':65 'out':11 'permit':8 'removal':16 'robert':49 'sf735935':4 'short':5 'siding':33 'steps':23 't13':42 'to':13,22 'trash':15 'wy':64
            [OCCUPANCYTYPE] => 1-3FAM
            [ADDRESS] => 4    Coppersmith WY
            [OWNER] => GIOVANNIELLO ROBERT
            [_id] => 330105
            [Property_ID] => 42502
        )
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
FROM "6ddcd912-32a0-43df-9908-63574f8c7e77"
WHERE
	"ZIP" = '02128'
	AND "Location" != ''
	AND "ISSUED_DATE" > '%s'
	AND "WORKTYPE" IN ('INTDEM','EXTREN','INTEXT','INTREN','EXTDEM','ERECT','SIDE','CHGOCC','COB','NEWCON')
ORDER BY "ISSUED_DATE" DESC
LIMIT 100
SQL;


$date = strftime('%Y-%m-%d',(strtotime('90 days ago')));

$results = json_decode(file_get_contents(ENDPOINT.rawurlencode(sprintf($sql,$date))));

//print_R($results->result->records);

$table = <<<HTML
<table>
<thead>
	<tr>
		<th>Parcel_ID</th>
		<th>Neighborhood</th>
		<th>ADDRESS</th>
		<th>DESCRIPTION</th>
		<th>DECLARED VALUATION</th>
		<th>Comments</th>
	</tr>
</thead>
<tbody>
HTML;

foreach($results->result->records as $record) {
	$FORMATTED_VALUE = money_format('%.0n', $record->DECLARED_VALUATION);
	$table .= "
	<tr>
		<td><a href=\"https://www.cityofboston.gov/assessing/search/?pid={$record->Parcel_ID}\" title=\"Go to assement details for property\">{$record->Parcel_ID}</a></td>
		<td>{$record->Neighborhood}</td>
		<td>{$record->ADDRESS}</td>
		<td>{$record->DESCRIPTION}</td>
		<td>{$FORMATTED_VALUE}</td>
		<td>{$record->Comments}</td>
	</tr>";
}

$table .= "</tbody></table>";

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<title>East Boston Building Permits</title>
	<style>
		tr:nth-child(even) {background: #EFEFEF}
		tr td:nth-child(3), tr td:nth-child(4) {white-space: nowrap;}
	</style>
</head>
<body>
<h1>East Boston Building Permits</h1>
<h2>Issued in Last 90 Days</h2>
<p>Issued building permits sourced from <a href="https://data.boston.gov/dataset/approved-building-permits">data.boston.gov</a>.  Neighborhood boundaries are drawn arbitrarily.</p>
%s
</body>
</html>
HTML;

echo sprintf($html, $table);
