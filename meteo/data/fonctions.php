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
    } else if ($uv_mv < 503) {
        $indice = 3;
    } else if ($uv_mv < 606) {
        $indice = 4;
    } else if ($uv_mv < 696) {
        $indice = 5;
    } else if ($uv_mv < 795) {
        $indice = 6;
    } else if ($uv_mv < 881) {
        $indice = 7;
    } else if ($uv_mv < 976) {
        $indice = 8;
    } else if ($uv_mv < 1079) {
        $indice = 9;
    } else if ($uv_mv < 1170) {
        $indice = 10;
    } else {
        $indice = 11;
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

function soleil($category, $date, $pos_lat, $pos_long)
{
    switch ($category) {
        case "leve":
            $sun_info = date_sun_info($date, $pos_lat, $pos_long);
            $heure = date("H:i:s", $sun_info["sunrise"]);
            break;
        case "couche":
            $sun_info = date_sun_info($date, $pos_lat, $pos_long);
            $heure = date("H:i:s", $sun_info["sunset"]);
            break;
        case "leve_aero":
            $sun_info = date_sun_info($date, $pos_lat, $pos_long);
            $heure = date("H:i:s", $sun_info["sunrise"]);
            $heure2 = date_create_from_format('H:i:s', $heure);
            $heure2->sub(new DateInterval('P0Y0M0DT0H30M0S'));
            $heure = $heure2->format('H:i:s');

            break;
        case "couche_aero":
            $sun_info = date_sun_info($date, $pos_lat, $pos_long);
            $heure = date("H:i:s", $sun_info["sunset"]);
            $heure2 = date_create_from_format('H:i:s', $heure);
            $heure2->add(new DateInterval('P0Y0M0DT0H30M0S'));
            $heure = $heure2->format('H:i:s');
            break;
        default:
            $heure = "00:00:00";
    }

    return $heure;
}