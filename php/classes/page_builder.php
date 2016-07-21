<?php

/*
 * Puzzle Page Builder
 * Page Builder Class
 */

class PuzzlePageBuilder {
    /*
     * Returns the markup for fields
     *
     * $data - array, the data that the field is being saved to
     * $fields - array, the PuzzleField objects to loop through
     * $input_name_prefix - string, the prefix for the name attribute in the
     * input field
     */
    private function fields_markup($data, $fields, $input_name_prefix) {
        $puzzle_icon_libraries = new PuzzleIconLibraries;
        $output = '';
            
        foreach($fields as $field) {
            $id = $field->id();
            $input_name = $input_name_prefix . '[' . $id . ']';
            $input_width = 'xs-span12 sm-span' . ($field->width());
            
            $tip = '';
            if (!empty($field->tip())) {
                $tip = '<i class="puzzle-field-tip-button fa fa-question-circle"></i><span class="puzzle-field-tip-content"><span>' . $field->tip() . '</span></span>';
            }
            
            $output .= '<div class="column ' . $input_width . ($field->input_type() == 'icon' ? ' puzzle-icon-preview' : '') . '">';
            
            if (!isset($data[$id])) {
                $data[$id] = '';
            }
            
            switch ($field->input_type()) {
                case 'textarea':
                    $output .= $field->name() . $tip;
                    $output .= '<textarea name="' . $input_name . '" rows="' . (!empty($field->rows()) ? $field->rows() : '5') . '">' . $data[$id] . '</textarea><br />';
                    $output .= '<button class="puzzle-button open-editor-button">Open Editor</button>';
                    break;
                case 'checkbox':
                    $output .= '<input type="checkbox" name="' . $input_name . '" id="' . $input_name . '"' . (!empty($data[$id]) ? ' checked' : '') . '><label for="' . $input_name . '">' . $field->name() . '</label>' . $tip;
                    break;
                case 'select':
                    $output .= $field->name() . $tip;
                    $output .= '<select name="' . $input_name . '">';
                    foreach ($field->options() as $option_key => $option_label) {
                        $output .= '<option value="' . $option_key . '"' . ($data[$id] == $option_key || (empty($data[$id]) && !empty($field->selected()) && $field->selected() == $option_key) ? ' selected' : '') . '>' . $option_label . '</option>';
                    }
                    $output .= '</select>';
                    break;
                case 'icon':
                    $icon_value = (!empty($data[$id]) ? $data[$id] : $puzzle_icon_libraries->default_icon());
            
                    $output .= $field->name() . $tip;
                    $output .= '<i class="' . $icon_value . '"></i>';
                    $output .= '<input name="' . $input_name . '" type="hidden" value="' . $icon_value . '" readonly />';
                    $output .= '<button class="puzzle-button puzzle-add-icon">Choose Icon</button>';
                    break;
                case 'image':
                    $image_id = $data[$id];
                    $image = (!empty($image_id) ? wp_get_attachment_image($image_id, 'large') : '<img src="" />');
                
                    $output .= $field->name() . $tip;
                    $output .= $image . '<br />';
                    $output .= '<input name="' . $input_name . '" type="hidden" value="' . $image_id . '" readonly />';
                    $output .= '<button class="puzzle-add-image-button puzzle-button" data-editor="content" title="Add Image">Add Image</button> ';
                    $output .= '<button class="puzzle-remove-image-button puzzle-button">Remove Image</button>';
                    break;
                case 'color':
                    $output .= $field->name() . $tip;
                    $output .= '<input class="puzzle-color-field" name="' . $input_name . '" value="' . esc_attr($data[$id]) . '" type="text" />';
                    break;
                default:
                    $output .= $field->name() . $tip;
                    $output .= '<input name="' . $input_name . '" value="' . esc_attr($data[$id]) . '" type="' . $field->input_type() . '"' . (!empty($field->placeholder()) ? ' placeholder="' . $field->placeholder() . '"' : '') . ' />';
            }
            
            $output .= '</div>';
        }
        
        return $output;
    }
    
