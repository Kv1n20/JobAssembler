<?php
require_once(__DIR__ . "/classes/database.php");
require_once(__DIR__ . "/classes/user.php");
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: /index.php");
    die(0);
}
$user = $_SESSION["user"];
if (!$user->is_authenticated()) {
    header("Location: /index.php");
    die(0);
}
$jobs = array();
$pdo = Database::connect();
$query = "SELECT JobPostings.*, UserJobs.UserAccepted, UserJobs.CompanyAccepted FROM JobPostings INNER JOIN UserJobs ON JobPostings.JobID = UserJobs.JobID WHERE UserJobs.UserID = ? LIMIT 20";
$statement = $pdo->prepare($query);
$statement->execute([$user->user_id]);
$jobs = $statement->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Viewed Jobs</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
<main>
<table>
    <tr>
        <th>Company</th>
        <th>Job Title</th>
        <th>About</th>
        <th>Location</th>
        <th>Accepted</th>
    </tr>
    <?php foreach($jobs as $line): ?>
    <tr>
        <td><?= $line["JobID"]?></td>
        <td><?= $line["Title"]?></td>
        <td><?= $line["Details"]?></td>
        <td><?= "Unknown"?></td>
        <td><input type="checkbox" disabled <?= $line["UserAccepted"] ? "checked": ""?>></td>
    </tr>
    <?php endforeach; ?>
</table>
</main>
</body>
</html>