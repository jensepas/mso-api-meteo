<?php
// fichier de parametrage

$cfg_debug = 0; // mode débugage 0:inactif 1:actif
$cfg_version = "Version 2.0"; //version

$cfg_protec_uv_conseil[0] = "Aucune";
$cfg_protec_uv_conseil[1] = "Port de lunettes de soleil";
$cfg_protec_uv_conseil[2] = $cfg_protec_uv_conseil[1];
$cfg_protec_uv_conseil[3] = "Se couvrir et porter un chapeau et des lunettes de soleil. Appliquer un écran solaire de protection moyenne (indice de protection de 15 à 29), surtout pour une exposition à l’extérieur pendant plus de trente minutes. Rechercher l’ombre aux alentours de midi, quand le soleil est au zénith.";
$cfg_protec_uv_conseil[4] = $cfg_protec_uv_conseil[3];
$cfg_protec_uv_conseil[5] = $cfg_protec_uv_conseil[3];

$cfg_protec_uv_conseil[6] = "Réduire l’exposition entre 11 h et 17 h. Appliquer un écran solaire de haute protection (indice de 30 à 50), porter un chapeau et des lunettes de soleil, et se placer à l’ombre.";
$cfg_protec_uv_conseil[7] = $cfg_protec_uv_conseil[6];

$cfg_protec_uv_conseil[8] = "Sans protection, la peau sera endommagée et peut brûler. L’exposition au soleil peut être dangereuse entre 11 h et 17 h ; la recherche de l’ombre est donc importante. Sont recommandables le port de vêtements longs, d'un chapeau et de lunettes de soleil, ainsi que l'application d'un écran solaire de très haute protection (indice + 50).";
$cfg_protec_uv_conseil[9] = $cfg_protec_uv_conseil[8];
$cfg_protec_uv_conseil[10] = $cfg_protec_uv_conseil[8];

$cfg_protec_uv_conseil[11] = "La peau non protégée sera endommagée et peut brûler en quelques minutes. Toute exposition au soleil est dangereuse, et en cas de sortie il faut se couvrir absolument (chapeau, lunettes de soleil, application d'un écran solaire de très haute protection d'indice + 50).";
$cfg_protec_uv_conseil[12] = $cfg_protec_uv_conseil[11];

$cfg_protec_uv[0] = "Nul";
$cfg_protec_uv[1] = "Faible";
$cfg_protec_uv[2] = $cfg_protec_uv[1];
$cfg_protec_uv[3] = "Modéré";
$cfg_protec_uv[4] = $cfg_protec_uv[3];
$cfg_protec_uv[5] = $cfg_protec_uv[3];
$cfg_protec_uv[6] = "Élevé";
$cfg_protec_uv[7] = $cfg_protec_uv[6];
$cfg_protec_uv[8] = "Très élevé";
$cfg_protec_uv[9] = $cfg_protec_uv[8];
$cfg_protec_uv[10] = $cfg_protec_uv[8];
$cfg_protec_uv[11] = "Extrême";


/* Calcul l'heure du coucher du soleil pour Cast 29150, bretagne, france
Latitude: 48.15 North
Longitude: 4.13 West

 pour Brest, bretagne, france
Latitude: 48.39 North
Longitude: 4.485 West

Zenith ~= 90.5
90°50'	Coucher du soleil: Le point où le soleil devient invisible.
96°	Crépuscule civil: conventionnellement utilisé pour signifier la fin du crépuscule.
102°	Crépuscule nautique: le point de fin de l'horizon étant visible en mer.
108°	Crépuscule astronomique: le point où le soleil finit d'être la source de toute illumination.

offset: +1 GMT
Pour les coordonnées, taper nom ville dans wikipedia puis convertir les coordonnées avec :
https://www.latlong.net/degrees-minutes-seconds-to-decimal-degrees
*/

$pos_lat = 48.39;
$pos_long = -4.485;
$sun_angle = 90.5;
$time_offset = 1;


$cfg_station[1] = array(1, "Station Salon", "O");
$cfg_station[2] = array(2, "Station Extérieure", "O");