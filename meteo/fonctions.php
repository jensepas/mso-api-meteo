<?php

//include "data/mysql_connect.php";
//include "data/config.inc";

function indice_uv($uv_mv)
{
    // a mettre a jour
    $indice = 0;
    if ($uv_mv < 227) {
        $indice = 0;
    } else if ($uv_mv < 318) {
        $indice = 1;
    } else if ($uv_mv < 408) {
        $indice = 2;
    }

    return $indice;
}

function dewpoint($humidity, $temp)
{
    //temp in C, humidity in %

    $dewpoint = round(((pow(($humidity / 100), 0.125)) * (112 + 0.9 * $temp) + (0.1 * $temp) - 112), 2);

    return $dewpoint;
}

function humidex($humidity, $temp)
{
    // humidex n'est pertinant que pour t° supérieure à 20° et que si humidex supérieur à T ambiant

    //temp in C, humidity in %
    //Constant used to convert Degrees Celsius to Kelvin
    $Kconst = 273.15;

    $dewpoint = dewpoint($humidity, $temp);

    //Convert the dewpoint from C to K
    $dewpointK = $dewpoint + $Kconst;
    $humidex = round($temp + 0.5555 * (6.11 * pow(exp(1), (5417.7530 * ((1 / 273.16) - (1 / $dewpointK)))) - 10), 2);
    $comfort_level = 0;
    // 0 non applicable
    // 1 = confortable
    // 2 = un certain inconfort
    // 3 = beaucoup d'inconfort
    // 4 = danger coup de chaleur probable
    // 5 = coup de chaleur imminent
    if (round($humidex) < 20) {
        $comfort_level = 1;
    } else if (round($humidex) >= 20 && round($humidex) < 30) {
        $comfort_level = 2;
    } else if (round($humidex) >= 30 && round($humidex) < 40) {
        $comfort_level = 3;
    } else if (round($humidex) >= 40 && round($humidex) <= 45) {
        $comfort_level = 4;
    } else if (round($humidex) > 45) {
        $comfort_level = 5;
    }

    return $humidex;
}

function froid_ressenti($vent, $temp)
{
    //temp in C, vent in km/h

    //$dewpoint = round(((pow(($humidity/100), 0.125))*(112+0.9*$temp)+(0.1*$temp)-112),2);
    if ($vent > 4.8) {
        $froidressenti = round((13.12 + (0.6215 * $temp) + (0.3965 * $temp - 11.37) * pow($vent, 0.16)), 1);
    } else {
        $froidressenti = round($temp + 0.2 * ((0.1345 * $temp) - 1.59) * $vent, 1);
    }

    return $froidressenti;
}

$temp = 22;
$humidite = 44;
$vent = 3;
Print "temp = " . $temp . " C<br>";
Print "Humidite = " . $humidite . " %<br>";
Print "vent = " . $vent . " km/h<br>";
Print "------<br>";
print "point rose : " . dewpoint($humidite, $temp) . "<br>";

//Calculate the dewpoint which is needed in the equeation
$dewpoint = pow(($humidite / 100), (1 / 8)) * (112 + (0.9 * $temp)) + (0.1 * $temp) - 112;

echo "Dewpoint: " . round($dewpoint, 2) . "&#176;C<br/><br/>";

Print "humidex = " . humidex($humidite, $temp) . " C<br>";
Print "Froid ressenti = " . froid_ressenti($vent, $temp) . " C<br>";