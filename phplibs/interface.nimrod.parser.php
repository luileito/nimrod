<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */

/** NimrodParser interface. */
interface NimrodParserInterface {

  /** 
   * Configuration setup. 
   * @param array $conf The config properties.
   */
  function configure( $conf );

  /** 
   * Load file to the DOM. 
   * @param string $file Input file.
   * @return NimrodParser
   */
  function loadFile( $file );

  /** 
   * Load string to the DOM.
   * @param string $html Input HTML.
   * @return NimrodParser
   */
  function loadString( $html );

  /** 
   * Parse the DOM.
   * @return string
   */
  function parse();
  
}

