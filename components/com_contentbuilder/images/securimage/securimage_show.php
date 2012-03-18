<?php

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage_show.php<br />
 *
 * Copyright (c) 2011, Drew Phillips
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2011 Drew Phillips
 * @author Drew Phillips <drew@drew-phillips.com>
 * @version 3.0 (October 2011)
 * @package Securimage
 *
 */

// Remove the "//" from the following line for debugging problems
// error_reporting(E_ALL); ini_set('display_errors', 1);

ob_start();

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

if (file_exists(dirname(__FILE__) . '/../../../../defines.php')) {
	include_once dirname(__FILE__) . '/../../../../defines.php';
}

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', dirname(__FILE__) . '/../../../../');
	require_once JPATH_BASE.'/includes/defines.php';
}

require_once JPATH_BASE.'/includes/framework.php';
/* To use Joomla's Database Class */
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'factory.php' );

// Instantiate the application.
$app = JFactory::getApplication('site');

ob_end_clean();

include JPATH_SITE . DS . 'components' . DS . 'com_contentbuilder' . DS . 'images' . DS . 'securimage' . DS . 'securimage.php';

$img = new securimage();



// You can customize the image by making changes below, some examples are included - remove the "//" to uncomment

//$img->ttf_file        = './Quiff.ttf';
//$img->captcha_type    = Securimage::SI_CAPTCHA_MATHEMATIC; // show a simple math problem instead of text
//$img->case_sensitive  = true;                              // true to use case sensitve codes - not recommended
//$img->image_height    = 90;                                // width in pixels of the image
//$img->image_width     = $img->image_height * M_E;          // a good formula for image size
//$img->perturbation    = .75;                               // 1.0 = high distortion, higher numbers = more distortion
//$img->image_bg_color  = new Securimage_Color("#0099CC");   // image background color
//$img->text_color      = new Securimage_Color("#EAEAEA");   // captcha text color
//$img->num_lines       = 8;                                 // how many lines to draw over the image
//$img->line_color      = new Securimage_Color("#0000CC");   // color of lines over the image
//$img->image_type      = SI_IMAGE_JPEG;                     // render as a jpeg image
//$img->signature_color = new Securimage_Color(rand(0, 64),
//                                             rand(64, 128),
//                                             rand(128, 255));  // random signature color

// see securimage.php for more options that can be set

//Change some settings
$img->image_width = 250;
$img->image_height = 80;
$img->perturbation = 0.85;
$img->image_bg_color = new Securimage_Color("#f6f6f6");
$img->multi_text_color = array(new Securimage_Color("#3399ff"),
                               new Securimage_Color("#3300cc"),
                               new Securimage_Color("#3333cc"),
                               new Securimage_Color("#6666ff"),
                               new Securimage_Color("#99cccc")
                               );
$img->use_multi_text = true;
$img->text_angle_minimum = -5;
$img->text_angle_maximum = 5;
$img->use_transparent_text = true;
$img->text_transparency_percentage = 30; // 100 = completely transparent
$img->num_lines = 7;
$img->line_color = new Securimage_Color("#eaeaea");
$img->image_signature = '';
$img->signature_color = new Securimage_Color(rand(0, 64), rand(64, 128), rand(128, 255));
$img->use_wordlist = true;
$img->use_sqlite_db = false;

$img->show(JPATH_SITE . DS . 'components' . DS . 'com_contentbuilder' . DS . 'images' . DS . 'securimage' . DS . 'backgrounds' . DS . 'bg3.jpg');


// alternate use: 
// $img->show('/path/to/background_image.jpg');
