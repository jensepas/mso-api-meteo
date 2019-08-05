<?php
/**
 * PHP 7.0
 *
 *
 * @location /lib/handlers.php
 */


spl_autoload_register(function ($className): bool {
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $file = DIR_ROOT . "/{$className}.php";
    if (is_readable($file)) {
        require_once($file);

        return true;
    }

    return false;
});

function sanitize(): array
{
    if ($_SERVER['REQUEST_METHOD'] === "GET" && !empty($_GET)) {
        return filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING) ?? [];
    } else {
        if (!empty($_POST)) {
            return filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING) ?? [];
        } else {
            $tocheck = json_decode(file_get_contents("php://input"), true) ?? [];
            foreach ($tocheck as $key => $value) {
                $tocheck[$key] = $value; //filter_var($value, FILTER_SANITIZE_STRING);
            }

            return $tocheck;
        }
    }
}