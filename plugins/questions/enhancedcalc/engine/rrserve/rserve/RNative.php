<?php
/**
 * Rserve native array wrapper
 * @author Clément Turbelin
 * From Rserve java Client & php Client
 */
 
/**
* php Native array with attributes feature
*/
class Rserve_RNative implements ArrayAccess {
    
    /**
     * @param mixed[] $data
     * @param mixed[] $attributes
     */
    public function __construct(
        /**
         * @var array Data
         */
        private $data,
        /**
         * @var array Attributes
         */
        private $attr = NULL
    )
    {
    }
    
    public function getAttr($name) {
        return $this->attr[$name] ?? NULL;
    }
    
    public function hasAttr($name) {
        return (isset($this->attr[$name])) ? TRUE : FALSE;
    }
    
    public function getAttributes() {
        return $this->attr;
    }
   
    // ArrayAccess Implementation
    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }
    
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
    
    public function offsetGet($offset) {
        return $this->data[$offset] ?? null;
    }
    
}