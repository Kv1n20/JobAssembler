<?php
require_once(__DIR__ . "/classes/database.php");
require_once(__DIR__ . "/classes/user.php");
session_start();
if (!isset($_SESSION["user"]) || !($_SESSION["user"] instanceof User)) {
    header("Location: index.php");
    die(0);
}
$user = $_SESSION["user"];
$userID = $user->user_id;
if (!$user->is_authenticated()) {
    header("Location: index.php");
    die(0);
}
if($user->company_id < 1){
    header("Location: main.php");
    die(0);
}
$companyID = $user->company_id;

$applicants = array();
$pdo = Database::connect();

$query = "SELECT * FROM JobPostings WHERE CompanyID = :companyID";
$statement = $pdo->prepare($query);
$statement->execute(["companyID" => $companyID]);
$jobs = $statement->fetchAll();

if(count($jobs) == 0){
    //If they have no jobs, send then to create job screen.
    header("Location: JobCreation.php");
    die(0);
}
$jobID = $jobs[0][0];

$jobString = "";
//Make the array of job IDs belonging to the company into a string ready for the sql query.
//Want it like 2,3,4
for($x=0; $x<count($jobs); $x++){
    $jobString .= $jobs[$x][0];
    $jobString .= ",";
}
$jobString = substr($jobString, 0, -1); //Removes the last comma


$query = "SELECT DISTINCT UserAccounts.UserID, UserAccounts.Forename, UserAccounts.Surname, UserAccounts.Biography, UserJobs.JobID, UserAccounts.Latitude, UserAccounts.Longitude, JobPostings.Latitude, JobPostings.Longitude, UserAccounts.Remote, JobPostings.RemoteJob FROM ((UserAccounts 
INNER JOIN UserJobs ON UserJobs.UserID = UserAccounts.UserID) 
INNER JOIN JobPostings ON JobPostings.JobID = UserJobs.JobID) 
WHERE UserJobs.UserAccepted = 1 AND UserJobs.CompanySeen = 0 AND JobPostings.CompanyID = ?";
//Printing two of each person because
$statement = $pdo->prepare($query);
$statement->execute([$companyID]);
$userAccounts = $statement->fetchAll(PDO::FETCH_NUM);

//Score at 11

function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000){
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);
  
    $lonDelta = $lonTo - $lonFrom;
    $a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
    $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
  
    $angle = atan2(sqrt($a), $b);
    return $angle * $earthRadius;
}

//userAccounts is an array that has user details FOR EACH JOB.
$scores = array();
for($x=0;$x<count($userAccounts);$x++){
    $query = "SELECT COUNT(*) FROM (UserSkills INNER JOIN JobSkills ON UserSkills.SkillID = JobSkills.SkillID) WHERE UserSkills.UserID = :userid AND JobSkills.jobid = :jobid";
    $statement = $pdo->prepare($query);
    $statement->execute([
        "userid" => $userAccounts[$x][0],
        "jobid" => $userAccounts[$x][4]
    ]);
    $count = intval($statement->fetch()[0]);
    array_push($scores, $count);
    array_push($userAccounts[$x], $count);
}

//UserRemote at 9. JobRemote at 10.
//If you're swiping for a job that is remote AND the user is remote then add 10 points.
//If you're swiping for a job that is remote and the user is NOT remote then add points based off distance.
//If you're swiping for a job that isn't remote then just add points based on distance for users that aren't remote.

for($x=0; $x<count($userAccounts); $x++){
    if(($userAccounts[$x][9] == 1) && ($userAccounts[$x][10] == 1)){
        $userAccounts[$x][11] = $userAccounts[$x][11] + 10;
    }
    if((!$userAccounts[$x][5] == null) && (!$userAccounts[$x][6] == null) && (!$userAccounts[$x][7] == null) && (!$userAccounts[$x][8] == null) && ($userAccounts[$x][9] == 0)){
        $latitude1 = $userAccounts[$x][5];
        $longitude1 = $userAccounts[$x][6];
        $latitude2 = $userAccounts[$x][7];
        $longitude2 = $userAccounts[$x][8];

        $distance = vincentyGreatCircleDistance($latitude1, $longitude1, $latitude2, $longitude2);
        //Round to nearest metre.
        $distance = round($distance);
        array_push($userAccounts[$x], $distance);
        $distanceScore = 0;
        if($distance <= 50000){
            $distanceScore += 1;
        }
        if($distance <= 40000){
            $distanceScore += 1;
        }
        if($distance <= 30000){
            $distanceScore += 1;
        }
        if($distance <= 20000){
            $distanceScore += 1;
        }
        if($distance <= 10000){
            $distanceScore += 1;
        }
        //Score is at index 11
        $userAccounts[$x][11] = $userAccounts[$x][11] + $distanceScore;
    }else{
        array_push($userAccounts[$x], 0);
    }
}

