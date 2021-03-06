<?php

require_once(__DIR__ . "/classes/database.php");
require_once(__DIR__ . "/classes/user.php");
session_start();
if (!isset($_SESSION["user"]) || !($_SESSION["user"] instanceof User)) {
    header("Location: index.php");
    die(0);
}
$user = $_SESSION["user"];
$companyID = $user->company_id;
$userID = $user->user_id;

if(!$user->is_authenticated()){
    header("Location: /index.php");
    die(0);
}
$user->get_user();

//Get the search entry
$txt = isset($_GET['txt']) ? $_GET['txt'] : '';
$usersApplied = array();
$pdo = Database::connect();
$wordsForDisplay = "";
if(empty($txt)){
    //If the user hasn't searched yet, just display all the entries.
    //Get list of users who are in the JoinRequest table for the company the present user is a part of (and where companyAccepted is 0).
    $query = "SELECT UserAccounts.UserID, UserAccounts.Username, UserAccounts.Forename, UserAccounts.Surname 
    FROM (UserAccounts INNER JOIN CompanyJoinRequests ON UserAccounts.UserID = CompanyJoinRequests.UserID)
    WHERE CompanyJoinRequests.CompanyID = :companyid AND CompanyJoinRequests.CompanyAccepted = 0";
    $statement = $pdo->prepare($query);
    $statement->execute([
        "companyid" => $companyID
    ]);
    $usersApplied = $statement->fetchAll(PDO::FETCH_NUM); 
}else{
    $query = "SELECT UserAccounts.UserID, UserAccounts.Username, UserAccounts.Forename, UserAccounts.Surname
    FROM (UserAccounts INNER JOIN CompanyJoinRequests ON UserAccounts.UserID = CompanyJoinRequests.UserID)
    WHERE (CompanyJoinRequests.CompanyID = :companyid AND CompanyJoinRequests.CompanyAccepted = 0) AND (";
    //Format each of the search words
    $keywords = explode(' ', $txt);
    foreach($keywords as $kw){
        //Check if the search keyword is similar to Username, Forename or Surname
        $query .= "UserAccounts.Username LIKE '%" . $kw . "%' OR ";
        $query .= "UserAccounts.Forename LIKE '%" . $kw . "%' OR ";
        $query .= "UserAccounts.Surname LIKE '%" . $kw . "%' OR ";
        $wordsForDisplay .= $kw.' ';
    }
    //Get rid of the last part and close the bracket from the start of the query
    $query = substr($query, 0, strlen($query)-4);
    $query .= ")";
    $wordsForDisplay = substr($wordsForDisplay, 0, strlen($wordsForDisplay) -1);
    $statement = $pdo->prepare($query);
    $statement->execute([
        "companyid" => $companyID
    ]);
    $usersApplied = $statement->fetchAll(PDO::FETCH_NUM);
}
$noOfUsers = count($usersApplied);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Add Users To Company - JobAssembler</title>
        <meta name="viewport" content="width=device-width, intitial-scale=1">
        <!--Bootstrap CSS 4.6.1-->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script>
            //var userID = <?php echo($userID); ?>;
            var companyID = <?php echo($companyID); ?>;

            function acceptPressed(userID){
                //userID here is the user that I am currently accepting.
                dataArray = {"companyID":companyID, "userID":userID};
                $.ajax({
                    type:"POST",
                    url:"api/companyAccepting.php",
                    data: dataArray,
                    success:function(data){
                        document.getElementById("errorMsg").innerHTML = "User successfully added to your company. Go and tell them they can now go through and accept or decline job applicants.";
                    },
                    error: function(xhr){
                        var obj = xhr.responseJSON;
                        if(Object.keys(obj).includes("message")){
                            document.getElementById("errorMsg").innerHTML = obj["message"];
                        }else{
                            document.getElementById("errorMsg").innerHTML = "An unknown error has occurred. Please try again later.";
                        }
                    }
                })
            }
        </script>

        <style>
            * {
            padding: 0;
            margin: 0;
            }

            body {
                font-family: sans-serif;
                background: url(Images/pexels-pixabay-210158.jpg) no-repeat 
                center fixed;
                background-size: cover;
            }
            
            .container-fluid {
            margin-top: 20vh;
            margin-left:30vw;
            width: 40vw;
            height: 80vh;
            position: absolute;
            /* display: flex; */
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            /* background:white; */
            }

            .d-flex flex-column{
                display: flex;
                position: relative;
                align-content: center;
                text-align: center;
            }

            .item1{
                align-content: center;
                text-align: center;
                font-family:"Helvetica";
                color: black;
            }
            
            .item2{
                align-content: center;
                text-align: center;
                position:relative;
                /* background:blue;  */
                /* -webkit-backdrop-filter: blur(10px); */
            }

            .item3{
                align-content: center;
                text-align: center;
                position: relative;
                color: black;
                background: rgba(255, 255, 255, 0.6);
                border-radius: 1rem;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
                z-index: 1;
                /* background: pink; */
            }

            .item4{
                align-content: center;
                text-align: center;
                /* vertical-align: center; */
            }
/* ----------------------------------------------------------------------------------------------------------------- */
/* search bar */
            input {
            padding: 1rem 2rem;
            /* width: 600px; */
            outline: none;
            border: none;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 1rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            z-index: 1;
            }

            #input1 {
                width: 400px;
                font-size: 1rem;  
            }

            #input2 {
                margin-left: 50px; 
                width: 100px;  
                font-size: 1rem;
            }
