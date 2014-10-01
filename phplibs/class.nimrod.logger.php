<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
 
/** Class dependencies. */
require_once 'interface.nimrod.logger.php';
require_once 'class.nimrod.poutil.php';

/**
 * NimrodLogger class.
 * Minimal working code example:
 * <pre>
 * require 'class.nimrod.logger.php';
 * // Inside your translation function just use:
 * NimrodLogger::logGettext( $translated_text, $text, $domain );
 * </pre>
 */
class NimrodLogger implements NimrodLoggerInterface
{
  /**
   * Optional. The absolute path is used to remove (unwanted?) directory names 
   *   that are previous to the current base directory; 
   *   e.g. `/var/www/site/cms/path/file.php` becomes `cms/path/file.php`. 
   */
  public static $abspath = "";

  /** 
   * Read-only. Assign a unique number to each localizable string.
   */
  protected static $strId = 0;

  /** 
   * Getter of strId property.
   * @param string $text    The gettext msgid.
   * @param string $context The gettext context.
   * @param string $domain  The gettext domain.
   * @return string
   */
  public static function getStrId( $text, $context, $domain ) 
  {
    $hash = self::doHash( $text, $context, $domain );
    return self::$messages[$hash]['id'];
  }

  /** 
   * Hashing method according to text, context, and domain.
   * @param string $text    The gettext msgid.
   * @param string $context The gettext context.
   * @param string $domain  The gettext domain.
   * @return string
   */
  protected static function doHash( $text, $context, $domain ) 
  {
    return md5( $text . $context . $domain );
  }
  
  /** Database for regular entries. */
  protected static $messages = array();
  /** Database for Nimrod comments. */
  protected static $comments = array();

  /** 
   * Assign a user ID, ensuring that Nimrod has write permissions on the logs directory.
   * @throws Exception if the logs directory is not writable.
   * @return string
   */
  protected static function allocateUid() 
  {
    $base_dir = self::getLogPath();
    if ( !is_writable($base_dir) ) {
      throw new Exception( sprintf('%s is not writable.', $base_dir) );
      return false;
    }
    return uniqid();
  }

  /** 
   * Retrieve user ID.
   * @return string
   */
  public static function getUid() 
  {
    $key = "nimrod-id";
    if ( isset($_COOKIE[$key]) && !empty($_COOKIE[$key]) ) {
      $value = $_COOKIE[$key];
    } else {
      $value = self::allocateUid();
      $expires = time() + 31536000;
      setcookie( $key, $value, $expires, "/" );
      // Note that the cookie isn't ready until the the client requests a new page.
      $_COOKIE[$key] = $value;
    }
    return $value;
  }

  /** 
   * Retrieve log dir path.
   * @return string
   */
  protected function getLogPath() 
  {
    return defined('GETTEXT_LOG_UNTRANSLATED') ? GETTEXT_LOG_UNTRANSLATED : self::$abspath;
  }

  /** 
   * Retrieve user dir path.
   * @return string
   */
  protected function getUserDir() 
  {
    $target = self::getTargetFile();
    $user_dir = self::getLogPath() . "/" . self::getUid() . "/" . $target['hash'];
    if ( !is_dir($user_dir) ) {
      mkdir( $user_dir, 0775, TRUE );
    }
    return $user_dir;
  }

  /** 
   * Retrieve POT file path according to the gettext domain.
   * @param string $domain The gettext domain.
   * @return string
   */
  protected function getPotFile( $domain )
  {
    return self::getUserDir() . "/" . $domain . ".npot";
  }

  /** 
   * Retrieve the file currently shown in the browser.
   * @return array
   */
  public static function getTargetFile() 
  {
    $requested_file = self::removeBasepath( $_SERVER['SCRIPT_FILENAME'] );
    return array(
      "path" => $requested_file,       // The path may contain multiple directories,
      "hash" => md5($requested_file),  // so this hashing creates a flat file hierarchy.
    );
  }
  
