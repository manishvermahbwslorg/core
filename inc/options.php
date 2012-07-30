<?php
/**
 * FIXME: Edit Title Content
 *
 * FIXME: Edit Description Content
 *
 * Please do not edit this file. This file is part of the Response core framework and all modifications
 * should be made in a child theme.
 * FIXME: POINT USERS TO DOWNLOAD OUR STARTER CHILD THEME AND DOCUMENTATION
 *
 * @category Response
 * @package  Framework
 * @since    1.0
 * @author   CyberChimps
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.cyberchimps.com/
 */


/* If the user can't edit theme options, no use running this plugin */
add_action('init', 'response_edit_themes_role_check' );
function response_edit_themes_role_check() {
	if ( current_user_can( 'edit_theme_options' ) ) {
		// If the user can edit theme options, let the fun begin!
		add_action( 'admin_menu', 'response_admin_add_page');
		add_action( 'admin_init', 'response_admin_init' );
		add_action( 'admin_init', 'response_mlu_init' );
		add_action( 'wp_before_admin_bar_render', 'response_admin_bar' );
	}
}

//TODO HS add styles and js for page meta boxes
add_action('admin_head', 'meta_boxes_styles_scripts');

function meta_boxes_styles_scripts() {

	global $post_type; 
	
	//TODO HS Will need to add more post types as they are created
	if ( ( $_GET['post_type'] == 'page' ) || ( $post_type == 'page' ) ) :		

		wp_enqueue_style( 'meta-boxes-css', get_template_directory_uri().'/core/lib/css/metabox-tabs.css' );
		wp_enqueue_script('meta-boxes-js', get_template_directory_uri().'/core/lib/js/metabox-tabs.js', array('jquery'));	

	endif;

}

// create the admin menu for the theme options page
add_action('admin_menu', 'response_admin_add_page');
function response_admin_add_page() {

	$response_page = add_theme_page(
		__('Framework Options Page', 'response'),
		__('Framework Options', 'response'),
		'edit_theme_options',
		'response-theme-options',
		'response_options_page'
	);

	add_action( "admin_print_styles-$response_page", 'response_load_styles');
	add_action( "admin_print_scripts-$response_page", 'response_load_scripts');
}

function response_load_styles() {
	// TODO: Find better way to enqueque these scripts
	wp_enqueue_style( 'bootstrap', get_template_directory_uri().'/core/lib/bootstrap/css/bootstrap.css' );
	wp_enqueue_style( 'bootstrap-responsive', get_template_directory_uri().'/core/lib/bootstrap/css/bootstrap-responsive.css', 'bootstrap' );
	
	wp_enqueue_style( 'plugin_option_styles', get_template_directory_uri().'/core/lib/css/options-style.css', array( 'bootstrap', 'bootstrap-responsive' ) );
	
	wp_enqueue_style('color-picker', get_template_directory_uri().'/core/lib/css/colorpicker.css');
	wp_enqueue_style('thickbox');
}

function response_load_scripts() {
	// Enqueued scripts
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('color-picker', get_template_directory_uri().'/core/lib/js/colorpicker.js', array('jquery'));
	wp_enqueue_script('options-custom', get_template_directory_uri().'/core/lib/js/options-custom.js', array('jquery'));
	wp_enqueue_script('media-uploader', get_template_directory_uri().'/core/lib/js/of-medialibrary-uploader.js', array('jquery'));
}

/* Loads the file for option sanitization */
add_action('init', 'response_load_sanitization' );
function response_load_sanitization() {
	require_once dirname( __FILE__ ) . '/options-sanitize.php';
}

// add core and theme settings to options page
add_action('admin_init', 'response_admin_init');
function response_admin_init(){
	
	require_once dirname( __FILE__ ) . '/options-medialibrary-uploader.php';
	
	// pull in default core settings
	require_once dirname( __FILE__ ) . '/options-core.php';
	
	// register theme options settings
	register_setting( 'response_options', 'response_options', 'response_options_validate' );
	
	// add all core settings
	// Create sections
	$sections_list = response_get_sections();
	response_create_sections( $sections_list );
	
	// Create fields
	$fields_list = response_get_fields();
	response_create_fields( $fields_list );
}

