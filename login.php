<!doctype html>
<html lang="en">
  <head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="X17 Group">
    <meta name="generator" content="Hugo 0.88.1">
    <title>Signin - Job Assembler</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/sign-in/">
    <!-- Bootstrap CSS -->

    <link href="CSS/signin.css" rel="stylesheet">
    <link href="../JobAssembler/CSS/signin.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
      
    <style>
      * {
        margin: 0;
        padding: 0;
        border: 0;
        }

      .mb-4{border-radius: 5px;}
      .text-center{
        width: 100vw;
        height: 100vh;
        border-width:0px ;
        background: linear-gradient(
        135deg,
            hsl(170deg, 80%, 70%),
            hsl(190deg, 80%, 70%),
            hsl(250deg, 80%, 70%),
            hsl(320deg, 80%, 70%));
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
        // Shamelessly stole this directly from James's code
        $(document).ready(function(){
            $("#loginForm").submit(function (e){
                let warning = document.getElementById("warningMessage");
                e.preventDefault();     //Stops the normal HTML form behaviour of changing files
                let form = document.getElementById('loginForm');
                $.ajax({
                    type:"POST",
                    url:"api/login.php",
                    data: $(this).serialize(),
                    success: function(data){
                        window.location = "main.php"  //Where to go if successful
                    },
                    error: function(xhr){
                        var obj = xhr.responseJSON;
                        if(Object.keys(obj).includes("message")){
                            warning.innerHTML = obj["message"];
                        }else{
                            warning.innerHTML = "An unknown error has occurred. Please try again later.";
                        }
                    }
                })
            });})
    </script>

  </head>

  <body class="text-center">
    
<main class="form-signin">
    <img class="mb-4" src="Images/Logo1.png" alt="" width="72" height="57">
    <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

    <form name="loginForm" id="loginForm">
    <div class="form-floating">
      <input type="text" class="form-control" name="username" id="username" placeholder="Username">
      <label for="username">Username</label>
    </div>
    <div class="form-floating">
      <input type="password" class="form-control" name="password" id="password" placeholder="Password">
      <label for="password">Password</label>
    </div>

    <button class="w-30 btn btn-lg btn-primary" type="submit">Sign in</button>
    <p class="mt-5 mb-3 text-muted">&copy; X17 2021</p>

    <p id="warningMessage"></p>
  </form>
</main>


    
  </body>
</html>
