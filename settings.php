<?php

// ini_set('display_errors', 1);
// error_reporting(-1);
error_reporting(0);


define('PROLIFIC_COMPLETION_CODE', "9VFF7CPU");
define('ISPRESTUDY', 0);

define('STUDYLENGTH', 15); // in minutes


define('TITLE', 'Online Study');


// study type: between-subject, within-subject
define('STUDY', 'within-subject');

// condition options:
// NOROLE = tasks without roles
// ROLE = tasks with roles
// ROLEIMG = tasks with roles + priming with images
// TODO: ROLECOMPLEMENT = tasks with roles, user is asked to choose complementary roles

// stages
define('NUMSTAGES', 3);

// answers per stage
define('NUM_ANSWERS_REQUIRED', 4);
define('NUM_MAX_ANSWERS', 4);

