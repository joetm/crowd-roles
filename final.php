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

  $design = $_POST['design'];
  $task = $_POST['task'];

  $time = time();

  $input = $_POST;
  // remove unwanted items - keep only answers
  unset($input['design']);
  unset($input['task']);
  unset($input['uid']);

  var_dump('POST', $input);


  // store everything
  $stmt = $conn->prepare("INSERT INTO `answers` (
        `sessionid`,
        `question`,
        `item`,
        `design`,
        `task`,
        `value`,
        `dateline`
      ) VALUES (
        :sessionid,
        :question,
        :item,
        :design,
        :task,
        :value,
        :dateline
      )
      ON DUPLICATE KEY UPDATE
        `value` = :value2,
        `dateline` = :dateline2
      ");

    $stmt->bindParam(':sessionid', $_POST['uid'], PDO::PARAM_STR);
    $stmt->bindParam(':design', $design, PDO::PARAM_STR);
    $stmt->bindParam(':task', $task, PDO::PARAM_STR);
    $stmt->bindParam(':dateline', $time, PDO::PARAM_INT);
    $stmt->bindParam(':dateline2', $time, PDO::PARAM_INT);

    foreach ($input as $key => $answer) {

      if (is_array($answer)) {
        foreach ($answer as $k => $a) {
          $item = 0;
          $stmt->bindParam(':question', $key, PDO::PARAM_STR);
          $stmt->bindParam(':item', $k, PDO::PARAM_INT);
          $stmt->bindParam(':value', $a, PDO::PARAM_STR);
          $stmt->bindParam(':value2', $a, PDO::PARAM_STR);
          $stmt->execute();
        }
      } else {
        $item = 0;
        $stmt->bindParam(':question', $key, PDO::PARAM_STR);
        $stmt->bindParam(':item', $item, PDO::PARAM_INT);
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
      `users`.`design` AS `design`,
      `designs`.`task` AS `task`
    FROM `users`
    JOIN `designs` ON (`users`.`design` = `designs`.`ID`)
    WHERE `sessionid` = :uid
    LIMIT 1
    ");
$stmt->execute(array('uid' => $uid));
$res = $stmt->fetch();
if ($res) {
  $design = $res['design'];
  $task = $res['task'];
}
unset($res);

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
          <input type="hidden" name="design" value="<?php echo htmlspecialchars($design, ENT_QUOTES) ?>" />
          <input type="hidden" name="task" value="<?php echo htmlspecialchars($task, ENT_QUOTES) ?>" />

          <div class="callout">

            <?php echo $required_HTML ?>

            <h4>
              Think back over your entire experience with the tasks.
              <!-- in coming up with unusual and unique uses for the <?php echo TASK ?>.-->
              <br />
              Please answer the following questions.
            </h4>
            <br />

            <div id="error" style="display:none;color:red;">
              Please fill all required fields (marked with a red star).
            </div>

            <!-- DIFFICULTY -->
            <fieldset class="large-12 cell">
              <div>
                <strong><em>Overall, how difficult were the tasks for you?</em></strong>
                <?php echo $required_asterisk ?>
              </div>
              <div class="grid-x grid-margin-x" style="margin:0.5em 0">
                <div class="cell small-2">
                  <label for="d1">
                    Very difficult
                  </label>
                  <input id="d1" autocomplete="nope" name="difficulty" value="1" type="radio" />
                </div>
                <div class="cell small-2">
                  <label for="d2">
                    Difficult
                  </label>
                    <input id="d2" autocomplete="nope" name="difficulty" value="2" type="radio" />
                </div>
                <div class="cell small-2">
                  <label for="d3">
                    Neither
                  </label>
                    <input id="d3" autocomplete="nope" name="difficulty" value="3" type="radio" />
                </div>
                <div class="cell small-2">
                  <label for="d4">
                    Easy
                  </label>
                    <input id="d4" autocomplete="nope" name="difficulty" value="4" type="radio" />
                </div>
                <div class="cell small-2">
                  <label for="d5">
                    Very easy
                  </label>
                  <input id="d5" autocomplete="nope" name="difficulty" value="5" type="radio" />
                </div>
              </div>
            </fieldset>

<!--
             <fieldset class="large-12 cell">
              <div>
                Overall, how useful were the different roles for you in completing the tasks?
                <?php echo $required_asterisk ?>
              </div>
              <div class="grid-x grid-margin-x" style="margin:0.5em 0">
                <div class="cell small-2">
                  <label>
                    <input autocomplete="nope" name="roles_userfulness" value="1" type="radio" />
                    Very easy
                  </label>
                </div>
                <div class="cell small-2">
                  <label>
                    <input autocomplete="nope" name="roles_userfulness" value="2" type="radio" />
                    Easy
                  </label>
                </div>
                <div class="cell small-2">
                  <label>
                    <input autocomplete="nope" name="roles_userfulness" value="3" type="radio" />
                    Neither
                  </label>
                </div>
                <div class="cell small-2">
                  <label>
                    <input autocomplete="nope" name="roles_userfulness" value="4" type="radio" />
                    Difficult
                  </label>
                </div>
                <div class="cell small-2">
                  <label>
                    <input autocomplete="nope" name="roles_userfulness" value="5" type="radio" />
                    Very difficult
                  </label>
                </div>
              </div>
            </fieldset>
 -->

            <!-- NOVELTY -->
            <fieldset class="large-12 cell">
              Please rate the overall <strong>novelty</strong> of the uses you came up with, on a scale of 1 (not novel at all) to 7 (very novel).
              <?php echo $required_asterisk ?>
              <div <?php echo @$errors['novelty'] ? '' : 'style="display:none"' ?> id="error-novelty" class="error-text">Please select a value.</div>
              <select autocomplete="nope" id="novelty" name="novelty">
                <option value=""></option>
                <option value="1">1 - Not novel at all</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7 - Very novel</option>
              </select>
            </fieldset>

            <fieldset class="large-12 cell">
              Please rate the overall <strong>usefulness</strong> of the uses you came up with, on a scale of 1 (not useful at all) to 7 (very useful).
              <?php echo $required_asterisk ?>
              <div <?php echo @$errors['usefulness'] ? '' : 'style="display:none"' ?> id="error-usefulness" class="error-text">Please select a value.</div>
              <select autocomplete="nope" id="usefulness_answers" name="usefulness_answers">
                <option value=""></option>
                <option value="1">1 - Not useful at all</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7 - Very useful</option>
              </select>
            </fieldset>

            <?php
              /*
              PERCEIVED USEFULNESS
              (davis1989.pdf, 2015-hamarikoivisto-why_do_people_use_gamification_services.pdf)
              */
              $i = 0;
              psychometrics(++$i, 'usefulness_roles', 'The roles make it easier for me to complete the tasks.', false);
              psychometrics(++$i, 'usefulness_roles', 'The roles made me feel more effective when completing the tasks.', false);
              psychometrics(++$i, 'usefulness_roles', 'The roles helped me to accomplish the tasks more quickly.', false);
              psychometrics(++$i, 'usefulness_roles', 'The roles helped me to accomplish more with regards to the tasks.', false);
              psychometrics(++$i, 'usefulness_roles', 'I find the roles useful in accomplishing the task.', false);

              /*
              ATTITUDE towards roles
              Ajzen (1991)
              */
              $i = 0;
              psychometrics(++$i, 'attitude', 'All things considered, I find using roles to be a wise thing to do.', false);
              psychometrics(++$i, 'attitude', 'All things considered, I find using roles to be a good idea.', false);
              psychometrics(++$i, 'attitude', 'All things considered, I find using roles to be a positive thing.', false);
              psychometrics(++$i, 'attitude', 'All things considered, I find using roles to be favorable.', false);
            ?>


            <?php
              /*
              PERCEIVED ENJOYMENT (Venkatesh, 2000) and van der Heijden (2004)
              (seven-point semantic differentials; Cheung et al. 2000; Igbaria et al. 1995).
              */
              // $i = 0;
              // psychometrics(++$i, 'enjoyment', 'The tasks were enjoyable.', false);
              // psychometrics(++$i, 'enjoyment', 'The tasks were pleasant.', false);
              // psychometrics(++$i, 'enjoyment', 'The tasks were exciting.', false);
              // psychometrics(++$i, 'enjoyment', 'The tasks were interesting.', 'Strong disagreement', 'Neutral', 'Strong agreement', false);
              // psychometrics(++$i, 'enjoyment', 'The tasks were entertaining.', 'Strong disagreement', 'Neutral', 'Strong agreement', false);
            ?>

            <?php
              /*
              Engagement
              // Engagement is "a superordinate construct composed of the 1) interest, 2) enjoyment, and 3) concentration constructs" ( 1-s2.0-S074756321530056X-main.pdf )
              In Shernoff 2013: "Thus, I have 
conceptualized engagement with learning as the phenomenological combination of 
 concentration ,  enjoyment , and  interest  (Shernoff  2010 ; Shernoff et al.  2003 )."
              */
              $i = 0;
              psychometrics(++$i, 'engagement', 'How hard were you concentrating?', false, "Not at all", "Neutral", "Very hard");
              psychometrics(++$i, 'engagement', 'The task provided content that focused my attention.', false);
              // psychometrics(++$i, 'engagement', 'How much did you enjoy what you were doing?', false);
              psychometrics(++$i, 'engagement', 'The tasks were enjoyable.', false);
              psychometrics(++$i, 'engagement', 'Interacting with the tasks was entertaining.', false);
              psychometrics(++$i, 'engagement', 'Interacting with the tasks was fun.', false);
              psychometrics(++$i, 'engagement', 'The tasks were interesting.', false);
              psychometrics(++$i, 'engagement', 'The tasks were pleasant.', false);
              psychometrics(++$i, 'engagement', 'Did you feel bored with the tasks?', false);
              psychometrics(++$i, 'engagement', 'Did you wish you were doing something else?', false);
            ?>

            <?php
              // flow (operationalized as heightened challenge and skill)
              /*
              CHALLENGE
              1-s2.0-S074756321530056X-main.pdf
              */
              $i = 0;
              psychometrics(++$i, 'challenge', 'The tasks were challenging.', false);
              psychometrics(++$i, 'challenge', 'The tasks stretched my capabilities to the limit.', false);

              /*
              SKILL
              1-s2.0-S074756321530056X-main.pdf
              */
              $i = 0;
              psychometrics(++$i, 'skill', 'I was not very good at the tasks.', false);
              psychometrics(++$i, 'skill', 'I was very skilled at the tasks.', false);
            ?>

<!--
             <div class="grid-x grid-padding-x">
              <fieldset class="large-12 cell">
                <legend>Optionally enter your e-mail address</legend>
                <p class="help-text" style="padding-bottom:0;margin-bottom:0;">If we can contact you for a follow-up study, please enter your e-mail address. Otherwise, leave this field blank.</p>
                <input id="email" name="email" type="text" value="" />
              </fieldset>
            </div>
 -->

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
                  Do you have any other feedback for us (e.g. did you have technical difficulties)? (optional)
                </legend>
                <textarea autocomplete="nope" name="feedback" id="feedback" rows="2"></textarea>
              </fieldset>
            </div>

            <input id="submitbtn" type="submit" class="button" value="Next" />

      </form>

    </div>
  </div>
</div>


<script src="js/jquery.matchHeight-min.js"></script>
<script type="text/javascript">
  $(function() {

    $('.psychometrics label').matchHeight({
      byRow: true,
      property: 'height'
    });

    $("input,select").on( "change", function() {
        $(this).removeClass("input-error");
        $(this).parent().removeClass("input-error");
        $(this).parent().find('div.error-text').hide();
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
        // 'enjoyment[1]', 'enjoyment[2]', 'enjoyment[3]', 'enjoyment[4]', 'enjoyment[5]',
        'engagement[1]', 'engagement[2]', 'engagement[3]', 'engagement[4]', 'engagement[5]', 'engagement[6]', 'engagement[7]', 'engagement[8]', 'engagement[9]',
        'usefulness_roles[1]','usefulness_roles[2]','usefulness_roles[3]','usefulness_roles[4]','usefulness_roles[5]',
        'attitude[1]','attitude[2]','attitude[3]','attitude[4]',
        'challenge[1]', 'challenge[2]',
        'skill[1]', 'skill[2]',
        'difficulty',
        'cheat', 'past'
      ], function (index, field) {
        if (!$("input[name='"+field+"']:checked").val()) {
          console.log(field + " missing");
          errors.push(field);
          $("input[name='"+field+"']").parent().parent().addClass('input-error');
        }
      });
      // select lists
      $.each(['novelty', 'usefulness_answers'], function (index, field) {
        if (!$("select[name='"+field+"']").val()) {
          console.log(field + " missing");
          errors.push(field);
          $("select[name='"+field+"']").parent().addClass('input-error');
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
  });
</script>


<?php

  print_footer();

?>
</body>
</html>