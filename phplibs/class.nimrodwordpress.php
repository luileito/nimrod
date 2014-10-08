<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
 
/** Class dependencies. */
require_once 'class.nimrod.php';
require_once 'class.nimrod.logger.php';

/**
 * WP_Nimrod class.
 * Integrates Nimrod in Wordpress.
 */
class WP_Nimrod extends Nimrod 
{
  /**
   * Register action hooks.
   * @param array $config Configuration options.
   */
  public function init( $config = array() ) 
  {
    // Point to Wordpress base path
    NimrodLogger::$abspath = ABSPATH;
    // Do not reset logs when accessing the admin page
    if ( !in_array( $this->getPath() . '/nimrod.php', get_included_files()) ) {
      NimrodLogger::resetFiles();
    }
    // Handle output buffering
    add_action( 'init', array( $this, 'bufferStart' ) );
    add_action( 'shutdown', array( $this, 'bufferEnd' ) );
    // Plugin Admin Interface starts here
    add_action( 'admin_menu', array( $this, 'adminMenu' ) );
    // Expose Nimrod only to logged users on admin area
    //add_action( 'admin_init', array( $this, 'load' ) );
    // Expose Nimrod to everybody, incl. the public part of the website
    add_action( 'plugins_loaded', array( $this, 'onLoad' ) );
  }

  /**
   * Hook for plugins_loaded().
   * @todo Implement localizePlugin().
   */
  function onLoad() 
  {
    // Overload gettext functions
    add_filter( 'gettext', array( $this, 'gettext' ), 10, 3 );
    add_filter( 'gettext_with_context', array( $this, 'gettextWithContext' ), 10, 3 );
  }

  /**
   * Hook of gettext().
   * @param string $translated_text The translated message.
   * @param string $text            The gettext msgid.
   * @param string $domain          The gettext domain.
   */
  function gettext( $translated_text, $text, $domain = 'default' ) 
  {
    if ( !defined('GETTEXT_LOG_UNTRANSLATED') ) return $translated_text;
    
    NimrodLogger::logGettext( $translated_text, $text, $domain );
    return NimrodParser::TOK_OP . $translated_text . NimrodParser::TOK_MID . NimrodLogger::getStrId( $text, NULL, $domain ) . NimrodParser::TOK_CL;
  }

  /**
   * Hook of gettext_with_context().
   * @param string $translated_text The translated message.
   * @param string $text            The gettext msgid.
   * @param string $context         The gettext msgctxt.
   * @param string $domain          The gettext domain.
   */
  function gettextWithContext( $translated_text, $text, $context, $domain = 'default' ) 
  {
    if ( !defined('GETTEXT_LOG_UNTRANSLATED') ) return $translated_text;
    
    NimrodLogger::logGettextWithContext( $translated_text, $text, $context, $domain );
    return NimrodParser::TOK_OP . $translated_text . NimrodParser::TOK_MID . NimrodLogger::getStrId( $text, $context, $domain ) . NimrodParser::TOK_CL;
  }

  /**
   * Load translation files of this plugin.
   * @see http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
   */
  protected function localizePlugin() 
  {
    $tr = $this->getPath() . '/locale';
    load_plugin_textdomain( 'nimrod', FALSE, $tr );
  }

  /**
   * Hook of admin_menu().
   */
  function adminMenu() 
  {
    add_submenu_page( 'plugins.php', __('Nimrod Admin', 'nimrod'), 'Nimrod', 'manage_options', 'nimrod_admin', array( $this, 'adminPage' ) );
    add_options_page( __('Nimrod Settings', 'nimrod'), 'Nimrod', 'manage_options', 'nimrod_settings', array($this, 'settingsPage') );
  }

  /**
   * Callback for add_submenu_page in adminMenu().
   */
  function adminPage() 
  {
    include $this->getPath() . '/admin.php';
  }

  /**
   * Callback for add_options_page in adminMenu().
   */
  function settingsPage() 
  {
    include $this->getPath() . '/settings.php';
  }
  
}

