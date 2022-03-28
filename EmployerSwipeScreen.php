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
$companyID = $user->company_id;

$applicants = array();
$pdo = Database::connect();

$query = "SELECT * FROM JobPostings WHERE CompanyID = :companyID";
$statement = $pdo->prepare($query);
$statement->execute(["companyID" => $companyID]);
$jobs = $statement->fetchAll();

$jobString = "";
//Make the array of job IDs belonging to the company into a string ready for the sql query.
//Want it like 2,3,4
for($x=0; $x<count($jobs); $x++){
    $jobString .= $jobs[$x][0];
    $jobString .= ",";
}
$jobString = substr($jobString, 0, -1); //Removes the last comma


$query = "SELECT DISTINCT UserAccounts.UserID, UserAccounts.Forename, UserAccounts.Surname, UserAccounts.Biography, UserJobs.JobID FROM ((UserAccounts 
INNER JOIN UserJobs ON UserJobs.UserID = UserAccounts.UserID) 
INNER JOIN JobPostings ON JobPostings.JobID = UserJobs.JobID) 
WHERE UserJobs.UserAccepted = 1 AND UserJobs.CompanySeen = 0 AND JobPostings.CompanyID = ?";
//Printing two of each person because
$statement = $pdo->prepare($query);
$statement->execute([$companyID]);
$userAccounts = $statement->fetchAll(PDO::FETCH_NUM);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Employer Swipe Screen - JobAssembler</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- Bootstrap CSS 4.6.1-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
        
        <!-- Jquery -->
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        
        <style type="text/css">
            body {
            width: 100vw;
			height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: salmon;
            /* background: linear-gradient(to left, #e1eec3, #f05053); */
            align-items: center;
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

            .cards-wrap {
            border-radius: 15px;
			margin-top: 10px;
			width: 1140px;
			height: 1100px;
			margin: auto;
			perspective: 100px;
			perspective-origin: 50% 90%;
            }
            .card {
                border-radius: 15px;
                width: 960px;
                height: 720px;
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

            #jobName {
                text-align: center;
                font-size: 1.5em;
                text-transform: uppercase;
            }
            #card {                
                text-align: center;
                justify-content: center;
                align-items: center;
                background: bisque;
            }
            /* .box{
                width: 70vw;
                height: 70vh;
                top: 0;
                position:absolute;
                overflow: hidden;
                background: bisque;
            }

            
        */
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
          
          
            /*
            .btn btn-outline-primary {
                margin-left:auto;
                margin-right:auto;
                border: none;
                padding: 30px 60px;
                text-align: center;
                display: inline-block;
                font-size: 30px;
                cursor: pointer;
                background-color:aqua;
                bottom:0px;
            } */
       /* ----------------------------------------------------------------------------------------------------------------- */
/* For DownMenu */
            .buttonHolder{
                justify-content:center;
                display:flex;
                align-items:center;
                height:5em;
                font-size:40px;
                position:relative;
                z-index: 1;
            }
            .button {
                margin-left:auto;
                margin-right:auto;
                border: none;
                color: black;
                padding: 30px 60px;
                text-align: center;
                text-decoration: none;
                display: inline-block;
                font-size: 16px;
                cursor: pointer;
                background-color:aqua;
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
                    document.getElementById("jobName").innerHTML = "Current job: " + currentJob;
                });
            });

            function getRightJobs(){
                //NOT GETTING THE RIGHT JOBS AT THE MOMENT. maybe currentjobid is always 2.
                userAccountsForJob = [];
                userCounter = 0;
                //When user presses the dropdown button and changes jobs, you want to change the jobs displayed
                //Get array of jobs with jobID just selected.
                for(let i=0; i<userAccounts.length;i++){
                    console.log(userAccounts[i].join());
                    if(userAccounts[i][4] == currentJobID){
                        console.log(currentJobID);
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
                
                document.getElementById("card").innerHTML = ("Forename: " + forename + " <br> "
                + "Surname: " + surname + " <br> <br> "
                + "Biography: " + "<br> <textarea columns=120 rows=4 readonly>" + biography + "</textarea><br>");
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
        <nav class="navbar navbar-expand-sm bg-primary navbar-dark fixed-top">
            <a class="navbar-brand" href="#">
            <img src="Images/Logo1.png" width="30" height="30" class="d-inline-block-align-top" alt="Logo";>
        <?php
            echo("You are signed in as: &nbsp;" . $user->username);
        ?>
        </nav>

        <!-- Main Part -->
        <div class="container">
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
<!-- <div class="box">
                
            </div>   -->

        <!-- <div class="buttonHolder">
                <div class="dropdown">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Choose job</button>
               
                    <div class="dropdown-menu">
                    <?php
                            //for($x=0; $x<count($jobs);$x++){
                            //    echo('<a class="dropdown-item" id="dropdown'.$x.'">' . $jobs[$x][1]);
                                //set ID to dropdown$x  8
                            //}
                        ?>
                    </div>
                </div>
        </div> -->
    </body>
        <script>
        document.getElementById("jobName").innerHTML = "Current job: " + currentJob;
        getRightJobs();
        writeToCard();
        
        // Swipe
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