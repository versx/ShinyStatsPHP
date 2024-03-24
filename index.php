<?php

require 'config.php';
require 'Medoo.php';
require 'pokedex.php';

use Medoo\Medoo;

// Initialize
$db = new Medoo($config['db']);
if ($db === NULL) { die(); }

$timeZoneSet = date_default_timezone_set($config['timezone']);
if ($timeZoneSet === false) {
	echo "Failed to set timezone";
}


$today = date('Y-m-d');
$shinyStats = $db->select('pokemon_shiny_stats', [
    'date',
    'pokemon_id',
    'count' => Medoo::raw('SUM(`count`)')
], [
    'date' => $today,
    'count[>]' => 0,
    'GROUP' => 'pokemon_id'
]);

$totalStats = $db->select('pokemon_iv_stats', [
    'date',
    'pokemon_id',
    'count' => Medoo::raw('SUM(`count`)')
], [
    'date' => $today,
    'GROUP' => 'pokemon_id'
]);

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <title>Live shiny stats for Pokémon Go</title>

  <style>
  	.icon {
  		width: 60px;
			height: 60px;
  	}

	#header {
		font-weight: bold;
		font-size: 25px;
		text-align: center;
		margin: 10px 0 0 0;
	}

	#event, #data_period {
		font-size: 15px;
		text-align: center;
		margin: 10px;
	}

	#event_name {
		font-weight: bold;
	}

	.table > thead > tr > th {
		 vertical-align: middle;
	}

    .table > tbody > tr > td {
      vertical-align: middle;
    }

	#footer {
		font-size: 13px;
		text-align: center;
		margin: 10px;
	}
  </style>
</head>
<body>
	<div id="header">
		Live Shiny Stats for Pokémon Go
	</div>
	<div id="data_period">
		Data from the last 24 hours.
	</div>
	<div id="shiny_table">
		<table class="table table-striped table-hover table-sm">
		    <thead class="thead-dark">
		        <tr>
			        <th scope="col"> </th>
		            <th scope="col">Pokemon</th>
		            <th scope="col">Shiny Rate</th>
		            <th scope="col">Shiny / Total</th>
		        </tr>
		    </thead>
		    <tbody id="table_body">';
for ($i = 0; $i < count($shinyStats); $i++) {
	$row = $shinyStats[$i];
	$pokemonId = $row['pokemon_id'];
	$name = $pokedex[$pokemonId];
	$shiny = $row['count'];
	$total = getTotalCount($totalStats, $pokemonId);
	$rate = round($total / $shiny);
	$pokemonImageUrl = sprintf($config['images'], $pokemonId);

	$html .= '<tr>';
	$html .= '<td><img src="' . $pokemonImageUrl . '" width="48" height="48"/></td>';
	$html .= '<td>' . $name . ' (#' . $pokemonId . ')</td>';
	$html .= '<td>1/' . $rate . '</td>';
	$html .= '<td>' . number_format($shiny) . '/' . number_format($total) . '</td>';
	$html .= '</tr>';
}
$html .= '</tbody>
		</table>
	</div>
	<div id="footer"></div> 
</body>
</html>
';

echo $html;

function getTotalCount($pokemon, $pokemonId) {
	for ($i = 0; $i < count($pokemon); $i++) {
		$row = $pokemon[$i];
		if ($row['pokemon_id'] === $pokemonId) {
			return $row['count'];
		}
	}
	return 0;
}
?>
