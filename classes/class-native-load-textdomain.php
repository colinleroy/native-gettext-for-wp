<?php
/**
 * Class for loading textdomains via gettext
 *
 * based on Björn Ahrens "WP Performance Pack"
 * (Björn Ahrens <bjoern@ahrens.net>, https://wordpress.org/support/plugin/wp-performance-pack/)
 *
 * based on Bernd Holzmuellers "Native GetText-Support for WordPress" revision 02
 * (Bernd Holzmueller <bernd@tiggerswelt.net>, http://oss.tiggerswelt.net/wordpress/3.3.1/)
 *
 * @author Colin Leroy-Mira <colin@colino.net>
 * @author Bernd Holzmueller <bernd@tiggerswelt.net>
 * @author Björn Ahrens <bjoern@ahrens.net>
 * @package WP Native Gettext
 * @since 0.1
 * @license GNU General Public License version 3 or later
 */

require __DIR__ . '/class-native-mo.php';

class NativeLoadTextdomain {

  public function init () {
    add_filter( 'override_load_textdomain', array( $this, 'nltd_load_textdomain_override' ), 0, 3 );
  }

  function nltd_load_textdomain_override( $retval, $domain, $mofile ) {
    global $l10n;
    $result = false;
    $mo = NULL;

    do_action( 'load_textdomain', $domain, $mofile );
    $mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

    if ( !is_readable( $mofile ) ) {
      return false; // return false is important so load_plugin_textdomain/load_theme_textdomain/... can call load_textdomain for different locations
    }

    $mo = new NativeMO ();
    if ( $mo->import_from_file( $mofile ) ) {
      $result = true;
    }

    if ( $mo !== NULL ) {
      if ( isset( $l10n[$domain] ) ) {
        $mo->merge_with( $l10n[$domain] );
      }
      $l10n[$domain] = $mo;
    }

    return $result;
  }
}
