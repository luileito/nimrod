<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */

/**
 * NimrodPOUtil class.
 * Minimal working code example:
 * <pre>
 * require 'class.nimrod.poutil.php';
 * $poutil = new NimrodPOUtil;
 * // Assuming some $pofile and a $items catalog:
 * echo $poutil->read($pofile)->update($items)->sort();
 * </pre>
 */
class NimrodPOUtil 
{
  // Tokens that will be used to rearrange PO entries.
  const TOK_NID     = '# nid ';
  const TOK_FREQ    = '# freq ';
  const TOK_REF     = '#: ';
  const TOK_COM_TR  = '# ';
  const TOK_COM_XT  = '#.';
  // Standard fields used in PO files.
  const KEY_MSGID   = 'msgid';
  const KEY_MSGSTR  = 'msgstr';
  const KEY_MSGCTXT = 'msgctxt';
  // Custom fields used by Nimrod.
  const KEY_NID     = 'nid';
  const KEY_FREQ    = 'freq';
  const KEY_REF     = 'ref';
  const KEY_COM_TR  = 'comtr';
  const KEY_COM_XT  = 'comxt';
  const KEY_EL_NUM  = 'elnum';
  const KEY_EL_VIS  = 'elvis';
  const KEY_EL_SIZE = 'elsiz';
  const KEY_EL_INTERACT = 'elint';
  const KEY_EL_CONTRAST = 'elcon';
  const KEY_EL_SEMANTIC = 'elsem';
  
  /** PO header. */
  protected $header = "";

  /** Message catalog database. */  
  public $db = array();

  /** Constructor. */
  function __construct() 
  {
    return $this;
  }

  /** 
   * Add quotes to string.
   * @param string $str Input string.
   * @return string
   */
  public static function quotize( $str )
  {
    return '"' . $str . '"';
  }
  
  /** 
   * Remove quotes from string.
   * @param string $str Input string.
   * @return string
   */
  public static function unquotize( $str )
  {
    return substr($str, 1, -1);
  }

  /** 
   * Read PO file.
   * @param string $pofile The (path to) PO file.
   * @return NimrodPOUtil
   */
  public function read( $pofile ) 
  {
    $header_saved = FALSE;
    $msgid_val_empty  = FALSE;
    $msgstr_val_empty = FALSE;
    $fp = fopen( $pofile, 'r' );
    while ( !feof($fp) ) {
      $line = trim( fgets($fp) );
      if ( strpos($line, self::TOK_NID) === 0 ) {
        $id = (int) substr($line, strlen(self::TOK_NID));
        $entry = array();             // Init entry
        $entry[self::KEY_NID] = $id;  // Save the ID for later multisorting
        $header_saved = TRUE;         // Flag line to indicate end of header
      } elseif ( strpos($line, self::TOK_COM_TR) === 0 && isset($id) ) {
        // Parse translator comment(s).
        $entry[self::KEY_COM_TR][] = substr($line, strlen(self::TOK_COM_TR));
        // Remember potential string visibility on the UI.
        if ( strpos($line, 'TRANSLATORS:') !== FALSE ) {
          $entry[self::KEY_EL_VIS] = (int) (strpos($line, "element") !== FALSE);
        }
        // Remember also the number of occurrences, on a per-element basis.
        @preg_match(substr($line, 1), '/appears on ([0-9]+)/', $matches);
        if ($matches) $el_num += (int) $matches[1];
        else $el_num += 1;
        $entry[self::KEY_EL_NUM] = $el_num;
      } elseif ( strpos($line, self::TOK_COM_XT) === 0 && isset($id) ) {
        // Parse extracted comment(s).
        $entry[self::KEY_COM_XT][] = substr($line, strlen(self::TOK_COM_XT));
      } elseif ( strpos($line, self::TOK_REF) === 0 ) {
        // Parse file reference(s).
        $entry[self::KEY_REF][] = substr($line, strlen(self::TOK_REF));
      } elseif ( strpos($line, self::KEY_MSGCTXT . ' ') === 0 ) {
        // Parse message context.
        $entry[self::KEY_MSGCTXT] = self::unquotize( substr($line, strlen(self::KEY_MSGCTXT) + 1) );
      } elseif ( strpos($line, self::KEY_MSGID . ' ') === 0 ) {
        // Parse message source.
        $msgid_val = self::unquotize( substr($line, strlen(self::KEY_MSGID) + 1) );
        if (empty($msgid_val)) $msgid_val_empty = TRUE;
        $entry[self::KEY_MSGID] = $msgid_val;
      } elseif ( strpos($line, self::KEY_MSGSTR . ' ') === 0 ) {
        // Parse message translation.
        $entry[self::KEY_MSGSTR] = self::unquotize( substr($line, strlen(self::KEY_MSGSTR) + 1) );
      } elseif ( strpos($line, self::KEY_FREQ . ' ') === 0 ) {
        // Parse message (overall) frequency.
        $entry[self::KEY_FREQ] = substr($line, strlen(self::KEY_FREQ) + 1);
      } elseif ( !empty($line) ) {
        // Save remaining fields of entry.
        $entry['other'][] = $line;
      } elseif ( empty($line) && isset($id) ) {
        // End of entry, so just save it.
        $this->db[$id] = $entry;
        $el_num = 0;
      }
      if ( !$header_saved ) $this->header .= $line . PHP_EOL;
    }
    fclose($fp);
    return $this;
  }

