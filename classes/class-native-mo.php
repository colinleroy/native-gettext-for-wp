<?PHP
/**
* Class for using native gettext for translations
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

class NativeGettextMO extends Gettext_Translations {

  private static $header_sent = false;

  const CONTEXT_SEP = "\x04";

  private $wp_domain = null;
  public $mo_file = null;

  private $domain = null;
  private $codepage = 'UTF8';

  // Merged domains
  private $pOthers = array ();
  private $sOthers = array ();

  public $entries = array ();

  public function __construct($wp_domain) {
    $this->wp_domain = $wp_domain;
  }

  // Some Dummy-Function just to be API-compatible
  function add_entry ( $entry ) { return false; }
  function add_entry_or_merge ( $entry ) { return false; }
  function set_header ( $header, $value ) { return false; }
  function set_headers ( $headers ) { return false; }
  function get_header ( $header ) { return false; }
  function translate_entry ( &$entry ) { return false; }
  function get_filename () { return $mo_file; }

  static $locale_not_supported_notice_displayed = false;
  static $multiple_loadtextdomain_displayed = false;

  /**
  * Given the number of items, returns the 0-based index of the plural form to use
  *
  * Here, in the base Translations class, the common logic for English is implemented:
  *      0 if there is one element, 1 otherwise
  *
  * This function should be overrided by the sub-classes. For example MO/PO can derive the logic
  * from their headers.
  *
  * @param integer $count number of items
  **/
  function select_plural_form ($count) {
    return (1 == $count? 0 : 1);
  }

  function get_plural_forms_count () {
    return 2;
  }

  /**
  * Merge this translation with another one, the other one takes precedence
  *
  * @param object $other
  *
  * @access public
  * @return void
  **/
  function merge_with (&$other) {
    if ($other instanceof NativeGettextMO) {
      $this->pOthers [] = $other;
    } else if ( !( $other instanceof NOOP_Translations ) ) {
      if ( ! self::$multiple_loadtextdomain_displayed && is_admin() ) {
        add_action( 'admin_notices', function() use ( $other ) {
          multiple_textdomain_notice ( $other );
        } );
        self::$multiple_loadtextdomain_displayed = true;
      }
      foreach ( $other->entries as $entry ) {
        $this->entries[ $entry->key() ] = $entry;
      }
    }
  }

  /**
  * Merge this translation with another one, this one takes precedence
  *
  * @param object $other
  *
  * @access public
  * @return void
  **/
  function merge_originals_with (&$other) {
    if ( !( $other instanceof NOOP_Translations ) ) {
      $this->sOthers [] = $Other;
    }
  }

  /**
  * Try to translate a given string
  *
  * @param string $singular
  * @param string $context (optional)
  *
  * @access public
  * @return string
  **/
  function translate ($singular, $context = null) {
    // Check for an empty string
    if (strlen ($singular) == 0) {
      return $singular;
    }

    // Check other domains that take precedence
    foreach ($this->pOthers as $o) {
      if (($t = $o->translate ($singular, $context)) != $singular) {
        return $t;
      }
    }

    // Make sure we have a domain assigned
    if ($this->domain === null) {
      return $singular;
    }

    if ($context === null) {
      // Translate without a context
      if (($t = dgettext ($this->domain, $singular)) != $singular) {
        if (!self::$header_sent) {
          $this->add_header("1");
          self::$header_sent = true;
        }
        return $t;
      }
    } else {
      // Translate with a given context
      $T = $context . self::CONTEXT_SEP . $singular;
      $t = dgettext ($this->domain, $T);

      if ($T != $t) {
        if (!self::$header_sent) {
          $this->add_header("1");
          self::$header_sent = true;
        }
        return $t;
      }
    }

    // Check for other domains
    foreach ($this->sOthers as $o) {
      if (($t = $o->translate ($singular, $context)) != $singular) {
        return $t;
      }
    }

    return $singular;
  }

  /**
  * Try to translate a plural string
  *
  * @param string $singular Singular version
  * @param string $plural Plural version
  * @param int $count Number of "items"
  * @param string $context (optional)
  *
  * @access public
  * @return string
  **/
  function translate_plural ($singular, $plural, $count, $context = null) {
    // Check for an empty string
    if (strlen ($singular) == 0) {
      return $singular;
    }

    // Get the "default" return-value
    $default = ($count == 1 ? $singular : $plural);

    // Check other domains that take precedence
    foreach ($this->pOthers as $o) {
      if (($t = $o->translate_plural ($singular, $plural, $count, $context)) != $default) {
        return $t;
      }
    }

    // Make sure we have a domain assigned
    if ($this->domain === null) {
      return $default;
    }

    if ($context === null) {
      // Translate without context
      $t = dngettext ($this->domain, $singular, $plural, $count);

      if (($t != $singular) && ($t != $plural)) {
        if (!self::$header_sent) {
          $this->add_header("1");
          self::$header_sent = true;
        }
        return $t;
      }
    } else {
      // Translate using a given context
      $T = $context . self::CONTEXT_SEP . $singular;
      $t = dngettext ($this->domain, $T, $plural, $count);

      if (($T != $t) && ($t != $plural)) {
        if (!self::$header_sent) {
          $this->add_header("1");
          self::$header_sent = true;
        }
        return $t;
      }
    }

    // Check other domains
    foreach ($this->sOthers as $o) {
      if (($t = $o->translate_plural ($singular, $plural, $count, $context)) != $default) {
        return $t;
      }
    }

    return $default;
  }

  static function isPutenvAvailable() {
    static $check_done = false;
    static $available = false;

    if ($check_done) {
      return $available;
    }

    $check_done = true;
    if (ini_get('safe_mode')) {
      $available = false;
      return false;
    }

    $disabledFuncs = ini_get('disable_functions');
    if ($disabledFuncs) {
      $disabledArr = array_map('trim', explode(',', $disabledFuncs));
      $available = !in_array('putenv', $disabledArr);
      return $available;
    }
    $available = true;
    return $available;
  }

  /**
  * Fills up with the entries from MO file $filename
  *
  * @param string $filename MO file to load
  **/
  function import_from_file( $filename ) {
    static $dir_created = false;
    // Make sure that the locale is set correctly in environment
    $locale = get_locale();

    if( !defined( 'LC_MESSAGES' ) ) {
      define( 'LC_MESSAGES', LC_CTYPE );
    }

    if ( self::isPutenvAvailable() ) {
      putenv( 'LC_MESSAGES=' . $locale . '.' . $this->codepage );
      putenv( 'LANGUAGE=' . $locale . '.' . $this->codepage );
    }
    //setlocale (LC_ALL, $locale);
    if ( ! setlocale( LC_MESSAGES, $locale, $locale . '.' . $this->codepage ) ) {
      if ( ! self::$header_sent) {
          $this->add_header($locale." not supported.");
          self::$header_sent = true;
      }
      if ( ! self::$locale_not_supported_notice_displayed && is_admin() ) {
        add_action( 'admin_notices', function() use ( $locale ) {
          locale_not_supported_notice ( $locale );
        } );
        self::$locale_not_supported_notice_displayed = true;
      }
      return false;
    }

    $info = pathinfo( $filename );
    $name = basename( $filename, '.' . $info[ 'extension' ] );
    $stat = stat( $filename );

    if ( !( $domain = $name . '-' . $stat["mtime"] ) ) {
      return false;
    }

    $textdomains_path = WP_CONTENT_DIR . '/native-gettext/localize/';
    // Make sure that the language-directory exists
    $path = $textdomains_path . $locale . '/LC_MESSAGES';

    if ( !$dir_created && !wp_mkdir_p( $path ) ) {
      return false;
    }
    $dir_created = true;

    // Make sure that the MO-File is existant at the destination
    $fn = $path . '/' . $domain . '.mo';

    if ( !is_file( $fn ) ) {
      // Try to symlink, fallback to copy
      if ( ! @symlink( $filename, $fn ) && !@copy( $filename, $fn ) ) {
        return false;
      }
    }

    // Setup the "domain" for gettext
    if ( ! bindtextdomain( $domain, $textdomains_path ) ) {
      return false;
    }

    bind_textdomain_codeset( $domain, $this->codepage );

    // Do the final stuff and return success
    $this->domain = $domain;
    $this->mo_file = $filename;

    return true;
  }

  function add_header($header_value) {
    add_filter( 'wp_headers', function( array $headers ) use ($header_value) : array {
      $headers["X-Native-Gettext"] = $header_value;
      return $headers;
    });
  }
}

function locale_not_supported_notice( $locale ) {
  $class = 'notice notice-warning is-dismissible';
  $message = sprintf (__( 'Native Gettext disabled: Locale %s is not supported on this system', 'native-gettext' ), $locale );

  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

function multiple_textdomain_notice( $other ) {
  $class = 'notice notice-warning is-dismissible';
  $message = sprintf (__( 'Native Gettext warning: Another plugin has an override_load_textdomain filter active and some translations have been handled by the %s class instead of the NativeGettextMO class. Performance is not the best it could be.', 'native-gettext' ), get_class($other));

  printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}
