<?php
/**
 * Nimrod -- Prioritize gettext messages by actual importance. 
 * @author Luis A. Leiva and Vicent Alabau
 * @license http://opensource.org/licenses/GPL-3.0 GNU Public License
 */
 
/**
 * ExtDOMDocument class.
 * Adds functionality to PHP's built-in DOMDocument class.
 */
class ExtDOMDocument extends DOMDocument
{
  /**
   * Retrieve all nodes in the document according to a class name.
   * @param string $cls The class name.
   * @return DOMNodeList
   */
  public function getElementsByClassName($cls)
  {
    $res = $this->createDocumentFragment();
    if ( $this->hasChildNodes() ) {
      $els = $this->getElementsByTagName("*");
      foreach ($els as $node) {
        if ( $node->hasClass($cls) ) {
          $res->appendChild($node);
        }
      }
    }
    return $res->childNodes;
  }
  
}

/**
 * ExtDOMElement class.
 * Adds functionality to PHP's built-in DOMElement class.
 */
class ExtDOMElement extends DOMElement 
{
  /**
   * Retrieve all child nodes in the node according to a class name.
   * @param string $cls The class name.
   * @return DOMNodeList
   */
  public function getElementsByClassName($cls)
  {
    $res = $this->ownerDocument->createDocumentFragment();
    if ( $this->hasChildNodes() ) {
      $els = $this->getElementsByTagName("*");
      foreach ($els as $node) {
        if ( $node->hasClass($cls) ) {
          $res->appendChild($node);
        }
      }
    }
    return $res->childNodes;
  }

  /**
   * Retrieve the innerHTML of the node.
   * @return string
   */
  public function getInnerHTML() 
  {
    $innerHTML = "";
    foreach ($this->childNodes as $child) {
      $innerHTML .= $child->ownerDocument->saveXML( $child ); 
    } 
    return $innerHTML;  
  }

 /**
  * Set the innerHTML of the node.
  * @param string $value HTML content.
  */
  public function setInnerHTML( $value ) 
  {
    // See http://www.keyvan.net/2010/07/javascript-like-innerhtml-access-in-php/
    for ($x = $this->childNodes->length-1; $x >= 0; $x--) {
      $this->removeChild( $this->childNodes->item($x) );
    }
    if (!empty($value)) {
      $f = $this->ownerDocument->createDocumentFragment();
      // appendXML() expects well-formed markup.
      $result = @$f->appendXML( $value );
      if ($result) {
        if ( $f->hasChildNodes() ) $this->appendChild($f);
      } else {
        $f = new DOMDocument();
        $value = mb_convert_encoding( $value, 'HTML-ENTITIES', 'UTF-8' );
        $result = @$f->loadHTML( '<htmlfragment>'.$value.'</htmlfragment>' );
        if ($result) {
          $import = $f->getElementsByTagName('htmlfragment')->item(0);
          foreach ($import->childNodes as $child) {
            $importedNode = $this->ownerDocument->importNode( $child, TRUE );
            $this->appendChild( $importedNode );
          }
        }
      }
    }
  }

 /**
  * Detect if the the node has the specified class name.
  * @param string $cls The class name.
  * @return bool TRUE on success, FALSE otherwise.
  */
  public function hasClass($cls) 
  {
    if ( $this->hasAttribute('class') ) {
      $className = $this->getAttribute('class');
      if ( $className ) {
        $classes = explode( " ", $className );
        return in_array( $cls, $classes );
      }
    }
    return FALSE;
  }

  /**
   * Add a class to the node.
   * @param string $cls The class name.
   */
  public function addClass($cls) 
  {
    if ($this->hasAttribute('class')) {
      // Ensure that we are not adding the same class.
      if ( !$this->hasClass($cls) ) {
        $className = $this->getAttribute('class');
        $this->setAttribute('class', $className . " " . $cls);
      }
    } else {
      $this->setAttribute('class', $cls);
    }
  }

  /**
   * Retrieve the data- attributes from the node.
   * @return array
   */
  public function getDatasets() 
  {
    $data = array();
    if ( $this->hasAttributes() ) {
      foreach ($this->attributes as $attr) {
        $prefix = "data-";
        $attrib = $attr->nodeName;
        if ( strpos($attrib, $prefix) === 0 ) {
          // Don't put "data-" in attribute name.
          $attrName = substr( $attrib, strlen($prefix) );
          $data[$attrName] = $this->getAttribute( $attrib );
        }
      }
    }
    return $data;
  }

  /**
   * Add a key/value pair to the dataset of the node.
   * @param string $key   The item name.
   * @param string $value The item value.
   */
  public function addDatastack($key, $value) 
  {
    $data = $this->getDatasets();
    $item = $data[$key];
    if ($item) {
      $item = json_decode($item, TRUE);
      $item[] = $value;
    } else {
      $item = array($value);
    }
    $this->setAttribute( "data-" . $key, json_encode($item) );
  }

  /**
   * Retrieve the outerHTML of the node.
   * @return string
   */
  public function getOuterHTML()
  {
    $doc = new DOMDocument();
    $doc->appendChild( $doc->importNode($this, TRUE) );
    $content = $doc->saveHTML();
    return $content;
  }
    
}

