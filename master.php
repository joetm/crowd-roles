<?php

require_once("./helpers.php");
require_once("./settings.php");

print_header();


$uid = get_userid();


$conn = connect();

// ================
// form validation
// ================


$errors = array();



if (count($_POST) > 0) {

	if (!isset($_POST['selected_use'])) {
		die('Invalid form field');
	}


	$fields = explode("-", $_POST['selected_use']);
	$qnum = $fields[0];
	$role = $fields[1];
	$num = $fields[2];
	$task = $fields[3];
	$dateline = $fields[4];

	$time_past = $_POST['time_past'];
	$reason = $_POST['reason'];
	$fromcond = $_POST['fromcond'];
	$confidence = $_POST['confidence'];


var_dump($role);

// get role name
$roles = get_roles();
if ($role == 0) {
	$role_name = '';
} else {
	$role_name = $roles[intval($role)]['name'];
}

// get the answer as text

$stmt = $conn->prepare("SELECT
      answer
    FROM `uses`
	    JOIN `users` ON (users.sessionid = uses.sessionid)
    WHERE users.prolific_id = :PROLIFIC_PID
    AND uses.qnum = :qnum
    AND uses.role = :role
    AND uses.num = :num
    AND uses.task = :task
    LIMIT 1
    ");
$stmt->execute(array(
	'PROLIFIC_PID' => $_POST['prolific_id'],
	'qnum' => $qnum,
	'role' => $role,
	'num' => $num,
	'task' => $task,
));
$answer = '';
if ($stmt) {
	$answer = $stmt->fetch();
	$answer = $answer['answer'];
}


// var_dump($answer);
// var_dump($_POST);
// die;

  // store everything
  $stmt = $conn->prepare("INSERT INTO `master` (
        `prolific_id`,
        `selected_use`,
        `answer`,
        `design`,
        `task`,
        `qnum`,
        `role`,
        `role_name`,
        `num`,
        `time_past`,
        `reason`,
        `fromcond`,
        `confidence`,
        `dateline`
      ) VALUES (
        :prolific_id,
        :selected_use,
        :answer,
        :design,
        :task,
        :qnum,
        :role,
        :role_name,
        :num,
        :time_past,
        :reason,
        :fromcond,
        :confidence,
        :dateline
      )
      ");

    $stmt->bindParam(':prolific_id', $_POST['prolific_id'], PDO::PARAM_STR);
    $stmt->bindParam(':selected_use', $_POST['selected_use'], PDO::PARAM_STR);
    $stmt->bindParam(':answer', $answer, PDO::PARAM_STR);
    $stmt->bindParam(':design', $_POST['design'], PDO::PARAM_INT);
    $stmt->bindParam(':task', $task, PDO::PARAM_STR);
    $stmt->bindParam(':qnum', $qnum, PDO::PARAM_INT);
    $stmt->bindParam(':role', $role, PDO::PARAM_INT);
    $stmt->bindParam(':role_name', $role_name, PDO::PARAM_STR);
    $stmt->bindParam(':num', $num, PDO::PARAM_STR);
    $stmt->bindParam(':time_past', $time_past, PDO::PARAM_INT);
    $stmt->bindParam(':reason', $_POST['reason'], PDO::PARAM_STR);
    $stmt->bindParam(':fromcond', $_POST['fromcond'], PDO::PARAM_STR);
    $stmt->bindParam(':confidence', $_POST['confidence'], PDO::PARAM_INT);
    $stmt->bindParam(':dateline', $dateline, PDO::PARAM_INT);

    $stmt->execute();

    // // todo: error handling
    // if ($stmt->errorCode()) {
    //   print_r($stmt->errorInfo());
    //   die("DB error [".$stmt->errorCode()."]: " . $stmt->errorInfo()[2]);
    // }

    //no errors?
    if ( count($errors) === 0 ) {
      // if all went well, store data and go to next stage

      //redirect to next stage
      redirect('endmaster.php');

    }

  }



// get the participant info from the database

$design = false;
$task = false;
$initial_date = false;
$time_past = 0;
$prolific_id = false;
$stmt = $conn->prepare("SELECT
      users.design AS `design`,
      designs.task AS `task`,
      users.dateline AS `initial_date`,
      users.prolific_id AS `prolific_id`
    FROM `users`
	    JOIN `designs` ON (users.design = designs.ID)
    WHERE `prolific_id` = :PROLIFIC_PID
    LIMIT 1
    ");
$stmt->execute(array('PROLIFIC_PID' => @$_GET['PROLIFIC_PID']));
$res = $stmt->fetch();
if ($res) {
  $prolific_id = $res['prolific_id'];
  $design = $res['design'];
  $task = $res['task'];
  $initial_date = $res['initial_date'];
  $time_past = time() - $initial_date;
}
unset($res);


$stmt = $conn->prepare("SELECT
      `uses`.`qnum`,
      `uses`.`role`,
      `uses`.`num`,
      `uses`.`cond`,
      `uses`.`task`,
      `uses`.`answer`,
      `uses`.`dateline`
    FROM `uses`
	    JOIN `users` ON (users.sessionid = uses.sessionid)
    WHERE users.prolific_id = :PROLIFIC_PID
    ");
$stmt->execute(array('PROLIFIC_PID' => @$_GET['PROLIFIC_PID']));

$uses = array();
if ($stmt) {
	while($use = $stmt->fetch()) {
		array_push($uses, $use);
	}
}

$stmt = null;
$conn = null;



if (count($uses) == 0 || !isset($_GET['PROLIFIC_PID'])) {
echo <<<EOT
<html>
<head>
<title>ERROR - Master Idea Selection</title>
</head>
<body align="center">
<h2>ERROR - Missing or invalid Prolific ID.</h2>
We could not detect your Prolific ID.<br />
You must visit this study through the link in Prolific.
</body>
</html>
EOT;
exit();
}


?>

<div class="grid-container">

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <h1>Master Idea Selection</h1>
    </div>
  </div>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">

        <form autocomplete="nope" autocomplete="off" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid, ENT_QUOTES) ?>" />
          <input type="hidden" name="design" value="<?php echo htmlspecialchars($design, ENT_QUOTES) ?>" />
          <input type="hidden" name="task" value="<?php echo htmlspecialchars($task, ENT_QUOTES) ?>" />
          <input type="hidden" name="time_past" value="<?php echo htmlspecialchars($time_past, ENT_QUOTES) ?>" />
          <input type="hidden" name="prolific_id" value="<?php echo htmlspecialchars($prolific_id, ENT_QUOTES) ?>" />

          <div class="callout">

						<span class="required right">* required</span>

            <p>
              Below is a list of unique and unusual uses for a <?php echo $task ?>.<br />
              You provided us this list in a study on <?php echo date('D, d M Y', $initial_date) ?>.
              <!-- in coming up with unusual and unique uses for the <?php echo TASK ?>.-->
              <br />
              Please answer the following questions.
            </p>
            <br />


            <div id="error" style="display:none;color:red;">
              Please fill all required fields (marked with a red star).
            </div>

            <fieldset class="large-12 cell">
            	<p>
            		<strong>From the list, please select the use of the <?php echo $task ?> that you think is<br />
            			<i>most unique and unusual</i>.</strong>
						<span class="required">*</span>
				</p>
<div class="grid-x">
					<?php
					shuffle($uses);
					for ($i = 0; $i < count($uses); $i++) {
						// var_dump($uses[$i]);
						echo '
<div class="large-1 cell">
								<input type="radio" name="selected_use"
								value="' .
									$uses[$i]['qnum'] . '-' .
									$uses[$i]['role'] . '-' .
									$uses[$i]['num'] . '-' .
									$uses[$i]['task'] . '-' .
									$uses[$i]['dateline'] .
								'"
								id="use'.$i.'" required>
</div>
<div class="large-11 cell">
								<label for="use'.$i.'">' . $uses[$i]['answer'] . '</label>
</div>
              ';
					}
					?>
</div>
            </fieldset>


            <div class="grid-x grid-padding-x">
              <fieldset class="large-12 cell">
                <p>
                  <strong>Why did you select this particular use? What makes it particularly <i>unique and unusual</i>?
                  </strong>
            <span class="required">*</span>
                </>
                <textarea autocomplete="nope" name="reason" id="reason" rows="3"></textarea>
              </fieldset>
            </div>


            <fieldset class="large-12 cell">
            	<p>
            		Remember, in the study, you were going through three stages:
            		one without a role, one with a role, and one with role and an additional image.<br />
            		<strong>From which of these three conditions do you think the selected use from above is from?</strong>
						<span class="required">*</span>
				</p>
				<div>
	              <select autocomplete="nope" id="fromcond" name="fromcond">
	                <option value=""></option>
	                <option value="NOROLE">No Role</option>
	                <option value="ROLE">Role</option>
	                <option value="ROLEIMG">Role + Image</option>
	              </select>
				</div>
            </fieldset>


            <fieldset class="large-12 cell">
              <div>
                <strong><em>How confident are you that the selected use was produced in this role?</em></strong>
						<span class="required">*</span>
              </div>
              <div class="grid-x grid-margin-x" style="margin:0.5em 0">
                <div class="cell small-2">
                  <label for="d1">
                    Not at all confident
                  </label>
                  <input id="d1" autocomplete="nope" name="confidence" value="1" type="radio" />
                </div>
                <div class="cell small-2">
                  <label for="d2">
                    Little confidence
                  </label>
                    <input id="d2" autocomplete="nope" name="confidence" value="2" type="radio" />
                </div>
                <div class="cell small-2">
                  <label for="d3">
                    Neither
                  </label>
                    <input id="d3" autocomplete="nope" name="confidence" value="3" type="radio" />
                </div>
                <div class="cell small-2">
                  <label for="d4">
                    Somewhat confident
                  </label>
                    <input id="d4" autocomplete="nope" name="confidence" value="4" type="radio" />
                </div>
                <div class="cell small-2">
                  <label for="d5">
                    Very Confident
                  </label>
                  <input id="d5" autocomplete="nope" name="confidence" value="5" type="radio" />
                </div>
              </div>
            </fieldset>



            <input id="submitbtn" type="submit" class="button" value="Next" />

      </form>

    </div>
  </div>
</div>


<script src="js/jquery.matchHeight-min.js"></script>
<script type="text/javascript">
  $(function() {

    $("input,textarea,select").on("change", function() {
        $(this).removeClass("input-error");
        $(this).parent().find('div.error-text').hide();
        $(this).parent().removeClass("input-error");
        $(this).parent().parent().removeClass('input-error');
    });

    $("#submitbtn").on( "click", function(e) {
      e.preventDefault();
      e.stopPropagation();

      $("#error").hide();

      var errors = [];

      // ------------------
      // validate the form
      // ------------------
      // radio fields
      $.each([
        'selected_use',
        'confidence'
      ], function (index, field) {
        if (!$("input[name='"+field+"']:checked").val()) {
          console.log(field + " missing");
          errors.push(field);
          $("input[name='"+field+"']").parent().parent().addClass('input-error');
        }
      });

      $.each([
        '#reason'
      ], function (index, field) {
        if (!$(field).val()) {
          console.log(field + " missing");
          errors.push(field);
          $(field).parent().addClass('input-error');
        }
      });

      if (!$('#fromcond').val()) {
        console.log("fromcond missing");
        errors.push('fromcond');
        $('#fromcond').parent().parent().addClass('input-error');
      }
 

      // error checking
      if (errors.length > 0) {
        $("#error").show();
        // alert('error');
        return false;
      }

      $("#error").hide();

      $("form").submit();
      return true;

    });
  });
</script>


<?php

  print_footer();

?>
</body>
</html>