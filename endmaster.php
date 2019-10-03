<?php

require_once("./helpers.php");
require_once("./settings.php");

print_header();

$completion_url = "https://app.prolific.ac/submissions/complete?cc=" . "LDJTYD22";

?>

<div class="grid-container">

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <h1><?php print_title() ?></h1>
    </div>
  </div>

  <?php echo progress(100); ?>

  <div class="grid-x grid-padding-x">
    <div class="large-12 cell">
      <div class="callout">
        <h2>Thank you for completing our study!</h2>
        <p>
          This survey was part of a research study on supporting creativity,
          conducted as a data collection survey by scientists at the University of Oulu, Finland.<br />
<!--           Your anonymity will be preserved, and your response data will contribute to the advancement of scientific research. -->
        </p>
        <p>
          The completion code for Prolific is
          <strong><?php echo "LDJTYD22" ?></strong>.
          <br />
          Visit this url to get credit for the study on Prolific:<br />
          <a href="<?php echo $completion_url ?>"><?php echo $completion_url ?></a>.
        </p>
      </div>
    </div>

  </div>

</div>


<?php print_footer() ?>

</body>
</html>
