<!DOCTYPE html>
<html lang="en">
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <title>Sign Up - Job Assembler</title>
       

        <link href="CSS/SignUp.css" rel="stylesheet">
        <link href="../JobAssembler/CSS/SignUp.css" rel="stylesheet">
        <!-- Bootstrap CSS 5.1-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/sign-in/">

        <style>
        * {
        margin: 0;
        padding: 0;
        border: 0;
        box-sizing: border-box;
        }

        body {
            overflow: hidden;
        }
/*-----------------------------------------------------------------------------------------------  */
/* gradient color */
        .text-center{
        width: 100vw;
        height: 100vh;
        border-width:0px ;
        background: linear-gradient(
        135deg,
            
            rgb(247,121,125),
            rgb(253,200,48),
            rgb(251,215,134),
            rgb(168,255,120),
            rgb(48,232,191)

            );
        background-size: 200% 200%;
        animation: gradient-move 10s ease alternate infinite;}
      

        /* Dynamic */
        @keyframes gradient-move {
            0% {
            background-position: 0% 0%;
            }
            100% {
            background-position: 100% 100%;
            }
        }

/*-----------------------------------------------------------------------------------------------  */
/* input styling */
        input{
            outline-color: invert ;
            outline-style: none ;
            outline-width: 0px ;

            border: 1px solid #ccc; 
            border-radius: 5px;
            padding: 10px 10px;
            text-shadow: none ;
            -webkit-appearance: none ;
            -webkit-user-select: text ;
            outline-color: transparent ;
            box-shadow: none;
        }

        input:focus{
            border-color: #66afe9;
            outline: 0;
            -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)
        }

/*-----------------------------------------------------------------------------------------------  */
/* From bootstrap */
        .bd-placeholder-img {
                font-size: 1.125rem;
                text-anchor: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                user-select: none;
                }

                @media (min-width: 768px) {
                .bd-placeholder-img-lg {
                font-size: 3.5rem;
                    }
                }

        </style>


        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script>
            function ValidateForm(username, forename, surname, password, confirmPassword, warning){
                warning.innerHTML = ""; //Set it to blank first in case the user got the validation wrong first time round.
                if(/^[0-9a-z]+$/i.test(username.value) == false || username.value.length > 30 || username.value.length < 6){
    	            warning.innerHTML = "Username must be 6-30 alphanumeric characters.";
                }else if(password.value != confirmPassword.value){
                    warning.innerHTML = "Make sure you've typed the same password twice.";
                }else if(password.value.length < 8 || password.value.length > 1024){
                    warning.innerHTML = "Password must be between 8 and 1024 characters long.";
                }else if(forename.value.length < 1 || surname.value.length < 1){
                    warning.innerHTML = "Invalid name given. Both forename and surname must be given.";
                }else if(forename.value.length > 63){
                    warning.innerHTML = "Invalid forename given. Make sure it's less than 64 characters long.";
                }else if(surname.value.length > 63){
                    warning.innerHTML = "Invalid surname given. Make sure it's less than 64 characters long.";
                }
                //This just makes it so you don't have to put 'return false' in every if statement.
                if(warning.innerHTML != ""){
                    return false;
                }else{
                    return true;
                }
            }

            $(document).ready(function(){
                $("#signUpForm").submit(function (e){  
                    //Change message in the sign up screen
                    let warning = document.getElementById("validationMsg");
                    e.preventDefault();     //Stops the normal HTML form behaviour of changing files
                    let form = document.getElementById('signUpForm');
                    var accountType = document.querySelector('input[name = "accountType"]:checked').value;
                    var validForm = ValidateForm(form.elements[0], form.elements[1], form.elements[2], form.elements[3], form.elements[4], warning);
                    if(validForm){
                        $.ajax({
                            type:"POST",
                            //url:"https://web.cs.manchester.ac.uk/v31903mb/JobAssembler/api/register.php",
                            url:"api/register.php",
                            data: $(this).serialize(),
                            success: function(data, textStatus, xhr){   //Where to go if successful
                                localStorage.setItem("userID", xhr.responseJSON["id"]);
                                if (accountType == "employee"){
                                    window.location = "login.php";
                                }else{
                                    window.location = "CompanyDetails.php";
                                }
                            },
                            error: function(xhr){
                                //alert($(this).serialize);
                                var obj = xhr.responseJSON;
                                //alert("An error occured: " + xhr.status + " " + xhr.statusText);
                                if(Object.keys(obj).includes("message")){
                                    warning.innerHTML = obj["message"];
                                }else{
                                    warning.innerHTML = "An unknown error has occurred. Please try again later.";
                                }
                            }

                        })
                    }
                })
            })

        </script>
    </head>

    <body class="text-center">
    <div class="container-fluid">
        <main class="form-signin">
            <form id="signUpForm" name="signUpForm">
<!-------------------------------------------------------------------------------------------------------------->
<!-- header -->
                    <h2>Join</h2>
                    <h1><b>JobAssembler</b></h1>
                    <h2>Now!</h2>
<!-- form -->
                    <div class="form-floating">
                    <!-- <label for="username">Username:</label> -->
                    <input type="text" name="username" id="username" class="inputBox" placeholder="Username">                  
                     </div>
                   
                    <div class="form-floating">
                    <!-- <label for="forename">Forename:</label> -->
                    <input type="text" name="forename" id="forename" class="inputBox" placeholder="Forename"> 
                    </div>
                   
                    <div class="form-floating">
                    <!-- <label for="surname">Surname:</label> -->
                    <input type="text" name="surname" id="surname" class="inputBox" placeholder="Surname">
                    </div>
                   
                    <div class="form-floating">
                    <!-- <label for="password">Password:</label> -->
                    <input type="password" name="password" id="password" class="inputBox" placeholder="Password">
                    </div>
                    
                    <div class="form-floating">
                    <!-- <label for="confirmPassword">Confirm Password:</label> -->
                    <input type="password" name="confirmPassword" id="confirmPassword" class="inputBox" placeholder="Confirm Password">
                    </div>
             
 <!-------------------------------------------------------------------------------------------------------------->
 <!-- Link to the log in page -->
                    <a href="login.php">Already have a JobAssembler account?</a>
                    <br>
<!-- radio frame -->
                    <br>
                    <p class="choicetext" style="color: black;">You want to be:</p>
                    
                    <label class="radio-inline">
                        <input type="radio"  name="accountType" id="employee" value="employee" required>
                        <span class="check" style="color: black;"></span>
                        <label for="yes" style="color: black;">Employee </label>
                    </label>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <label class="radio-inline">
                        <input type="radio"  name="accountType" id="employer" value="employer" required>
                        <span class="check" style="color: black;"></span>
                        <label for="yes" style="color: black;">Employer</label>
                    </label>
                    <br><br><br>

 <!-------------------------------------------------------------------------------------------------------------->
<!-- submit  -->
                    <!-- <input type="submit" value="Submit"> -->
                    <button class="w-30 btn btn-lg btn-primary" type="submit">Sign Up</button>
                    <br><br>
                    <p id="validationMsg" style="color: red;"></p>
                    
<!-- End part-->
                    <p class="mt-5 mb-3 text-muted">&copy; X17 2021-2022</p>
                    
                    

                    <?php
                        ini_set('error_reporting', E_ALL);
                        ini_set('display_errors', 1);
                        
                        
                    ?>
            </form>
            
            </div>
        </main>
    </body>
</html>
