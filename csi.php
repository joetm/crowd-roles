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

  // check required fields only

  // if (!isset($_POST['age']) || !$_POST['age']) {
  //   $errors['age'] = true;
  // } else {
  //   // is numeric?
  //   if (!is_numeric($_POST['age'])) {
  //     $errors['age'] = true;
  //   } else {
  //     $age = intval($_POST['age']);
  //   }
  // }

  $input = $_POST;
  // remove unwanted items - keep only answers
  unset($input['uid']);

  // var_dump('POST', $_POST);
  // echo "<br />";

  // store everything
  $stmt = $conn->prepare("INSERT INTO `csi` (
        `sessionid`,
        `question`,
        `cond`,
        `task`,
        `value`,
        `dateline`
      ) VALUES (
        :sessionid,
        :question,
        :condition,
        :task,
        :value,
        :dateline
      )
      ON DUPLICATE KEY UPDATE
        `value` = :value2,
        `dateline` = :dateline2
      ");

// TODO
    $condition = CONDITION;
    $task = TASK;

    $time = time();
    $stmt->bindParam(':sessionid', $_POST['uid'], PDO::PARAM_STR);
    $stmt->bindParam(':condition', $condition, PDO::PARAM_STR);
    $stmt->bindParam(':task', $task, PDO::PARAM_STR);
    $stmt->bindParam(':dateline', $time, PDO::PARAM_INT);
    $stmt->bindParam(':dateline2', $time, PDO::PARAM_INT);

    foreach ($input as $key => $answer) {
      if (is_array($answer)) {
        foreach ($answer as $k => $a) {
          $item = 0;
          $stmt->bindParam(':question', $k, PDO::PARAM_STR);
          $stmt->bindParam(':value', $a, PDO::PARAM_STR);
          $stmt->bindParam(':value2', $a, PDO::PARAM_STR);
          $stmt->execute();
        }
      } else {
        $item = 0;
        $stmt->bindParam(':question', $key, PDO::PARAM_STR);
        $stmt->bindParam(':value', $answer, PDO::PARAM_STR);
        $stmt->bindParam(':value2', $answer, PDO::PARAM_STR);
        $stmt->execute();
      }

      // // todo: error handling
      // if ($stmt->errorCode()) {
      //   print_r($stmt->errorInfo());
      //   die("DB error [".$stmt->errorCode()."]: " . $stmt->errorInfo()[2]);
      // }

    } // foreach

    //no errors?
    if ( count($errors) === 0 ) {
      // if all went well, store data and go to next stage

      //redirect to next stage
      redirect('end.php');

    }

  }





// get the condition for this user
$condition = false;
$task = false;
$stmt = $conn->prepare("SELECT
      `cond`,
      `task`
    FROM `users`
    WHERE `sessionid` = :uid
    LIMIT 1
    ");
$stmt->execute(array('uid' => $uid));
$res = $stmt->fetch();
if ($res) {
  $condition = $res['cond'];
  $task = $res['task'];
}
// var_dump($condition);
// var_dump($task);


$stmt = null;
$conn = null;



?>

<div class="grid-container">

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <h1><?php print_title() ?></h1>
    </div>
  </div>

  <?php echo progress(95); ?>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">

        <form autocomplete="nope" autocomplete="off" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid, ENT_QUOTES) ?>" />

          <div class="callout">

            <?php echo $required_HTML ?>

            <h4>
              <!-- Think back over your entire experience with the tasks.<br /> -->
              <!-- Then --> Rate your agreement with the following statements:
            </h4>

            <?php

              $i = 0;
              csi_question(++$i, 'worth', 'Results worth the effort', 'What I was able to produce was worth the effort I had to exert to produce it.');
              csi_question(++$i, 'expressiveness', 'Expressiveness', 'I was able to be very expressive and creative while doing the activity.');
              csi_question(++$i, 'exploration', 'Exploration', 'It was easy for me to explore many different ideas, options, designs, or outcomes.');
              csi_question(++$i, 'immersion', 'Immersion', 'My attention was fully tuned to the activity, and I forgot about the system/tool I was using.');
              csi_question(++$i, 'enjoyment', 'Enjoyment', 'I was very engaged in this activity - I enjoyed this activity and would do it again.');
              csi_question(++$i, 'collaboration', 'Collaboration', 'The system/tool allowed other people to work with me easily.');

            ?>

            <h4>
              For each pair below, please select which factor is more important to you when doing the activity:
            </h4>

            <div id="pairs">

              <?php

              $id = 0;
              csi_pair(++$id, 'Exploration', 'Collaboration');
              csi_pair(++$id, 'Exploration', 'Enjoyment');
              csi_pair(++$id, 'Exploration', 'Results Worth Effort');
              csi_pair(++$id, 'Exploration', 'Immersion');
              csi_pair(++$id, 'Exploration', 'Expressiveness');
              csi_pair(++$id, 'Collaboration', 'Enjoyment');
              csi_pair(++$id, 'Collaboration', 'Results Worth Effort');
              csi_pair(++$id, 'Collaboration', 'Immersion');
              csi_pair(++$id, 'Collaboration', 'Expressiveness');
              csi_pair(++$id, 'Expressiveness', 'Results Worth Effort');
              csi_pair(++$id, 'Expressiveness', 'Immersion');
              csi_pair(++$id, 'Expressiveness', 'Enjoyment');
              csi_pair(++$id, 'Enjoyment', 'Immersion');
              csi_pair(++$id, 'Enjoyment', 'Results Worth Effort');
              csi_pair(++$id, 'Immersion', 'Results Worth Effort');

              ?>

            </div><!-- pairs-->

