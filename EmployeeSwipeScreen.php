<?php
require_once(__DIR__ . "/classes/database.php");
require_once(__DIR__ . "/classes/user.php");
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: /index.php");
    die(0);
}
$user = $_SESSION["user"];
$userID = $user->user_id;
if (!$user->is_authenticated()) {
    header("Location: /index.php");
    die(0);
}
$jobs = array();
$pdo = Database::connect();
//NEED to insert rows with UserSeen=0 for jobs that aren't in the database yet.
/*
INSERT INTO UserJobs (UserID, JobID, UserAccepted, CompanyAccepted, UserSeen) VALUES (17, 2, 0, 0, 0)
*/
/*
Look through the database and see if there is an entry in UserJobs for every job in JobPostings
1. Get the list of jobIDs related to this userID (UserJobs)
2. Get list of jobIDs in JobPostings
3. Go through JobIDs in JobPostings. If there isn't the same number in UserJobs JobIDs:
3a. INSERT INTO UserJobs (UserID, JobID, UserAccepted, CompanyAccepted, UserSeen) VALUES (:userID, :jobID, 0, 0, 0) 

*/
$query = "SELECT JobID FROM UserJobs WHERE UserID = :userID";
$statement = $pdo->prepare($query);
$statement->execute(["userID" => $userID]);
$UserJobsIDs = $statement->fetchAll();
$UserJobsIDs = array_map('implode', $UserJobsIDs);

$query = "SELECT JobID FROM JobPostings";
$statement = $pdo->prepare($query);
$statement->execute();
$JobPostingsIDs = $statement->fetchAll();
$JobPostingsIDs = array_map('implode', $JobPostingsIDs);

//For some reason each number is read twice, so each array element is two numbers. Shouldn't make a difference though.

for($x=0;$x<count($JobPostingsIDs);$x++){
    if(in_array($JobPostingsIDs[$x], $UserJobsIDs) == false){
        //Need to INSERT this entry
        $query = "INSERT INTO UserJobs(UserID, JobID, UserAccepted, CompanyAccepted, UserSeen, CompanySeen) VALUES (:userID, :jobID, 0, 0, 0, 0)";
        $statement = $pdo->prepare($query);
        $statement->execute([
            "userID" => $userID,
            "jobID" => $JobPostingsIDs[$x][0]
        ]);
    }
}

//Now do the thing that gets the jobs you haven't seen yet.
$columns = array("JobID", "Title", "Details", "CompanyID", "UserSeen", "CompanyID", "Name", "Description", "CompanyImage");
//It's SELECT DISTINCT because without it the same row is returned multiple times. I think it's because my INNER JOIN stuff is outdated but this solves it easily.
$query = "SELECT DISTINCT JobPostings.*, UserJobs.UserSeen, Companies.* FROM ((JobPostings
INNER JOIN UserJobs ON JobPostings.JobID = UserJobs.JobID)
INNER JOIN Companies ON JobPostings.CompanyID = Companies.CompanyID) 
WHERE UserJobs.UserSeen = 0 AND UserJobs.UserID=:userID;";
$statement = $pdo->prepare($query);
$statement->execute(["userID" => $userID]);
$data = $statement->fetchAll();
$jobs = array_reverse($data);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Employee Swipe Screen</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">

        <style type="text/css">
            body {
            display: flex;
            justify-content: center;
            background: linear-gradient(
                135deg,
            rgb(30,150,0),
            rgb(89,193,115),
            rgb(161,127,224),
            rgb(93,38,193));
            background-size: 200% 200%;
            animation: gradient-move 10s ease alternate infinite;
            }

            /* Dynamic */
            @keyframes gradient-move {
                    0% {
                    background-position: 0% 0%;
                    }
                    100% {
                    background-position: 100% 100%;
                    }
                }
