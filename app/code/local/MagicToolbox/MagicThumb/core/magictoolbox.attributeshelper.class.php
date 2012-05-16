<?php

if(!in_array('MagicToolboxAttributesHelper', get_declared_classes())) {

    class MagicToolboxAttributesHelper {

        function check($attributes) {
            foreach($attributes as $name => $value) {
                $value = htmlspecialchars(htmlspecialchars_decode($value));
                switch($key) {
                    case 'width':
                    case 'height':
                        $value = intval($value) . 'px';
                        break;
                }
            }
        }

        function output($attributes, $autoCheck = true) {
            if($autoCheck) {
                $attributes = self::check($attributes);
            }
            $output = array();
            foreach($attributes as $name => $value) {
                $output[] = $name . '"' . $value . '"';
            }
            return implode(' ', $output);
        }

    }

}

?>