/* ----------------------------------------------------------------------------------------------------------------- */

            /* .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transition: 0.3s ease-out;
            pointer-events: none;
            }

            input:focus + .overlay {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            pointer-events: auto;
            } */

/* ----------------------------------------------------------------------------------------------------------------- */
            /* For navbar */   
            .navbar-nav{
                position:absolute;
                right: 50px;
            }     
            /* For Logo border*/
            .d-inline-block-align-top {border-radius: 5px;}

        </style>

    </head>
    <body>
    

    <!-- For navbar -->
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
    <!-- Links -->
        <ul class="navbar-nav" >
            <form class="form-inline">
            
            <li class="nav-item">
            <a class="nav-link" href="EmployerSwipeScreen.php" style="margin-left:5%; white-space: nowrap;">Home</a>
            </li>
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
            <a class="btn-danger" style="margin-left: 30%; padding: 10px; white-space: nowrap;"  href="api/logout.php" >Log Out</a>     
            </li> 
                 
            </form>
        </ul>
    </nav>

        <!-- Main part -->
        <div class="container-fluid">
        <div class="d-flex flex-column">

        <div class="item1">
            <header>
            <h1>Add Users To Company</h1>
            </header>
            <hr>
            <p>Please search for the account you're trying to add by </p>
            <p>their Username, Forename or Surname.</p>
             
        </div>
            
        <div class="item2" style="margin-left: 5%; margin-right: 5%; margin-top: 1vh; ">
            <form action="CompanyAddUsers.php" method="GET" name="searchForm" >
                <table>
                    <tr>
                        <td><input type="text" id="input1" name="txt" value="<?php echo isset($_GET['txt']) ? $_GET['txt'] : ''; ?>" placeholder="Enter a Username, Forename or Surname" /></td>
                        <td><input type="submit" id="input2" name="" value="Search" /></td>
                    </tr>
                </table>
            </form> 
        </div>
       
        <div class="item3" style="margin-top: 1vh;" >
            <?php
                //Show the user the keywords they have entered
                //If the txt variable is empty then everything is displayed.
                if($noOfUsers > 0){
                    echo("The search returned: <b>" . $noOfUsers . " </b>results. <br>");
                    if(!empty($txt)){
                        echo("You searched for: <b>'" . $wordsForDisplay . "'</b>");
                    }
                    echo("<br><hr>");                
                    for($x=0;$x<$noOfUsers;$x++){
                        echo('
                        <h3>'.$usersApplied[$x][1].'</h3>
                        '.$usersApplied[$x][2].' '.$usersApplied[$x][3].'<br>
                        <button class="btn btn-outline-primary id="accept'.$usersApplied[$x][0].'" onclick="acceptPressed('.$usersApplied[$x][0].')">Accept User Into Company</button>
                        <hr>');
                    }
                }else{
                    if(empty($txt)){
                        echo("<br>There are no users who have applied to your <br><br> company that you haven't already dealt with.");
                    }else{
                        echo("<br>There were no results for your search. <br><br>Please ensure you've entered the correct information<br>");
                        echo("You searched for: <b>'" . $wordsForDisplay . "'</b>");
                    }
                }
            ?>

        <div class="item4"> 
            <b><i><p id="errorMsg"></p></i></b>
        </div>

        <div class="overlay">
        </div>

        </div>
    </div>
    </body>
</html>