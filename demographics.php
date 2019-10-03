<?php

require_once("./helpers.php");
require_once("./settings.php");

print_header();

$uid = get_userid();

$errors = array();

$age = '';
if (isset($_POST['age']) && $_POST['age']) {
  $age = intval($_POST['age']);
}

// ================
// form validation
// ================
if (isset($_POST['uid'])) {

  $conn = connect();

  $stmt = $conn->prepare("UPDATE `users`
    SET `age` = :age,
        `gender` = :gender,
        `education` = :education,
        `nationality` = :nationality
    WHERE  `sessionid` = :sessionid
    LIMIT 1");

  $stmt->bindParam(':sessionid', $uid, PDO::PARAM_STR);
  $stmt->bindParam(':age',  $_POST['age'], PDO::PARAM_INT);
  $stmt->bindParam(':gender',  $_POST['gender'], PDO::PARAM_STR);
  $stmt->bindParam(':education',  $_POST['education'], PDO::PARAM_INT);
  $stmt->bindParam(':nationality',  $_POST['nationality'], PDO::PARAM_STR);
  $stmt->execute();

  // todo: error handling
  // if ($stmt->errorCode()) {
  //   print_r($stmt->errorInfo());
  //   die("DB error [".$stmt->errorCode()."]: " . $stmt->errorInfo()[2]);
  // }

  $stmt = null;
  $conn = null;

  //redirect to next stage
	redirect('roles.php');

}

?>

<div class="grid-container">

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <h1><?php print_title() ?></h1>
    </div>
  </div>

  <?php echo progress(5); ?>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">

        <form autocomplete="nope" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid, ENT_QUOTES); ?>" />

          <h3>About you</h3>

          <div class="callout">
            <label>What is your age?
              <?php echo $required_HTML ?>
              <div <?php echo isset($errors['age']) ? '' : 'style="display:none"' ?> id="error-age" class="error-text">Please enter your age as a number.</div>
              <input autocomplete="nope" id="age" name="age" type="number" value="<?php echo htmlspecialchars($age, ENT_QUOTES) ?>" />
            </label>
          </div>

          <div class="callout">
            <label>What is your gender?
              <?php echo $required_HTML ?>
              <div <?php echo isset($errors['gender']) ? '' : 'style="display:none"' ?> id="error-gender" class="error-text">Please select your gender.</div>
              <select autocomplete="nope" id="gender" name="gender">
                <option value=""></option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="trans">Neither male nor female</option>
                <option value="na">Prefer not to say</option>
              </select>
            </label>
          </div>

          <div class="callout">
            <label>What is the highest level of education you have completed?
              <?php echo $required_HTML ?>
              <div <?php echo isset($errors['education']) ? '' : 'style="display:none"' ?> id="error-education" class="error-text">Please select your education.</div>
              <select autocomplete="nope" id="education" name="education">
                <option value=""></option>
                <option value="1">No formal qualifications</option>
                <option value="2">Secondary school/GCSE</option>
                <option value="3">College/A levels</option>
                <option value="4">Undergraduate degree (BA/BSc/other)</option>
                <option value="5">Graduate degree (MA/MSc/MPhil/other)</option>
                <option value="6">Doctorate degree (PhD/MD/other)</option>
              </select>
            </label>
          </div>

          <div class="callout">
            <label>What is your nationality?
              <?php echo $required_HTML ?>
              <div <?php echo isset($errors['nationality']) ? '' : 'style="display:none"' ?> id="error-nationality" class="error-text">Please select your nationality.</div>
              <select autocomplete="nope" id="nationality" name="nationality">
                <option value=""></option>
                <option value="united-kingdom">United Kingdom</option>
                <option value="united-states">United States</option>
                <option value="canada">Canada</option>
                <option value="poland">Poland</option>
                <option value="germany">Germany</option>
                <option value="italy">Italy</option>
                <option value="australia">Australia</option>
                <option value="mexico">Mexico</option>
                <option value="spain">Spain</option>
                <option value="india">India</option>
                <option value="other">Other</option>
              </select>
            </label>
          </div>

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

      if (!$("#age").val()) {
        $("#error-age").show();
        $("#age").addClass("input-error")
        errors.push("age")
      } else {
        $("#error-age").hide();
        $("#age").removeClass("input-error")
      }

      if ($("#gender").val() == "") {
        $("#error-gender").show();
        $("#gender").addClass("input-error")
        errors.push("gender")
      } else {
        $("#error-gender").hide();
        $("#gender").removeClass("input-error")
      }

      if ($("#education").val() == "") {
        $("#error-education").show();
        $("#education").addClass("input-error")
        errors.push("education")
      } else {
        $("#error-education").hide();
        $("#education").removeClass("input-error")
      }

      if ($("#nationality").val() == "") {
        $("#error-nationality").show();
        $("#nationality").addClass("input-error")
        errors.push("nationality")
      } else {
        $("#error-nationality").hide();
        $("#nationality").removeClass("input-error")
      }


      // do not submit form when there are errors
      if (errors.length > 0) {
        return false;
      }

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
