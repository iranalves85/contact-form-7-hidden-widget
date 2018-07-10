<?php
/**
 * Plugin Name: Contact Form 7 - Hidden Widget
 * Description: An add-on for Contact Form 7 to show content in widget when a event form is fired.
 * Version: 0.1
 * Author: Iran Alves
 * Author URI: makingpie.com.br
 * License: GPLv3
 * Copyright (C) 2018 Iran
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
 
/**
 * Verify CF7 dependencies
 * 
 * @since 0.1
 */
function cf7_hw_data_admin_notice() {
    // Verify that CF7 is active and updated to the required version (currently 3.9.0)
    if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) {
        $wpcf7_path = plugin_dir_path( dirname(__FILE__) ) . 'contact-form-7/wp-contact-form-7.php';
        $wpcf7_plugin_data = get_plugin_data( $wpcf7_path, false, false);
        $wpcf7_version = (int)preg_replace('/[.]/', '', $wpcf7_plugin_data['Version']);
        // CF7 drops the ending ".0" for new major releases (e.g. Version 4.0 instead of 4.0.0...which would make the above version "40")
        // We need to make sure this value has a digit in the 100s place.
        if ( $wpcf7_version < 100 ) {
            $wpcf7_version = $wpcf7_version * 10;
        }
        // If CF7 version is < 3.9.0
        if ( $wpcf7_version < 390 ) {
            echo '<div class="error"><p><strong>'. _x('Warning:', 'contact-form-7-hidden-widget') . '</strong> '. _x('Contact Form 7 - Hidden Widget requires that you have the latest version of Contact Form 7 installed. Please upgrade now', 'contact-form-7-hidden-widget') .'</p></div>';
        }
    }
    // If it's not installed and activated, throw an error
    else {
        echo '<div class="error"><p>' . _x('Contact Form 7 is not activated. The Contact Form 7 Plugin must be installed and activated before you can use Contact Form 7 - Hidden Widget', 'contact-form-7-hidden-widget') .'</p></div>';
    }
}
add_action('admin_notices', 'cf7_hw_data_admin_notice');

/** 
 * Register required widget class
 * @since 0.1
 */
class Widget_CF7HW extends WP_Widget {
    
    /**
    * Instantiate object
    *
    * @since 0.1
    */
    public function __construct() {
        // Instantiate the parent object
        parent::__construct( 'cc7_hw',
            _x('Contact Form 7 - Hidden Widget', 'contact-form-7-hidden-widget'),
        array(
            'description' => _x('Widget with hidden content that are show when a selected Contact Form 7 is submitted', 'contact-form-7-hidden-widget'),
            'classname' => 'cc7_hw_widget'
            ));
    } 
    
