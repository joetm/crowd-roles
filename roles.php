<?php

require_once("./helpers.php");
require_once("./settings.php");

print_header();


$uid = get_userid();


$ROLES = get_roles();


// ================
// form validation
// ================

$errors = array();


// check if selected roles are unique
$roles_are_unique = true;
if (isset($_POST['roles'])) {
  $tmp = array();
  foreach ($_POST['roles'] as $role) {
    if (!in_array($role, $tmp)) {
      array_push($tmp, $role);
    } else {
      $roles_are_unique = false;
      break;
    }
  }
}



if (isset($_POST['roles']) && $roles_are_unique) {

  // store roles

  $conn = connect();

  $stmt = $conn->prepare("UPDATE `roles`
              SET `role` = :role
              WHERE `sessionid` = :sessionid
              AND `cond` = :condition
              LIMIT 1
          ");

  $stmt->bindParam(':sessionid', $uid, PDO::PARAM_STR);

  // insert first choice for ROLE condition
  $condition = 'ROLE';
  $role1 = $_POST['roles'][1];
  $stmt->bindParam(':role', $role1, PDO::PARAM_INT);
  $stmt->bindParam(':condition', $condition, PDO::PARAM_STR);
  $stmt->execute();

  // todo: error handling
  // if ($stmt->errorCode()) {
  //   print_r($stmt->errorInfo());
  //   die("DB error [".$stmt->errorCode()."]: " . $stmt->errorInfo()[2]);
  // }

  // insert second choice for ROLEIMG condition
  $condition = 'ROLEIMG';
  $role2 = $_POST['roles'][2];
  $stmt->bindParam(':role', $role2, PDO::PARAM_INT);
  $stmt->bindParam(':condition', $condition, PDO::PARAM_STR);
  $stmt->execute();

  // todo: error handling
  // if ($stmt->errorCode()) {
  //   print_r($stmt->errorInfo());
  //   die("DB error [".$stmt->errorCode()."]: " . $stmt->errorInfo()[2]);
  // }

  $stmt = null;
  $conn = null;

  //redirect to next stage
  redirect('answers.php');

}


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

  <?php echo progress(20); ?>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <div class="callout">
        <h2>Pick Two Roles</h2>
        <p>
          Please pick two different roles that you are familiar with from the lists below.
        </p>
      </div>
    </div>
  </div>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <div class="callout">
        <form autocomplete="nope" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">

          <input type="hidden" name="uid" value="<?php echo htmlspecialchars($uid, ENT_QUOTES) ?>" />

          <p style="<?php echo $roles_are_unique ? 'display:none;' : '' ?>" class="error-text" id="difference-error">
            The roles must be different from each other.
          </p>
          <p style="display:none;" class="error-text" id="empty-error">
            You must select two different roles.
          </p>

          <?php for ($i = 1; $i <= 2; $i++) {
            echo '
            <label>Role ' . $i . '
              <select autocomplete="nope" name="roles[' . $i . ']" id="role' . $i . '"></select>
            </label>
            ';
          }
          ?>

          <input id="submitbtn" type="submit" class="button" value="Next" />

        </form>
      </div>
    </div>
  </div>

</div>

<script type="text/javascript">
$(function () {

  <?php
    $roles_array_str = '';
    foreach ($ROLES as $role) {
      $roles_array_str .= $role['name'] . '","';
    }
  ?>

  var permroles = ["<?php echo $roles_array_str ?>"];

  // initially populate the select boxes
  $('#role1').append($('<option>', { value: '', text: '' }));
  $('#role2').append($('<option>', { value: '', text: '' }));
  // $('#role3').append($('<option>', { value: '', text: '' }));
  $.each(permroles, function (i, role) {
    $('#role1').append($('<option>', { value: i + 1, text: role }));
    $('#role2').append($('<option>', { value: i + 1, text: role }));
    // $('#role3').append($('<option>', { value: i + 1, text: role }));
  });

  // form validation
  $('#submitbtn').on('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    if(
      $('#role1')[0].value == "" ||
      $('#role2')[0].value == ""
      // || $('#role3')[0].value == ""
    ) {
      $('#empty-error').show();
      return false;
    } else {
      $('#empty-error').hide();
    }
    if(
      $('#role1')[0].value == $('#role2')[0].value
      // || $('#role1')[0].value == $('#role3')[0].value
      // || $('#role2')[0].value == $('#role3')[0].value
    ) {
      $('#difference-error').show();
      return false;
    } else {
      $('#difference-error').hide();

      $("form").submit();

    }
  });

});
</script>


<?php print_footer() ?>

</body>
</html>