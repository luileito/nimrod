<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */

/** NimrodLogger interface. */
interface NimrodLoggerInterface {

  /** 
   * Log gettext message.
   * @param string $translated_text The translated message.
   * @param string $text            The gettext msgid.
   * @param string $domain          The gettext domain.
   */
  static function logGettext( $translated_text, $text, $domain );

  /** 
   * Log gettext message with context.
   * @param string $translated_text The translated message.
   * @param string $text            The gettext msgid.
   * @param string $context         The gettext msgctxt.
   * @param string $domain          The gettext domain.
   */
  static function logGettextWithContext( $translated_text, $text, $context, $domain );

  /** 
   * Truncates all user files.
   */
  static function resetFiles();

  /** 
   * Save log.
   */
  static function save();

  /** 
   * Getter of strId property.
   * @param string $text    The gettext msgid.
   * @param string $context The gettext context.
   * @param string $domain  The gettext domain.
   * @return string
   */
  static function getStrId( $text, $context, $domain );

}