    /*
     * Returns the page builder markup for a section's column
     *
     * $puzzle_section - PuzzleSection object
     * $s - integer, the counter keeping track of what section we are on
     * $c - integer, the counter keeping track of what column we are on
     * $column_data - array, the column's data
     */
    function column_markup($puzzle_section, $s, $c, $column_data = array('show' => 'show')) {
        $output = '<div class="column puzzle-page-builder-column ' . $puzzle_section->admin_column_classes() . '">';

        if ($puzzle_section->has_unlimited_columns()) {
            $output .= '<div class="puzzle-collapsable-menu' . ($column_data['show'] == 'show' ? '' : ' collapsed-state') . '">';
            $output .= '<i class="fa fa-chevron-' . ($column_data['show'] == 'show' ? 'down' : 'up') . ' puzzle-collapse"></i>';
            $output .= '<h5>' . $puzzle_section->single_name() . '</h5>';
            $output .= '<i class="fa fa-close puzzle-remove-section"></i>';
            $output .= '<input name="puzzle_page_sections[' . $s . '][columns][' . $c . '][show]" type="hidden" value="' . ($column_data['show'] != 'hide' ? 'show' : 'hide') . '"></input>';
            $output .= '</div>';
            $output .= '<div class="puzzle-collapsable-content' . ($column_data['show'] != 'hide' ? ' show' : '') . '">';
        }
    
        $output .= '<h4>' . $puzzle_section->single_name() . '</h4>';
        $output .= '<div class="row">';
        $output .= $this->fields_markup($column_data, $puzzle_section->column_fields(), 'puzzle_page_sections[' . $s . '][columns][' . $c . ']');
        $output .= '</div>';
    
        if ($puzzle_section->has_unlimited_columns()) {
            $output .= '</div>';
        }
    
        $output .= '</div>';
    
        return $output;
    }
    
    /*
     * Returns the page builder markup for the add new column button
     *
     * $puzzle_section - PuzzleSection object
     */
    function add_new_column_button_markup($puzzle_section) {
        $output  = '';
        
        if ($puzzle_section->has_unlimited_columns()) {
            $output .= '<div class="puzzle-add-column-area">';
            $output .= '<button class="puzzle-button puzzle-button-primary puzzle-button-large puzzle-add-column">Add ' . $puzzle_section->single_name() . '</button>';
            $output .= '</div>';
        }
        
        return $output;
    }
    
    /*
     * Returns the page builder markup for a section's options
     *
     * $puzzle_section - PuzzleSection object
     * $s - integer, the counter keeping track of what section we are on
     * $puzzle_options_data - array, the section's options data
     */
    function options_markup($puzzle_section, $s, $options_data = array()) {
        $output = self::fields_markup($options_data, $puzzle_section->option_fields(), 'puzzle_page_sections[' . $s . '][options]');
        return $output;
    }
    
    /*
     * Returns the admin markup for a section
     *
     * $puzzle_section - PuzzleSection object
     * $s - integer, the counter keeping track of what section we are on
     * $options_data - array, the section's options data
     * $columns_data - array, the section's columns data
     * $show - boolean, whether or not the section is collapsed in the admin view
     */
    function admin_section_markup($puzzle_section, $s, $options_data = array(), $columns_data = array(), $show = 'show') {
        $c = 0;
        
        $output  = '<div class="puzzle-section puzzle-' . $puzzle_section->slug() . '-area">';
        
        $output .= '<div class="puzzle-collapsable-menu' . ($show == 'show' ? '' : ' collapsed-state') . '">';
        $output .= '<i class="fa fa-chevron-' . ($show == 'show' ? 'down' : 'up') . ' puzzle-collapse"></i>';
        $output .= '<h5>' . $puzzle_section->name() . '</h5>';
        $output .= '<i class="fa fa-close puzzle-remove-section"></i>';
        $output .= '<input name="puzzle_page_sections[' . $s . '][show]" type="hidden" value="' . $show . '"></input>';
        $output .= '</div>';

        $output .= '<div class="puzzle-collapsable-content' . ($show == 'show' ? ' show' : '') . '">';
        $output .= '<h3>' . $puzzle_section->name() . ' Section</h3>';
        
        if ($puzzle_section->option_fields()) {
            $output .= '<div class="row puzzle-general-options-area">';
            $output .= '<div class="column xs-span12">';
            $output .= '<h4>General Options</h4>';
            $output .= '<div class="row">';
            $output .= self::options_markup($puzzle_section, $s, $options_data);
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
        }
        
        $output .= self::add_new_column_button_markup($puzzle_section);
        
        $output .= '<div class="row puzzle-columns-area ' . ($puzzle_section->has_unlimited_columns() ? 'puzzle-unlimited-columns' : 'puzzle-fixed-columns') . '">';

        // Gets the max number of columns.
        $max_columns = $puzzle_section->columns_num();
        
        // Adds necessary number of columns if there is previously saved data,
        // or just adds one column with empty fields if this is a new section.
        if (!empty($columns_data)) {
            foreach ($columns_data as $puzzle_column) {
                if ($puzzle_section->has_unlimited_columns() || $c < $max_columns) {
                    $output .= self::column_markup($puzzle_section, $s, $c, $puzzle_column);
                    $c++;
                }
            }
        } else if ($puzzle_section->has_unlimited_columns() && is_array($columns_data)) {
            $output .= self::column_markup($puzzle_section, $s, $c);
            $c++;
        }
            
        // Adds more sections equal to the fixed number of columns
        // if the section has a fixed number of columns.
        while ($c < $max_columns) {
            $output .= self::column_markup($puzzle_section, $s, $c);
            $c++;
        }
        
        $output .= '</div>';
        
        $output .= self::add_new_column_button_markup($puzzle_section);
        
        $output .= '<input class="puzzle-section-type-field" name="puzzle_page_sections[' . $s . '][type]" type="hidden" value="' . $puzzle_section->slug() . '" />';
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }
    
