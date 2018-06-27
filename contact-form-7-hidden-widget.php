<?php
/**
 * Plugin Name: Contact Form 7 - Hidden Widget
 * Description: An add-on for Contact Form 7 show content in widget when a form is success submitted.
 * Version: 0.2
 * Author: Iran Alves
 * Author URI: https://www.makingpie.com.br
 * License: GPLv3
 */
 
/**
 * Verify CF7 dependencies.
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
            echo '<div class="error"><p><strong>Warning: </strong>Contact Form 7 - Hidden Widget requires that you have the latest version of Contact Form 7 installed. Please upgrade now.</p></div>';
        }
    }
    // If it's not installed and activated, throw an error
    else {
        echo '<div class="error"><p>Contact Form 7 is not activated. The Contact Form 7 Plugin must be installed and activated before you can use Hidden Widget.</p></div>';
    }
}
add_action('admin_notices', 'cf7_hw_data_admin_notice');

/** 
 * Registrando widgets especificos do tema  
 */
class Widget_CF7HW extends WP_Widget {

	function __construct() {
        // Instantiate the parent object
        parent::__construct( false,
            _x('Contact Form 7 - Hidden Widget', 'contact-form-7-hidden-widget') );
    }
    
    /**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

        var_dump($this);

        /** Verify if eixsted saved data */
        $ID = ! empty($instance['cc7_form_id']) ? $instance['cc7_form_id'] : null; 

        // Query to return existing forms CF7
        $c7forms = new WP_Query(array( 'post_type'=> 'wpcf7_contact_form'));
        
        //Construct form to input data
        $html = "<form method='POST'><p><label>" . _x('Selecione o formulário','contact-form-7-hidden-widget') . "</label><select class='widefat' name='widget-cf7_hw'>";

        //Construct option of forms to select
        foreach ($c7forms->posts as $key => $value) {
            $selected = ($value->ID == $ID)? " selected='selected'" : '';
            $html .= "<option name='" . esc_attr( $this->get_field_name( 'cc7_form_id' ) ) ."' value='" . $value->ID . "'" . $selected .">". $value->post_title . "</option>";
        }

        //Close tags
        $html .= "</select></p></form>";

        wp_reset_query();
        wp_reset_postdata();

        echo $html;
    }
    
    /**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['cc7_form_id'] = ( ! empty($new_instance['cc7_form_id'] ) ) ? sanitize_text_field($new_instance['cc7_form_id']) : ''; 
        
        return $instance;
	}

    /**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
        cf7_hw_form_submitted();
	}
}

/**
 * When form has submitted
 */
function cf7_hw_form_submitted(){

    /* Origem dos arquivos */
    $folder = get_field('product_folder', get_the_ID());
    $subscriptionFile = get_field('product_subscriptionFile', get_the_ID());
    $price = (float) get_field('product_price', get_the_ID());
    $qtdParc = 6;
    $paymentLink = get_field('product_payment', get_the_ID());

    //if(!empty($folder)):

    ?>
        <script>
            var $showWidget = `
            <h1 class="text-center price">
                R$ <?= $price; ?>
                <br /><small>Em até <?= $qtdParc; ?> vezes de <b>R$ <?= $price / 6; ?></b></small>
            </h1>
            <ul>
                <li>
                    <a href="<?= $folder; ?>" target="_blank">Baixe <b>Folder</b> do curso</a>
                </li>
                <li>
                    <a href="<?= $subscriptionFile; ?>" target="_blank">Baixe a <b>Ficha de Inscrição</b></a>
                </li>
            </ul>
            <p class="text-center">
                <a id="inscricao-botao" class="btn btn-md btn-block btn-primary" target="_blank" href="<?= $paymentLink; ?>">Faça sua inscrição</a>                
            </p>
            `;

            $evento = 'wpcf7submit'; // 'wpcf7mailsent' 'wpcf7submit'

            document.addEventListener( $evento, function( event ) {
                if(event.detail.contactFormId == 57){
                    $widgetList = document.querySelector('.sidebar ul');
                    $element = document.createElement('li');
                    $element.setAttribute("class", "widget widget_course_more_detail");
                    $element.innerHTML = $showWidget;  
                    //$widgetList.appendChild($element);         
                    $widgetList.insertBefore($element,$widgetList.childNodes[0]);  
                    $element.className += " " + "show";  

                    $botao = document.getElementById("inscricao-botao");
                    $botao.addEventListener( 'click', function( event ) {
                        // Google Analytics - Meta
                        ga('send', 'event', 'Inscricao', 'Clique botão de inscrição ', window.location.pathname);

                        // FB Pixel
                        fbq('track', 'Inscricao', {
                        content_name: window.location.pathname,
                        content_category: 'Cursos',
                        value: 2.00,
                        currency: 'BRL'
                        });

                    }, false );

                }
            }, false );

        </script>
        <style>
            /*Mais informações de cursos */
            .widget_course_more_detail{
                border: 1px solid #ddd !important;
                background-color: #FFF;
                box-shadow: 1px 1px 1px 1px rgba(100,100,100,0.1);
                border-radius: 5px;
                overflow: hidden;
                max-height: 0px;          
                transition: max-height 0.15s ease-out;
            }

            .widget_course_more_detail.show{
                max-height: 270px;
                transition: max-height 0.15s ease-in;
            }

            .widget_course_more_detail ul{
                margin-bottom: 2px;
            }

            .widget_course_more_detail h1{
                margin: -15px -15px 10px -15px;
                padding: 10px;
                background-color: #793680;
                color: #fcaf26;
            }

            .widget_course_more_detail h1 small{
                font-size: 16px;
                color: #fff;
            }
        </style>
    <?php

    //endif;
}
add_action('wp_footer', 'cf7_hw_form_submitted');

/** 
 * Registrar widgets 
 * */
function cf7_hw_register_widgets() {
    register_widget('Widget_CF7HW'); 
}
add_action('widgets_init', 'cf7_hw_register_widgets');