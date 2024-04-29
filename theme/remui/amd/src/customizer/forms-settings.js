// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Theme customizer global-body js
 *
 * @package   theme_remui/customizer
 * @copyright (c) 2021 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Gourav G
 */

define('theme_remui/customizer/forms-settings', ['jquery', './utils'], function($, Utils) {

    var SELECTOR = {
        BASE: 'formselementdesign',
        RADIO: 'cust-sele-input',
    };

    var choosenOption = '';
 
    /**
     * Get site body content.
     * @return {string} site body content
     */
    function getContent(tag, config) {
        var content = `body:not(.default-formstyle) div:not(.edwiserform-root-container) fieldset{margin:'[[conf:edwf-fieldset-margin]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) fieldset.collapsible{box-shadow:'[[conf:edwf-fieldset-box-shadow]]';border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) fieldset.collapsible .ftoggler{border-top-left-radius:'[[conf:edwf-borderrad-tl]]';border-top-right-radius:'[[conf:edwf-borderrad-tr]]';background:'[[conf:edwf-fieldset-background-color]]';border-bottom:'[[conf:edwf-fieldset-ftoggler-b-bottom]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) fieldset.collapsible .fcontainer{border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]';border:'[[conf:edwf-fieldset-border]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) fieldset.collapsible.collapsed .ftoggler{border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]';border-top-left-radius:'[[conf:edwf-borderrad-tl]]';border-top-right-radius:'[[conf:edwf-borderrad-tr]]';background:'[[conf:edwf-fieldset-background-color]]';border:none}body:not(.default-formstyle) div:not(.edwiserform-root-container) .collapsible-actions{margin-bottom:10px}body:not(.default-formstyle) div:not(.edwiserform-root-container) .felement[data-fieldtype=modgrade]{border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]';border-top-left-radius:'[[conf:edwf-borderrad-tl]]';border-top-right-radius:'[[conf:edwf-borderrad-tr]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .felement .custom-select,body:not(.default-formstyle) div:not(.edwiserform-root-container) .felement .form-control,body:not(.default-formstyle) div:not(.edwiserform-root-container) .felement input[type=text]{width:'[[conf:edw-input-text-width]]';min-height:'[[conf:edwf-input-text-height]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .felement .custom-select[data-passwordunmask=edit],body:not(.default-formstyle) div:not(.edwiserform-root-container) .felement .form-control[data-passwordunmask=edit],body:not(.default-formstyle) div:not(.edwiserform-root-container) .felement input[type=text][data-passwordunmask=edit]{width:initial!important}body:not(.default-formstyle) div:not(.edwiserform-root-container) .felement [data-passwordunmask=wrapper] [data-passwordunmask=editor] input{width:initial!important}body:not(.default-formstyle) div:not(.edwiserform-root-container) .form-check input[type=checkbox]+span{margin-left:5px}body:not(.default-formstyle) div:not(.edwiserform-root-container) .form-control,body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=text]{background-color:'[[conf:edw-input-text-bgcolor]]';color:'[[conf:edw-input-text-textcolor]]';border-top:'[[conf:edwf-borderwidth-top]]' solid '[[conf:edwf-bordercolor]]';border-right:'[[conf:edwf-borderwidth-right]]' solid '[[conf:edwf-bordercolor]]';border-bottom:'[[conf:edwf-borderwidth-bottom]]' solid '[[conf:edwf-bordercolor]]';border-left:'[[conf:edwf-borderwidth-left]]' solid '[[conf:edwf-bordercolor]]';min-height:'[[conf:edwf-input-text-height]]';font-size:'[[conf:edwf-input-text-fontsize]]';box-shadow:'[[conf:edwf-shadow-hoffset]]' '[[conf:edwf-shadow-voffset]]' '[[conf:edwf-shadow-blur]]' '[[conf:edwf-shadow-spread]]' '[[conf:edwf-shadow-color]]';padding:'[[conf:edwf-pad-top]]' '[[conf:edwf-pad-right]]' '[[conf:edwf-pad-bottom]]' '[[conf:edwf-pad-left]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .form-control:not(.editor_atto_content),body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=text]:not(.editor_atto_content){border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]';border-top-left-radius:'[[conf:edwf-borderrad-tl]]';border-top-right-radius:'[[conf:edwf-borderrad-tr]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .form-control::-webkit-scrollbar-track,body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=text]::-webkit-scrollbar-track{background:0 0;margin:'[[conf:edwf-borderrad-bl]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .form-control:focus,body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=text]:focus{background-color:#fff;border-top:'[[conf:edwf-input-focus-tborder]]';border-bottom:'[[conf:edwf-input-focus-bborder]]';border-left:'[[conf:edwf-input-focus-lborder]]';border-right:'[[conf:edwf-input-focus-rborder]]';box-shadow:'[[conf:edwf-input-focus-shadow]]';outline:0}body:not(.default-formstyle) div:not(.edwiserform-root-container) .form-control[disabled],body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=text][disabled]{color:#cad2db}body:not(.default-formstyle) div:not(.edwiserform-root-container) textarea.form-control{padding:'[[conf:edwf-pad-top]]' '[[conf:edwf-pad-right]]' '[[conf:edwf-pad-bottom]]' '[[conf:edwf-pad-left]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) textarea.form-control::-webkit-scrollbar-track{background:0 0;margin:'[[conf:edwf-borderrad-bl]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .custom-select{display:inline-block;height:'[[conf:edwf-input-text-height]]';padding:'[[conf:edwf-pad-top]]' '[[conf:edwf-pad-right]]' '[[conf:edwf-pad-bottom]]' '[[conf:edwf-pad-left]]';font-weight:400;line-height:1.5;font-size:'[[conf:edwf-input-text-fontsize]]';color:'[[conf:edw-input-text-textcolor]]';vertical-align:middle;background-color:'[[conf:edw-input-text-bgcolor]]';border-top:'[[conf:edwf-borderwidth-top]]' solid '[[conf:edwf-bordercolor]]';border-right:'[[conf:edwf-borderwidth-right]]' solid '[[conf:edwf-bordercolor]]';border-bottom:'[[conf:edwf-borderwidth-bottom]]' solid '[[conf:edwf-bordercolor]]';border-left:'[[conf:edwf-borderwidth-left]]' solid '[[conf:edwf-bordercolor]]';border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]';border-top-left-radius:'[[conf:edwf-borderrad-tl]]';border-top-right-radius:'[[conf:edwf-borderrad-tr]]';box-shadow:'[[conf:edwf-shadow-hoffset]]' '[[conf:edwf-shadow-voffset]]' '[[conf:edwf-shadow-blur]]' '[[conf:edwf-shadow-spread]]' '[[conf:edwf-shadow-color]]';-webkit-appearance:none;-moz-appearance:none;appearance:none}body:not(.default-formstyle) div:not(.edwiserform-root-container) .custom-select:focus{box-shadow:'[[conf:edwf-input-focus-shadow]]';border-top:'[[conf:edwf-input-focus-tborder]]';border-bottom:'[[conf:edwf-input-focus-bborder]]';border-left:'[[conf:edwf-input-focus-lborder]]';border-right:'[[conf:edwf-input-focus-rborder]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=checkbox]:checked{width:20px;height:20px;border:'[[conf:edwf-checkbox-borderwidth]]' solid '[[conf:edwf-checkbox-bordercolor]]';background-color:'[[conf:edwf-checkbox-backcolor]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=checkbox]{width:20px;height:20px;border:'[[conf:edwf-checkbox-borderwidth]]' solid '[[conf:edwf-checkbox-bordercolor]]';justify-content:center;align-items:center;appearance:none;display:inline-flex}body:not(.default-formstyle) div:not(.edwiserform-root-container) .efb-switch input[type=checkbox]{display:none!important}body:not(.default-formstyle) div:not(.edwiserform-root-container) .checkbox .form-check label{margin:.2rem .5rem}body:not(.default-formstyle) div:not(.edwiserform-root-container) #categoryquestions .checkbox{padding-right:20px}body:not(.default-formstyle) div:not(.edwiserform-root-container) #categoryquestions .creatorname{padding-left:1rem}body:not(.default-formstyle) div:not(.edwiserform-root-container) .custom-control-input:after,body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=checkbox]:after{content:'\\2714';font-size:16px;color:#fff;width:20px;height:20px;justify-content:center;align-items:center;display:flex}body:not(.default-formstyle) div:not(.edwiserform-root-container) .custom-control-input:checked:after,body:not(.default-formstyle) div:not(.edwiserform-root-container) input[type=checkbox]:checked:after{content:'\\2714';font-size:16px;color:#fff;width:20px;height:20px;justify-content:center;align-items:center;display:flex}body:not(.default-formstyle) div:not(.edwiserform-root-container) .custom-control.custom-checkbox .custom-control-input:checked~.custom-control-label::after{background-image:none}body:not(.default-formstyle) div:not(.edwiserform-root-container) .custom-control.custom-checkbox .custom-control-input{opacity:1;z-index:1!important}body:not(.default-formstyle) div:not(.edwiserform-root-container) .custom-control.custom-checkbox .custom-control-label :after,body:not(.default-formstyle) div:not(.edwiserform-root-container) .custom-control.custom-checkbox .custom-control-label:before{display:none}body:not(.default-formstyle) div:not(.edwiserform-root-container) .checkbox-inline{display:inline-flex}body:not(.default-formstyle) div:not(.edwiserform-root-container) .checkbox-inline input[type=checkbox]{margin-right:5px}body:not(.default-formstyle) div:not(.edwiserform-root-container) .form-inline .form-check,body:not(.default-formstyle) div:not(.edwiserform-root-container) .form-inline .form-check-inline{display:inline-flex!important}body:not(.default-formstyle) div:not(.edwiserform-root-container) .editor_atto div.editor_atto_toolbar{border:'[[conf:edwf-atto-borderwidth]]' solid '[[conf:edwf-atto-bordercolor]]';border-top-left-radius:'[[conf:edwf-borderrad-tl]]';border-top-right-radius:'[[conf:edwf-borderrad-tr]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .editor_atto .editor_atto_content,body:not(.default-formstyle) div:not(.edwiserform-root-container) .editor_atto div.editor_atto_content_wrap{border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .editor_atto:focus div.editor_atto_toolbar{border-top:'[[conf:edwf-input-focus-tborder]]';border-bottom:'[[conf:edwf-input-focus-bborder]]';border-left:'[[conf:edwf-input-focus-lborder]]';border-right:'[[conf:edwf-input-focus-rborder]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .filemanager .fp-content{border-top:'[[conf:edwf-borderwidth-top]]' solid '[[conf:edwf-bordercolor]]';border-right:'[[conf:edwf-borderwidth-right]]' solid '[[conf:edwf-bordercolor]]';border-bottom:'[[conf:edwf-borderwidth-bottom]]' solid '[[conf:edwf-bordercolor]]';border-left:'[[conf:edwf-borderwidth-left]]' solid '[[conf:edwf-bordercolor]]';box-shadow:'[[conf:edwf-shadow-hoffset]]' '[[conf:edwf-shadow-voffset]]' '[[conf:edwf-shadow-blur]]' '[[conf:edwf-shadow-spread]]' '[[conf:edwf-shadow-color]]';border-top-left-radius:'[[conf:edwf-borderrad-tl]]';border-top-right-radius:'[[conf:edwf-borderrad-tr]]';border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .filemanager .filemanager-container{border-top-left-radius:'[[conf:edwf-borderrad-tl]]';border-top-right-radius:'[[conf:edwf-borderrad-tr]]';border-bottom-left-radius:'[[conf:edwf-borderrad-bl]]';border-bottom-right-radius:'[[conf:edwf-borderrad-br]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .filemanager .filemanager-container{border-top:'[[conf:edwf-borderwidth-top]]' solid '[[conf:edwf-bordercolor]]';border-right:'[[conf:edwf-borderwidth-right]]' solid '[[conf:edwf-bordercolor]]';border-bottom:'[[conf:edwf-borderwidth-bottom]]' solid '[[conf:edwf-bordercolor]]';border-left:'[[conf:edwf-borderwidth-left]]' solid '[[conf:edwf-bordercolor]]';box-shadow:'[[conf:edwf-shadow-hoffset]]' '[[conf:edwf-shadow-voffset]]' '[[conf:edwf-shadow-blur]]' '[[conf:edwf-shadow-spread]]' '[[conf:edwf-shadow-color]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .filemanager .fm-empty-container{border-color:'[[conf:edwf-bordercolor]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .dropdown .dropdown-toggle:not(.btn-round):not(.qnbutton){padding:'[[conf:edwf-btn-hpad]]' '[[conf:edwf-btn-vpad]]';font-size:'[[conf:edwf-btn-fontsize]]';border-radius:'[[conf:edwf-btn-borderrad]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .bootstrap-select .dropdown-toggle{padding:'[[conf:edwf-btn-hpad]]' 1.6rem '[[conf:edwf-btn-hpad]]' '[[conf:edwf-btn-vpad]]';font-size:'[[conf:edwf-btn-fontsize]]';border-radius:'[[conf:edwf-btn-borderrad]]'}body:not(.default-formstyle) div:not(.edwiserform-root-container) .is-invalid{border-top:'[[conf:edwf-borderwidth-top]]' solid red!important;border-right:'[[conf:edwf-borderwidth-right]]' solid red!important;border-bottom:'[[conf:edwf-borderwidth-bottom]]' solid red!important;border-left:'[[conf:edwf-borderwidth-left]]' solid red!important}`;
        
        for (var i = 0; i < tag.length; i++) {
            content = content.split(tag[i]).join(config[i]); // This tweak replaces every instance in the string.
        }
        return content;
    }

    /**
     * Apply settings.
     */
    function apply() {
        choosenOption = $(`[name='${SELECTOR.BASE}']`).val();
        if (choosenOption == 'default') {
            $(Utils.getDocument()).find('body').addClass('default-formstyle');
            Utils.putStyle(SELECTOR.BASE, "");
            return;
        }
        $(Utils.getDocument()).find('body').removeClass('default-formstyle');
        Utils.putStyle(SELECTOR.BASE, getContent(formssettingtag, formssettings[choosenOption]));
    }

    // function applyFormGroupStyle() {
    //     removeExistingClasses();
        
    //     choosenOption = $(`input.cust-sele-input[name="radio_${SELECTOR2.BASE}"]:checked`).attr("data-value");
    //     if (choosenOption != 'default') {
    //         $(Utils.getDocument()).find('body').addClass(choosenOption);
    //     }

    //     $(`[name='${SELECTOR2.BASE}']`).val(choosenOption); // set the value of select element
    // }


    // function removeExistingClasses () {
    //     $(Utils.getDocument()).find('body').removeClass(`${SELECTOR2.STYLE1}`);
    // }

    /**
     * Initialize events.
     */
    function init() {
        choosenOption = $(`[name='${SELECTOR.BASE}']`).val();
        $(`[name='${SELECTOR.BASE}']`).bind('input', function() {
           apply();
        });

        $(`ul#${SELECTOR.BASE} .${SELECTOR.RADIO}`).bind('click', function() {
            choosenOption = $(`input.cust-sele-input[name="radio_${SELECTOR.BASE}"]:checked`).attr("data-value");
            $(`[name='${SELECTOR.BASE}']`).val(choosenOption);
            apply();
        });
        
        // $(`ul#${SELECTOR2.BASE} .${SELECTOR2.RADIO}`).bind('click', function() {
        //     applyFormGroupStyle();
        // });
    }

    return {
        init: init,
        apply: apply
    };
});