  /** 
   * Truncates all user files.
   */
  public static function resetFiles() 
  {
    $user_dir = self::getUserDir();
    $iterator = new RecursiveIteratorIterator(
                  new RecursiveDirectoryIterator( $user_dir ), 
                  RecursiveIteratorIterator::SELF_FIRST
                );
    foreach ($iterator as $name => $item) {
      $filename = $item->getPathname();
      if ( is_file($filename) ) {
        file_put_contents( $filename, "" );
      }
    }
  }

  /** 
   * Creates useful comments for each gettext entry.
   * @param array $res  Resource string collection.
   */
  public static function addResourceComments( $res ) 
  {
    // See http://www.gnu.org/software/gettext/manual/gettext.html#PO-Files
    $occurrences   = self::commonResources($res);
    $seen_multiple = FALSE;
    foreach ($res as $xpath => $types) {
      foreach ($types as $type => $props) {
        foreach ($props as $prop => $vals) {
          foreach ($vals as $v) {
            // First notify translators with basic info about markup.
            $cnt = count($occurrences[$v][$type]);
            $msg = '';
            if ( !isset(self::$comments[$v]) ) {
              $msg .= NimrodPOUtil::TOK_COM_TR . sprintf( 'TRANSLATORS: This message appears on this %s:', self::fmtElemType($type, $prop) );
            } elseif ($cnt > 1) {
              if ($seen_multiple) continue;
              $msg .= NimrodPOUtil::TOK_COM_TR . sprintf( 'It appears on %1$d more %2$ss.', $cnt, self::fmtElemType($type, $prop) );
              $seen_multiple = TRUE;
            } else {
              $msg .= NimrodPOUtil::TOK_COM_TR . sprintf( 'It also appears on this %s:', self::fmtElemType($type, $prop) );
              $seen_multiple = FALSE;
            }
            // Then add XPath context
            if ( !isset(self::$comments[$v]) || $cnt === 1) {
              $msg .= PHP_EOL;
              $msg .= self::fmtXpathType($type, $xpath);
            }
            self::$comments[$v][] = $msg;
          }
        }
      }
    }
  }

  /** 
   * Retrieve gettext resources.
   * @param array $res  Resource string collection.
   * @return array
   */
  protected function commonResources($res) 
  {
    $occurrences = array();
    /* 
     * A stringified example of how do $res entries look like:
     * "/html/body/p/a": {
     *   "natt": { "title": [1,2], "alt": [1] },
     *   "ntag": { "a": [3,1,4] }
     * },
     * ...
     */
    foreach ($res as $xpath => $types)
      foreach ($types as $type => $props)
        foreach ($props as $prop => $vals)
          foreach ($vals as $v)
            $occurrences[$v][$type][] = $prop;
    return $occurrences;
  }

  /** 
   * Format element.
   * @param string $type  Element type (tag name).
   * @param string $prop  Element property (attribute).
   * @return string
   */
  protected function fmtElemType($type, $prop) 
  {
    if ( $type == "ntag" )     $msg = sprintf( '<%s> element', $prop );
    elseif ( $type == "natt" ) $msg = sprintf( '%s attribute', strtoupper($prop) );
    return $msg;
  }
  
  /** 
   * Format xpath.
   * @param string $type  Element type (tag name).
   * @param string $xpath Element's XPath representation.
   * @return string
   */
  protected function fmtXpathType($type, $xpath) 
  {
    $msg = '';
    // Point to the 'viscontextualizer' URL.
    $url  = "http" . ($_SERVER['HTTPS'] == "on" ? 's' : NULL) . '://';
    $url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $url .= !empty($_SERVER['QUERY_STRING']) ? '&' : '?';
    $url .= 'xvis=' . urlencode( $xpath );
    $msg = NimrodPOUtil::TOK_COM_TR . $url;
    // Don't add newline ending to $msg
    return $msg;
  }

