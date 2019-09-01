<?php
/**
 * PHP 7.0
 *
 *
 * @location /lib/Bigbrother.php
 */

namespace lib;

class BigBrother
{
    public function check($params): bool
    {
        if (empty($params)) {

            return false;
        }

        return true;
    }

    public function checkApiKey($params): bool
    {
        $db = new \PDO('mysql:host=192.168.0.30;dbname=meteo','meteo','klikeul');
        $select = $db->prepare("SELECT id FROM sensor WHERE token = ?");
        $select->execute([$params]);

    if (count($select->fetchAll()) === 1) {
        return true;
    }

    return false;
    }
}