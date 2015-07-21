<?php
/**
 * Created by IntelliJ IDEA.
 * User: platon
 * Date: 25.03.15
 * Time: 12:25
 */

namespace Heonozis\AR;

use DOMDocument;

require_once('Html/simple_html_dom.php');


/**
 * Class HtmlAr
 * @package Heonozis\AR
 */
class HtmlAr {

    /**
     * Extract action_url from HTML form given
     *
     * @param $input_html
     * @return string
     */
    public static function extractActionUrl($input_html){
        $dom = new DOMDocument('1.0');
        $dom->loadHTML($input_html);

        $form = $dom->getElementsByTagName('form');
        $action_url = $form->item(0)->attributes->getNamedItem('action')->nodeValue;
        return $action_url;
    }

    /**
     * Extract fields to array from HTML form given
     *
     * @param $input_html
     * @return array
     */
    public static function extractInputs($input_html) {

        $fields = array();

        try {
            $dom = new DOMDocument('1.0');
            $dom->loadHTML($input_html);
            $nodes = $dom->getElementsByTagName('input');

            foreach ($nodes as $node) {
                if ($node->hasAttributes()) {
                    $attributes = array();
                    foreach ($node->attributes as $attribute) {
                        $attributes[$attribute->nodeName] = $attribute->nodeValue;
                    }
                    $fields[] = $attributes;
                }
            };
        }catch (Exception $e) {
            return redirect()->back()->with('error', 'Woops! There was an error... Invalid HTML');

        }

        return $fields;
    }

    /**
     * Save extracted fields to DB (delete previous)
     *
     * @param $fields
     * @return mixed
     */
    public static function saveInputs($fields) {
        try {

            HtmlInputs::truncate();
            foreach ($fields as $field) {
                if ($field['type'] != 'submit') {

                    $value = null;
                    if (isset($field['value'])) {
                        $value = $field['value'];
                    }
                    HtmlInputs::create(array(
                        'name' => $field['name'],
                        'type' => $field['type'],
                        'value' => $value,
                    ));
                }
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Woops! There was an error... Check your inputs');

        }

    }

    /**
     * Get AWeber settings from DB
     *
     * You can specify name of settings (if null - all settings)
     * @param null $name
     * @return array
     */
    public static function getSettings($name = null) {

        return HtmlSettings::getSettings($name);

    }

    /**
     * Save array of settings to DB
     *
     * @param $array
     */
    public static function saveSettings($array) {

        return HtmlSettings::saveSettings($array);

    }

    /**
     * Render inputs from DB
     *
     * @return string
     */
    public static function renderInputs() {

        $fields = HtmlInputs::all();
        $html = '';
        foreach ($fields as $field) {
            if($field->name != 'submit' && $field->type != 'submit'){
            $html .= (($field->type != 'hidden')?"<label for=\"$field->name\">$field->name</label>\n ":"").
                "<input type=\"$field->type\" name=\"$field->name\" value=\"$field->value\">\n";
            }
        }
        return $html;

    }

    /**
     * Send CURL request to action_url with params array $input
     *
     * @param $input
     * @return mixed
     */
    public static function subscribe($input) {


            $url = HtmlAr::getSettings('action_url');
            $postData = '';
            //create name value pairs seperated by &
            foreach ($input as $k => $v) {
                if ($k != '_token') {
                    $postData .= $k . '=' . $v . '&';
                }
            }

            rtrim($postData, '&');

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, count($postData));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $output = curl_exec($ch);

            curl_close($ch);
            return $output;

    }

}