function response_options_links() {
	
	$output = apply_filters('response_options_support_link', '<li><a href="http://cyberchimps.com/support/" target="_blank">Support</a></li>' );
	$output .= apply_filters('response_options_documentation_link', '<li><a href="http://cyberchimps.com/docs/" target="_blank">Documentation</a></li>' );
	$output .= apply_filters('response_options_buy_link', '<li><a href="http://cyberchimps.com/store/" target="_blank">Buy Themes</a></li>' );
	$output .= apply_filters('response_options_upgrade_link', '<li><a href="http://cyberchimps.com/store/" target="_blank">Upgrade to Pro</a></li>' );
	
	return apply_filters('response_options_links', $output);
}

// create and display theme options page
function response_options_page() {
	settings_errors();
?>

	<div class="wrap">
		<div class="container-fluid cc-options">
			
			<form action="options.php" method="post">
			<?php settings_fields('response_options'); ?>
			<?php $headings_list = response_get_headings(); ?>
			<?php $sections_list = response_get_sections(); ?>

			<!-- header -->
			<div class="row-fluid cc-header">
				<div class="span3 cc-title">
					<div class="icon32" id="icon-tools"> <br /> </div>
					<h2>Theme Options</h2>
				</div><!-- span3 -->
				<div class="span9">
					<ul class="cc-header-links">
						<?php  echo response_options_links(); ?>
					</ul>
				</div><!-- span9 -->
			</div><!-- row-fluid -->
			<!-- end header -->
			
			<!-- start sub menu --> 
			<div class="row-fluid cc-submenu">
				<!-- TODO: hiding this section till we can implement 
				<div class="span3 cc-collapse">
					<p><a href="#">Open All</a> / <a href="#">Collapse All</a></p>
				</div>
				-->
				<div class="span12">
					<input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'response' ); ?>" />
					<input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'response' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'response' ) ); ?>' );" />
					<div class="clear"></div>
				</div><!-- span 9 -->
			</div><!-- row fluid -->
			<!-- end sub menu -->
			
			<!-- start left menu --> 
			<div class="row-fluid cc-content">
				
				<div class="span2 cc-left-menu">
					<ul class="cc-parent nav-tab-wrapper">
						<?php
						foreach ( $headings_list as $heading ) {
							
							$jquery_click_hook = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($heading['title']) );
							$jquery_click_hook = "of-option-" . $jquery_click_hook;
							
							echo '<li class="cc-has-children">';
							echo '<div class="cc-menu-arrow"></div>';
							echo '<a id="'.  esc_attr( $jquery_click_hook ) . '-tab" title="' . esc_attr( $heading['title'] ) . '" href="' . esc_attr( '#'.  $jquery_click_hook ) . '">' . esc_html( $heading['title'] ) . '<i class="icon-chevron-down"></i></a>';
							
							echo '<ul class="cc-child">';
							foreach( $sections_list as $section ) {
								if ( in_array( $heading['id'], $section) ) { 
									$jquery_click_section_hook = '';
									$jquery_click_section_hook = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($section['label']) );
									$jquery_click_section_hook = "of-option-" . $jquery_click_section_hook;

									echo '<li><a id="'.  esc_attr( $jquery_click_section_hook ) . '-tab" title="' . esc_attr( $section['label'] ) . '" href="' . esc_attr( '#'.  $jquery_click_section_hook ) . '">' . esc_html( $section['label'] ) . '</a></li>';
								}
							}
							echo '</ul>';
							echo '</li>';
						} ?>
					</ul>
				</div><!-- span 2 -->
				<!-- end left menu -->
				
				<!-- start main content -->
				<div class="span10 cc-main-content">
					<?php foreach( $headings_list as $heading ) {
						
						$jquery_click_hook = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($heading['title']) );
						$jquery_click_hook = "of-option-" . $jquery_click_hook;
					
						echo '<div class="group cc-content-section" id="' . esc_attr( $jquery_click_hook ) . '">';
						echo '<h2>' . esc_html( $heading['title'] ) . '</h2>';
						if ( $heading['description'] ) {
							echo '<p>' . esc_html( $heading['description'] ) . '</p>';
						}
						response_do_settings_sections( $heading['id'] );
						echo '</div>';
					} ?>
				</div><!-- span 10 -->
			</div><!-- row fluid -->
			<!-- end main content -->
			
			<!-- start footer -->
			<div class="row-fluid cc-footer">
				<div class="span6 cc-social">
					<p>CyberChimps <a href="#">Twitter</a> | <a href="#">Facebook</a></p>
				</div><!-- span 6 -->
				<div class="span6">
					<input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'response' ); ?>" />
					<input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'response' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'response' ) ); ?>' );" />
					<div class="clear"></div>
				</div><!-- span 6 -->
			</div><!-- row fluid -->
			<!-- end footer -->
				
			</form>
			
		</div><!-- container-fluid -->
	</div><!-- wrap -->