  /** 
   * Log gettext message.
   * @param string $translated_text The translated message.
   * @param string $text            The gettext msgid.
   * @param string $domain          The gettext domain.
   */
  public static function logGettext( $translated_text, $text, $domain ) 
  {
    $hash = self::doHash( $text, NULL, $domain );
    $mref = self::fmtReference();
    if ( !isset(self::$messages[$hash]) ) {
      self::$strId++;
      self::$messages[$hash] = array(
        "id"      => self::$strId,
        "ref"     => array( $mref ),
        "msgctxt" => NULL,
        "msgid"   => $text,
        "msgstr"  => $translated_text,
        "msgfreq" => 1,
        "target"  => self::getTargetFile(),
        "potfile" => self::getPotFile( $domain ),
      );      
    } else {
      $po_item = &self::$messages[$hash];
      $po_item['msgfreq']++;
      $ref_arr = $po_item['ref'];
      if ( !in_array($mref, $ref_arr) ) {
        $ref_arr[] = $mref;
      }
    }
  }

  /** 
   * Log gettext message with context.
   * @param string $translated_text The translated message.
   * @param string $text            The gettext msgid.
   * @param string $context         The gettext msgctxt.
   * @param string $domain          The gettext domain.
   */
  public static function logGettextWithContext( $translated_text, $text, $context, $domain ) 
  {
    $hash = self::doHash($text, $context, $domain);
    $mref = self::fmtReference();
    if (!isset(self::$messages[$hash])) {
      self::$strId++;
      self::$messages[$hash] = array(
        "id"      => self::$strId,
        "ref"     => array( $mref ),
        "msgctxt" => $context,
        "msgid"   => $text,
        "msgstr"  => $translated_text,
        "msgfreq" => 1,
        "target"  => self::getTargetFile(),
        "potfile" => self::getPotFile($domain),
      );
    } else {
      $po_item = &self::$messages[$hash];
      $po_item['msgfreq']++;
      $ref_arr = $po_item['ref'];
      if ( !in_array($mref, $ref_arr) ) {
        $ref_arr[] = $mref;
      }
    }
  }

  /** 
   * Save log.
   */
  public static function save() 
  {
    $pfiles = array(); // POT files
    $jfiles = array(); // JSON files
    foreach (self::$messages as $hash => $data) {
      $id = $data['id'];
      $entry  = NimrodPOUtil::TOK_NID . $id . PHP_EOL;
      $entry .= NimrodPOUtil::TOK_FREQ . $data['msgfreq'] . PHP_EOL;
      if ( !empty(self::$comments[$id]) ) {
        $entry .= implode(PHP_EOL, self::$comments[$id]) . PHP_EOL;
      }
      if ( !empty($data['ref']) ) {
        $refs = implode(PHP_EOL, $data['ref']);
        if ($refs) $entry .= $refs . PHP_EOL;
      }
      if ( !empty($data['msgctxt']) ) {
        $entry .= NimrodPOUtil::KEY_MSGCTXT . ' ' . NimrodPOUtil::quotize($data['msgctxt']) . PHP_EOL;
      }
      $entry .= NimrodPOUtil::KEY_MSGID . ' ' . NimrodPOUtil::quotize($data['msgid']) . PHP_EOL;
      if ( $data['msgstr'] == $data['msgid'] ) {
        $data['msgstr'] = NULL;
      }
      $entry .= NimrodPOUtil::KEY_MSGSTR . ' ' . NimrodPOUtil::quotize($data['msgstr']) . PHP_EOL;
#      $entry .= PHP_EOL;
      // Split log files by domain (PO files).
      $pfiles[ $data['potfile'] ][] = $entry;
      // Save metadata about the URL currently being interacted with.
      $target = $data['target'];
      $fhash  = $target['hash'];
      if ( !isset($jfiles[$fhash]) ) {
        $jfiles[$fhash] = array(
          'path' => $target['path'],
          'nmsg' => 0,
          'npot' => array(),
        );
      }
      $jfiles[$fhash]['nmsg']++;
      $domain = basename($data['potfile']);
      if ( !in_array($domain, $jfiles[$fhash]['npot']) ) {
        $jfiles[$fhash]['npot'][] = $domain;
      }
    }
    // Finally write files.
    $header = self::getLogHeader() . PHP_EOL;
    foreach ($pfiles as $pfile => $entries) {
      $npo_data = $header . implode(PHP_EOL, $entries);
      file_put_contents($pfile, $npo_data);
    }
    // Reuse last $pfile to get the current log dir.
    $json_file = dirname($pfile) . '/meta.json';
    $json_data = json_encode($jfiles);
    file_put_contents($json_file, $json_data);
  }
   
