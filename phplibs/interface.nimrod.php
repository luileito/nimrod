<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */

/** Nimrod interface. */
interface NimrodInterface {

  /** 
   * Empty function, to be called from inherited classes.
   */
  function init();
  
  /** 
   * Retrieve the (absolute) path to the base dir.
   * @return string
   */
  function getPath();
  
  /** 
   * Retrieve the URL to this file.
   * @return string
   */
  function getUrl();

}

