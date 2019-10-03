<?php

require_once("./helpers.php");
require_once("./settings.php");


// the defaults control the redirect. 0 means no unfilled role found.
$stagenum = 0; // 1...3
$role = 0;


print_header();


$uid = get_userid();


$ROLES = get_roles();
// print_r($ROLES);


$conn = connect();


// ================
// form validation
// ================

$errors = array();

// print_r($_POST);

if (isset($_POST['answers'])
  && isset($_POST['stagenum'])
  && isset($_POST['role'])
  && isset($_POST['task'])
  && isset($_POST['condition'])
) {

  if (!$_POST['stagenum']) {
    $errors['stagenum'] = true;
  }
  if (!$_POST['task']) {
    $errors['task'] = true;
  }
  if (!$_POST['condition']) {
    $errors['condition'] = true;
  }
  if (!$_POST['answers']) {
    $errors['answers'] = true;
  }
  // check non-empty
  $cnt = 0;
  foreach ($_POST['answers'] as $answer) {
    if (trim($answer) !== "") {
      $cnt++;
    }
  }
  if ($cnt === 0) {
    $errors['answers'] = true;
  }

  // print_r($errors);

  if (count($errors) == 0) {

    // store answers
    $stmt = $conn->prepare("INSERT INTO `uses` (
          `sessionid`,
          `qnum`,
          `role`,
          `num`,
          `cond`,
          `task`,
          `answer`,
          `dateline`
        ) VALUES (
          :sessionid,
          :qnum,
          :role,
          :num,
          :condition,
          :task,
          :answer,
          :dateline
        )
        ON DUPLICATE KEY UPDATE
          `answer` = :answer2,
          `dateline` = :dateline2
        ");

    $condition = $_POST['condition'];
    $task = $_POST['task'];
    $time = time();
    $stmt->bindParam(':sessionid', $_POST['uid'], PDO::PARAM_STR);
    $stmt->bindParam(':qnum', $_POST['stagenum'], PDO::PARAM_INT);
    $stmt->bindParam(':role', $_POST['role'], PDO::PARAM_INT);
    $stmt->bindParam(':condition', $condition, PDO::PARAM_STR);
    $stmt->bindParam(':task', $task, PDO::PARAM_STR);

    foreach ($_POST['answers'] as $key => $answer) {
      $humankey = $key;
      $stmt->bindParam(':num', $humankey, PDO::PARAM_INT);
      $stmt->bindParam(':answer', $answer, PDO::PARAM_STR);
      $stmt->bindParam(':answer2', $answer, PDO::PARAM_STR);
      $stmt->bindParam(':dateline', $time, PDO::PARAM_INT);
      $stmt->bindParam(':dateline2', $time, PDO::PARAM_INT);
      $stmt->execute();

    }

    // // todo: error handling
    // if ($stmt->errorCode()) {
    //   print_r($stmt->errorInfo());
    //   die("DB error [".$stmt->errorCode()."]: " . $stmt->errorInfo()[2]);
    // }

    // mark this role as filled by
    // recording the number of answers given for this role
    // and record the end time for this role
    $stmt = $conn->prepare("UPDATE `roles`
          SET `num_answers` = :cnt,
              `dateline_end` = :dateline
          WHERE `sessionid` = :sessionid
          AND `stagenum` = :stagenum
          LIMIT 1
        ");
    $time = time();
    $stmt->bindParam(':sessionid', $_POST['uid'], PDO::PARAM_STR);
    $stmt->bindParam(':cnt', $cnt, PDO::PARAM_INT);
    $stmt->bindParam(':stagenum', $_POST['stagenum'], PDO::PARAM_INT);
    $stmt->bindParam(':dateline', $time, PDO::PARAM_INT);
    $stmt->execute();

  }// no errors

} // form was posted



// ================
// get unfilled role
// ================

$stmt = $conn->prepare("SELECT
      `stagenum`,
      `task`,
      `cond`,
      `role`
    FROM `roles`
    WHERE `sessionid` = :uid
    AND `num_answers` = '0'
    ORDER BY `stagenum` ASC
    LIMIT 1
    ");
$stmt->execute(array('uid' => $uid));
$role = $stmt->fetch();
// var_dump($role);

// redirect, if all roles were filled
if ($role === false) {
  redirect('final.php');
}



// if we have an unfilled role, populate $stagenum and $role
if ($role) {
    $stagenum =  $role['stagenum'];
    $condition = $role['cond'];
    $task = $role['task'];
    $role = $role['role'];
}

// record the start time for this role
$stmt = $conn->prepare("UPDATE `roles`
      SET `dateline_start` = :dateline
      WHERE `sessionid` = :sessionid
      AND `stagenum` = :stagenum
      AND `cond` = :condition
      AND `task` = :task
      LIMIT 1
    ");
$time = time();
$stmt->bindParam(':sessionid', $uid, PDO::PARAM_STR);
$stmt->bindParam(':stagenum', $stagenum, PDO::PARAM_INT);
$stmt->bindParam(':condition', $condition, PDO::PARAM_STR);
$stmt->bindParam(':task', $task, PDO::PARAM_STR);
$stmt->bindParam(':dateline', $time, PDO::PARAM_INT);
$stmt->execute();


$stmt = null;
$conn = null;


$progress = 30 + ( 45 / NUMSTAGES * ($stagenum - 1));


// ================
// output
// ================

?>

<div class="grid-container">

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <h1><?php print_title() ?></h1>
    </div>
  </div>

  <?php echo progress($progress); ?>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <div class="callout">
        <?php
        $pronoun = 'a';
        if (isset($ROLES[$role]['name'][0]) &&
          in_array(strtolower($ROLES[$role]['name'][0]), array('a','e','i'))) {
          $pronoun = 'an';
        }
        if (isset ($ROLES[$role]['name'])) {
          echo '<h2>Imagine you are ' . $pronoun . ' ' . $ROLES[$role]['name'] . '</h2>';
        } else {
          echo '<h2>Your Task (' . $stagenum . '/' . NUMSTAGES . ')</h2>';
        }
        if (isset($ROLES[$role]) && $condition === 'ROLEIMG') {
          echo "<img src='" . $ROLES[$role]['img'] . "' alt='" . htmlspecialchars($ROLES[$role]['name'], ENT_QUOTES) . "' />";
        }
        ?>
        <img src="img/<?php echo $task ?>.jpg"
          style="float:right;max-height:210px;max-width:30%"
          alt="<?php echo $task ?>"
        />
        <p style="padding-top:1em">
          <?php
          if (isset($ROLES[$role]['name'])) { // && PRIMING === true
            echo '<strong>As ' . $pronoun .' ' . $ROLES[$role]['name'] . '</strong>, ';
          } else {
            echo 'Your goal is to ';
          }
          ?>
          think of <em>unique and unusual</em> uses for <!--a common object:-->
          a <strong><?php echo strtoupper($task) ?></strong>.<br />
          <?php 
          	if (strtolower($task) === 'brick' ) {
              echo "For example, using a brick as an earring is an unusual and unique use. However, using a brick to build a wall is not unique or unusual.";
	        } else {
              echo "For example, using a paper clip as an earring is an unusual and unique use. However, using a paper clip to bind papers is not unique or unusual.";
            }
          ?>
        </p>
<!--         <p>
          Please DO NOT (!) use any external sources (e.g., websites, people) to complete this task.
        </p>
 --> 
        <p>
          This task is spread over <?php echo NUMSTAGES ?> stages.
          Stage <?php echo $stagenum ?> is below.<br />
          Your answers must, however, be <strong>unique</strong> across all stages.
        </p>
        <p>
          <!-- Try to think of as many unique and unusual uses as possible.<br /> -->
          Please provide at least <?php echo NUM_ANSWERS_REQUIRED ?> <em>different</em> answers
          <?php if (NUM_ANSWERS_REQUIRED < NUM_MAX_ANSWERS) {
            echo ", but you can optionally enter up to " . NUM_MAX_ANSWERS;
          } ?>
          - one answer per textbox below.<br />
          There is no minimum or maximum word count, simply explain the use case concisely.
        </p>
        <p>
          <!-- Again: -->
          DO NOT (!) use any external sources (e.g., websites, people) to complete this task.
        </p>
      </div>
    </div>
  </div>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <div class="callout">
        <form autocomplete="nope" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

          <input type="hidden" name="stagenum" value="<?php echo htmlspecialchars($stagenum, ENT_QUOTES) ?>" />
          <input type="hidden" name="task" value="<?php echo htmlspecialchars($task, ENT_QUOTES) ?>" />
          <input type="hidden" name="condition" value="<?php echo htmlspecialchars($condition, ENT_QUOTES) ?>" />
          <input type="hidden" name="role" value="<?php echo htmlspecialchars($role, ENT_QUOTES) ?>" />
          <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid, ENT_QUOTES) ?>" />

          <div id="error" style="<?php echo isset($errors['answers']) && $errors['answers'] ? '' : 'display:none;' ?>float:none;" class="error-text required">Please enter at least <?php echo NUM_ANSWERS_REQUIRED ?> ideas.</div>

          <?php
            for ($i = 1; $i <= NUM_MAX_ANSWERS; $i++) {
              echo '<label>Use ' . $i;
              $is_required = $i <= NUM_ANSWERS_REQUIRED;
              if ($is_required) {
                echo '<span class="required" style="float:right;display:none">* required</span>';
              }
              echo '<textarea autocomplete="nope" ' . ($is_required ? " required " : '') . '" name="answers[' . $i . ']" rows="2">'.
                (isset($errors['answers']) && $errors['answers'] && isset($_POST['answers'][$i]) ? htmlspecialchars(trim($_POST['answers'][$i])) : '') .
                '</textarea></label>';
            }
          ?>

          <input id="submitbtn" type="submit" class="button" value="Next" />

        </form>
      </div>
    </div>
  </div>

</div>



<script type="text/javascript">
$(function() {

  $('textarea').on('change', function () {
    $(this).removeClass("input-error");
  })

  $("#submitbtn").on( "click", function(e) {
    e.preventDefault();
    e.stopPropagation();

    var errors = [];

    // validate the form
    $.each($('textarea[required]'), function(key, item) {
      var txt = $(item).val().trim();
      // console.log(key, txt, item, $(item).attr('required'));
      if (txt === "") {
        // console.log('error', item);
        errors.push('required');
        $(item).addClass("input-error");
        $(".required").show();
      } else {
        $(item).removeClass("input-error");
        $(".required").hide();
      }
    });

    // console.log('errors', errors);

    if (errors.length > 0) {
      $('#error').show();
      return false;
    }

    $('#error').hide();

    $("form").submit();
    return true;

  });
});
</script>


<?php print_footer() ?>

</body>
</html>