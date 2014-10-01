<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */

/** Class dependencies. */
require_once 'functions.php';
require_once 'class.extdom.php';
require_once 'class.nimrod.logger.php';

/**
 * NimrodParser class.
 * Minimal working code example:
 * <pre>
 * require 'class.nimrod.parser.php';
 * $doc = new NimrodParser;
 * echo $doc->loadFile("page.html")->parse();
 * </pre>
 */
class NimrodParser implements NimrodParserInterface 
{
  /** Full regex token. */
  const TOK_RE  = '/\(\{\|(.*)\|\|(\d+)\|\}\)/';
  /** Opening token. */
  const TOK_OP  = '({|';
  /** Closing token. */
  const TOK_CL  = '|})';
  /** Middle token. */
  const TOK_MID = '||';

  /** Main configuration. */
  public $config = array(
    // Relative to the directory where the caller PHP is in.
    'base_url'  => ".",
    // CSS class identifier.
    'class_id'  => "nimrod-gettext",
    // TODO: pass in a file path (logo) instead?
    'watermark' => TRUE,
  );
  
  /** Gettext resources. */
  public $resources = array();

  /** The page DOM. */
  protected $pageDom;

  /** Constructor. */
  public function __construct() 
  {
    $this->pageDom = new ExtDOMDocument();
    // Attach some custom functions to DOM nodes.
    $this->pageDom->registerNodeClass( 'DOMElement', 'ExtDOMElement' );
  }

  /** String class representation. */
  public function __toString()
  {
    return $this->saveDOM();
  }

  /** 
   * Configuration setup. 
   * @param array $conf The config properties.
   */
  public function configure( $conf = array() )
  {
    foreach ($conf as $key => $value) {
      $this->config[$key] = $value;
    }
  }
  
  /** 
   * Save parsed DOM structure.
   * @return string
   */
  protected function saveDOM() 
  {
    return trim( $this->pageDom->saveHTML() );
  }
  
  /** 
   * Load file to the DOM. 
   * @param string $file Input file.
   * @return NimrodParser
   */
  public function loadFile( $file ) 
  {
    $this->pageDom->loadHTMLFile( $file );
    return $this;
  }

  /** 
   * Load string to the DOM.
   * @param string $html Input HTML.
   * @return NimrodParser
   */
  public function loadString( $html ) {
    $this->pageDom->loadHTML( $html );
    return $this;
  }

  /** 
   * Parse the DOM.
   * @return string
   */
  public function parse() 
  {
    $head = $this->pageDom->getElementsByTagName('head')->item(0);
    $body = $this->pageDom->getElementsByTagName('body')->item(0);
    $this->treeWalker( $head );
    $this->treeWalker( $body );
    
    $body->setAttribute( 'data-nid', NimrodLogger::getUid() );
    $target = NimrodLogger::getTargetFile();
    $body->setAttribute( 'data-nih', $target['hash'] );
    
    $this->putScripts( array("lazy", "nimrod"), $body );
    if ( $this->config['watermark'] ) {
      $this->putWatermark( $body );
    }
    //return $this->debugDOM();
    return $this->saveDOM();
  }

  /** 
   * Create SCRIPT elements and append them to the DOM.
   * @param array      $modules File dependencies, inside jslibs dir.
   * @param DOMElement $node    DOM node which SCRIPT will be appended to.
   */
  protected function putScripts($modules, $node) 
  {
    foreach ($modules as $mod) {
      $script = $this->pageDom->createElement('script');
      $script->setAttribute('src', $this->config['base_url'] . "/jslibs/" . $mod . ".js");
      $node->appendChild( $script );
    }
  }

  /** 
   * Debug parsed DOM nodes.
   * @return string
   */
  private function debugDOM() 
  {
    $nimrod_els = $this->pageDom->getElementsByClassName($this->config['class_id']);
    $content = "";
    foreach ($nimrod_els as $el) {
      $content .= $el->nodeName . ' &rarr; ' . $el->nodeValue . '<br>';
    }
    return $content;
  }

  /** 
   * Create an image watermark.
   * @param DOMElement $node DOM node which SCRIPT will be appended to.
   */
  protected function putWatermark($node) 
  {
    $img = $this->pageDom->createElement( 'img' );
    $img->setAttribute('src', $this->config['base_url'] . "/css/nimrod-logo-bw2.png");
    $img->setAttribute('alt', "Nimrod logo");
    $img->setAttribute('style', "position:fixed; bottom:0; left:5px; opacity:0.5; z-index:2147483647");
    // Optionally allow to hide the logo when clicking on it.
    $img->setAttribute('onclick', "this.style.display='none';");
    $node->appendChild( $img );
  }

  /** 
   * Check whether node or attribute content is well balanced.
   * @param string $str The content.
   */
  protected function isBalanced($str) 
  {
    if ( strpos( $str, self::TOK_OP ) !== FALSE ) {
      return ( strpos( $str, self::TOK_CL ) !== FALSE && 
               substr_count( $str, self::TOK_OP ) == substr_count( $str, self::TOK_CL ) );
    }
    return FALSE;
  }

