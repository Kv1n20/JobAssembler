<?php
require_once(__DIR__ . "/prevent_direct_access.php");
require_once(__DIR__ . "/../config.inc.php");
require_once(__DIR__ . "/../classes/api_response_generator.php");

if (!isset($database_host) || !isset($database_user) || !isset($database_pass) || !isset($group_dbnames)){
    ApiResponseGenerator::generate_error_json(500, "There was an error with the website configuration. Please try again later.");
} elseif (!in_array(Database::$database_name, $group_dbnames)){
    ApiResponseGenerator::generate_error_json(500, "There was an error with the website configuration. Please try again later.");
}
class Database {
    public static string $database_name = "2021_comp10120_x17";

    public static function connect(): PDO {
        global $database_host;
        global $database_user;
        global $database_pass;
        $database_name = self::$database_name;
        try {
            $pdo = new PDO("mysql:host=$database_host;dbname=$database_name", $database_user, $database_pass);
        } catch (PDOException $exception) {
            ApiResponseGenerator::generate_error_json(500, "There was an error connecting to the database. {$exception->getMessage()}. Please try again later.");
        }
        return $pdo;
    }


}