<?php
}

/**
 * FIXME: Fix documentation
 *
 * forked version of core function do_settings_sections()
 * modified core code call response_do_settings_fields() and apply markup for section title and description
 * returns mixed data
 */
function response_do_settings_sections( $page ) {
	global $wp_settings_sections, $wp_settings_fields;
	
	if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
		return;
	
	foreach ( (array) $wp_settings_sections[$page] as $section ) {
		$jquery_click_section_hook = '';
		$jquery_click_section_hook = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($section['title']) );
		$jquery_click_section_hook = "of-option-" . $jquery_click_section_hook;
		
		echo '<div class="section-group" id="' . esc_attr( $jquery_click_section_hook ) . '">';
		if ( $section['title'] ) {
			echo "<h3>{$section['title']}</h3>\n";
		}
		call_user_func($section['callback'], $section);
		
		if ( isset($wp_settings_fields) && isset($wp_settings_fields[$page]) && isset($wp_settings_fields[$page][$section['id']]) ) {
			response_do_settings_fields($page, $section['id']);
		}
		echo '</div>';
	}
}

/**
 * FIXME: Fix documentation
 *
 * forked version of core function do_settings_fields()
 * modified core code to remove table cell markup and apply custom markup
 * returns mixed data
 */
function response_do_settings_fields($page, $section) {
	global $wp_settings_fields;

	if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]) )
		return;

	foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
		call_user_func($field['callback'], $field['args']);
	}
}

function response_get_headings() {
	$headings_list = array();
	// pull in both default sections and users custom sections
	return apply_filters('response_heading_list', $headings_list);
}

function response_get_sections() {
	$sections_list = array();
	// pull in both default sections and users custom sections
	return apply_filters('response_section_list', $sections_list);
}

function response_get_fields() {
	$fields_list = array();
	// pull in both default fields and users custom fields
	return apply_filters('response_field_list', $fields_list);
}

function response_create_sections( $sections ) {
	if ( empty($sections) )
		return false;
	
	// add in error checking and proper validation, escaping, and translation calls
	foreach($sections as $section ) {
		if ( response_section_exists( $section['heading'], $section['id']) ){
			continue;
		} else {
			add_settings_section(
				$section['id'],
				$section['label'],
				'response_sections_callback',
				$section['heading']
			);
		}
	}
}

