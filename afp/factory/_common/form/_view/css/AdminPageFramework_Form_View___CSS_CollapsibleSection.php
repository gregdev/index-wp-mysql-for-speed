<?php 
/**
	Admin Page Framework v3.8.34 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/index-wp-mysql-for-speed>
	Copyright (c) 2013-2021, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
abstract class Imfs_AdminPageFramework_Form_View___CSS_Base extends Imfs_AdminPageFramework_FrameworkUtility {
    public $aAdded = array();
    public function add($sCSSRules) {
        $this->aAdded[] = $sCSSRules;
    }
    public function get() {
        $_sCSSRules = $this->_get() . PHP_EOL;
        $_sCSSRules.= $this->_getVersionSpecific();
        $_sCSSRules.= implode(PHP_EOL, $this->aAdded);
        return $_sCSSRules;
    }
    protected function _get() {
        return '';
    }
    protected function _getVersionSpecific() {
        return '';
    }
    }
    class Imfs_AdminPageFramework_Form_View___CSS_CollapsibleSection extends Imfs_AdminPageFramework_Form_View___CSS_Base {
        protected function _get() {
            return $this->___getCollapsibleSectionsRules();
        }
        private function ___getCollapsibleSectionsRules() {
            $_sCSSRules = ".index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box, .index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box{font-size:13px;background-color: #fff;padding: 1em 2.6em 1em 2em;border-top: 1px solid #eee;border-bottom: 1px solid #eee;}.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box.collapsed.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.collapsed {border-bottom: 1px solid #dfdfdf;margin-bottom: 1em; }.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box {margin-top: 0;}.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.collapsed {margin-bottom: 0;}#poststuff .index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box.index-wp-mysql-for-speed-section-title > .section-title-outer-container > .section-title-container > .section-title,#poststuff .index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.index-wp-mysql-for-speed-section-title > .section-title-outer-container > .section-title-container > .section-title{font-size: 1em;margin: 0 1em 0 0; }#poststuff .index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.index-wp-mysql-for-speed-section-title > .section-title-outer-container > .section-title-container > fieldset {line-height: 0; }#poststuff .index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.index-wp-mysql-for-speed-section-title > .section-title-outer-container > .section-title-container > fieldset .index-wp-mysql-for-speed-field {margin: 0;padding: 0;}.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title:after,.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title:after {top: 0.88em;top: 34%;right: 15px;}.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title:after,.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title:after {content: '\\f142';}.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title.collapsed:after,.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title.collapsed:after {content: '\\f140';} .index-wp-mysql-for-speed-collapsible-sections-content.index-wp-mysql-for-speed-collapsible-content.accordion-section-content,.index-wp-mysql-for-speed-collapsible-section-content.index-wp-mysql-for-speed-collapsible-content.accordion-section-content,.index-wp-mysql-for-speed-collapsible-sections-content.index-wp-mysql-for-speed-collapsible-content-type-box, .index-wp-mysql-for-speed-collapsible-section-content.index-wp-mysql-for-speed-collapsible-content-type-box{border: 1px solid #dfdfdf;border-top: 0;background-color: #fff;}tbody.index-wp-mysql-for-speed-collapsible-content {display: table-caption; padding: 10px 20px 15px 20px;}tbody.index-wp-mysql-for-speed-collapsible-content.table-caption {display: table-caption; }.index-wp-mysql-for-speed-collapsible-toggle-all-button-container {margin-top: 1em;margin-bottom: 1em;width: 100%;display: table; }.index-wp-mysql-for-speed-collapsible-toggle-all-button.button {height: 36px;line-height: 34px;padding: 0 16px 6px;font-size: 20px;width: auto;}.flipped > .index-wp-mysql-for-speed-collapsible-toggle-all-button.button.dashicons {-moz-transform: scaleY(-1);-webkit-transform: scaleY(-1);transform: scaleY(-1);filter: flipv; }.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .index-wp-mysql-for-speed-repeatable-section-buttons {margin: 0; }.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .index-wp-mysql-for-speed-repeatable-section-buttons.section_title_field_sibling {margin-top: 0;}.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .repeatable-section-button {background: none; line-height: 1.8em; margin: 0;padding: 0;width: 2em;height: 2em;text-align: center;}.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box .section-title-height-fixer, .index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .section-title-height-fixer {height: 100%;width: 0;display: inline-block;vertical-align: middle;}.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box .section-title-outer-container, .index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .section-title-outer-container {width: 88%;display: inline-block;text-align: left;vertical-align: middle;}.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box .index-wp-mysql-for-speed-repeatable-section-buttons-outer-container,.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .index-wp-mysql-for-speed-repeatable-section-buttons-outer-container {width: 10.88%;min-width: 60px; display: inline-block;text-align: right;vertical-align: middle;}@media only screen and ( max-width: 782px ) {.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box .section-title-outer-container, .index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .section-title-outer-container {width: 87.8%;}}.accordion-section-content.index-wp-mysql-for-speed-collapsible-content-type-button {background-color: transparent;}.index-wp-mysql-for-speed-collapsible-button {color: #888;margin-right: 0.4em;font-size: 0.8em;}.index-wp-mysql-for-speed-collapsible-button-collapse {display: inline;} .collapsed .index-wp-mysql-for-speed-collapsible-button-collapse {display: none;}.index-wp-mysql-for-speed-collapsible-button-expand {display: none;}.collapsed .index-wp-mysql-for-speed-collapsible-button-expand {display: inline;}.index-wp-mysql-for-speed-collapsible-section-title .index-wp-mysql-for-speed-fields {display: inline;vertical-align: middle; line-height: 1em; }.index-wp-mysql-for-speed-collapsible-section-title .index-wp-mysql-for-speed-field {float: none;}.index-wp-mysql-for-speed-collapsible-section-title .index-wp-mysql-for-speed-fieldset {display: inline;margin-right: 1em;vertical-align: middle; }#poststuff .index-wp-mysql-for-speed-collapsible-title.index-wp-mysql-for-speed-collapsible-section-title .section-title-container.has-fields .section-title{width: auto;display: inline-block;margin: 0 1em 0 0.4em;vertical-align: middle;}";
            $_sCSSRules.= $this->___getForWP38OrBelow();
            $_sCSSRules.= $this->___getForWP53OrAbove();
            return $_sCSSRules;
        }
        private function ___getForWP53OrAbove() {
            if (version_compare($GLOBALS['wp_version'], '5.3', '<')) {
                return '';
            }
            return ".index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .repeatable-section-button {width: 32px;height: 32px;margin: 0 0.1em;}.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .repeatable-section-button .dashicons {height: 100%;}";
        }
        private function ___getForWP38OrBelow() {
            if (version_compare($GLOBALS['wp_version'], '3.8', '>=')) {
                return '';
            }
            return ".index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title:after,.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title:after {content: '';top: 18px;}.index-wp-mysql-for-speed-collapsible-sections-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title.collapsed:after,.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box.accordion-section-title.collapsed:after {content: '';} .index-wp-mysql-for-speed-collapsible-toggle-all-button.button {font-size: 1em;}.index-wp-mysql-for-speed-collapsible-section-title.index-wp-mysql-for-speed-collapsible-type-box .index-wp-mysql-for-speed-repeatable-section-buttons {top: -8px;}";
        }
    }
    