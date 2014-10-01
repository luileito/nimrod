<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
 
/** @throws Fatal run-time error if gettext library is not installed. */
if ( !function_exists('_') ) trigger_error("Gettext library is not installed.", E_USER_ERROR);

/** Global dependencies. */
require_once 'class.nimrod.parser.php';
require_once 'class.nimrod.logger.php';

/** 
 * Translation function wrapper.
 * @param string $text    The gettext msgid.
 * @param string $domain  The gettext domain.
 */
function __t( $text, $domain ) {
  setlocale( LC_MESSAGES, "es_ES.utf8");
  bindtextdomain( $domain, "/path/to/my/locale/folder" );
  textdomain( $domain );
  
  return _( $text );
}

/** 
 * Decorated gettext function for translation.
 * @param string $text    The gettext msgid.
 * @param string $domain  The gettext domain.
 */
function _gt( $text, $domain = 'default' ) {
  $translated_text = __t( $text, $domain );
  if ( !defined('GETTEXT_LOG_UNTRANSLATED') ) return $translated_text;
  
  NimrodLogger::logGettext( $translated_text, $text, $domain );
  return NimrodParser::TOK_OP . $translated_text . NimrodParser::TOK_MID . NimrodLogger::getStrId( $text, NULL, $domain ) . NimrodParser::TOK_CL;
}

/** 
 * Decorated gettext function for translation with context.
 * @param string $text    The gettext msgid.
 * @param string $context The gettext msgctxt.
 * @param string $domain  The gettext domain.
 */
function _gx( $text, $context, $domain = 'default' ) {
  $translated_text = __t( $text, $domain );
  if ( defined('GETTEXT_LOG_UNTRANSLATED') ) return $translated_text;
  
  NimrodLogger::logGettextWithContext( $translated_text, $text, $context, $domain );
  return NimrodParser::TOK_OP . $translated_text . NimrodParser::TOK_MID . NimrodLogger::getStrId( $text, $context, $domain ) . NimrodParser::TOK_CL;  
}