    /**
	 * Construct widget form fields
     * 
     * @since 0.1 
	 * @param array $instance The widget options
     * @return string Return data to show or not form in admin
	 */
	public function form( $instance ) {
        
        /** Verify if existed saved data */
        $ID     = ! empty($instance['formID']) ? (int) $instance['formID'] : '';
        $event  = ! empty($instance['formEvent']) ? $instance['formEvent'] : '';
        $content = ! empty($instance['formContent']) ? $instance['formContent'] : '';

        // Query to return existing forms CF7
        $c7forms = new WP_Query(array( 'post_type'=> 'wpcf7_contact_form'));
        
        //Register a select input for forms cc7
        $html = "<p><label>" . _x('Select one Contact Form 7\'s form:', 'contact-form-7-hidden-widget') . "</label><select id='". esc_attr( $this->get_field_id( 'formID' ) ). "' class='widefat' name='" . esc_attr($this->get_field_name('formID')) . "'>";

        //Construct option of forms to select
        foreach ($c7forms->posts as $key => $value) {
            $selected = ($value->ID == $ID)? " selected='selected'" : '';
            $html .= "<option value='" . esc_attr($value->ID) . "'" . $selected .">". esc_html($value->post_title) . "</option>";
        }

        //Close tags
        $html .= "</select></p>";

        //Register a select input for forms cc7
        $html .= "<p><label>" . _x('Select form event to trigger:', 'contact-form-7-hidden-widget') . "</label><select id='". esc_attr( $this->get_field_id( 'formEvent' ) ). "' class='widefat' name='" . esc_attr($this->get_field_name('formEvent')) . "'>";

        //Define Contact Form 7 DOM Events
        $cc7JsEvents = array(
            'wpcf7invalid' => _x('Invalid Input\'s', 'contact-form-7-hidden-widget'), 
            'wpcf7spam' => _x('Spam', 'contact-form-7-hidden-widget'), 
            'wpcf7mailsent' => _x('Mail Sent', 'contact-form-7-hidden-widget'), 
            'wpcf7mailfailed' => _x('Mail Failed', 'contact-form-7-hidden-widget'), 
            'wpcf7submit' => _x('Complete Success', 'contact-form-7-hidden-widget'));

        //Construct option of forms to select
        foreach ($cc7JsEvents as $key => $value) {
            $selected = ($event == $key)? " selected='selected'" : '';
            $html .= "<option value='" . esc_attr($key) . "'" . $selected .">". esc_html($value) . "</option>";
        }

        //Close tags
        $html .= "</select></p>";

        wp_reset_query(); //Reset current Query

        //Register a select input for forms cc7
        $html .= "<p><label>" . _x('Content to show when trigger occurred success:', 'contact-form-7-hidden-widget') . "</label><textarea id='". esc_attr( $this->get_field_id( 'formContent' ) ). "' class='widefat' rows='10' cols='20' name='" . esc_attr($this->get_field_name('formContent')) . "'>" . $content .  "</textarea></p>";

        $html.= "<div style='display:block;padding:10px;background-color: #deebf1;position:relative;margin-bottom:15px;'>

            <p style='margin-top:0px;font-size:11px;'>". _x('Developed by ', 'contact-form-7-hidden-widget') . "Iran Alves [https://github.com/iranalves85], " . _x('Thank you for using my plugin!', 'contact-form-7-hidden-widget') .
            "</p><p style='margin-top:0px;font-size:11px;'>" .  _x('If this plugin helped you in any way, pay me a coffee or evaluate the plugin in the repository of Wordpress plugins, thank you immensely.', 'contact-form-7-hidden-widget') . " <strong>". _x('Wordpress is love!', 'contact-form-7-hidden-widget') ."</strong>
                <span style='color: #999;'>"
                . _x('Are you looking for a developer for your project?', 'contact-form-7-hidden-widget') . "<strong>iranjosealves@gmail.com</strong> | <a target='_blank' href='https://makingpie.com.br'>makingpie.com.br</a>
                </span>
            </p>

            <a href='https://goo.gl/dN6U3T'
                target='_blank' class='button button-primary link'>"
            . _x('Donate', 'contact-form-7-hidden-widget') .
            "</a>
            <a href='#' target='_blank' class='button link'>"
            . _x('Rate my plugin', 'contact-form-7-hidden-widget') .
            "</a>
        </div>";
        
        echo $html;

        return 'onform'; //Override default 'noform'
    }
    
    /**
	 * Update user defined options in wordpress database
     * 
     * @since 0.1 
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
        $value = array();
        
        //ID's form
        $value['formID'] = ( ! empty($new_instance['formID'] ) ) ? sanitize_text_field($new_instance['formID']) : '';

        //Event selected
        $value['formEvent'] = ( ! empty($new_instance['formEvent'] ) ) ? sanitize_text_field($new_instance['formEvent']) : '';

        //Widget content
        $value['formContent'] = ( ! empty($new_instance['formContent'] ) ) ? sanitize_textarea_field($new_instance['formContent']) : '';
        
        return $value;
    }
    
    /**
	 * Outputs the content of the widget
     * 
	 * @since 0.1 
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {

        //Defined content
        $GLOBALS['cc7_hw_widget_content']  = (!empty($instance['formContent']))? apply_filters('the_content', $instance['formContent']) : '';
        $event      = (!empty($instance['formEvent']))? $instance['formEvent'] : 'wpcf7submit';
        $id         = (!empty($instance['formID']))? $instance['formID'] : 0;

        //Construct javascript vars
        $scriptVars = "
        <script>
            var cc7_hw_event = '$event';
            var cc7_hw_id = '$id';
        </script>
        ";

        //Add custom content after title widget
        $args['after_title'] .= $scriptVars;

        //Draw widget in frontend
        echo $args['before_widget'] . $args['after_title'] .  $args['after_widget'];
	}
}

/**
 * Action do add javascript function in page footer with content
 * 
 * @since 0.1 
 */
function cf7_hw_form_submitted() {

    global $cc7_hw_widget_content;

    ?>
        <script>
            /* 
            ** Contact Form 7 - Hidden Widget 
            ** Get widget element DOM and add content when a event is fired
            */
            document.addEventListener( cc7_hw_event, function( event ) {
                if(event.detail.contactFormId == cc7_hw_id ){                    
                    $widgetElement = document.querySelector('.widget.cc7_hw_widget');
                    $widgetElement.innerHTML = `<?php _e($cc7_hw_widget_content); ?>`;

                }
            }, false );

        </script>

    <?php
}
add_action('wp_footer', 'cf7_hw_form_submitted');

/** 
 * Registrar widgets 
 * */
function cf7_hw_register_widgets() {
    register_widget('Widget_CF7HW'); 
}
add_action('widgets_init', 'cf7_hw_register_widgets');