/* ----------------------------------------------------------------------------------------------------------------- */
            .container-fluid{
            /* background-color: #fff; */
            width: 75vw;
            height: 90vh;
            position: relative;
            display: flex;
            border-radius: 15px;
            justify-content: center;
            align-items: center;
            top: 10vh;
            font-size:1.5em;
            background: white;
            }

            .box{
                width: 75vw;
                height: 85vh;
                top: 0;
                overflow: hidden;
       
            }
/* ----------------------------------------------------------------------------------------------------------------- */
/* For Yes/No Button */
            .btn-group{
                justify-content:center;
                display:flex;
                align-items:center;
                height:5em;
                font-size:40px;
                position: sticky;
                bottom: -5vh;
            }
            .btn btn-outline-primary {
                margin-left:auto;
                margin-right:auto;
                border: none;
                color: black;
                padding: 15px 32px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                cursor: pointer;
                background-color:aqua;
                width: 50%;
              
            } 
        </style>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script>
            var userID = <?php echo($userID); ?>;
            var jobCounter = 0;
            //To see whole jobArray do JSON.stringify(jobArray) because it's encoded using json to make it more secure.
            var jobArray = <?php echo json_encode($jobs) ?>;    //If this is empty, disable buttons
            var columns = ["JobID", "Title", "Details", "CompanyID", "UserSeen", "CompanyID", "Name", "Description", "CompanyImage"];
            
            function writeToCard(){
                var companyName = jobArray[jobCounter][columns.indexOf("Name")];
                var jobTitle = jobArray[jobCounter][columns.indexOf("Title")];
                var jobDetails = jobArray[jobCounter][columns.indexOf("Details")];
                var companyDescription = jobArray[jobCounter][columns.indexOf("Description")];

                document.getElementById("card").innerHTML = "Company: " + companyName + " <br> "
                 + "Job Title: " + jobTitle + "<br>"
                 + "Job Details: " + "<br> <textarea cols=80 rows=4 readonly>" + jobDetails + "</textarea><br>"
                 + "Company Description: " + "<br> <textarea cols=80 rows=4 readonly>" + companyDescription + "</textarea><br>";
            }

            function buttonPressed(yesOrNo){
                dataArray = {"userAccepted":yesOrNo, "userID":userID, "jobID":jobArray[jobCounter][columns.indexOf("JobID")]};
                $.ajax({
                    type:"POST",
                    url:"api/jobUpdating.php",
                    data:dataArray,
                    success:function(data){
                        alert("Job Done")
                    },
                    error: function(xhr){
                        var obj = xhr.responseJSON;
                        if(Object.keys(obj).includes("message")){
                            alert(obj["message"]);
                        }else{
                            alert("An unknown error has occurred. Please try again later.")
                        }
                    }
                })
                if(jobCounter < jobArray.length-1){
                    jobCounter += 1;
                    writeToCard();
                }else{
                    document.getElementById("card").innerHTML = "Sorry, you've seen every available job.";
                    document.getElementById("noButton").disabled = true;
                    document.getElementById("yesButton").disabled = true;
                }
                
            }
        </script>
    </head>


    <body>
        <!-- Nav bar -->
        <nav class="navbar navbar-expand-sm bg-primary navbar-dark fixed-top">
            <a class="navbar-brand" href="#">
            <img src="Images/Logo1.png" width="30" height="30" class="d-inline-block-align-top" alt="Logo";>
            <!-- <span class="navbar-text">
            &nbsp;&nbsp; JobAssembler  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </span> -->
            <?php
                echo("You are signed in as: &nbsp;" . $user->username);
            ?>
        </nav>

        <div class="container-fluid">
            <div class="box">
                <p id="card" name="card">
                    Sorry, you've seen all the available jobs
                </p>
               
                <br>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" id="noButton" onclick="buttonPressed(0)">NO</button>
                    <button class="btn btn-outline-primary" id="yesButton" onclick="buttonPressed(1)">YES</button>
                </div>
                <br>  
               
            </div>
        </div>


    </body>
    <script>
        writeToCard();
    </script>
</html>