<?php
require_once 'include/functions.php';
sec_session_start();
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
    <title>The BBBoys mat-info - Hem</title>
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
                        <li class="nav-item active">
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
                                <h1 style="margin-top: 30px;">Maten för veckan, Vecka <?php echo date("W"); ?></h1>
                                <ul class="nav nav-tabs" id="foodTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="timeout-tab" data-toggle="tab" href="#timeout" role="tab" aria-controls="timeout" aria-selected="true">Timeout</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="villa-tab" data-toggle="tab" href="#villa" role="tab" aria-controls="villa" aria-selected="false">Villa Oscar</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="bistroj-tab" data-toggle="tab" href="#bistroj" role="tab" aria-controls="bistroj" aria-selected="false">Bistro J</a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="foodTabsContent">
                                    <div class="tab-pane fade show active" id="timeout" role="tabpanel" aria-labelledby="timeout-tab">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Dag</th>
                                                    <th scope="col">Maträtt</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach (getTimeoutItems() as $key => $value) {
                                                ?>
                                                    <tr class="<?php echo currentDayClass($key); ?>">
                                                        <td><?php echo convertDay($key) ?></td>
                                                        <td><?php echo $value ?></td>
                                                    </tr>
                                            <?php
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade" id="villa" role="tabpanel" aria-labelledby="villa-tab">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr class="justify-content-start">
                                                    <th scope="col" style="width: 110px;">Dag</th>
                                                    <th scope="col">Maträtter</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach (getVillaItems() as $key => $value) {
                                                ?>
                                                <tr class="<?php echo currentDayClass($key); ?>">
                                                    <td>
                                                        <a class="dropdown-toggle <?php echo currentDayExpand($key) == 'true' ? '' : 'collapsed'; ?>" data-toggle="collapse" href="#villa<?php echo $key; ?>" role="button" aria-expanded="<?php echo currentDayExpand($key); ?>" aria-controls="villa<?php echo $key; ?>">
                                                            <?php echo convertDay($key); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="collapse <?php echo currentDayExpand($key) == 'true' ? 'show' : ''; ?>" id="villa<?php echo $key; ?>">
                                                            <table class="table table-hover table-bordered">
                                                                <thead>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    foreach ($value as $val) {
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?php
                                                                                echo $val;
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                              </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade" id="bistroj" role="tabpanel" aria-labelledby="bistroj-tab">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr class="justify-content-start">
                                                    <th scope="col" style="width: 110px;">Dag</th>
                                                    <th scope="col">Maträtter</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach (getBistroJItems() as $key => $value) {
                                                ?>
                                                <tr class="<?php echo currentDayClass($key); ?>">
                                                    <td>
                                                        <a class="dropdown-toggle <?php echo currentDayExpand($key) == 'true' ? '' : 'collapsed'; ?>" data-toggle="collapse" href="#bistroj<?php echo $key; ?>" role="button" aria-expanded="<?php echo currentDayExpand($key); ?>" aria-controls="bistroj<?php echo $key; ?>">
                                                            <?php echo convertDay($key); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="collapse <?php echo currentDayExpand($key) == 'true' ? 'show' : ''; ?>" id="bistroj<?php echo $key; ?>">
                                                            <table class="table table-hover table-bordered">
                                                                <thead>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    foreach ($value as $val) {
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?php
                                                                                echo $val;
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                              </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                
                                <div class="col-6 offset-3 text-center">
                                    <hr>
                                    <?php
                                    $poll = getAllPolls()[0];
                                    $choices = getPollChoicesByPollId($poll->id);
                                    if (login_check()):
                                    ?>
                                    <form method="post" id="pollvoteform">
                                        <h2>Idag är det <?php echo getDateSwe(); ?></h2>
                                        <h3><?php echo $poll->question ?></h3>
                                        <?php
                                            foreach ($choices as $choice) {
                                                if (userCanVoteToday($poll->id) && votingIsAllowed()) {
                                                    echo <<<EOD
                                                        <div class="form-group">
                                                            <div class="custom-control custom-radio">
                                                                <input type="radio" id="choice_$choice->id" name="choice" value="$choice->id" class="custom-control-input">
                                                                <label class="custom-control-label" for="choice_$choice->id">$choice->value</label>
                                                            </div>
                                                        </div>
EOD;
                                                } else {
                                                    $checked = "";
                                                    if (userHasVotedForChoice($poll->id, $choice->id)) {
                                                        $checked = "checked";
                                                    }
                                                    echo <<<EOD
                                                        <div class="form-group">
                                                            <div class="custom-control custom-radio">
                                                                <input $checked type="radio" id="choice_$choice->id" name="choice" value="$choice->id" class="custom-control-input" disabled>
                                                                <label class="custom-control-label" for="choice_$choice->id">$choice->value</label>
                                                            </div>
                                                        </div>
EOD;
                                                }
                                            }
                                        ?>
                                        <?php if (votingIsAllowed()): ?>
                                            <?php if (userCanVoteToday($poll->id)): ?>
                                            <button type="submit" class="btn btn-primary">Rösta</button>
                                            <?php else: ?>
                                            <button type="submit" disabled class="btn btn-primary disabled">Redan röstat</button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button type="submit" disabled class="btn btn-primary disabled">Röstning stängd</button>
                                        <?php endif; ?>

                                        <input type="hidden" name="poll_id" value="<?php echo $poll->id; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf->getToken(); ?>">
                                    </form>
                                    <h4 class="<?php echo votingIsAllowed() ? "text-success" : "text-danger" ?>">Röstningen är <?php echo votingIsAllowed() ? "öppen tills 11:30" : "stängd för dagen" ?></h4>
                                    <div class="error" id="voteFormError"></div>
                                    <?php else: ?>
                                    <h2>Idag är det <?php echo getDateSwe(); ?></h2>
                                    <h3><?php echo $poll->question ?></h3>
                                    <h3><a href="login.php">Logga in för att rösta</a></h3>
                                    <?php endif; ?>
                                    <hr>
                                    <?php
                                        $answers = getAnswersForPollByPollId($poll->id);
                                        $amountOfAnswers = $answers === false ? 0 : count($answers);
                                        echo "<h3>" . $amountOfAnswers . " Svar</h3>";
                                        foreach ($choices as $choice) {
                                            $amount = 0;
                                            if ($amountOfAnswers > 0) {
                                                foreach ($answers as $answer) {
                                                    if ($choice->id == $answer->choice_id) {
                                                        $amount++;
                                                    }
                                                }
                                            }
                                            
                                            if ($amountOfAnswers == 0) {
                                                echo <<<EOD
                                                    <div class="text-left">$choice->value (0%)</div>
                                                    <div class="progress answerprogress">
                                                        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="0"></div>
                                                    </div><br>
EOD;
                                            } else {    
                                                $percent = $amount > 0 ? $amount/$amountOfAnswers : "0%";
                                                $percent_friendly = $amount > 0 ? number_format($percent * 100, 2) . '%' : "0%";
                                                echo <<<EOD
                                                    <div class="text-left">$choice->value ($percent_friendly)</div>
                                                    <div class="progress answerprogress">
                                                        <div class="progress-bar" role="progressbar" aria-valuenow="$amount" aria-valuemin="0" aria-valuemax="$amountOfAnswers" style="width:$percent_friendly;"></div>
                                                    </div><br>
EOD;
                                            }
                                        }
                                    ?>
                                </div>
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
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $("#pollvoteform").submit(function(event) {
                $("#voteFormError").empty();

                event.preventDefault();
                var form = $(this);

                $.ajax({
                    type: "POST",
                    url: "/api/post.php?action=vote",
                    data: form.serialize(),
                    success: function(data) {
                        var res = JSON.parse(data);
                        if(res.success) {
                            window.location.reload(true);
                        } else {
                            $("#voteFormError").append(res.msg);
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