//Distance now at 12

function compare($element1, $element2){
    $acc1 = $element1[11];
    $acc2 = $element2[11];
    return $acc2 - $acc1;
}

usort($userAccounts, 'compare');

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Employer Swipe Screen - JobAssembler</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap CSS 4.6.1-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
        
        <!-- Jquery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>

        <style type="text/css">
            body {
            width: 90%;
			height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url("Images/pexels-burst-374870.jpg");
		    background-repeat:repeat-x;
            overflow-x: hidden;
        }

            .container{
            width: 85vw;
            height: 90vh;
            position: relative;  
            margin: auto; 
            justify-content: center;
            align-items: center;
            display: flex;
            top: 5vh;
            }
/* ----------------------------------------------------------------------------------------------------------------- */
/* Central Cards */
            .cards-wrap {
            border-radius: 15px;
			margin-top: 10px;
			width: 950px;
			height: 1000px;
			margin: auto;
			perspective: 100px;
			perspective-origin: 50% 90%;
            }

            .card {
                border-radius: 15px;
                width: 800px;
                height: 700px;
                padding: 50px 30px;
                background: #ffffff;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2);
                position: absolute;
                transform-origin: 50% 50%;
                transition: all 0.8s;
            }
            .card.first {
                transform: translate3d(0, 0, 0px);
                z-index: 3;
            }

            .card.second {
                transform: translate3d(0, 0, -10px);
                z-index: 2;
            }

            .card.third {
                transform: translate3d(0, 0, -20px);
                z-index: 1;
            }

            .card.last-card {
                transform: translate3d(0, 0, -20px);
                z-index: 1;
            }

            .card:not(:first-child) {
                opacity: 0.95;
            }

            .card.swipe {
                transform: rotate(30deg) translate3d(120%, -50%, 0px) !important;
                opacity: 0;
                visibility: hidden;
            }

            .card p {
                font-size: 20px;
                font-weight: 400;
                margin-bottom: 20px;
                margin-top: 0;
            }

/* Card contents */
            #jobName {
                color: blue;
                font-family:"Helvetica";
                text-align: center;
                font-size: 1.3em;
                text-transform: uppercase;
                
            }

            #card {                
                text-align: center;
                font-size: 1.7em;
                justify-content: center;
                align-items: center;
                
            }
           
/* ----------------------------------------------------------------------------------------------------------------- */
/* For Yes/No Button */
            .option {
                width: 100%;
                padding: 18px 10px 18px 84px;
                text-align: left;
                border: none;
                outline: none;
                cursor: pointer;
                font-size: 16px;
                font-weight: 400;
                background: #f8f8f8 url(ic_option_normal.svg) left 50px center no-repeat;
                background-size: 24px;
            }

            .option:nth-child(2) {
                margin-bottom: 20px;
            }

            .option:hover,
            .option.checked {
                background: #faeeee url(ic_option_checked.svg) left 50px center no-repeat;
            }

            .btn-group{
                    justify-content:center;
                    display:flex;
                    align-items:center;
                    height:5em;
                    font-size:40px;
                    position: sticky;
                    bottom: -5vh;
            }
          
/* ----------------------------------------------------------------------------------------------------------------- */

/* ----------------------------------------------------------------------------------------------------------------- */
/* For navbar */        
        /* For Logo border*/
        .d-inline-block-align-top {border-radius: 5px;}
        /* For DownMenu */
        .navbar-text{
                color: yellow;
            }
       .navbar-nav{
           position:absolute;
           right: 50px;
       } 