<br />

            <h5>Please be truthful in the following two questions.
            <!-- Your compensation is not affected by your answers. -->
            You will be paid no matter what you answer.</h5>


            <div <?php echo @$errors['cheat'] ? '' : 'style="display:none"' ?> id="error-cheat" class="error-text">Please answer this question.</div>
            <div class="grid-x grid-padding-x">
              <fieldset class="large-12 cell">
                <legend>
                  Did you cheat and use the internet in answering the tasks?
                  <?php echo $required_asterisk ?>
                </legend>
                <input autocomplete="nope" type="radio" name="cheat" value="0" id="cheatN"><label for="cheatN">No</label>
                <input autocomplete="nope" type="radio" name="cheat" value="1" id="cheatY" required><label for="cheatY">Yes</label>
              </fieldset>
            </div>

            <div class="grid-x grid-padding-x">
              <fieldset class="large-12 cell">
                <div <?php echo @$errors['past'] ? '' : 'style="display:none"' ?> id="error-past" class="error-text">Please answer this question.</div>
                <legend>
                  Did you ever complete a similar task before that asked for creative uses of a <?php echo $task; ?>?
                  <?php echo $required_asterisk ?>
                </legend>
                <input autocomplete="nope" type="radio" name="past" value="0" id="pastN"><label for="pastN">No</label>
                <input autocomplete="nope" type="radio" name="past" value="1" id="pastY" required><label for="pastY">Yes</label>
              </fieldset>
            </div>


            <div class="grid-x grid-padding-x">
              <fieldset class="large-12 cell">
                <legend>
                  Do you have any other feedback for us? (optional)
                </legend>
                <textarea autocomplete="nope" name="feedback" id="feedback" rows="2"></textarea>
              </fieldset>
            </div>

            <div id="error" style="display:none;color:red;">
              Please fill all required fields (marked with a red star).
            </div>

            <input id="submitbtn" type="submit" class="button" value="Next" />

      </form>

    </div>
  </div>
</div>


<script src="js/jquery.matchHeight-min.js"></script>
<script type="text/javascript">
$(function() {

    $("input,select").on( "change", function() {
        $(this).removeClass("input-error");
        $(this).parent().removeClass("input-error");
        $(this).parent().find('div.error-text').hide();
        $(this).parent().parent().removeClass('input-error');
    });

    $('.slider').on('moved.zf.slider', function () {
        $(this).removeClass("input-error");
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
      // csi sliders
      $.each($('.sliderinput'), function (index, si) {
        var v = $(si).val();
        console.log('slider', index, ':', v);
        if (!v || v == 5.5) {
          // console.log('slider',index, ' missing value');
          errors.push("slider" + index);
          $(si).parent().parent().parent().addClass('input-error');
        }
      });

      // csi pairs
      for (var i = 1; i <= 15; i++) {
        if (!$("input[name='csipair\["+i+"\]']:checked").val()) {
          console.log('csi pair ' + i + " missing");
          errors.push("csipair["+i+"]");
          $("input[name='csipair\["+i+"\]']").parent().parent().addClass('input-error');
        }
      }

      // radio fields
      $.each([
        'cheat', 'past'
      ], function (index, field) {
        if (!$("input[name='"+field+"']:checked").val()) {
          console.log(field + " missing");
          errors.push(field);
          $("input[name='"+field+"']").parent().parent().addClass('input-error');
        }
      });

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

    // $('.slider').on('click', function () {
    //   alert('slider clicked');
    //   $(this).removeClass('disabled');
    // });

});
</script>


<?php

  print_footer();

?>
</body>
</html>