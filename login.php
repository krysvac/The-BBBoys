<?php
require_once 'include/functions.php';
sec_session_start();

if (login_check()) {
    header('HTTP/1.1 307 Temporary redirect', false, 307);
    header('Location: /index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Slay,mat,food">
    <meta name="author" content="Grandmaster Sick True Slay Fuck">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="En sida för information om mat">
    <title>The BBBoys mat-info - Logga in</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>

<body>
    <div class="row">
        <div class="col-12">
            <div class="text-center hotice" style="margin-bottom:0">
                <h1 class="outline">The BBBoys</h1>
            </div>
        </div> 
        <div class="col-12">
            <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
                <a class="navbar-brand" href="#">The BBBoys</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="collapsibleNavbar">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Hem</a>
                        </li>
                        <?php
                        if (login_check()):
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">Inställningar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logga ut</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item active">
                            <a class="nav-link" href="login.php">Logga in</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="col-12">
            <div class="row">
                <div class="col-2 d-none d-lg-flex minecraft">
                
                </div>
            
                <div class="col-lg-8 col-md-12">
                    <div class="container-fluid main">
                        <div class="row" style="margin-top: 30px;">
                            <div class="col-12">
                                <h1>Logga in</h1>
                                <form id="loginForm">
                                    <div class="form-group">
                                        <label for="username">Användarnamn</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Sick_Steve_69" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Lösenord</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="*********" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Logga in</button>
                                </form>
                                <div class="error" id="loginError"></div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="col-2 d-none d-lg-flex minecraft">
                
                </div>
            </div>
        </div>
    </div>

    <div class="text-center council" style="margin-bottom:0">
        <p class="outline">Copyright &copy; The BBBoys <?php date("Y"); ?></p>
    </div>
        
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="
    anonymous"></script>
    <script src="js/bootstrap-validate.js"></script>

    <script>
        var formValid = false;
        $(document).ready(function() {
            $("#loginForm").submit(function(event) {
                $("#loginError").empty();

                event.preventDefault();
                var form = $(this);

                if (formValid) {
                    $.ajax({
                        type: "POST",
                        url: "/api/post.php?action=login",
                        data: form.serialize(),
                        success: function(data) {
                            var res = JSON.parse(data);
                            if(res.success) {
                                window.location.replace("index.php");
                            } else {
                                $("#loginError").append(res.msg);
                            }
                        }
                    });
                }
            });

            bootstrapValidate('#username',
                'required:Du måste skriva ett användarnamn|regex:^[a-zA-ZåÅäÄöÖ\_0-9]+$:Ditt användarnamn får bara innehålla A-Ö, understreck och 0-9|max:50:Ditt användarnamn får inte vara mer än 50 tecken långt!',
                function(isValid) {
                    if (isValid) {
                        formValid = true;
                    } else {
                        formValid = false;
                    }
                }
            );
        });
    </script>
</body>

</html>