  /** 
   * Parse DOM node.
   * @param DOMElement $node The DOM node.
   */
  protected function processNimrodCode($node) 
  {
    if ($node->nodeType === 3 || $node->nodeType === 4) {
      $val = $node->nodeValue;
      $nod = $node->parentNode;
    }
    elseif ($node->nodeType === 1) {
      $val = $node->getInnerHTML();
      $nod = $node;
    }
    
    $res = $this->tokparser($val);
    $data = array( $nod->nodeName => array_keys($res['dict']) );
    $this->flagNode($nod, $data, "ntag");
    
    if ($node->nodeType === 3 || $node->nodeType === 4) {
      $node->nodeValue = $res['text'];
    }
    elseif ($node->nodeType === 1) {
      $node->setInnerHTML($res['text']);
    }
  }

  /** 
   * Mark DOM node, saving the resulting structure.
   * @param DOMElement  $node   The DOM node.
   * @param string      $data   Node data.
   * @param string      $type   Node type.
   */
  protected function flagNode( $node, $data, $type ) 
  {
    $node->addDatastack($type, $data);
    $node->addClass($this->config['class_id']);
    $this->saveResource($node->getNodePath(), $data, $type);
  }

  /** 
   * Save DOM node.
   * @param string $key   Node name.
   * @param string $value Node value.
   * @param string $type  Node type.
   */
  protected function saveResource($key, $value, $type) 
  {
    foreach ($value as $k => $v) {
      $entry = &$this->resources[$key][$type][$k];
      if ($entry) {
        $entry[] = $v;
      } else {
        $entry = $v;
      }
    }
  }

  /** 
   * Parse tokenized string.
   * @param string $str   The string.
   * @return string
   */
  protected function tokparser($str) 
  {
    $res = $this->tokparserDeep($str);
    // Process remaining string parts.
    $op = strpos($str, self::TOK_OP, $res['pos']);
    $remain = ($op !== FALSE) ? $op : strlen($str);
    $res['text'] .= substring($str, $res['pos'], $remain);
    return $res;
  }

  /** 
   * Deep parse of tokenized string.
   * @param string  $str     Input string.
   * @param integer $offset  The string.
   * @param array   $dict    Cache structure.
   * @return array
   */
  protected function tokparserDeep( $str, $offset = 0, $dict = array() ) 
  {
    if (strpos($str, self::TOK_OP, $offset) === FALSE) {
      return array(
        'text' => $str,
        'dict' => $dict,
        'pos'  => -1,
      );
    } else {
      $op1  = FALSE;
      $text = "";
      while ( ($op1 = strpos($str, self::TOK_OP, $offset)) !== FALSE) {
        $op1 = strpos($str, self::TOK_OP, $offset);
        $cl  = strpos($str, self::TOK_CL, $offset);
        if ($cl <= $op1) break;
        $text .= substring($str, $offset, $op1);
        $op1 += strlen(self::TOK_OP);
        $op2 = strpos($str, self::TOK_OP, $op1);
        $mid = strpos($str, self::TOK_MID, $op1);
        $prevText = "";
        if ($op2 !== FALSE && $op2 < $mid) {
          $prevText = substring($str, $op1, $op2);
          $res = $this->tokparserDeep($str, $op2, $dict);
          $op1 = $res['pos'];
          $dict += $res['dict']; // Merge preserving keys.
          $mid = strpos($str, self::TOK_MID, $op1);
          $prevText .= $res['text'];
        }
        $cl = strpos($str, self::TOK_CL, $mid + strlen(self::TOK_MID) );
        $key = substring($str, $mid + strlen(self::TOK_MID), $cl);
        $val = $prevText . substring($str, $op1, $mid);
        $dict[$key] = $val;
        $offset = $cl + strlen(self::TOK_CL);
        $text .= $val;
      }
      return array(
        'text' => $text,
        'dict' => $dict,
        'pos'  => $offset,
      );
    }
  }

  /** 
   * Parse element attributes.
   * @param HTMLElement $el Input element.
   */
  protected function parseAttributes( $el ) 
  {
    $na_atts = array("value", "title", "alt", "placeholder");
    foreach ($na_atts as $att) {
      $val = $el->getAttribute($att);
      if ( preg_match_all(self::TOK_RE, $val, $matches) ) {
        $res = $this->tokparser($val);
        $el->setAttribute($att, $res['text']);
        // An element may have multiple localized attributes.
        $data = array( $att => array_keys($res['dict']) );
        $this->flagNode($el, $data, "natt");
      }
    }
  }

  /** 
   * Recursive DOM iteration.
   * @param HTMLElement $node DOM node.
   */
  protected function treeWalker( $node ) 
  {
    if ($node->nodeType === 3 || $node->nodeType === 4) {
      if (strpos($node->nodeValue, self::TOK_OP) !== FALSE) {
        $parent = $node->parentNode;
        if (!$this->isBalanced($node->nodeValue)) {
          // Find the closest balanced node parent.
          while (!$this->isBalanced($parent->nodeValue)) {
            $parent = $parent->parentNode;
          }
          $node = $parent;
        }
        $this->processNimrodCode($node);
      }
    } 
    elseif ($node->nodeType === 1) {
      $this->parseAttributes($node);
      foreach ($node->childNodes as $n) {
        $this->treeWalker($n);
      }
    }
  }

}

