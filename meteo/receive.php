<?php

include "data/mysql_connect.php";
include "data/config.inc";

function indice_uv($uv_mv)
{
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


// --- CAPTEUR ----
if (isset($_GET['capteur'])) {
    print "Capteur n° " . $_GET['capteur'] . "<br>";
    $capteur_id = $_GET['capteur'];
} else {
    print "# no capteur_id #";
    exit();
}

// --- TEMPERATURE ----
if (isset($_GET['temperature'])) {
    print "Temperature " . $_GET['temperature'] . "°<br>";
    $temperature = $_GET['temperature'];
} else {
    print "# no temp #";
    $temperature = -999;
}

// --- PRESSION AT ----
if (isset($_GET['pression'])) {
// Unite : Pascals. 100 Pascals = 1 hPa = 1 millibar.
    print "Pression atmospherique " . $_GET['pression'] . "°<br>";
    $pression = $_GET['pression'];
} else {
    print "# no pression #";
    $pression = -999;
}

// --- HUMIDITE ----
if (isset($_GET['humidite'])) {
// Unite : %
    print "Humiite " . $_GET['humidite'] . "°<br>";
    $humidite = $_GET['humidite'];
} else {
    print "# no humidite #";
    $humidite = -999;
}

// --- SOLEIL UV ----
if (isset($_GET['sunuv'])) {
// Unite : %
    print "sunuv " . $_GET['sunuv'] . "°<br>";
    $sunuv = $_GET['sunuv'];
} else {
    print "# no sunuv #";
    $sunuv = -999;
}

// --- LUMINOSITE Lux ----
if (isset($_GET['light_lux'])) {
// Unite : %
    print "light_lux " . $_GET['light_lux'] . "°<br>";
    $light_lux = $_GET['light_lux'];
} else {
    print "# no light_lux #";
    $light_lux = -999;
}


// --- LUMINOSITE 1 ----
if (isset($_GET['light_1'])) {
// Unite : %
    print "light_1 " . $_GET['light_1'] . "°<br>";
    $light_1 = $_GET['light_1'];
} else {
    print "# no light_1 #";
    $light_1 = -999;
}

// --- LUMINOSITE 2 ----
if (isset($_GET['light_2'])) {
// Unite : %
    print "light_2 " . $_GET['light_2'] . "°<br>";
    $light_2 = $_GET['light_2'];
} else {
    print "# no light_2 #";
    $light_2 = -999;
}

//recep_capteur_id
//recep_date_time
//recep_temperature
//recep_pression_atmo
//recep_humidite
//recep_luminosite_1
//recep_luminosite_2
//recep_luminosite_3
//recep_uv_level
//recep_uv_indice
//recep_vent_vitesse


$date = date('Y/m/d H:i:s', time());
print "<BR>";


mysqli_query($mysqli, "INSERT INTO  reception SET recep_date_time='$date', recep_capteur_id='$capteur_id' , recep_temperature='$temperature' ,recep_pression_atmo='$pression' ,recep_humidite='$humidite'  ,recep_uv_level='$sunuv' ,
recep_luminosite_lux='$light_lux'   ,recep_luminosite_1='$light_1'  ,recep_luminosite_2='$light_2'   ") or die("erreur d'insertion");


?>