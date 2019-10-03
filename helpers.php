<?php

session_start();

require_once('vendor/autoload.php');

require_once(dirname(__FILE__)."/credentials.php");
require_once(dirname(__FILE__)."/settings.php");

use EndyJasmi\Cuid;

function get_roles() {
	return array(
	  1  => array( 'name' => 'Architect', 'img' => 'img/architect.jpg'),
	  2  => array( 'name' => 'Baker', 'img' => 'img/baker.jpg'),
	  3  => array( 'name' => 'Bodybuilder', 'img' => 'img/bodybuilder.jpg'),
	  4  => array( 'name' => 'Carpenter', 'img' => 'img/carpenter.jpg'),
	  5  => array( 'name' => 'Cashier', 'img' => 'img/cashier.jpg'),
	  6  => array( 'name' => 'Construction Worker', 'img' => 'img/construction-worker.jpg'),
	  7  => array( 'name' => 'Designer', 'img' => 'img/designer.jpg'),
	  8  => array( 'name' => 'Expert on Japanese Aesthetics', 'img' => 'img/japanese-aesthetics.jpg'),
	  9  => array( 'name' => 'Expert on Topology', 'img' => 'img/topologist.jpg'),
	  10 => array( 'name' => 'Firefighter', 'img' => 'img/firefighter.jpg'),
	  11 => array( 'name' => 'Forklift Operator', 'img' => 'img/forklift-operator.jpg'),
	  12 => array( 'name' => 'Landscaper', 'img' => 'img/landscaper.jpg'),
	  13 => array( 'name' => 'Magician', 'img' => 'img/magician.jpg'),
	  14 => array( 'name' => 'Mail Sorter', 'img' => 'img/mail-sorter.jpg'),
	  15 => array( 'name' => 'Mathematician specializing in Geometry', 'img' => 'img/mathematician.jpg'),
	  16 => array( 'name' => 'Meteorologist', 'img' => 'img/meteorologist.jpg'),
	  17 => array( 'name' => 'Parking Lot Attendant', 'img' => 'img/parkinglot-attendant.jpg'),
	  18 => array( 'name' => 'Physicist', 'img' => 'img/physicist.jpg'),
	  19 => array( 'name' => 'Pianist', 'img' => 'img/pianist.jpg'),
	  20 => array( 'name' => 'Sculptor', 'img' => 'img/sculptor.jpg'),
	  21 => array( 'name' => 'User Interface Designer', 'img' => 'img/user-interface-designer.jpg'),
	  22 => array( 'name' => 'Warehouse Dock Loader', 'img' => 'img/warehouse-dock-loader.jpg'),
	);
}


function print_title() {
	echo TITLE;
}
function get_title() {
	return TITLE;
}

function get_userid() {
  // take uid from form to avoid session timeouts
	if (isset($_POST['uid'])) {
	  $uid = $_POST['uid'];
  } elseif (isset($_SESSION["uid"]) && $_SESSION["uid"]) {
    // take uid from session
    $uid = $_SESSION["uid"];
  } else {
	  // generate initial uid
    $uid = Cuid::cuid();
    $_SESSION["uid"] = $uid;
  }
	return $uid;
}

function progress($val) {
  echo '<div class="progress" role="progressbar" tabindex="0"
        aria-valuenow="' . $val . '"
        aria-valuemin="0" aria-valuetext="50 percent" aria-valuemax="100">
        <div class="progress-meter" style="width: ' . $val . '%"></div>
      </div>
  ';
}

$required_HTML = '<span class="required right">* required</span>';
$required_asterisk = '<span class="required">*</span>';

function print_footer() {
	// echo '<div style="text-align:center;margin:1rem;"><small>' . htmlspecialchars(get_userid()) . '</small></div>';
  echo '<div style="text-align:center;margin:1rem;"><small>&copy; '.date('Y').' Center for Ubiquitous Computing, Oulu</small></div>';
}