  /** 
   * Write PO file.
   * @param string $pofile The (path to) PO file.
   */
  public function write( $pofile ) 
  {
    $data = $this->header;
    foreach ($this->db as $id => $entries) {
      $data .= self::TOK_NID . $id . PHP_EOL;
      foreach ($entries as $key => $value) {
        switch ($key) {
          case self::KEY_COM_TR:
            $data .= $this->arrtok($value, self::TOK_COM_TR);
            break;
          case self::KEY_COM_XT:
            $data .= $this->arrtok($value, self::TOK_COM_XT);
            break;
          case self::KEY_REF:
            $data .= $this->arrtok($value, self::TOK_REF);
            break;
          case self::KEY_MSGCTXT:
          case self::KEY_MSGID:
          case self::KEY_MSGSTR:
            $data .= $key . ' ' . self::quotize($value) . PHP_EOL;
            break;
          case self::KEY_FREQ:
            $data .= self::TOK_FREQ . $value . PHP_EOL;
            break;
          case 'other':
            $data .= implode(PHP_EOL, $value) . PHP_EOL;
            break;
          default:
            break;
        }
      }
      $data .= PHP_EOL;
    }
    file_put_contents( $pofile, $data );
  }

  /** 
   * Add token to the beginning of each given text lines.
   * @param array  $lines The text lines.
   * @param string $tok   The token.
   * @return string
   */
  private function arrtok( $lines, $tok ) 
  {
    $data = "";
    foreach ($lines as $line) {
      $data .= $tok . $line . PHP_EOL;
    }
    return $data;
  }

  /** 
   * Update message catalog.
   * @param array $items The message catalog.
   * @return NimrodPOUtil
   */
  public function update( $items )
  {
    foreach ($items as $id => $fields) {
      $old_entry = &$this->db[$id];
      foreach ($fields as $key => $value) {
        $old_entry[$key] = $value;
      }
#      // Add a counter for the number of times this item appears in different pages?
#      if (isset($old_entry['msgfreq'])) {
#        $old_entry['msgfreq']++;
#      } else {
#        $old_entry['msgfreq'] = 1;
#      }
    }
    return $this;
  }

  /** 
   * Sort messages in the message catalog.
   * @param array $weights Sorting weights (optional).
   * @return NimrodPOUtil
   */
  public function sort( $weights = array() )
  {
    $scs = array();
    $wts = array(
      self::KEY_COM_TR  => 0,
      self::KEY_COM_XT  => 0,
      self::KEY_EL_NUM  => 0,
      self::KEY_REF     => 0,
      self::KEY_EL_VIS  => 0,
      self::KEY_EL_SIZE => 0,
    );
    if ($weights) {
      foreach ($weights as $key => $value) {
        $wts[$key] = $value;
      }
    }
    $feats = array();
    foreach ($this->db as $id => $entries) {
      foreach ($entries as $key => $value) {
        switch ($key) {
          case self::KEY_COM_TR:
          case self::KEY_COM_XT:
          case self::KEY_REF:
            $feats[$id][$key] = count($value);
            break;
          case self::KEY_FREQ:
          case self::KEY_EL_NUM:
          case self::KEY_EL_VIS:
          case self::KEY_EL_SIZE:
            $feats[$id][$key] = $value;
            break;
          default:
            break;
        }
      }
    }
    whiten($feats);
    foreach ($feats as $id => $entries) {
      $scs[$id] = 0;
      foreach ($entries as $key => $value) {
        $scs[$id] += $value * $wts[$key];
      }
    }
    arsort($scs);
    $sorted_db = array();
    foreach ($scs as $id => $value) {
      $sorted_db[$id] = $this->db[$id];
    }
    $this->db = $sorted_db;
    return $this;
  }

}

