<?php
/**
 * PHP 7.0
 *
 *
 * @location /lib/Bigbrother.php
 */

namespace lib;

use PDO;

class BigBrother
{
    public function check($params): bool
    {
        if (empty($params)) {

            return false;
        }

        return true;
    }

    public function checkApiKey($params): array
    {
        $db = new PDO('mysql:host=' . BDD_ADRESS . ';dbname=' . BDD_BASE, BDD_LOGIN, BDD_PASSWORD);
        $select = $db->prepare("SELECT id, label FROM sensor WHERE token = :apiKey");
        $select->execute(['apiKey' => $params]);

        if ($row = $select->fetch()) {
            return $row;
        }

        return [];
    }
}