function response_drag_drop_field( $value ) {
	global $allowedtags;

	$option_name = 'response_options';
	$settings = get_option($option_name);

	$val = '';
	$output = '';
	
	// Set default value to $val
	if ( isset( $value['std'] ) ) {
		if (is_array($value['std'])) {
			$val = implode(',', array_keys($value['std']));
		} else {
			$val = $value['std'];	
		}
	}
	
	// If the option is already saved, ovveride $val
	if ( ( $value['type'] != 'heading' ) && ( $value['type'] != 'info') ) {
		if ( isset( $settings[($value['id'])]) ) {
			$val = $settings[($value['id'])];
			// Striping slashes of non-array options
			if ( !is_array($val) ) {
				$val = stripslashes( $val );
			}
		}
	}
	
	$values = explode(",", $val);
 
	$output .=  "<div class='section_order' id=" . esc_attr($value['id']) . ">";
	$output .=  "<div class='left_list span6'>";
	$output .=  "<div class='inactive'>Inactive Elements</div>";
	$output .=  "<div class='list_items'>";
	foreach($value['options'] as $k => $v) {
		if(in_array($k, $values)) continue;
		$output .=  "<div class='list_item'>";
		$output .=  '<img src="'. get_template_directory_uri(). '/core/lib/images/minus.png" class="action" title="Remove"/>';
		$output .=  "<span data-key='{$k}'>{$v}</span>";
		$output .=  "</div>";
	}
	$output .=  "</div>";
	$output .=  "</div>";
	$output .=  '<div class="arrow span1 hidden-phone"><img src="'. get_template_directory_uri(). '/core/lib/images/arrowdrag.png" /></div>';
	$output .=  "<div class='right_list span5'>";
	$output .=  "<div class='active'>Active Elements</div>";
	$output .=  "<div class='drag'>Drag & Drop Elements</div>";
	$output .=  "<div class='list_items'>";
	foreach($values as $k) {
		if(!$k) continue;
		$val = $value['options'][$k];
		$output .=  "<div class='list_item'>";
		$output .=  '<img src="'. get_template_directory_uri(). '/core/lib/images/minus.png" class="action" title="Remove"/>';
		$output .=  "<span data-key='{$k}'>{$val}</span>";
		$output .=  "</div>";
	}
	$output .=  "</div>";
	$output .=  "</div>";
	$output .=  "<input type='hidden' id='{$value['id']}' name='{$option_name}[{$value['id']}]' />";
	$output .= '<div class="clear"></div>';
	$output .=  "</div>";
	
	echo $output;
}


function response_sections_callback( $section_passed ) {
	$sections = response_get_sections();
	
	if ( empty($sections) && empty($section_passed ) )
		return false;
	
	foreach ( $sections as $section ) {
		if ($section_passed['id'] == $section['id'] ) {
			echo '<p>' . $section['description'] . '</p>';
		}
	}
}

/**
 * FIXME: Fix documentation
 *
 * custom function that checks if the section has been run through add_settings_section() function
 * returns bool value true if section exists and false if it does not
 */
function response_section_exists( $heading, $section ) {
	global $wp_settings_sections;

	if ( $wp_settings_sections[$heading][$section] ) {
		return true;
	}
	return false;
}

function response_create_fields( $fields ) {
	if ( empty($fields) )
		return false;
		
	// loop through and create each field
	foreach ($fields as $field_args) {
		$field_defaults = array(
			'id' => false,
			'name' => __('Default Field', 'response'),
			'callback' => 'response_fields_callback',
			'section' => 'response_default_section',
			'heading' => 'response_default_heading',
		);
		$field_args = wp_parse_args( $field_args, $field_defaults );
		
		if ( empty($field_args['id']) ) {
			continue;
		} elseif ( !response_section_exists( $field_args['heading'], $field_args['section']) ){
			continue;
		} else {
			add_settings_field(
				$field_args['id'],
				$field_args['name'],
				$field_args['callback'],
				$field_args['heading'],
				$field_args['section'],
				$field_args
			);
		}
	}
}