    /*
     * Gets data from the page builder that should be saved to the post
     * content. Used by the saveable_content() function to loop through both
     * options and columns.
     *
     * $fields - array, PuzzleField objects
     * $data - array, the user input for the fields
     *
     * Returns a string of content that should be saved
     */
    function get_saveable_data($fields, $data) {
        $content = '';
        
        foreach ($data as $key => $value) {
            if (empty($fields[$key])) continue;
            
            if (!empty($fields[$key]->save_as()) && !empty($value)) {
                $tag = $fields[$key]->save_as();
            
                if ($tag == 'content') {
                    $content .= apply_filters('the_content', $value);
                } elseif ($tag == 'link') {
                    $link_text = $fields[$key]->name();
                    $open_link_in_new_tab = '';
                    
                    if (!empty($fields[$key]->save_as_link_text())) {
                        $link_text_key = $fields[$key]->save_as_link_text();
                        $link_text = $data[$link_text_key];
                    }
                    
                    if (!empty($data['open_link_in_new_tab'])) {
                        $open_link_in_new_tab = ' target="_blank"';
                    }
                    
                    $content .= '<p><a href="' . $value . '"' . $open_link_in_new_tab . '>' . $link_text . '</a></p>';
                } else {
                    $content .= '<' . $tag . '>' . $value . '</' . $tag . '>';
                }
            }
        }
        
        return $content;
    }
    
    /*
     * Processes the fields in the page builder and returns content to save to
     * the actual post content. This is so the user isn't locked into this
     * plugin because all the content is in the post meta.
     *
     * $puzzle_sections_data - array, data from the page builder form
     *
     * Returns a string of HTML to save to the post content
     */
    function saveable_content($puzzle_sections_data) {
        $puzzle_sections = (new PuzzleSections)->sections();
        $content = '';
        
        // Loops through each page section
        foreach ($puzzle_sections_data as $puzzle_section_data) {
            $options_data = $puzzle_section_data['options'];
            $columns_data = (!empty($puzzle_section_data['columns']) ? $puzzle_section_data['columns'] : NULL);
            $section_type = $puzzle_section_data['type'];
            
            $puzzle_section = $puzzle_sections[$section_type];
            $option_fields = $puzzle_section->option_fields();
            $column_fields = $puzzle_section->column_fields();
            
            // Loops through the options of each page section and adds to
            // the post content.
            if ($option_fields) {
                $content .= self::get_saveable_data($option_fields, $option_data);
            }
            
            // Loops through the columns of each page section and adds to
            // the post content.
            if (!empty($columns_data)) {
                foreach ($columns_data as $column_data) {
                    $content .= self::get_saveable_data($column_fields, $column_data);
                }
            }
        }
        
        return $content;
    }
}
?>
