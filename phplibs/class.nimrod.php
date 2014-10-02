<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
 
/** Class dependencies. */
require_once 'interface.nimrod.php';
require_once 'class.nimrod.parser.php';

/**
 * Nimrod class.
 * This class is not abstract but was intended to be extended by other classes. 
 * It should be instantiated "as is", though it has not been tested.
 */
class Nimrod implements NimrodInterface {

  /** Constructor. */
  public function __construct()
  {
    $this->init();
  }

  /** 
   * Empty function, to be called from inherited classes.
   * @example class.nimrodwordpress.php
   */
  public function init() {}

  /** 
   * Retrieve the (absolute) path to the base dir.
   * @return string
   */
  public function getPath() 
  {
    return realpath( dirname( __FILE__ ) . '/../' );
  }

  /** 
   * Retrieve the URL to this file.
   * @return string
   */  
  public function getUrl() 
  {
    $path = $this->getPath();
    $admin_dir = dirname( $_SERVER['SCRIPT_NAME'] );
    $web_root  = dirname( $admin_dir );
    return substr( $path, strpos($path, $web_root) );
  }

  /** 
   * Callback for page buffering init.
   */
  function bufferStart() 
  {
    ob_start( array( $this, 'bufferParse' ) );
  }

  /** 
   * Callback for page buffering shutdown.
   */
  function bufferEnd() 
  {
    if ( ob_get_level() > 0 ) ob_end_clean();
  }

  /**
   * Callback for bufferStart(). Parses the page and outputs its contents.
   * @param string $html Rendered HTML page.
   * @return string
   */
  function bufferParse( $html ) 
  {
    if ( !defined('GETTEXT_LOG_UNTRANSLATED') ) return $html;
    
    $np = new NimrodParser();
    $np->configure(array(
      'base_url'  => $this->getUrl(),
      //'watermark' => FALSE,
    ));
    $np->loadString($html)->parse();
    NimrodLogger::addResourceComments($np->resources);
    NimrodLogger::save();
    return $np;
  }
  
}