function response_fields_callback( $value ) {
	global $allowedtags;

	$option_name = 'response_options';
	$settings = get_option($option_name);

	$val = '';
	$select_value = '';
	$checked = '';
	$output = '';
	
	// Set default value to $val
	if ( isset( $value['std'] ) ) {
		$val = $value['std'];
	}

	// If the option is already saved, ovveride $val
	if ( ( $value['type'] != 'heading' ) && ( $value['type'] != 'info') ) {
		if ( isset( $settings[($value['id'])]) ) {
			$val = $settings[($value['id'])];
			// Striping slashes of non-array options
			if ( !is_array($val) ) {
				$val = stripslashes( $val );
			}
		}
	}

	// If there is a description save it for labels
	$explain_value = '';
	if ( isset( $value['desc'] ) ) {
		$explain_value = $value['desc'];
	}
	
	// field wrapper
	$output .= '<div class="field-container">';
	
	// Output field name
	if ($value['name']) {
		$output .= '<label for="' . esc_attr( $value['id'] ) . '">'. $value['name'] . '</label>';
	}
	
	switch ( $value['type'] ) {
		
		// Basic text input
		case 'text':
			$output .= '<input id="' . esc_attr( $value['id'] ) . '" class="of-input ' . esc_attr( $value['class'] ) . '" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" type="text" value="' . esc_attr( $val ) . '" />';
			break;

		// Textarea
		case 'textarea':
			$rows = '8';

			if ( isset( $value['settings']['rows'] ) ) {
				$custom_rows = $value['settings']['rows'];
				if ( is_numeric( $custom_rows ) ) {
					$rows = $custom_rows;
				}
			}

			$val = stripslashes( $val );
			$output .= '<textarea id="' . esc_attr( $value['id'] ) . '" class="of-input" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" rows="' . $rows . '">' . esc_textarea( $val ) . '</textarea>';
			break;

		// Select Box
		case ($value['type'] == 'select'):
			$output .= '<select class="of-input ' . esc_attr( $value['class'] ) . '" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '">';

			foreach ($value['options'] as $key => $option ) {
				$selected = '';
				if ( $val != '' ) {
					if ( $val == $key) { $selected = ' selected="selected"';}
				}
				$output .= '<option'. $selected .' value="' . esc_attr( $key ) . '">' . esc_html( $option ) . '</option>';
			}
			$output .= '</select>';
			break;


		// Radio Box
		case "radio":
			$name = $option_name .'['. $value['id'] .']';
			foreach ($value['options'] as $key => $option) {
				$id = $option_name . '-' . $value['id'] .'-'. $key;
				$output .= '<div class="radio-container"><input class="of-input of-radio" type="radio" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="'. esc_attr( $key ) . '" '. checked( $val, $key, false) .' /><label for="' . esc_attr( $id ) . '" class="of-radio">' . esc_html( $option ) . '</label></div>';
			}
			break;

		// Image Selectors
		case "images":
			$name = $option_name .'['. $value['id'] .']';
			$output .= '<div class="images-radio-container">';
			foreach ( $value['options'] as $key => $option ) {
				$selected = '';
				$checked = '';
				if ( $val != '' ) {
					if ( $val == $key ) {
						$selected = ' of-radio-img-selected';
						$checked = ' checked="checked"';
					}
				}
				$output .= '<div class="images-radio-subcontainer"><input type="radio" id="' . esc_attr( $value['id'] .'_'. $key) . '" class="of-radio-img-radio" value="' . esc_attr( $key ) . '" name="' . esc_attr( $name ) . '" '. $checked .' />';
				$output .= '<div class="of-radio-img-label">' . esc_html( $key ) . '</div>';
				$output .= '<img src="' . esc_url( $option ) . '" alt="' . $option .'" class="of-radio-img-img' . $selected .'" onclick="document.getElementById(\''. esc_attr($value['id'] .'_'. $key) .'\').checked=true;" /></div>';
			}
			$output .= '</div>';
			break;

		// Checkbox
		case "checkbox":
			$output .= '<div class="checkbox-container"><input id="' . esc_attr( $value['id'] ) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" '. checked( $val, 1, false) .' />';
			$output .= '<label class="explain" for="' . esc_attr( $value['id'] ) . '">' . wp_kses( $explain_value, $allowedtags) . '</label></div>';
			break;

		// Multicheck
		case "multicheck":
			foreach ($value['options'] as $key => $option) {
				$checked = '';
				$label = $option;
				$option = preg_replace('/[^a-zA-Z0-9._\-]/', '', strtolower($key));

				$id = $option_name . '-' . $value['id'] . '-'. $option;
				$name = $option_name . '[' . $value['id'] . '][' . $option .']';

				if ( isset($val[$option]) ) {
					$checked = checked($val[$option], 1, false);
				}

				$output .= '<input id="' . esc_attr( $id ) . '" class="checkbox of-input" type="checkbox" name="' . esc_attr( $name ) . '" ' . $checked . ' /><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
			}
			break;

		// Color picker
		case "color":
			$output .= '<div class="input-prepend"><div id="' . esc_attr( $value['id'] . '_picker' ) . '" class="add-on colorSelector"><div style="' . esc_attr( 'background-color:' . $val ) . '"></div></div>';
			$output .= '<input class="of-color" name="' . esc_attr( $option_name . '[' . $value['id'] . ']' ) . '" id="' . esc_attr( $value['id'] ) . '" type="text" value="' . esc_attr( $val ) . '" /></div>';
			break;

		// Uploader
		case "upload":
			$output .= response_medialibrary_uploader( $value['id'], $val, null );
			break;

			// Typography
		case 'typography':
		
			unset( $font_size, $font_style, $font_face, $font_color );
		
			$typography_defaults = array(
				'size' => '',
				'face' => '',
				'style' => '',
				'color' => ''
			);
			
			$typography_stored = wp_parse_args( $val, $typography_defaults );
			
			$typography_options = array(
				'sizes' => response_recognized_font_sizes(),
				'faces' => response_recognized_font_faces(),
				'styles' => response_recognized_font_styles(),
				'color' => true
			);
			
			if ( isset( $value['options'] ) ) {
				$typography_options = wp_parse_args( $value['options'], $typography_options );
			}

			// Font Size
			if ( $typography_options['sizes'] ) {
				$font_size = '<select class="of-typography of-typography-size" name="' . esc_attr( $option_name . '[' . $value['id'] . '][size]' ) . '" id="' . esc_attr( $value['id'] . '_size' ) . '">';
				$sizes = $typography_options['sizes'];
				foreach ( $sizes as $i ) {
					$size = $i . 'px';
					$font_size .= '<option value="' . esc_attr( $size ) . '" ' . selected( $typography_stored['size'], $size, false ) . '>' . esc_html( $size ) . '</option>';
				}
				$font_size .= '</select>';
			}

			// Font Face
			if ( $typography_options['faces'] ) {
				$font_face = '<select class="of-typography of-typography-face" name="' . esc_attr( $option_name . '[' . $value['id'] . '][face]' ) . '" id="' . esc_attr( $value['id'] . '_face' ) . '">';
				$faces = $typography_options['faces'];
				foreach ( $faces as $key => $face ) {
					$font_face .= '<option value="' . esc_attr( $key ) . '" ' . selected( $typography_stored['face'], $key, false ) . '>' . esc_html( $face ) . '</option>';
				}
				$font_face .= '</select>';
			}

			// Font Styles
			if ( $typography_options['styles'] ) {
				$font_style = '<select class="of-typography of-typography-style" name="'.$option_name.'['.$value['id'].'][style]" id="'. $value['id'].'_style">';
				$styles = $typography_options['styles'];
				foreach ( $styles as $key => $style ) {
					$font_style .= '<option value="' . esc_attr( $key ) . '" ' . selected( $typography_stored['style'], $key, false ) . '>'. $style .'</option>';
				}
				$font_style .= '</select>';
			}

			// Font Color
			if ( $typography_options['color'] ) {
				$font_color = '<div class="input-prepend of-typography"><div id="' . esc_attr( $value['id'] ) . '_color_picker" class="add-on colorSelector"><div style="' . esc_attr( 'background-color:' . $typography_stored['color'] ) . '"></div></div>';
				$font_color .= '<input class="of-color of-typography of-typography-color" name="' . esc_attr( $option_name . '[' . $value['id'] . '][color]' ) . '" id="' . esc_attr( $value['id'] . '_color' ) . '" type="text" value="' . esc_attr( $typography_stored['color'] ) . '" /></div>';
			}
	
			// Allow modification/injection of typography fields
			$typography_fields = compact( 'font_size', 'font_face', 'font_style', 'font_color' );
			$typography_fields = apply_filters( 'response_typography_fields', $typography_fields, $typography_stored, $option_name, $value );
			$output .= implode( '', $typography_fields );
			
			break;

		// Background
		case 'background':

			$background = $val;

			// Background Color
			$output .= '<div class="input-prepend"><div id="' . esc_attr( $value['id'] ) . '_color_picker" class="add-on colorSelector"><div style="' . esc_attr( 'background-color:' . $background['color'] ) . '"></div></div>';
			$output .= '<input class="of-color of-background of-background-color" name="' . esc_attr( $option_name . '[' . $value['id'] . '][color]' ) . '" id="' . esc_attr( $value['id'] . '_color' ) . '" type="text" value="' . esc_attr( $background['color'] ) . '" /></div>';

			// Background Image - New AJAX Uploader using Media Library
			if (!isset($background['image'])) {
				$background['image'] = '';
			}

			$output .= response_medialibrary_uploader( $value['id'], $background['image'], null, '',0,'image');
			$class = 'of-background-properties';
			if ( '' == $background['image'] ) {
				$class .= ' hide';
			}
			$output .= '<div class="' . esc_attr( $class ) . '">';

			// Background Repeat
			$output .= '<select class="of-background of-background-repeat" name="' . esc_attr( $option_name . '[' . $value['id'] . '][repeat]'  ) . '" id="' . esc_attr( $value['id'] . '_repeat' ) . '">';
			$repeats = response_recognized_background_repeat();

			foreach ($repeats as $key => $repeat) {
				$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['repeat'], $key, false ) . '>'. esc_html( $repeat ) . '</option>';
			}
			$output .= '</select>';

			// Background Position
			$output .= '<select class="of-background of-background-position" name="' . esc_attr( $option_name . '[' . $value['id'] . '][position]' ) . '" id="' . esc_attr( $value['id'] . '_position' ) . '">';
			$positions = response_recognized_background_position();

			foreach ($positions as $key=>$position) {
				$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['position'], $key, false ) . '>'. esc_html( $position ) . '</option>';
			}
			$output .= '</select>';

			// Background Attachment
			$output .= '<select class="of-background of-background-attachment" name="' . esc_attr( $option_name . '[' . $value['id'] . '][attachment]' ) . '" id="' . esc_attr( $value['id'] . '_attachment' ) . '">';
			$attachments = response_recognized_background_attachment();

			foreach ($attachments as $key => $attachment) {
				$output .= '<option value="' . esc_attr( $key ) . '" ' . selected( $background['attachment'], $key, false ) . '>' . esc_html( $attachment ) . '</option>';
			}
			$output .= '</select>';
			$output .= '</div>';

			break;

		// Editor
		case 'editor':
			$output .= '<div class="explain">' . wp_kses( $explain_value, $allowedtags) . '</div>'."\n";
			echo $output;
			$textarea_name = esc_attr( $option_name . '[' . $value['id'] . ']' );
			$default_editor_settings = array(
				'textarea_name' => $textarea_name,
				'media_buttons' => false,
				'tinymce' => array( 'plugins' => 'wordpress' )
			);
			$editor_settings = array();
			if ( isset( $value['settings'] ) ) {
				$editor_settings = $value['settings'];
			}
			$editor_settings = array_merge($editor_settings, $default_editor_settings);
			wp_editor( $val, $value['id'], $editor_settings );
			$output = '';
			break;

		// Info
		case "info":
			$id = '';
			$class = 'section';
			if ( isset( $value['id'] ) ) {
				$id = 'id="' . esc_attr( $value['id'] ) . '" ';
			}
			if ( isset( $value['type'] ) ) {
				$class .= ' section-' . $value['type'];
			}
			if ( isset( $value['class'] ) ) {
				$class .= ' ' . $value['class'];
			}

			$output .= '<div ' . $id . 'class="' . esc_attr( $class ) . '">' . "\n";
			if ( isset($value['name']) ) {
				$output .= '<h4 class="heading">' . esc_html( $value['name'] ) . '</h4>' . "\n";
			}
			if ( $value['desc'] ) {
				$output .= apply_filters('response_sanitize_info', $value['desc'] ) . "\n";
			}
			$output .= '</div>' . "\n";
			break;
	}

	if ( ( $value['type'] != "heading" ) && ( $value['type'] != "info" ) ) {
		if ( ( $value['type'] != "checkbox" ) && ( $value['type'] != "editor" ) ) {
			$output .= '<div class="desc">' . wp_kses( $explain_value, $allowedtags) . '</div>'."\n";
		}
	}
	
	// end field wrapper
	$output .= '</div>';
	
	echo $output;
}
/**
 * FIXME: Fix documentation
 *
 * 
 */
