<?php

namespace lkcodes\Mycode\other;

/**
 * Class Parser
 * @package marcocesarato\markdown
 * @author Marco Cesarato <cesarato.developer@gmail.com>
 * @copyright Copyright (c) 2018
 * @license http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link https://github.com/marcocesarato/PHP-Class-Markdown-Docs
 * @version 0.1.11
 */
class ClassParser {
  private $classes = array();
  private $extends = array();
  private $implements = array();

  const STATE_CLASS_HEAD = 100001;
  const STATE_FUNCTION_HEAD = 100002;

    /**
     * @return array
     */
    public function getClasses() {
    return $this->classes;
  }

    /**
     * @param $interface
     * @return array
     */
    public function getClassesImplementing($interface) {
    $implementers = array();
    if (isset($this->implements[$interface])) {
      foreach($this->implements[$interface] as $name) {
        $implementers[$name] = $this->classes[$name];
      }
    }
    return $implementers;
  }

    /**
     * @param $class
     * @return array
     */
    public function getClassesExtending($class) {
    $extenders = array();
    if (isset($this->extends[$class])) {
      foreach($this->extends[$class] as $name) {
        $extenders[$name] = $this->classes[$name];
      }
    }
    return $extenders;
  }

    /**
     * @param $file
     */
    public function parse($file) {
    $file = realpath($file);
    $tokens = token_get_all(file_get_contents($file));
    $classes = array();

    $si = NULL;
    $depth = 0;
    $mod = array();
    $doc = NULL;
    $state = NULL;
    $line = NULL;

    foreach ($tokens as $idx => &$token) {
      if (is_array($token)) {
        switch ($token[0]) {
          case T_DOC_COMMENT:
            $doc = $token[1];
            break;
          case T_PUBLIC:
          case T_PRIVATE:
          case T_STATIC:
          case T_ABSTRACT:
          case T_PROTECTED:
            $mod[] = $token[1];
            break;
          case T_CLASS:
          case T_FUNCTION:
            $state = $token[0];
            $line = $token[2];
            break;
          case T_EXTENDS:
          case T_IMPLEMENTS:
            switch ($state) {
              case self::STATE_CLASS_HEAD:
              case T_EXTENDS:
                $state = $token[0];
                break;
            }
            break;
          case T_STRING:
            switch ($state) {
              case T_CLASS:
                $state = self::STATE_CLASS_HEAD;
                $si = $token[1];
                $classes[] = array('name' => $token[1], 'modifiers' => $mod, 'line' => $line, 'doc' => $doc);
                break;
              case T_FUNCTION:
                $state = self::STATE_FUNCTION_HEAD;
                $clsc = count($classes);
                if ($depth>0 && $clsc) {
                  $classes[$clsc-1]['functions'][$token[1]] = array('modifiers' => $mod, 'line' => $line, 'doc' => $doc);
                }
                break;
              case T_IMPLEMENTS:
              case T_EXTENDS:
                $clsc = count($classes);
                $classes[$clsc-1][$state==T_IMPLEMENTS ? 'implements' : 'extends'][] = $token[1];
                break;
            }
            break;
        }
      }
      else {
        switch ($token) {
          case '{':
            $depth++;
            break;
          case '}':
            $depth--;
            break;
        }

        switch ($token) {
          case '{':
          case '}':
          case ';':
            $state = 0;
            $doc = NULL;
            $mod = array();
            break;
        }
      }
    }

    foreach ($classes as $class) {
      $class['file'] = $file;
      $this->classes[$class['name']] = $class;

      if (!empty($class['implements'])) {
        foreach ($class['implements'] as $name) {
          $this->implements[$name][] = $class['name'];
        }
      }

      if (!empty($class['extends'])) {
        foreach ($class['extends'] as $name) {
          $this->extends[$name][] = $class['name'];
        }
      }
    }
  }
}
