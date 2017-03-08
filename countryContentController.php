<?php
/**
 * Plugin Name: Country Content Controller
 * Description: This plugin will use the API from http://ip-api.com/ and block/show content based off users country
 * Version: 1.0
 * Author: Shawn Kemp
 * Author URI: http://besmartdesigns.com
 * License: GPL2
 */
/*  Copyright 2016  Shawn Kemp
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
**************************************************************************/
// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);
// Define CCC Plugin
define( 'WPCCC_PLUGIN_VERSION', '1.0' );
define( 'WPCCC_PLUGIN__MINIMUM_WP_VERSION', '4.5' );
define( 'WPCCC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPCCC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
// Blocks direct access to plugin
defined( 'ABSPATH' ) or die( "Access Forbidden" );

function CCC_enqueue()
{
    // JS
    wp_register_script('CCC_bootstrap', plugin_dir_url( __FILE__ ) . '/include/js/bootstrap.min.js');
    wp_enqueue_script('CCC_bootstrap');
    wp_register_script('CCC_jquery', plugin_dir_url( __FILE__ ) . '/include/js/jquery.min.js');
    wp_enqueue_script('CCC_jquery');
    // CSS
    wp_register_style('CCC_bootstrap', plugin_dir_url( __FILE__ ) . '/include/css/bootstrap.min.css');
    wp_enqueue_style('CCC_bootstrap');
}

//Build out admin UI
add_action('admin_menu', 'CCC_menu');
//Set menu settings
function CCC_menu(){
        add_menu_page(
            'CCC Admin Page',
            'CCC Admin',
            'manage_options',
            'ccc-admin',
            'ccc_init',
            'dashicons-visibility' );
}

//Create display inside admin section
function ccc_init(){
  CCC_enqueue();
  //Title section
  ?>
  <div class="row">
  <div class="col-sm-12">
  <h1>Country Content Controller Admin</h1>

    <h2>Shortcode Explained:</h2>
    <p>
      On the page you want to hide/show your content based off user location, you will need to insert the following code:
    </p>

      <div class="well">[CCC controller="" divid="" cc=""]</div>
    </div>
    <div class="col-sm-4">
      <h2>Instructions:</h2>
      <div class="panel panel-primary">
        <div class="panel-heading">Controller</div>
        <div class="panel-body">
          <p>
            Controller is the action you would like to do to the targeted div id when a user from the specified coutry comes to your website. There are currently only two actions built into this plugin and they are defined below:
          </p>
          <span class="label label-primary">Hide</span>
          <p>
            If you set the controller to hide, when a user from the specified country comes to your website, the plugin will hide the tageted div id. Meanwhile, anyone outside of this country will have the reverse affect and the div will be set to Show.
          </p>
          <br />
          <span class="label label-primary">Show</span>
          <p>
            If you set the controller to show, when a user from the specified country comes to your website, the plugin will show the tageted div id. Meanwhile, anyone outside of this country will have the reverse affect and the div will be set to Hide.
          </p>
        </div>
        </div>

      <div class="panel panel-primary">
        <div class="panel-heading">Div ID</div>
        <div class="panel-body">
          <p>
            The div id should be the id of the div that you want to take action on when a user from the specified country comes to your website.
          </p>
        </div>
        </div>

      <div class="panel panel-primary">
        <div class="panel-heading">CC</div>
        <div class="panel-body">
          <p>
            CC stands for country code (These are not case sensitive). For a complete list of country codes check here: <a href="http://data.okfn.org/data/core/country-list" target="_blank">2 letter country codes</a>
          </p>
          <h4>Examples:</h4>
        <ol>
          <li>
            United States - US
          </li>
          <li>
            Canada - CA
          </li>
        </ol>
        </div>
        </div>
      </div>
      <div class="col-sm-8">
      <h2>Examples:</h2>
      <div class="panel panel-success">
        <div class="panel-heading">Hide the div <b>contactForm</b> if the user is from the United States and show to everyone else.</div>
        <div class="panel-body">
          [CCC controller="hide" divid="contactForm" cc="US"]
        </div>
      </div>
      <div class="panel panel-success">
        <div class="panel-heading">Show the div <b>freeDownload</b> if the user is from Japan and hide it from everyone else.</div>
        <div class="panel-body">
          [CCC controller="show" divid="freeDownload" cc="JP"]
        </div>
      </div>
    </div>
  </div>
  <?php

  }

// [CCC] Code
function cccWrapper( $creds, $content = null ) {

  //Break Down Attributes
	$CCC = shortcode_atts( array (
			'controller' => '',
      'divid' => '',
			'cc' => ''
		), $creds );

    //try and fetch the users ip
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      }
    else {
        $ip = $_SERVER['REMOTE_ADDR'];
      }

  if (!empty($ip)){
    $query = @unserialize(file_get_contents('http://ip-api.com/php/'.$ip));
    // $query['country']
    if($query && $query['status'] == 'success') {

      //remove case sensitive issues
      $ccBrowser = strtoupper($query['countryCode']);
      $ccInput = strtoupper($CCC['cc']);

      //build controller switch
      $cccSwitch = $CCC['controller'];

      //set our switch case
      switch ($cccSwitch) {
        case "show":
            $displayAction='block';
            $displayActionFalse='none';
            $cccContinue='true';
            break;
        case "hide":
            $displayAction='none';
            $displayActionFalse='block';
            $cccContinue='true';
            break;
        default:
            $displayAction='';
            $cccContinue='false';
      }

      if ($cccContinue == 'true') {
        $cccDivID = $CCC['divid'];
        if ($ccBrowser == $ccInput) {
          ?>
          <style>
          #<?php echo $cccDivID; ?> {
            display: <?php echo $displayAction; ?>;
          }
          </style>
          <?php
        }
          elseif ($ccBrowser !== $ccInput) {
            ?>
            <style>
            #<?php echo $cccDivID; ?> {
              display: <?php echo $displayActionFalse; ?>;
            }
            </style>
            <?php
          }
        }
      }
    }

}
add_shortcode( 'CCC', 'cccWrapper' );
