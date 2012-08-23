<?php

/**
 * FormHelper class.
 * 
 * Helps with forms.
 * 
 * @package Magister
 * @subpackage Helpers 
 */
class FormHelper {

    /**
     * SelectFromModel method.
     * 
     * Generates a select form field using all the rows from a model. See 
     * {@see FormHelper::selectFromArray()} for the option list.
     * 
     * @param Model $model
     * @param array $options
     * @param string $field
     * @return string 
     */
    public static function selectFromModel(Model $model, array $options = array(), $field = 'name') {
        if (!isset($options['name']))
            $options['name'] = strtolower(Inflect::singularize(substr(get_class($model), 0, -5)));
        $query = $model->getAll(null);
        $items = array();
        while ($row = $query->fetchObject($model->getClass()))
            $items[$row->id] = $row->{$field};
        return self::selectFromArray($items, $options);
    }

    /**
     * SelectFromArray method.
     * 
     * Generate a select form field from an array. Optional options are:
     * - height (int) height of the field
     * - multiple (bool) allow multiple selections
     * - name (string) name of the field
     * - selected (array) array of selected values
     * 
     * @param array $items
     * @param array $options 
     * @return string
     */
    public static function selectFromArray(array $items, array $options = array()) {
        $height = (int) getValue($options, 'height', 1);
        $multiple = (bool) getValue($options, 'multiple', false);
        $name = (string) getValue($options, 'name', '');
        $selected = (array) getValue($options, 'selected', array());
        if ($multiple)
            $name .= '[]';

        $html = '<select name="' . $name . '" size="' . $height . '"' . (($multiple) ? ' multiple' : '') . '>';
        foreach ($items as $value => $name)
            $html .= '<option value="' . $value . '"' . ((in_array($value, $selected)) ? ' selected' : '') . '>' . $name . '</option>';
        $html .= '</select>';
        return $html;
    }

}