function response_options_validate( $input ) {

	/*
	 * Restore Defaults.
	 *
	 * In the event that the user clicked the "Restore Defaults"
	 * button, the options defined in the theme's options.php
	 * file will be added to the option for the active theme.
	 */
	if ( isset( $_POST['reset'] ) ) {
		add_settings_error( 'response_options', 'restore_defaults', __( 'Default options restored.', 'response' ), 'updated fade' );
		return response_get_default_values();
		
	/*
	 * Update Settings
	 *
	 * This used to check for $_POST['update'], but has been updated
	 * to be compatible with the theme customizer introduced in WordPress 3.4
	 */
	} else {
		$clean = array();
		$options = response_get_fields();
		foreach ( $options as $option ) {
			if ( ! isset( $option['id'] ) ) {
				continue;
			}
		
			if ( ! isset( $option['type'] ) ) {
				continue;
			}
		
			$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $option['id'] ) );
		
			// Set checkbox to false if it wasn't sent in the $_POST
			if ( 'checkbox' == $option['type'] && ! isset( $input[$id] ) ) {
				$input[$id] = false;
			}
		
			// Set each item in the multicheck to false if it wasn't sent in the $_POST
			if ( 'multicheck' == $option['type'] && ! isset( $input[$id] ) ) {
				foreach ( $option['options'] as $key => $value ) {
					$input[$id][$key] = false;
				}
			}
		
			// For a value to be submitted to database it must pass through a sanitization filter
			if ( has_filter( 'response_sanitize_' . $option['type'] ) ) {
				$clean[$id] = apply_filters( 'response_sanitize_' . $option['type'], $input[$id], $option );
			}
		}
	
		add_settings_error( 'response_options', 'save_options', __( 'Options saved.', 'response' ), 'updated fade' );
		return $clean;
	}
}

/**
 * Format Configuration Array.
 *
 * Get an array of all default values as set in
 * options.php. The 'id','std' and 'type' keys need
 * to be defined in the configuration array. In the
 * event that these keys are not present the option
 * will not be included in this function's output.
 *
 * @return    array     Rey-keyed options configuration array.
 *
 * @access    private
 */
function response_get_default_values() {
	$output = array();
	$config = response_get_fields();
	foreach ( (array) $config as $option ) {
		if ( ! isset( $option['id'] ) ) {
			continue;
		}
		if ( ! isset( $option['std'] ) ) {
			continue;
		}
		if ( ! isset( $option['type'] ) ) {
			continue;
		}
		if ( has_filter( 'response_sanitize_' . $option['type'] ) ) {
			$output[$option['id']] = apply_filters( 'response_sanitize_' . $option['type'], $option['std'], $option );
		}
	}
	return $output;
}

/**
 * Add Theme Options menu item to Admin Bar.
 */
function response_admin_bar() {
	global $wp_admin_bar;
	
	$wp_admin_bar->add_menu( array(
		'parent' => 'appearance',
		'id' => 'response_options_page',
		'title' => __( 'Theme Options', 'response' ),
		'href' => admin_url( 'themes.php?page=response-theme-options' )
	));
}