  /**
   * Retrieves file and line number where each message is referenced.
   * @return array
   */
  protected static function getReference() 
  {
    $bt  = debug_backtrace();
    // Stack order: fmtReference -> getReference -> logGettext* -> call_user_func_array -> apply_filters -> translate -> __
    // FIXME: There should be a better way to figure out this.
    $fp  = $bt[7];
    // A privacy bit: Relate each file to the base dir where the CMS is installed, e.g.
    // transform `/var/www/my/path/to/worpress/wp-content/whatever.php` in `wp-content/whatever.php`
    $file_name = self::removeBasepath( $fp['file'] );
    $file_line = $fp['line'];
    return array( $file_name, $file_line );
  }

  /**
   * Format message reference.
   * @return string
   */
  protected static function fmtReference() 
  {
    $msg = '';
    // See http://www.gnu.org/software/gettext/manual/gettext.html#PO-Files
    list( $file_name, $file_line ) = self::getReference();
    if ( !empty($file_name) && !empty($file_line) ) {
      $msg .= NimrodPOUtil::TOK_REF . $file_name . ':' . $file_line;
#      // We shouldn't add format flags, as they are deprecated.
#      // See See http://www.gnu.org/software/gettext/manual/gettext.html#c_002dformat-Flag
#      $ext = pathinfo($file_name, PATHINFO_EXTENSION);
#      $msg .= PHP_EOL . NimrodPOUtil::TOK_FLG . $ext . '-format';
      // In any case, never add a newline ending to $msg
    }
    return $msg;
  }

  /**
   * Retrieves log header.
   * @return string
   */
  protected static function getLogHeader() 
  {
    $cur_year = date("Y");
    $mod_date = date("r");
    $header  = '# Translation Nimrod file.' . PHP_EOL;
    $header .= '# Copyright (C) ' . $cur_year . ' Nimrod contributors.' . PHP_EOL;
    $header .= '# This file is distributed under the same license as the Nimrod package.' . PHP_EOL;
    $header .= '# Nimrod Team <nimrod.team@lists.launchpad.net>, ' . $cur_year . '.' . PHP_EOL;
    $header .= '#' . PHP_EOL;
    $header .= 'msgid ""' . PHP_EOL;
    $header .= 'msgstr ""' . PHP_EOL;
    $header .= '"Project-Id-Version: Nimrod 0.2\n"' . PHP_EOL;
    $header .= '"Report-Msgid-Bugs-To: nimrod.team@lists.launchpad.net\n"' . PHP_EOL;
    $header .= '"POT-Creation-Date: ' . $mod_date . '\n"' . PHP_EOL;
    $header .= '"PO-Revision-Date: ' . $mod_date . '\n"' . PHP_EOL;
    $header .= '"Last-Translator: John Doe <nimrod.team@lists.launchpad.net>\n"' . PHP_EOL;
    $header .= '"Language-Team: English <https://launchpad.net/~nimrod>\n"' . PHP_EOL;
    $header .= '"MIME-Version: 1.0\n"' . PHP_EOL;
    $header .= '"Content-Type: text/plain; charset=UTF-8\n"' . PHP_EOL;
    $header .= '"Content-Transfer-Encoding: 8bit\n"' . PHP_EOL;
    return $header;
  }

  /**
   * Removes base path of file, preserving only the relative dir.
   * @param strin $filepath The input file path.
   * @return string
   */
  protected static function removeBasepath( $filepath ) 
  {
    if ( empty(self::$abspath) || empty($filepath) ) 
      return $filepath;
    
    if ( strrpos(self::$abspath, "/") < strlen(self::$abspath) - 1 )
      self::$abspath .= "/";
    
    $parts = explode( self::$abspath, $filepath );
    // In some shared hostings the ABSPATH does not match the root of SCRIPT_FILENAME,
    // which causes this function to return NULL. So we fix it by returning the full path.
    if ( empty($parts[1]) ) return $filepath;
    // Otherwise just return the (expected) relative file path.
    return $parts[1];
  }
    
}

