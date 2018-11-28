<?php
require_once 'include/functions.php';
sec_session_start();

if (!login_check()) {
    header('HTTP/1.1 307 Temporary redirect', false, 307);
    header('Location: /index.php');
    exit();
}
$csrf = new csrf();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Slay,mat,food">
    <meta name="author" content="Grandmaster Sick True Slay Fuck">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="En sida för information om mat">
    <title>The BBBoys mat-info - Inställningar</title>
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
                        <li class="nav-item active">
                            <a class="nav-link" href="settings.php">Inställningar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logga ut</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
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
                        <div class="row">
                            <div class="col-12">
                                <h1 style="margin-top: 30px;">Inställningar</h1>
                                <hr>
                                <h2 style="margin-top: 30px;">Byt lösenord</h1>
                                <form id="changePasswordForm">
                                    <div class="form-group">
                                        <label for="oldpw">Gammalt lösenord</label>
                                        <input type="password" class="form-control" id="oldpw" name="oldpw" placeholder="*********" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="newpw1">Nytt lösenord</label>
                                        <input type="password" class="form-control" id="newpw1" name="newpw1" placeholder="*********" required aria-describedby="passwordHelpBlock">
                                        <small id="passwordHelpBlock" class="form-text text-muted">
                                            Ditt lösenord måste vara mellan 10-64 tecken långt och får bara innehålla A-Ö 0-9 ! @ # _ .
                                        </small>
                                    </div>
                                    <div class="form-group">
                                        <label for="newpw2">Upprepa nytt lösenord</label>
                                        <input type="password" class="form-control" id="newpw2" name="newpw2" placeholder="*********" required>
                                    </div>
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf->getToken(); ?>">
                                    <button type="submit" class="btn btn-primary">Byt lösenord</button>
                                </form>
                                <div class="error" id="changePasswordError"></div>
                                <div class="success" id="changePasswordSuccess"></div>
                            </div>
                            <?php if (activeUserIsAdmin()): ?>
                            <div class="col-12">
                                <hr>
                                <h2 style="margin-top: 30px;">Skapa registreringslänk</h1>
                                <form id="createRegisterLinkForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf->getToken(); ?>">
                                    <button type="submit" class="btn btn-primary">Skapa länk</button>
                                </form>
                                <div class="error" id="createRegisterLinkError"></div>
                                <hr>
                                <h2 style="margin-top: 30px;">Aktiva registreringslänkar</h2>
                                <table class="table table-hover">
                                    <thead>
                                        <tr class="justify-content-start">
                                            <th scope="col">Länk</th>
                                            <th scope="col">Använd?</th>
                                            <th scope="col">Skapad</th>
                                            <th scope="col">Kopiera</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $links = getRegistrationLinks();
                                        if ($links !== false) {
                                            foreach (getRegistrationLinks() as $value) {
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php echo $value->token ?>
                                                </td>
                                                <td>
                                                    <?php echo $value->used === "1" ? "Ja": "Nej" ?>
                                                </td>
                                                <td>
                                                    <?php echo $value->timestamp ?>
                                                </td>
                                                <td>
                                                    <a data-token="<?php echo $value->token ?>" href="#" class="copyToken"><i class="fas fa-lg fa-clipboard"></i></a>
                                                </td>
                                            </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div id="snackbar">Länken kopierades!</div>
                            <?php endif; ?>
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <script src="js/bootstrap-validate.js"></script>

    <script>
        var formValid = false;
        $(document).ready(function() {
            $("#changePasswordForm").submit(function(event) {
                $("#changePasswordError").empty();
                $("#changePasswordSuccess").empty();

                event.preventDefault();
                var form = $(this);

                if (formValid) {
                    $.ajax({
                        type: "POST",
                        url: "/api/post.php?action=changepassword",
                        data: form.serialize(),
                        success: function(data) {
                            var res = JSON.parse(data);
                            if(res.success) {
                                $("#changePasswordSuccess").append(res.msg);
                                $("#oldpw").val("");
                                $("#newpw1").val("");
                                $("#newpw2").val("");
                            } else {
                                $("#changePasswordError").append(res.msg);
                            }
                        }
                    });
                }
            });

            bootstrapValidate('#oldpw',
                'required:Du måste skriva ett lösenord|max:64:Ditt lösenord får inte vara mer än 64 tecken långt!',
                function(isValid) {
                    if (isValid) {
                        formValid = true;
                    } else {
                        formValid = false;
                    }
                }
            );

            bootstrapValidate('#newpw1',
                'required:Du måste skriva ett lösenord|regex:^[a-zA-ZåÅäÄöÖ\_0-9!@#.]+$:Ditt lösenord får bara innehålla A-Ö 0-9 ! @ # _ .|max:64:Ditt lösenord får inte vara mer än 64 tecken långt!|min:10:Ditt lösenord måste vara minst 10 tecken långt!',
                function(isValid) {
                    if (isValid) {
                        formValid = true;
                    } else {
                        formValid = false;
                    }
                }
            );

            bootstrapValidate('#newpw2',
                'required:Du måste skriva ett lösenord|regex:^[a-zA-ZåÅäÄöÖ\_0-9!@#.]+$:Ditt lösenord får bara innehålla A-Ö 0-9 ! @ # _ .|max:64:Ditt lösenord får inte vara mer än 64 tecken långt!|min:10:Ditt lösenord måste vara minst 10 tecken långt!|matches:#newpw1:Dina lösenord måste matcha!',
                function(isValid) {
                    if (isValid) {
                        formValid = true;
                    } else {
                        formValid = false;
                    }
                }
            );

            <?php if (activeUserIsAdmin()): ?>

            $("#createRegisterLinkForm").submit(function(event) {
                $("#createRegisterLinkError").empty();

                event.preventDefault();
                var form = $(this);

                $.ajax({
                    type: "POST",
                    url: "/api/post.php?action=createlink",
                    data: form.serialize(),
                    success: function(data) {
                        var res = JSON.parse(data);
                        if(res.success) {
                            window.location.reload(true);
                        } else {
                            $("#createRegisterLinkError").append(res.msg);
                        }
                    }
                });
            });

            $('.copyToken').on('click', function(event){
                event.preventDefault();

                var token = $(this).data("token");
                var link = window.location.origin + "/register.php?token=" + token;

                var dummy = document.createElement("input");

                document.body.appendChild(dummy);
                
                dummy.setAttribute("id", "dummy_id");

                document.getElementById("dummy_id").value=link;
                
                dummy.select();

                document.execCommand("copy");

                document.body.removeChild(dummy);

                var x = document.getElementById("snackbar");
                x.className = "show";
                setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
            });

            <?php endif; ?>
        });
    </script>
</body>

</html>
