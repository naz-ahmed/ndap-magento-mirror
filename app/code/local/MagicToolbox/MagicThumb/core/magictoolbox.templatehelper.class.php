<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'magictoolbox.attributeshelper.class.php');

if(!in_array('MagicToolboxTemplateHelper', get_declared_classes())) {

    class MagicToolboxTemplateHelper {

        static $path;
        static $options;

        static function setPath($path) {
            self::$path = $path;
        }

        static function setOptions($options) {
            self::$options = $options;
        }

        static function img($options) {
            extract($options);
            return '<img />';
        }

        static function prepareMagicScrollClass() {
            $magicscroll = self::$options->checkValue('magicscroll', 'Yes') ? ' MagicScroll' : '';
            if(!empty($magicscroll)) {
                $additionalClasses = array(
                    'default' => '',
                    'with-borders' => 'msborder'
                );
                $additionalClass = $additionalClasses[self::$options->getValue('scroll-style')];
                if(!empty($additionalClass)) $magicscroll = $magicscroll . ' ' . $additionalClass;
            }
            return $magicscroll;
        }

        static function render($name, $options = null) {
            if(func_num_args() == 1) {
                $options = $name;
                $name = self::$options->getValue('template');
            }
            extract($options);

            $magicscroll = self::prepareMagicScrollClass();

            ob_start();
            require(self::$path . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9_]/is', '-', $name) . '.tpl.php');
            return str_replace("\n", ' ', str_replace("\r", ' ', ob_get_clean()));
        }

        static function renderStyle($css){
            $style = array();

            foreach($css as $attr => $value){
                $style[] = "$attr: $value";
            }
            return join('; ',$style);
        }

    }

}
?>