/* ----------------------------------------------------------------------------------------------------------------- */

        </style>
        
        <script>
            var userID = <?php echo($userID); ?>;
            var jobArray = <?php echo json_encode($jobs) ?>;    //If this is empty, disable buttons
            var currentJobID = jobArray[0][0];
            var currentJob = jobArray[0][1];
            var userAccounts = <?php echo json_encode($userAccounts) ?>;  //If this is empty, there are no users left to swipe through.
            var userCounter = 0;
            var userAccountsForJob = [];

            $(function(){
                $(".dropdown-menu a").click(function(){
                    currentJob = $(this).text();
                    currentJobID = $(this).attr('id');      //Gets the ID of the dropdown item
                    currentJobID = currentJobID.substring(8); //Every ID has 'dropdown' at the start. Remove it to just get the number
                    //Right now 'currentJobID' is only relative to the list in the dropdown. Need to make it relative to the array from the database
                    currentJobID = jobArray[currentJobID][0];
                    getRightJobs();
                    document.getElementById("jobName").innerHTML = "<b>Would you like to accept this applicant for the job: " + currentJob + "?</b>";
                });
            });

            function getRightJobs(){
                userAccountsForJob = [];
                userCounter = 0;
                //When user presses the dropdown button and changes jobs, you want to change the jobs displayed
                //Get array of jobs with jobID just selected.
                for(let i=0; i<userAccounts.length;i++){
                    //console.log(userAccounts[i].join());
                    //The userAccounts array will already be sorted into best first, 2ndbest second etc.
                    //So you can just go through it from the start and see which belongs to the job you've got selected.
                    if(userAccounts[i][4] == currentJobID){
                        //console.log(currentJobID);
                        //console.log(userAccounts[i].join());
                        userAccountsForJob.push(userAccounts[i]);
                    }
                }
                if(userAccountsForJob.length == 0){
                    document.getElementById("card").innerHTML = "Sorry, you've seen all available job applicants.";
                    document.getElementById("noButton").disabled = true;
                    document.getElementById("yesButton").disabled = true;
                }else{
                    document.getElementById("noButton").disabled = false;
                    document.getElementById("yesButton").disabled = false;
                    writeToCard();
                }
            }

            function writeToCard(){
                var forename = userAccountsForJob[userCounter][1];
                var surname = userAccountsForJob[userCounter][2];
                var biography = userAccountsForJob[userCounter][3];
                //Distance will be at userAccountsForJob[userCounter][12]
                var distance = userAccountsForJob[userCounter][12];
                distance = distance / 1000; //Convert into km
                var isRemote = userAccountsForJob[userCounter][9];  //this is for if the EMPLOYEE is remote
                var jobRemote = userAccountsForJob[userCounter][10];

                if(isRemote == 1){
                    document.getElementById("card").innerHTML = ("This user is remote.<br>"
                    + "Forename: " + forename + " <br> "
                    + "Surname: " + surname + " <br> <br> "
                    + "Biography: " + "<br> <textarea columns=140 rows=4 readonly>" + biography + "</textarea><br>");
                }else{
                    if(jobRemote == 1){
                        if(distance == 0){
                            document.getElementById("card").innerHTML = ("Applicant is not remote and location not found.<br>"
                            + "Forename: " + forename + " <br> "
                            + "Surname: " + surname + " <br> <br> "
                            + "Biography: " + "<br> <textarea columns=140 rows=4 readonly>" + biography + "</textarea><br>");
                        }else{
                            //If job is remote but the user isn't, then say so.
                            document.getElementById("card").innerHTML = ("Distance from Job: " + distance + "km - applicant is not remote<br>"
                            + "Forename: " + forename + " <br> "
                            + "Surname: " + surname + " <br> <br> "
                            + "Biography: " + "<br> <textarea columns=140 rows=4 readonly>" + biography + "</textarea><br>");
                        }
                        
                    }else{
                        if(distance == 0){
                            document.getElementById("card").innerHTML = ("Location not found.<br>"
                            + "Forename: " + forename + " <br> "
                            + "Surname: " + surname + " <br> <br> "
                            + "Biography: " + "<br> <textarea columns=140 rows=4 readonly>" + biography + "</textarea><br>");
                        }else{
                            document.getElementById("card").innerHTML = ("Distance from Job: " + distance + "km<br>"
                            + "Forename: " + forename + " <br> "
                            + "Surname: " + surname + " <br> <br> "
                            + "Biography: " + "<br> <textarea columns=140 rows=4 readonly>" + biography + "</textarea><br>");
                        }
                        
                    }
                }
                
                
                
            }

            function buttonPressed(yesOrNo){
                //dataArray = {"companyAccepted":yesOrNo, "userID":userAccounts[currentUser][0], "jobID":currentJobID};
                dataArray = {"companyAccepted":yesOrNo, "userID":userAccountsForJob[userCounter][0], "jobID":currentJobID};
                $.ajax({
                    type:"POST",
                    url:"api/companyJobUpdating.php",
                    data:dataArray,
                    error: function (xhr){
                        var obj = xhr.responseJSON;
                        if(Object.keys(obj).includes("message")){
                            alert(obj["message"]);
                        }else{
                            alert("An unknown error has occurred. Please try again later.");
                        }
                    }
                })
                if(userCounter < userAccountsForJob.length-1){
                    userCounter += 1;
                    writeToCard();
                }else{
                    document.getElementById("card").innerHTML = "Sorry, you've seen every current available job applicant. Try changing to another job.";
                    document.getElementById("noButton").disabled = true;
                    document.getElementById("yesButton").disabled = true;
                }
            }
        </script>
    </head>
    <body>
        <!-- Nav bar -->
        <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
        <!-- Brand LOGO -->
        <a class="navbar-brand">
            <img src="Images/Logo1.png" width="30" height="30" class="d-inline-block-align-top" alt="Logo";>
        </a>
        <span class="navbar-text">
            <?php
                echo("You are signed in as:&nbsp;" . $user->username . "&nbsp &nbsp");
            ?>
        </span>   

        <ul class="navbar-nav" >
        <!-- <div class="d-flex flex-row-reverse"> -->
            <!-- Dropdown -->
            <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">Choose Jobs</a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#">
                        <?php
                                        for($x=0; $x<count($jobs);$x++){
                                            echo('<a class="dropdown-item" id="dropdown'.$x.'">' . $jobs[$x][1]);
                                            //set ID to dropdown$x  8
                                        }
                                    ?>
                        </a>
                    </div>
            </li>    
            <!-- Links -->
            
            <form class="form-inline">
            
            <li class="nav-item">
            <a class="nav-link" href="JobSkills.php" style="margin-left:5%; white-space: nowrap;">Job Skills</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="JobCreation.php" style="margin-left:5%; white-space: nowrap;;">Job Creation</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="ApplicantList.php" style="margin-left:5%; white-space: nowrap;">Applicant List</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="CompanyJobs.php" style="margin-left:5%; white-space: nowrap;;">Job Postings</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="CompanyAddUsers.php" style="margin-left:5%; white-space: nowrap;">Add Users</a>
            </li>
            <li class="nav-item">
            <a class="btn-danger" style="margin-left: 30%; padding: 10px; white-space: nowrap;"  href="api/logout.php" >Log Out</a>     
            </li> 
                 
            </form>
            <!-- </div> -->
        </ul>
        
        </nav>


        <!-- Main Part -->
        <div class="container" style="margin-left: 25%">
            <div class="cards-wrap">

                <div class="card first">
                    <p id="jobName" name="jobName">placeholder</p>
                    <p id="card" name="card">Sorry, you've seen all available job applicants.</p>
                    <br>
                    <!-- <div class="btn-group"> -->
                    <button class="option" id="yesButton" onclick="buttonPressed(1)">YES</button>
                    <button class="option" id="noButton" onclick="buttonPressed(0)">NO</button>
                    <!-- </div> -->
                </div>

                <div class="card second"></div>
                <div class="card third"></div>
                
            </div>
        </div> 


    </body>
        <script>
        document.getElementById("jobName").innerHTML = "<b>Would you like to accept this applicant for the job: " + currentJob + "?</b>";
        getRightJobs();
        //writeToCard();
        
        // Swipe cards
        const cardWrap = document.querySelector(".cards-wrap");
		function pickOption() {
			const topCard = document.querySelector(".card:first-child");
			const tempStr = topCard.innerHTML;
			topCard.classList.add("swipe");
			setTimeout(function() {
				topCard.remove();
				const cards = document.querySelectorAll(".card");
				cards[0].style.transform = "translate3d(0, 0, 0px)";
				cards[0].style.zIndex = "3";
				cards[0].innerHTML = tempStr;
				cards[1].style.transform = "translate3d(0, 0, -10px)";
				cards[1].style.zIndex = "2";
				cardWrap.insertAdjacentHTML(
					"beforeend",
					'<div class="card last-card"></div>'
				);
				const newOptions = document.querySelectorAll(".option");
				newOptions.forEach(optionBtn =>
					optionBtn.addEventListener("click", pickOption)
				);
			}, 300);
		}

		const optionBtns = document.querySelectorAll(".option");
		optionBtns.forEach(optionBtn =>
			optionBtn.addEventListener("click", pickOption)
		);
    </script>
</html>