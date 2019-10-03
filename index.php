<?php

require_once("./helpers.php");
require_once("./settings.php");

print_header();

$uid = get_userid();

$errors = array();

$PROLIFIC_PID = '';
// prefill prolific id
if (
  isset($_GET['PROLIFIC_PID']) &&
  $_GET['PROLIFIC_PID']
) {
  $PROLIFIC_PID = htmlspecialchars($_GET['PROLIFIC_PID'], ENT_QUOTES);
}

$STUDY_ID = '';
if (
  isset($_GET['STUDY_ID']) &&
  $_GET['STUDY_ID']
) {
  $STUDY_ID = htmlspecialchars($_GET['STUDY_ID'], ENT_QUOTES);
}

$SESSION_ID = '';
if (
  isset($_GET['SESSION_ID']) &&
  $_GET['SESSION_ID']
) {
  $SESSION_ID = htmlspecialchars($_GET['SESSION_ID'], ENT_QUOTES);
}


// ================
// form validation
// ================
if (isset($_POST['uid'])) {

  $conn = connect();

  $time = time();

  // rotating designs
  // get the last design from users table
  // and switch to next design
  // $design = false;
  // $stmt = $conn->prepare("SELECT
  //       `design`
  //     FROM `users`
  //     ORDER BY `dateline` DESC
  //     LIMIT 1
  //     ");
  // $stmt->execute();
  // $res = $stmt->fetch();
  // // we have existing users
  // if ($res) {
  //   $design = $res['design'];
  //   if ($design == 12) {
  //     $design = 1;
  //   } else {
  //     $design++;
  //   }
  // } else {
  //   // for the first user, start with first design
  //   $design = 1;
  // }


  // manually filling up designs
  $design = 12;


  // fetch stages for this design
  $stages = [];
  $stmt = $conn->prepare("SELECT
        `task`, `stage1`, `stage2`, `stage3`
      FROM `designs`
      WHERE `ID` = :design
      LIMIT 1
      ");
  $stmt->execute(array('design' => $design));
  $res = $stmt->fetch();
  $stages = [ $res['stage1'], $res['stage2'], $res['stage3']];
  $task = $res['task'];


  // $prolific = isset($_POST['prolific']) ? $_POST['prolific'] : "";

  $stmt = $conn->prepare("INSERT INTO `users` (
        `sessionid`,
        `prestudy`,
        `design`,
        `prolific_id`,
        `study_id`,
        `session_id`,
        `dateline`
      ) VALUES (
        :sessionid,
        :prestudy,
        :design,
        :prolific_id,
        :study_id,
        :session_id,
        :dateline
      )
      ON DUPLICATE KEY UPDATE
        `prestudy` = :prestudy2,
        `design` = :design2,
        `prolific_id` = :prolific_id2,
        `study_id` = :study_id2,
        `session_id` = :session_id2,
        `dateline` = :dateline2
      ");

  $is_prestudy = ISPRESTUDY;
  $stmt->bindParam(':sessionid', $uid, PDO::PARAM_STR);
  $stmt->bindParam(':prestudy',  $is_prestudy, PDO::PARAM_INT);
  $stmt->bindParam(':prestudy2', $is_prestudy, PDO::PARAM_INT);
  $stmt->bindParam(':design',  $design, PDO::PARAM_INT);
  $stmt->bindParam(':design2', $design, PDO::PARAM_INT);
  $stmt->bindParam(':prolific_id',  $_POST['prolific'], PDO::PARAM_STR);
  $stmt->bindParam(':prolific_id2', $_POST['prolific'], PDO::PARAM_STR);
  $stmt->bindParam(':study_id',  $STUDY_ID, PDO::PARAM_STR);
  $stmt->bindParam(':study_id2', $STUDY_ID, PDO::PARAM_STR);
  $stmt->bindParam(':session_id',  $SESSION_ID, PDO::PARAM_STR);
  $stmt->bindParam(':session_id2', $SESSION_ID, PDO::PARAM_STR);
  $stmt->bindParam(':dateline',  $time, PDO::PARAM_INT);
  $stmt->bindParam(':dateline2', $time, PDO::PARAM_INT);
  $stmt->execute();

  // insert three blank roles to iterate over in any case (even for norole condition)
  $stmt = $conn->prepare("INSERT INTO `roles` (
        `sessionid`,
        `stagenum`,
        `design`,
        `task`,
        `cond`,
        `dateline_select`
      ) VALUES (
        :sessionid,
        :stagenum,
        :design,
        :task,
        :condition,
        :dateline
      )
      ON DUPLICATE KEY UPDATE
        `dateline_select` = :dateline2
      ");

  $stmt->bindParam(':sessionid', $uid, PDO::PARAM_STR);
  $stmt->bindParam(':design', $design, PDO::PARAM_INT);
  $stmt->bindParam(':task', $task, PDO::PARAM_STR);
  $stmt->bindParam(':dateline',  $time, PDO::PARAM_INT);
  $stmt->bindParam(':dateline2', $time, PDO::PARAM_INT);

  for ($num = 1; $num <= NUMSTAGES; $num++) {
    // echo "<br />binding: #" . $num . " - " . $r;
    $stmt->bindParam(':stagenum',  $num, PDO::PARAM_INT);
    $stmt->bindParam(':condition', $stages[$num - 1], PDO::PARAM_STR);
    $stmt->execute();
  }

  // todo: error handling
  // if ($stmt->errorCode()) {
  //   print_r($stmt->errorInfo());
  //   die("DB error [".$stmt->errorCode()."]: " . $stmt->errorInfo()[2]);
  // }


  $stmt = null;
  $conn = null;

  //redirect to next stage
	redirect('demographics.php');

}


?>

<div class="grid-container">

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <h1><?php print_title() ?></h1>
    </div>
  </div>

      <div class="callout">

    <div style="float:right">
      <img style="width:90%;max-width:330px;height:auto;" src="https://lh6.googleusercontent.com/0kLzDYD09WFb-wsYyxxeIWPvZllWlZ68uzYTTuo04qBxaAKoxBHeI_90lRFyocL0DedhXDePDhG2E15wk2zW90dhD-e8lQ9pEOEG3OrGt_jZZEQjD91cUAqeSP_2CzwqDWutGFJDPO3Cdho" alt="" />
    </div>

<!--
        <h2>Welcome to our study!</h2>
-->
        <p>
    Thanks for showing interest in this study.<br />
		On the following pages, you will be asked to come up with unusual and unique uses for a common object, to answer some multiple choice questions, and to provide demographic information.

<p>
  Please read the following before starting:<br />

    We estimate this task will take you around <?php echo STUDYLENGTH ?> minutes to complete.<br />

  Please complete this survey in one go.
  In other words, please only participate in this survey, if you have <?php echo STUDYLENGTH ?> minutes that you can dedicate to it.
  <br />Thank you very much.
</p>

<p>
  <b>Consent form</b>
</p>
 
<p>
  <!--
  I consent to participate in this session. 
  , which will involve <?php echo NUMSTAGES ?> tasks, multiple choice and a few explanations.<br />-->  

  I understand that all data will be kept confidential by the researcher. My personal information will not be stored with the data. I am free to withdraw at any time without giving a reason.<br />

  I consent to the publication of study results as long as the information is anonymous so that no identification of participants can be made.<br />
 
  I have read and understand the explanations and I voluntarily consent to participate in this study.
</p>

		Should you have any questions, feel free to contact us at: <a href="mailto:jonas.oppenlaender@oulu.fi">jonas.oppenlaender@oulu.fi</a>
        </p>
      </div>


  <?php echo progress(0); ?>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">

        <form autocomplete="nope" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid, ENT_QUOTES); ?>" />

          <?php if (!$PROLIFIC_PID) { ?>
            <div class="callout">
              <label>
                Enter your Prolific ID
                <?php echo $required_HTML ?>
                <div <?php echo @$errors['prolific'] ? '' : 'style="display:none"' ?> id="error-prolific" class="error-text">Please enter your prolific ID.</div>
                <input autocomplete="nope" type="text"
                  id="prolific" name="prolific"
                  value="<?php echo $PROLIFIC_PID ?>"
                />
              </label>
            </div>
          <?php } else {
              echo '<input type="hidden" name="prolific" value="'. $PROLIFIC_PID . '" />';
          } ?>

          <input id="submitbtn" type="submit" class="button" value="Next" />

        </form>

    </div>

  </div>


</div>



<script type="text/javascript">
  $(function() {
    // remove error highlighting on change
    $("input,select").on("change", function () {
        $(this).removeClass("input-error");
        $(this).parent().find('div.error-text').hide();
    });

    $("#submitbtn").on( "click", function(e) {
      e.preventDefault();
      e.stopPropagation();

      var errors = [];

      // validate the form

      <?php if (!$PROLIFIC_PID) { ?>
        if (!$("#prolific").val()) {
          $("#error-prolific").show();
          $("#prolific").addClass("input-error")
          errors.push("prolific")
        } else {
          $("#error-prolific").hide();
          $("#prolific").removeClass("input-error")
        }
      <?php } ?>

console.log(errors);

      // do not submit form when there are errors
      if (errors.length > 0) {
        return false;
      }

      $("form").submit();
      return true;

    });
  });
</script>


<?php print_footer() ?>

</body>
</html>