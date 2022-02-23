<?php
/**
 * Plugin Name:  Native Gettext
 * Plugin URI:   https://github.com/colinleroy/native-gettext-for-wp
 * Description:  A minimal native gettext implementation.
 * Version:      1.0.0
 * Author:       Colin Leroy-Mira <colin@colino.net>
 * Author URI:   https://www.colino.net/wordpress/
 * License:      GPL-3.0+
 * License URI:  http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Native Gettext
 * @copyright Copyright (c) 2022, Colin Leroy-Mira
 * @license   GPL-3.0+
 * @since     0.1.0
 */

require __DIR__ . '/classes/class-native-load-textdomain.php';

class NativeGettextForWP {

  private $nltd;
  public function __construct() {
    $this->nltd = new NativeLoadTextdomain();
    $this->nltd->init();
  }

  public function init() {
  }
}

if (extension_loaded('gettext')) {
  global $wpng;
  $wpng = new NativeGettextForWP();
}
