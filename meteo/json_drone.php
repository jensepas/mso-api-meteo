<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "data/config.inc";
include "data/fonctions.php";

function give_me_var($val_1, $val_2, $delta)
{
    //print $val_1."-->".$val_2." <>".$delta."<br>";
    $tendence = 0;
    if ($val_2 > $val_1 + $delta) {
        $tendence = 1;
    } else if ($val_2 < $val_1 - $delta) {
        $tendence = -1;
    } else {
        $tendence = 0;
    }

    return $tendence;
}

$delta_temp = 0.1;
$delta_hydro = 1;
$delta_pression = 0.5;

$return_arr = [];

$sql = 'SELECT * FROM reception WHERE recep_capteur_id=2 ORDER BY recep_date_time DESC Limit 1 ';
$result = mysqli_query($mysqli, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {

        //print  $row["recep_date_time"]."<br>";

        $date = new DateTime($row["recep_date_time"]);

        //print  $row["recep_temperature"].",".($row["recep_pression_atmo"]/100).",".$row["recep_humidite"].",".$row["recep_uv_level"].",".$row["recep_luminosite_1"].",".$row["recep_luminosite_2"]. "\n";

        $row_array['temperature'] = $row['recep_temperature'] . '°C';
        $memo['temperature'] = $row['recep_temperature'];
        $pression_1 = $row['recep_pression_atmo'];
        $row_array['pression'] = round($row['recep_pression_atmo'] / 100, 1);
        $memo['pression'] = $row['recep_pression_atmo'] / 100;

        $row_array['humidite'] = $row['recep_humidite'] . '%';
        $memo['humidite'] = $row['recep_humidite'];
        $row_array['dewpoint'] = round(dewpoint($row['recep_humidite'], $row['recep_temperature']), 1);
        $row_array['uvlevel'] = $row['recep_uv_level'];

        $uv_indic = indice_uv($row['recep_uv_indice']);
        $row_array['uvindice'] = $uv_indic;
        $row_array['uvniveau'] = $cfg_protec_uv[$uv_indic];
        $row_array['uvprotecreco'] = $cfg_protec_uv_conseil[$uv_indic];

        $row_array['light_1'] = $row['recep_luminosite_1'] . '%';
        $row_array['light_2'] = $row['recep_luminosite_2'] . '%';
        $new_date = substr($row["recep_date_time"], 8, 2) . "/" . substr($row["recep_date_time"], 5, 2) . "/" . substr($row["recep_date_time"], 0, 4) . substr($row["recep_date_time"], 10, 20);

        $row_array['date'] = $new_date;
        $row_array['capteur_id'] = $row['recep_capteur_id'];
        $row_array['capteur_nom'] = $row['recep_capteur_id'] . "nom";

        $vent = 0;
        $temp_ressenti = froid_ressenti($vent, $row['recep_temperature']);
        $temp_humidex = humidex($row['recep_humidite'], $row['recep_temperature']);

        $row_array['temp_ressenti'] = $temp_ressenti;
        $row_array['temp_humidex'] = $temp_humidex;

        $row_array['sun_leve'] = soleil('leve', time(), $pos_lat, $pos_long);
        $row_array['sun_couche'] = soleil('couche', time(), $pos_lat, $pos_long);
        $row_array['sun_leve_aero'] = soleil('leve_aero', time(), $pos_lat, $pos_long);
        $row_array['sun_couche_aero'] = soleil('couche_aero', time(), $pos_lat, $pos_long);
    }
}

// compare avec données de passée H-1

$next_date = $date;
$next_date->sub(new DateInterval('PT1H'));
//echo $next_date->format('Y-m-d H:i:s') . "<br>";
$date_1 = $next_date->format('Y-m-d H:i:s');

$sql2 = "SELECT * FROM reception WHERE recep_capteur_id=2 and recep_date_time<'" . $date_1 . "' ORDER BY recep_date_time DESC Limit 1 ";

$result2 = mysqli_query($mysqli, $sql2);

if (mysqli_num_rows($result2) > 0) {
    while ($row2 = mysqli_fetch_assoc($result2)) {

        //print  $row2["recep_date_time"]."<br>";
        // verification date trouvé pas trop éloigné date cible
        $datetime1 = new DateTime($date_1);
        $datetime2 = new DateTime($row2["recep_date_time"]);
        $dteDiff = $datetime2->diff($datetime1);
        $delta_secondes = $dteDiff->format("%S");
        //print $delta_secondes." secondes<br>";
        if ($delta_secondes < 1000) {
            //print "ok<br>";
            // temperature
            $varia_temp = give_me_var($row2['recep_temperature'], $memo['temperature'], $delta_temp);
            $row_array['tendence_temp'] = $varia_temp;

            // hydrometrie
            $varia_hydro = give_me_var($row2['recep_humidite'], $memo['humidite'], $delta_hydro);
            $row_array['tendence_hydro'] = $varia_hydro;

            // pression
            $varia_pression = give_me_var($row2['recep_pression_atmo'] / 100, $memo['pression'], $delta_pression);
            $row_array['tendence_pression'] = $varia_pression;
        } // fin si temps <1000
        else {
            $row_array['varia_temp'] = -999;
            $row_array['varia_hydro'] = -999;
        }// fin pas de variation
    }
}

array_push($return_arr, $row_array);

echo json_encode($return_arr[0]);