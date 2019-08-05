<?php
/**
 * PHP 7.0
 *
 *
 * @location /lib/Bigbrother.php
 */

namespace lib;

class Bigbrother
{
    public function check($params): bool
    {
        if (empty($params)) {
            $this->return = ["error" => "ERROR_00020", "message" => "La demande est vide"];

            return false;
        }

        return true;
    }
}