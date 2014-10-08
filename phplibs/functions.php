<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */

if ( !function_exists('substring') ) {
  /**
   * Extract characters from a string.
   * @param string  $str    The input string.
   * @param integer $start  Starting offset.
   * @param integer $end    Ending offset.
   * @return string
   */
  function substring($str, $start, $end) {
    return substr($str, $start, $end - $start);
  }
}

if ( !function_exists('str_startswith') ) {
  /**
   * Check if string starts with a given prefix.
   * @param string $str     The input string.
   * @param string $prefix  Matching prefix.
   * @return boolean
   */
  function str_startswith($str, $prefix) {
    return strncmp($str, $prefix, strlen($prefix)) === 0;
  }
}

if ( !function_exists('str_endswith') ) {
  /**
   * Check if string ends with a given suffix.
   * @param string $str     The input string.
   * @param string $suffix  Matching suffix.
   * @return boolean
   */
  function str_endswith($str, $suffix) {
    return substr_compare($str, $suffix, -strlen($suffix)) === 0;
  }
}

if ( !function_exists('pluralize') ) {
  /**
   * Generate a pluralized form according to the number of items involved in the text.
   * @param integer $count          Number of items in text.
   * @param string  $singular_form  Singular form for the text.
   * @param string  $plural_form    Plural form for the text.
   * @return string
   */
  function pluralize( $count, $singular_form, $plural_form = NULL ) {
    if ( !$plural_form ) $plural_form = $singular_form . 's';
    return ( $count == 1 ? $singular_form : $plural_form );
  }
}

if ( !function_exists('linkify') ) {
  /**
   * Generate a URL from given text item.
   * @param string $item    The input item.
   * @param string $baseurl Base URL.
   * @return string
   */
  function linkify($item, $baseurl) {
    if ( !str_endswith($baseurl, '/') ) $baseurl .= '/';
    return '<a href="' . $baseurl . $item . '">' . $item . '</a>';
  }
}

if ( !function_exists('tagwrap') ) {
  /**
   * Generate custom markup for a given text item according to given tag.
   * @param string $item  The input item.
   * @param string $tag   HTML tag.
   * @return string
   */
  function tagwrap($item, $tag) {
    return '<' . $tag . '>' . $item . '</' . $tag . '>';
  }
}

if ( !function_exists('str_rmprefix') ) {
  /**
   * Remove prefix from string.
   * @param string $str     The input string.
   * @param string $prefix  Matching prefix.
   * @return string
   */
  function str_rmprefix($str, $prefix) {
    $pos = strpos($str, $prefix);
    if ( $pos !== FALSE ) $str = substr( $str, $pos + strlen($prefix) );
    return $str;
  }
}

if ( !function_exists('http_request') ) {
  /**
   * Perform an HTTP request.
   * @param string $url   The target URL.
   * @param array  $opts  Options
   * @return array
   */
  function http_request( $url, $opts = array() )
  {
    $options = array(
                      CURLOPT_URL            => $url,
                      CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
                      CURLOPT_RETURNTRANSFER => true,   // return transfer as a string
                      CURLOPT_HEADER         => false,  // don't return headers
                      CURLOPT_ENCODING       => "",     // handle all encodings
                      CURLOPT_CONNECTTIMEOUT => 10,     // timeout on connect
                      CURLOPT_TIMEOUT        => 30,     // timeout on response
                      CURLOPT_SSL_VERIFYPEER => false,  // disable host verification
                      CURLOPT_SSL_VERIFYHOST => false,  // disable peer verification
                    );
    
    if (count($opts) > 0)
    {
      foreach ($opts as $key => $value) {
        $options[$key] = $value;
      }
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $content  = curl_exec($ch);     // the Web page
    $transfer = curl_getinfo($ch);  // transfer information (http://www.php.net/manual/en/function.curl-getinfo.php)
    $errnum   = curl_errno($ch);    // see codes at http://curl.haxx.se/libcurl/c/libcurl-errors.html
    $errmsg   = curl_error($ch);    // empty string on success
    curl_close($ch);
    // Extend transfer info.
    $transfer['errnum']  = $errnum;
    $transfer['errmsg']  = $errmsg;
    $transfer['content'] = $content;
    // $transfer['url'] is the final URL after redirections, if CURLOPT_FOLLOWLOCATION is set to true.
    
    return $transfer;
  }
}

if ( !function_exists('array_mean') ) {
  /**
   * Compute the mean of an array of values.
   * @param array  $arr Input array.
   * @return float
   */
  function array_mean($arr) {
    return array_sum($arr) / count($arr);
  }
}

if ( !function_exists('array_sd') ) {
  /**
   * Compute the standard deviation of an array of values.
   * @param array  $arr  Input array.
   * @param float  $mean Array mean (optional).
   * @return float
   */
  function array_sd($arr, $mean = NULL) {
    if ( is_null($mean) ) $mean = array_mean($arr);
    $variance = 0;
    foreach ($arr as $val) {
      $diff = $val - $mean;
      $variance += $diff * $diff;
    }
    return sqrt($variance / count($arr));
  }
}

if ( !function_exists('array_transpose') ) {
  /**
   * Transpose array, i.e., swap columns by rows.
   * @param array  $arr The input array.
   * @return array
   */
  function array_transpose($arr) {
    array_unshift($array, null);
    return call_user_func_array('array_map', $array);
  }
}

if ( !function_exists('whiten') ) {
  /**
   * Standardize features so that each has zero mean and unit variance.
   * The observations matrix is passed as reference. 
   * All array keys are preserved.
   * @param array  $obs Multidimensional matrix.
   */
  function whiten( &$obs ) {
    $keys_rows = array_keys($obs);
    $keys_cols = array();
    foreach ($obs as $o => $arr) {
      $keys_cols[] = array_keys($arr);
    }
    $cols = array_transpose($obs);
    $whiten = array();
    foreach ($cols as $c => $arr) {
      $mean = array_mean($arr);
      $sd   = array_sd($arr, $mean);
      $whiten[$c] = array( 'mean' => $mean, 'sd' => $sd );
    }
    foreach ($cols as $c => $arr) {
      $data = $whiten[$c];
      foreach ($arr as $a => $val) {
        $cols[$c][$a] = ($val - $data['mean']) / $data['sd'];
      }
    }
    $cols = array_transpose($cols);
    foreach ($cols as $c => $arr) {
      $kr = $keys_rows[$c];
      foreach ($arr as $a => $val) {
        $kc = $keys_cols[$a];
        $obs[$kr][ $kc[$a] ] = $val;
      }
    }
  }
}