function print_header() {
	$title = get_title();
	echo <<<OUT
<!DOCTYPE html>
<html class="no-js" lang="en" dir="ltr">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
<link rel="stylesheet" href="foundation-6.5.1-complete/css/foundation.css">
<style type="text/css">
  .error-text{color: #FF0000;font-size:0.875rem;}
  .input-error{background-color: #FFDDDD;}
  .required{color: #FF0000;}
  .right{float:right;}
  .grid-container{margin-bottom:3rem;margin-top:1rem;}
  fieldset{margin-top:0.5em}
  label{cursor:pointer;}
  select{cursor:pointer;}
  .psychometrics label{display:block;vertical-align:bottom;margin-bottom:0;vertical-align:bottom;text-align:center;}
  .psychometrics input{cursor:pointer;}
  .psychometrics .cell .small-1{width:7%;}
</style>
</head>
<body>
<script src="foundation-6.5.1-complete/js/vendor/jquery.js"></script>
<script src="foundation-6.5.1-complete/js/vendor/what-input.js"></script>
<script src="foundation-6.5.1-complete/js/vendor/foundation.js"></script>
OUT;
}


function connect() {
	// Create connection
	$dbh = new PDO('mysql:host='.SERVERNAME.';dbname=' . DATABASE, USERNAME, PASSWORD);
	return $dbh;
}

function redirect($url) {
  if (headers_sent()) {
  	echo "<p>go to next stage: <a href='".$url."'>".$url."</a></p>";
  } else {
    header('Location: ' . $url);
  }
}


function psychometrics($num, $attribute, $statement, $error = false, 
  $scaleMin = 'Strongly Disagree', $scaleMid='Neutral', $scaleMax = 'Strongly Agree'
) {
	$error_style = $error ? '' : 'style="display:none"';
	global $required_asterisk;
	echo <<<OUT
            <div {$error_style} id="error-{$attribute}" class="error-text">Please answer this question.</div>
            <div class="psychometrics grid-x grid-padding-x">
              <fieldset class="large-12 cell">
                <legend>
                  <strong><em>{$statement}</em></strong>
                  {$required_asterisk}
                </legend>
                <div class="grid-x grid-margin-x">
                  <div class="cell small-1" style="text-align:center;">
                    <label for="${attribute}{$num}1" class="middle" style="cursor:pointer;">
                      1 - {$scaleMin}
                    </label>
                    <input id="${attribute}{$num}1" autocomplete="nope" name="${attribute}[{$num}]" value="1" type="radio" />
                  </div>
                  <div class="cell small-1" style="text-align:center;">
                    <label for="${attribute}{$num}2" class="middle" style="cursor:pointer;">
                      2
                    </label>
                    <input id="${attribute}{$num}2" autocomplete="nope" name="${attribute}[{$num}]" value="2" type="radio" />
                  </div>
                  <div class="cell small-1" style="text-align:center;">
                    <label for="${attribute}{$num}3" class="middle" style="cursor:pointer;">
                      3
                    </label>
                    <input id="${attribute}{$num}3" autocomplete="nope" name="${attribute}[{$num}]" value="3" type="radio" />
                  </div>
                  <div class="cell small-1" style="text-align:center;">
                    <label for="${attribute}{$num}4" class="middle" style="cursor:pointer;">
                      4 - {$scaleMid}
                    </label>
                    <input id="${attribute}{$num}4" autocomplete="nope" name="${attribute}[{$num}]" value="4" type="radio" />
                  </div>
                  <div class="cell small-1" style="text-align:center;">
                    <label for="${attribute}{$num}5" class="middle" style="cursor:pointer;">
                      5
                    </label>
                    <input id="${attribute}{$num}5" autocomplete="nope" name="${attribute}[{$num}]" value="5" type="radio" />
                  </div>
                  <div class="cell small-1" style="text-align:center;">
                    <label for="${attribute}{$num}6" class="middle" style="cursor:pointer;">
                      6
                    </label>
                    <input id="${attribute}{$num}6" autocomplete="nope" name="${attribute}[{$num}]" value="6" type="radio" />
                  </div>
                  <div class="cell small-1" style="text-align:center;">
                    <label for="${attribute}{$num}7" class="middle" style="cursor:pointer;">
                      7 - {$scaleMax}
                    </label>
                    <input id="${attribute}{$num}7" autocomplete="nope" name="${attribute}[{$num}]" value="7" type="radio" />
                  </div>
                </div>
              </fieldset>
            </div>
OUT;
}

function csi_question($id, $item, $itemtitle, $description) {
  global $required_asterisk;
  echo <<<OUT
        <div class="psychometrics grid-x grid-padding-x">
          <fieldset class="large-12 cell">

            <div>
              <b>{$itemtitle}</b><br />
              {$description}
              {$required_asterisk}
            </div>

            <div class="grid-x grid-margin-x">
              <div class="cell small-2" style="text-align:right;line-height:45px">
                Highly Disagree
              </div>
              <div class="cell small-8">
                <div id="slider{$item}{$id}" class="slider"
                  data-slider
                  data-start="1"
                  data-initial-start="5.5"
                  data-end="10"
                  data-step="1"
                  style="cursor:pointer"
                >
                  <span class="slider-handle"  data-slider-handle role="slider" tabindex="1"></span>
                  <span class="slider-fill" data-slider-fill></span>
                  <input class="sliderinput" name="{$item}" value="5.5" type="hidden" />
                </div>
              </div>
              <div class="cell small-2" style="text-align:left;line-height:45px;">
                Highly Agree
              </div>
            </div>

        </fieldset>
      </div>
      <script type="text/javascript">
        var slider{$id} = new Foundation.Slider($("#slider{$item}{$id}"), {});
      </script>
OUT;
}

function csi_pair($id, $left, $right) {
  echo  <<<OUT
              <!-- <fieldset class="large-12 cell"> -->
                <div class="grid-x grid-margin-x" style="margin:0.5em 0">
                  <div class="cell small-4" style="text-align:right">
                    <label style="display:inline-block" for="csipair{$id}l">{$left}</label>
                    <input style="cursor:pointer" id="csipair{$id}l" autocomplete="nope" name="csipair[{$id}]" value="{$left}" type="radio" />
                  </div>
                  <div class="cell small-6" style="text-align:left">
                    <input style="cursor:pointer" id="csipair{$id}r" autocomplete="nope" name="csipair[{$id}]" value="{$right}" type="radio" />
                    <label style="display:inline-block" for="csipair{$id}r">{$right}</label>
                  </div>
                </div>
              <!-- </fieldset> -->
OUT;
}
