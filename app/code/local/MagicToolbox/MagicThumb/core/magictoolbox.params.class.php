<?php

@ini_set('memory_limit', '512M');

if(!function_exists('lcfirst')) {
    function lcfirst($str) {
        $str[0] = strtolower($str[0]);
        return $str;
    }
}

if(!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($string,$style=ENT_COMPAT) {
        $translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS,$style));
        if($style === ENT_QUOTES){ $translation['&#039;'] = '\''; }
        return strtr($string,$translation);
    }
}

if(!function_exists('file_put_contents')) {
    function file_put_contents($filename, $data) {
        $fp = fopen($filename, 'w+');
        if ($fp) {
            fwrite($fp, $data);
            fclose($fp);
        }
    }
}

if(!in_array('MagicToolboxParams', get_declared_classes())) {

    class MagicToolboxParams {
        var $params;
        var $tool;

        function MagicToolboxParams($tool = '') {
            $this->params = array();
            $this->tool = $tool;
        }

        function append($id, $value) {
            if(!is_array($value)) {
                $this->params[$id]["value"] = $value;
            } else {
                foreach($value as $k => $v) {
                    $this->params[$id][$k] = $v;
                }
            }
        }

        function appendArray($params) {
            foreach($params as $key => $param) {
                $this->append($key, $param);
            }
        }

        function exists($id) {
            return isset($this->params[$id]);
        }

        function get($id) {
            return isset($this->params[$id]) ? $this->params[$id] : false;
        }

        function set($id, $value) {
            $this->params[$id]['value'] = $value;
        }

        function getValue($id) {
            $p = $this->get($id);
            if($p) {
                return isset($p['value']) ? $p['value'] : $p['default'];
            } else return false;
        }

        function getValues($id) {
            $p = $this->get($id);
            if($p) {
                return isset($p['values']) ? $p['values'] : array($p['default']);
            } else return false;
        }

        function checkValue($id, $value = false) {
            if(!is_array($value)) $value = array($value);
            return in_array(strtolower($this->getValue($id)), array_map('strtolower', $value));
        }

        function getArray() {
            return $this->params;
        }

        function getNames() {
            return array_keys($this->params);
        }

        function clear() {
            $this->params = array();
        }

        function loadINI($file) {
            if(!file_exists($file)) return false;
            $ini = file($file);
            foreach($ini as $num=> $line) {
                $line = trim($line);
                if(empty($line) || in_array(substr($line, 0, 1), array(';','#'))) continue;
                $cur = explode('=', $line, 2);
                if(count($cur) != 2) {
                    error_log("WARNING: You have errors in you INI file ({$file}) on line " . ($num+1) . "!");
                    continue;
                }
                $this->set(trim($cur[0]), trim($cur[1]));
            }
            return true;
        }

        function updateINI($file, $params = null) {
            if(!file_exists($file)) return false;
            $iniLines = file($file);
            $iniParams = array();
            foreach($iniLines as $num => $line) {
                $line = trim($line);
                if(empty($line) || in_array(substr($line, 0, 1), array(';','#'))) continue;
                list($id, $value) = explode('=', $line, 2);
                $id = trim($id);
                $iniParams[$id] = $num;
            }
            if($params === null) $params = array_keys($this->params);

            foreach($params as $id) {
                if(isset($iniParams[$id])) {
                    $iniLines[$iniParams[$id]] = $id . ' = ' . $this->getValue($id) . "\n";
                } else {
                    $line = "\n";
                    if(isset($this->params[$id]['label'])) {
                        $line .= '# ' . $this->params[$id]['label'] . "\n";
                    }
                    if(isset($this->params[$id]['description'])) {
                        $line .= '# ' . $this->params[$id]['description'] . "\n";
                    }
                    if(isset($this->params[$id]['values'])) {
                        $line .= '# allowed values: ';
                        for($i = 0, $l = count($this->params[$id]['values']); $i < $l; $i++) {
                            $line .= $this->params[$id]['values'][$i];
                            if($i < $l-1) $line .= ', ';
                        }
                        $line .= "\n";
                    }
                    $iniLines[] = $line . $id . ' = ' . $this->getValue($id) . "\n";
                }
            }
            file_put_contents($file, implode("", $iniLines));

            return true;
        }

        function unserialize($str) {
            preg_match_all("/([a-z_\-]+):([^;]*)/ui", $str, $matches);

            $this->appendArray(array_combine($matches[1], $matches[2]));
        }

        function serialize() {
            if(!$config || !count($config)) return false;

            $str = array();
            foreach($this->getArray() as $p) {
                if(!isset($p['scope']) || empty($p['scope']) || $p['scope'] != $this->tool) continue;
                $str[]= $p['id'] . ':' . $p['value'];
            }
            return join(';',$str);
        }

    }

}
?>
