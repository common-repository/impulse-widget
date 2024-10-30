<?php
	/*
	Plugin Name: Impulse Widget
	Description: This plugin implements the Impulse functionality. Check the Impulse Settings for instructions.
	Version: 1.0.18
	Author: Enock Mudde, Annemiek Pronk
	License: GPL2
	*/

	class Impulse_Sprint_Ver_4_ImpulsePlugin {

		function __construct() {

			add_action( 'init', 'impulse_load_textdomain' );
			/**
			 * Load plugin textdomain.
			 *
			 * @since 1.0.0
			 */
			function impulse_load_textdomain() {
			  $loaded = load_plugin_textdomain( 'impulse', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			}

			if(!function_exists('deactivate_impulse')){
				register_deactivation_hook( __FILE__, 'impulse_deactivate' );
				function impulse_deactivate(){
					error_log('Impulse has been deactivated');
				}
			}

			if(!function_exists('has_trigger')){
				function has_trigger($post){
					$content = $post->post_content;
					$hasTrigger = true;
					if(!has_shortcode($content, 'impulse_trigger')) 
						$hasTrigger = false;
					
					return $hasTrigger;
				}
			}

			if(!function_exists('add_standard_impulse_trigger')){
				/**
				 * Filter the post content to check if it has a short code or not.
				 * Update the post meta if post default impulse status changed.
				 */
				add_filter('the_content', 'add_standard_impulse_trigger');
				function add_standard_impulse_trigger($content){
					global $post;
					$postType = get_post_type($post->ID);
					if($postType == 'page' || $postType == 'post'){
						if(in_the_loop() && is_main_query()){
							if(!has_shortcode($content, 'impulse_trigger')){
								$defaultTriggerSetting = get_post_meta($post->ID, '_impulse_trigger_option', true);
								if ($defaultTriggerSetting == 1){
									update_post_meta($post->ID, '_impulse_trigger_option', 0);
								}
							}
						}
					}

					return $content;
				}
			}

			if(!function_exists('add_default_trigger')){
				/**
				 * Filter the post title to add default trigger icon if the Default Trigger option is enabled
				 * 
				 */
				add_filter('the_title', 'add_default_trigger');
				function add_default_trigger($title){
					$options = get_option( 'impulse_options' );

					if(isset($options['impulse_field_default_trigger']) && $options['impulse_field_default_trigger'] == 'enabled' ){
						global $post;
						$postType = get_post_type($post->ID);
						$hasNoImpulse = has_trigger($post) == true ? 1 : 0;
						$fallbackIcon = plugin_dir_url(__FILE__) . 'assets/icons/Trigger_Gold_03.png';

						if($hasNoImpulse == 0){
							if($postType == 'page' || $postType == 'post'){
								#check if this page should have an auto trigger
								$autoTrigger = get_post_meta($post->ID, '_show_auto_trigger', true);
								if($autoTrigger == null || $autoTrigger == 1)
									$autoTrigger = 1;
								# else: auto trigger was set to 0 by user
								# they do not want it on this page/post
								# so chill out

								if(in_the_loop() && is_main_query() && $autoTrigger == 1){
									$options = get_option( 'impulse_options' );
									$triggerIcon = isset($options['impulse_field_default_trigger_icon']) ? $options['impulse_field_default_trigger_icon'] : $fallbackIcon;
						    		$clientId = isset($options['impulse_field_client_id']) ? $options['impulse_field_client_id'] : '';
						    		if($clientId != '' && is_singular()){
						    			#load custom impulse here
						    			$title = $title . " <img width='90' style='float: right;' class='impulseButton' clientId='" . esc_html( $clientId ) . "' src='" . esc_attr( $triggerIcon ) . "' />";

						    			#remove filter here
						    			remove_filter('the_title', 'add_default_trigger');
						    		}
								}
							}
						}
					}

					return $title;
				}
			}

			if(!function_exists('remove_impulse_menus')){
				/**
				 * Remove custom post menus so that users do9 not manually access them.
				 * 
				 */
				function remove_impulse_menus(){
					remove_menu_page('edit.php?post_type=impulse-logos');
					remove_menu_page('post-new.php?post_type=impulse-logos');

					remove_menu_page('edit.php?post_type=custom-triggers');
					remove_menu_page('post-new.php?post_type=custom-triggers');
				}
				add_action('admin_menu', 'remove_impulse_menus');
			}

			if(!function_exists('create_custom_trigger_post_type')){
				function create_custom_trigger_post_type() {
					register_post_type( 'custom-triggers',
					    array(
					      'labels' => array(
					        'name' => __( 'Custom Triggers' ),
					        'singular_name' => __( 'Custom Trigger' ),
					        'add_new'            => __( 'Add New' ),
							'add_new_item'       => __( 'Add New Custom Trigger' ),
							'new_item'           => __( 'New Custom Trigger' ),
							'edit_item'          => __( 'Edit Custom Trigger' ),
							'view_item'          => __( 'View Custom Trigger' ),
							'all_items'          => __( 'All Custom Triggers' ),
							'search_items'       => __( 'Search Custom Triggers' ),
					      ),
					      'public' => true,
					      'show_in_admin_bar' => false,
					      'show_in_nav_menus' => false,
					      'has_archive' => true,
					      'supports' => array( 'thumbnail' ),
					    )
					  );
				}
				add_action( 'init', 'create_custom_trigger_post_type' );
			}

			if(!function_exists('create_custom_logos_post_type')){
				function create_custom_logos_post_type() {
					register_post_type( 'impulse-logos',
					    array(
					      'labels' => array(
					        'name' => __( 'Impulse Logos' ),
					        'singular_name' => __( 'Impulse Logo' ),
					        'add_new'            => __( 'Add New' ),
							'add_new_item'       => __( 'Add New Impulse Logo' ),
							'new_item'           => __( 'New Impulse Logo' ),
							'edit_item'          => __( 'Edit Impulse Logo' ),
							'view_item'          => __( 'View Impulse Logo' ),
							'all_items'          => __( 'All Impulse Logos' ),
							'search_items'       => __( 'Search Impulse Logos' ),
							'show_ui'			 => false,
					      ),
					      'public' => true,
					      'show_in_admin_bar' => false,
					      'show_in_nav_menus' => false,
					      'has_archive' => true,
					      'supports' => array( 'thumbnail' ),
					    )
					  );
				}
				add_action( 'init', 'create_custom_logos_post_type' );
			}

			if(!function_exists('add_impulse_logo_metabox')){
				/**
				 * Meta box for saving logo for impulse-logos custom type
				 * 
				 */
				function add_impulse_logo_metabox(){
					add_meta_box(
						'custom_impulse_logo',
						__( 'Impulse Custom Logo', 'impulse' ),
						'show_impulse_image_html',
						'impulse-logos',
						'normal',
						'high'
					);
				}
				add_action('add_meta_boxes', 'add_impulse_logo_metabox');
			}

			if(!function_exists('add_impulse_image_metabox')){
				/**
				 * Meta box for saving an image for the custom-triggers custom type
				 * 
				 */
				function add_impulse_image_metabox(){
					add_meta_box(
						'custom_impulse_trigger',
						__( 'Impulse Custom Trigger', 'impulse' ),
						'show_impulse_image_html',
						'custom-triggers',
						'normal',
						'high'
					);
				}
				add_action('add_meta_boxes', 'add_impulse_image_metabox');
			}

			if(!function_exists('add_page_auto_trigger_metabox')){
				/**
				* Meta box for toggling the enable-auto-trigger for a particular page
				*/
				function add_page_auto_trigger_metabox(){
					add_meta_box(
						'page_auto_trigger_metabox',
						__( 'Enable / Disable Auto Trigger', 'impulse' ),
						'show_auto_trigger_html',
						'page',
						'side',
						'low'
					);
				}
				add_action('add_meta_boxes', 'add_page_auto_trigger_metabox');
			}

			if(!function_exists('add_post_auto_trigger_metabox')){
				function add_post_auto_trigger_metabox(){
					add_meta_box(
						'post_auto_trigger_metabox',
						__( 'Enable / Disable Auto Trigger', 'impulse' ),
						'show_auto_trigger_html',
						'post',
						'side',
						'low'
					);
				}
				add_action('add_meta_boxes', 'add_post_auto_trigger_metabox');
			}

			if(!function_exists('show_auto_trigger_html')){
				function show_auto_trigger_html(){
					global $post;
					$show_auto_trigger = null;

					if(isset($post->ID) && $post->ID > 0){
						$show_auto_trigger = get_post_meta($post->ID, '_show_auto_trigger', true);
					}

					?>
					<div>
						<select name="auto_trigger">
							<option value="1" <?php echo ($show_auto_trigger == null || $show_auto_trigger == 1) ? 'selected' : '';?> ><?php esc_html_e( 'Enabled', 'impulse' ); ?></option>
							<option value="0" <?php echo $show_auto_trigger != null && $show_auto_trigger == 0 ? 'selected' : '';?> ><?php esc_html_e( 'Disabled', 'impulse' ); ?></option>
						</select>
					</div>
					<?php
				}
			}

			if(!function_exists('save_page_auto_trigger')){
				function save_page_auto_trigger($post_id){
					// check autosave
				  	if ( wp_is_post_autosave( $post_id ) )
				      	return 'autosave';

				  	//check post revision
				  	if ( wp_is_post_revision( $post_id ) )
				    	return 'revision';

					$postType = get_post_type($post_id);
					if($postType == 'page' || $postType == 'post'){
						if(isset($_POST['auto_trigger']) && $_POST['auto_trigger'] != ''){
							$autoTriggerValue = intval($_POST['auto_trigger']);
							if($autoTriggerValue == 1 || $autoTriggerValue == 0){
								update_post_meta($post_id, '_show_auto_trigger', $autoTriggerValue);
							}
						}
					}
				}
				add_action('save_post', 'save_page_auto_trigger');
			}
			
			if(!function_exists('show_impulse_image_html')){
				/**
				 * Shows the UI for the custom-triggesr and impulse-logos meta boxes
				 * 
				 */
				function show_impulse_image_html() {
					#check if a return url was set
					#return url is set from within the plugin, so no need to validate/sanitize it
					$returnUrl = '';
					if(isset($_GET['return_url'])){
						$returnUrl = base64_decode($_GET['return_url']);
					}

					global $post;
				    // Use nonce for verification to secure data sending
				    wp_nonce_field( basename( __FILE__ ), 'wpse_impulse_logo_nonce' );

				    $imageSrc = '';
				    $imageId = get_post_meta($post->ID, '_impulse_attachment_id', true);
				    if($imageId != ''){
				    	$imageSrc = wp_get_attachment_image_src($imageId, 'full');
				    }

				    $imageExists = is_array($imageSrc);
				    ?>

				    <!-- my custom value input -->
				    <div id="logo-div" style="width: 100%; height: 300px; background: #f1f1f1;">
				    	<button type="button" id="logo-button" style="position: absolute; left: 45%; top: 40%; width: 10%; padding: 10px;"><?php esc_html_e( 'Select Logo', 'impulse' ); ?></button>
				    </div>
				    <div id="selected-logo" class="hidden" style="width: 50%; height: 300px;">
				    	<img id="logo-src" height="100%" />
				    </div>
				    <a href="<?php echo $returnUrl; ?>"><button style="margin: 5px; padding: 5px;" type="button" id="impulse-cancel"><?php esc_html_e( 'Cancel', 'impulse' ); ?></button></a>
				    <input type="hidden" name="attachment-id" id="selected-image-id" value="<?php echo esc_attr( $imageId ); ?>">
				    <input type="hidden" name="current-url" id="current-url" value="<?php echo $_GET['return_url']; ?>">

				    <script type="text/javascript">
				    	jQuery('#logo-button').on('click', function(){
				    		var frame;
				    		var selectLogoDiv = jQuery('#logo-div');
				    		var imageDiv = jQuery('#selected-logo');
				    		var logoSrc = jQuery('#logo-src');
				    		var imageAttachment = jQuery('#selected-image-id');

				    		if(frame){
					    		frame.open();
					    	}
					    	else{
					    		// Create a new media frame
							    frame = wp.media({
							      title: '<?php esc_html_e( 'Select Logo', 'impulse' ); ?>',
							      button: {
							        text: '<?php esc_html_e( 'Select Logo', 'impulse' ); ?>'
							      },
							      multiple: false  // Set to true to allow multiple files to be selected
							    });

							    
							    // When an image is selected in the media frame...
							    frame.on( 'select', function() {
							      
							      // Get media attachment details from the frame state
							      var attachment = frame.state().get('selection').first().toJSON();

							      // Send the attachment URL to our custom image input field.
							      //imgContainer.append( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );
							      logoSrc.attr('src', attachment.url);

							      // Send the attachment id to our hidden input
							      //imgIdInput.val( attachment.id );
							      imageAttachment.val( attachment.id );

							      // Hide the add image link
							      selectLogoDiv.addClass( 'hidden' );

							      // Unhide the remove image link
							      imageDiv.removeClass( 'hidden' );
							    });

							    // Finally, open the modal on click
							    frame.open();
					    	}
						  });
				    </script>
				    
				    <?php
				}
			}

			if(!function_exists('save_impulse_logo')){
				/**
				 * Saves an impulse-logo as post meta
				 * 
				 */
				function save_impulse_logo($post_id){
					$postType = get_post_type($post_id);
					if($postType != 'impulse-logos') return;

					// check autosave
				  	if ( wp_is_post_autosave( $post_id ) )
				      	return 'autosave';

				  	//check post revision
				  	if ( wp_is_post_revision( $post_id ) )
				    	return 'revision';

					if(isset($_POST['attachment-id'])){
						$attachmentId = intval($_POST['attachment-id']);
						if($attachmentId > 0){
							update_post_meta($post_id, '_impulse_attachment_id', $attachmentId);
						}
					}
				}
				add_action('save_post', 'save_impulse_logo', 10, 3);
			}

			if(!function_exists('save_impulse_trigger')){
				/**
				 * Saves a custom-reigger as post meta
				 * 
				 */
				function save_impulse_trigger($post_id){
					$postType = get_post_type($post_id);
					if($postType != 'custom-triggers') return;

					// check autosave
				  	if ( wp_is_post_autosave( $post_id ) )
				      	return 'autosave';

				  	//check post revision
				  	if ( wp_is_post_revision( $post_id ) )
				    	return 'revision';

					if(isset($_POST['attachment-id'])){
						$attachmentId = intval($_POST['attachment-id']);
						if($attachmentId > 0){
							update_post_meta($post_id, '_impulse_attachment_id', $attachmentId);
						}
					}
				}
				add_action('save_post', 'save_impulse_trigger', 10, 3);
			}

			/**
			 * Redirect user to either the Impulse Settings page or the post he was trying to add a Custom Impulse to after 
			 * uploading and saving an impulse-logo or custom-trigger
			 * 
			 */
			add_filter('redirect_post_location', function($location){
			    global $post;
			    $type = get_post_type($post->ID);
			    if (isset($_POST['publish'])) {
			    	if($type == 'impulse-logos'){
			    		$location = admin_url() . 'admin.php?page=impulse';
			    	}
			    	else if($type == 'custom-triggers'){
			    		$location = base64_decode($_POST['current-url']);
			    	}
			    }

			    return $location;
			});

			if(!function_exists('impulse_plugin_add_styles')){
				add_action('init', 'impulse_plugin_add_styles');
				function impulse_plugin_add_styles(){
					wp_register_style( 'impulse_plugin_css', 'https://widget.impulse.click/impulse_widget.css');
					wp_enqueue_style( 'impulse_plugin_css' );
				}
			}

			add_action('wp_head', function(){
				echo "
					<style>
						.impulseButton{
							cursor: pointer;
						}
					</style>
				";
			});

			if(!function_exists('impulse_plugin_add_js')){
				add_action('wp_footer', 'impulse_plugin_add_js', 1000);
				function impulse_plugin_add_js(){
					wp_enqueue_media();

			    	$options = get_option( 'impulse_options' );
			    	$navColor = isset($options['impulse_field_nav_color']) ? $options['impulse_field_nav_color'] : '#fc0';
			    	$widgetLogo = isset($options['impulse_field_widget_logo']) ? $options['impulse_field_widget_logo']: 'https://widget.impulse.click/AV_logo.png';
			    	$clientID = isset($options['impulse_field_client_id']) ? $options['impulse_field_client_id'] : '';

			    	echo
                        "<script type='text/javascript'>
                            
                                var impulseObject = {
                                    companyLogo: '$widgetLogo',
                                    fitLogo: true,
                                    navColor: '$navColor',
                                    clientId: '$clientID'
                                };
						</script>
						";

			    	echo "<script src='https://widget.impulse.click/dist/build.js'></script>";
				}
			}

			if(!function_exists('impulse_shortcode_handler')){
				/**
				 * Impulse short code handler
				 * Attributes: button, image, link
				 * 
				 * returns: a formatted impulse trigger. Can be an image, a url or a button
				 */
				function impulse_shortcode_handler($atts){
					global $post;

					if(isset($post->ID) && $post->ID > 0){
                        update_post_meta($post->ID, '_impulse_trigger_option', 1);
                    }

					$options = get_option( 'impulse_options' );
					$result = '';
			    	$clientId = isset($options['impulse_field_client_id']) ? $options['impulse_field_client_id'] : '';
			    	if ($clientId != ''){
			    		$buttonText = ''; $linkUrl = ''; $linkTitle = '';  $imageUrl = '';
						if(isset($atts['button']) && $atts['button'] != ''){
							$buttonText = esc_html( $atts['button'] );
							$result = "<a href='https://impulse.click'><button>$buttonText</button></a>";
						}

						if(isset($atts['title']) && $atts['title'] != ''){
							$linkTitle = esc_html( $atts['title'] );

							$result = "<a href='https://impulse.click'>$linkTitle</a>";
						}

						if(isset($atts['image']) && $atts['image'] != ''){
							$imageUrl = esc_url($atts['image']);
							$customTrigger = $atts['custom'];
							if(isset($imageUrl) && $imageUrl != ''){
								$align = isset($atts['align']) ? $atts['align'] : 'none';

								if(isset($customTrigger)){
									$selectedWidth = isset($atts['size']) ? $atts['size'] : 'normal';

									#set initial width
									$width = '100%';

									if($selectedWidth == 'small') $width = '150px';
									if($selectedWidth == 'medium') $width = '300px';

									if($align== 'left' || $align == 'right'){
										$result = "<a href='https://impulse.click'><img style='margin: 7px; float: $align;' class='impulseButton' src='" . esc_attr( $imageUrl ) ."' width='$width' /></a>";
									}
									else{
										#none, center?!
										$result = "<div style='text-align: center;'><a href='https://impulse.click'><img src='" . esc_attr( $imageUrl ) ."' width='$width' /></a></div>";
									}
								}
								else{
									if($align== 'left' || $align == 'right'){
										$result = "<a href='https://impulse.click'><img style='margin: 7px; float: $align;' src='" . esc_attr( $imageUrl ) ."' width='90' height='90' /></a>";
									}
									else{
										#none, center?!
										$result = "<div style='text-align: center'><a href='https://impulse.click'><img src='" . esc_attr( $imageUrl ) ."' style='align:$align;' width='90' height='90' /></a></div>";
									}
								}
							}
						}
			    	}

			    	return $result;
				}
				add_shortcode('impulse_trigger', 'impulse_shortcode_handler');
			}

			if(!function_exists('impulse_plugin_add_color_picker_js')){
				/**
				 * Enqueue plugin specific JS files
				 */
				add_action('admin_enqueue_scripts', 'impulse_plugin_add_color_picker_js');
				function impulse_plugin_add_color_picker_js() { 
					if(is_admin()){
						wp_enqueue_style( 'wp-color-picker' ); 
				    	wp_enqueue_script( 'impulse_color_picker_js', plugins_url( '/assets/js/impulse_color_picker.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ), '', true  );
				    	wp_enqueue_script( 'jquery-form' );
					}
				}
			}

			if(!function_exists('impulse_settings_init')){
				function impulse_settings_init() {
					 // register a new setting for "wporg" page
					 register_setting( 'impulse', 'impulse_options' );
					 
					 // register a new section in the "wporg" page
					 add_settings_section(
						 'impulse_section_developers',
						 __( 'Impulse Plugin Settings', 'impulse' ),
						 'impulse_section_developers_cb',
						 'impulse'
					 );

				 	add_settings_field(
						 'impulse_field_client_id',
						 __( 'Client ID', 'impulse' ),
						 'impulse_field_client_id_cb',
						 'impulse',
						 'impulse_section_developers',
						 array(
						 'label_for' => 'impulse_field_client_id',
						 'class' => 'impulse_row',
						 'impulse_custom_data' => 'custom',
						 )
					 );

				 	add_settings_field(
						 'impulse_field_nav_color',
						 __( 'Color Widget Header', 'impulse' ),
						 'impulse_field_nav_color_cb',
						 'impulse',
						 'impulse_section_developers',
						 array(
						 'label_for' => 'impulse_field_nav_color',
						 'class' => 'impulse_row',
						 'impulse_custom_data' => 'custom',
						 )
					 );

				 	add_settings_field(
						 'impulse_field_widget_logo',
						 __( 'Logo Widget Header', 'impulse' ),
						 'impulse_field_widget_logo_cb',
						 'impulse',
						 'impulse_section_developers',
						 array(
						 'label_for' => 'impulse_field_widget_logo',
						 'class' => 'impulse_row',
						 'impulse_custom_data' => 'custom',
						 )
					 );

				 	add_settings_field(
						 'impulse_field_default_trigger',
						 __( 'Default Theme Trigger', 'impulse' ),
						 'impulse_field_default_trigger_cb',
						 'impulse',
						 'impulse_section_developers',
						 array(
						 'label_for' => 'impulse_field_default_trigger',
						 'class' => 'impulse_row',
						 'impulse_custom_data' => 'custom',
						 )
					 );

				 	add_settings_field(
						 'impulse_field_default_trigger_icon',
						 __( 'Default Trigger Icon', 'impulse' ),
						 'impulse_field_default_icon_cb',
						 'impulse',
						 'impulse_section_developers',
						 array(
						 'label_for' => 'impulse_field_default_trigger_icon',
						 'class' => 'impulse_row',
						 'impulse_custom_data' => 'custom',
						 )
					 );
				}
				 
				/**
				 * register impulse_settings_init to the admin_init action hook
				 */
				add_action( 'admin_init', 'impulse_settings_init' );
			}
			 
			if(!function_exists('impulse_section_developers_cb')){
				/**
				 * custom option and settings:
				 * callback functions
				 */
				function impulse_section_developers_cb( $args ) {
				 ?>
				 	<div style="margin-left: 45px; margin-bottom: 20px;">
				 	<p><b><?php esc_html_e( 'Impulse Usage Notes:', 'impulse' ); ?></b></p>
				 	<p style="display: list-item;"><?php _e( 'Fill in your Client ID that you received from Impulse in the e-mail. Do you not have a Client ID? Please send an email to <a href="mailto:info@impulse.click">info@impulse.click</a>', 'impulse' ); ?></p>
				 	<p style="display: list-item;"><?php esc_html_e( 'Set the color for the Impulse Widget header using the color path or fill in the html code of the color you want. This color has to match with the style of your website.', 'impulse' ); ?> </p>
				 	<p style="display: list-item;"><?php esc_html_e( 'Select your logo. Your logo will appear in the Impulse Widget.', 'impulse' ); ?></p>
				 	<p style="display: list-item;"><?php esc_html_e( 'Do not forget to click on save.', 'impulse' ); ?></p>
				 	<p style="display: list-item;"><?php esc_html_e( 'The default trigger can be disabled for every page, by clicking on ’disable auto trigger” on the right side of the page.', 'impulse' ); ?></p>
				 	<p style="display: list-item;"><?php esc_html_e( 'When editing a Post or Page, you can click the \'Add Impulse\' button to add a trigger to it.', 'impulse' ); ?> </p>
				 	<hr>
				 	</div>
				 <?php
				}
			}


			if(!function_exists('impulse_field_client_id_cb')){
				function impulse_field_client_id_cb( $args ){
					// get the value of the setting we've registered with register_setting()
					 $options = get_option( 'impulse_options' );
					 // output the field
					 ?>
					 <input type="text" style="width: 250px; padding: 8px;" d="<?php echo esc_attr( $args['label_for'] ); ?>"
					 data-custom="<?php echo esc_attr( $args['impulse_custom_data'] ); ?>"
					 placeholder="Enter Client ID here"
					 name="impulse_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ''; ?>" />

					 <p class="description">
					 	<?php esc_html_e( 'Fill in your Client ID here. It will be used to display an Impulse Product on your website.', 'impulse' ); ?>
					 </p>
				 <?php
				}
			}

			if(!function_exists('impulse_field_default_trigger_cb')){
				function impulse_field_default_trigger_cb( $args ){
					$options = get_option( 'impulse_options' );
					 // output the field
					 ?>
					 <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
					 data-custom="<?php echo esc_attr( $args['impulse_custom_data'] ); ?>"
					 name="impulse_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
					 >
					 <option value="disabled" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'disabled', false ) ) : ( '' ); ?>>
					 <?php esc_html_e( 'Disabled', 'impulse' ); ?>
					 </option>
					 <option value="enabled" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'enabled', false ) ) : ( '' ); ?>>
					 <?php esc_html_e( 'Enabled', 'impulse' ); ?>
					 </option>
					 </select>
					 <p class="description">
					 	<?php esc_html_e( 'Do you want to enable a default trigger for all posts and pages?', 'impulse' ); ?>
					 </p>
					 <?php
				}
			}

			if(!function_exists('impulse_field_default_icon_cb')){
				function impulse_field_default_icon_cb( $args ){
					$options = get_option( 'impulse_options' );
					$defaultIconWhite =  plugin_dir_url(__FILE__) . 'assets/icons/Trigger_White_03.png';
					$defaultIconGold = plugin_dir_url(__FILE__) . 'assets/icons/Trigger_Gold_03.png';

					?>
						<div class="standard-trigger" style="margin-bottom: 25px; margin-right: 25px; float:left;"><label for="logo"><img src="<?php echo esc_attr( $defaultIconWhite ); ?>" height="60"></label><br><input type="radio" style="margin-left: 20px; margin-top:15px;" name="impulse_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $defaultIconWhite; ?>" <?php echo checked($defaultIconWhite, $options[ $args['label_for'] ], false); ?> /></div>
						<div class="standard-trigger" style="margin-bottom: 25px; margin-right: 25px; float:left;"><label for="logo"><img src="<?php echo esc_attr( $defaultIconGold ); ?>" height="60"></label><br><input type="radio" style="margin-left: 20px; margin-top:15px;" name="impulse_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $defaultIconGold; ?>" <?php echo checked($defaultIconGold, $options[ $args['label_for'] ], false); ?> /></div>
					<?php
				}
			}

			if(!function_exists('impulse_field_nav_color_cb')){
				/**
				 * Impulse color widget callback
				 * 
				 * @settings
				 */
				function impulse_field_nav_color_cb( $args ){
					// get the value of the setting we've registered with register_setting()
					 $options = get_option( 'impulse_options' );
					 // output the field
					 ?>
					 <input type="text" d="<?php echo esc_attr( $args['label_for'] ); ?>"
					 class="impulse_plugin-color-picker"
					 data-custom="<?php echo esc_attr( $args['impulse_custom_data'] ); ?>"
					 name="impulse_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : ''; ?>" />

					 <p class="description">
					 	<?php esc_html_e( 'Set the color for the Impulse Widget header using the color path or fill in the html code of the color you want.', 'impulse' ); ?>
					 </p>
				 <?php
				}
			}

			if(!function_exists('impulse_field_widget_logo_cb')){
				function impulse_field_widget_logo_cb( $args ){
					 #Set the correct return url hash just in case a user clicks 'Upload Logo' and then clicks 'Cancel'
					 $returnUrl = base64_encode(admin_url('admin.php?page=impulse'));
					 
					 // get the value of the setting we've registered with register_setting()
					 $options = get_option( 'impulse_options' );
					 // output the field

					 $args = array('post_type' => 'impulse-logos', 'numberposts' => 6);
					 $logos = get_posts($args);

					 $logoCount = 0;
					 if(count($logos) > 0){
						foreach ($logos as $logo) {
							$imageSrc = '';
						    $imageId = get_post_meta($logo->ID, '_impulse_attachment_id', true);
						    if($imageId != ''){
						    	$imageSrc = wp_get_attachment_image_src($imageId, 'full');
						    }

						    $imageExists = is_array($imageSrc);

						    if($imageExists){
						    	echo '<div class="standard-trigger" style="margin-bottom: 25px; margin-right: 25px; float:left;"><label for="logo"><img src="' . esc_attr( $imageSrc[0] ) . '" height="60"></label><br><input type="radio" style="margin-left: 20px; margin-top:15px;" name="impulse_options[impulse_field_widget_logo]" value="' . esc_attr( $imageSrc[0]) . '" ' . checked($imageSrc[0], $options[ 'impulse_field_widget_logo' ], false) . ' /></div>';

						    	/* This checks if we have printed out 5 logos, then prints out a new line {from 0 -> 4} */
						    	if($logoCount == 4){
									echo '<div style="clear:both;"></div>';
									$logoCount = 0;
									continue;
								}

								$logoCount++;
						    }
						}
					 }
					 ?>

					 <p class="description" style="clear: both;">
					 	<?php esc_html_e( 'Select your logo. Your logo will appear in the Impulse Widget.', 'impulse' ); ?><br>
					 	<a style="margin-top: 10px;" id="upload-logo-button" onclick="uploadLogo();" link="<?php echo admin_url('post-new.php?post_type=impulse-logos&return_url=' . $returnUrl); ?>" class="button" ><?php esc_html_e( 'Upload New Logo', 'impulse' ); ?></a>
					 </p>
				 <?php
				}
			}
			 
			if(!function_exists('impulse_field_pill_cb')){
				// pill field cb
			 
				// field callbacks can accept an $args parameter, which is an array.
				// $args is defined at the add_settings_field() function.
				// wordpress has magic interaction with the following keys: label_for, class.
				// the "label_for" key value is used for the "for" attribute of the <label>.
				// the "class" key value is used for the "class" attribute of the <tr> containing the field.
				// you can add custom key value pairs to be used inside your callbacks.
				function impulse_field_pill_cb( $args ) {
					 // get the value of the setting we've registered with register_setting()
					 $options = get_option( 'impulse_options' );
					 // output the field
					 ?>
					 <select id="<?php echo esc_attr( $args['label_for'] ); ?>"
					 data-custom="<?php echo esc_attr( $args['impulse_custom_data'] ); ?>"
					 name="impulse_options[<?php echo esc_attr( $args['label_for'] ); ?>]"
					 >
					 <option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
					 <?php esc_html_e( 'red pill', 'impulse' ); ?>
					 </option>
					 <option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
					 <?php esc_html_e( 'blue pill', 'impulse' ); ?>
					 </option>
					 </select>
					 <p class="description">
					 <?php esc_html_e( 'You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'impulse' ); ?>
					 </p>
					 <p class="description">
					 <?php esc_html_e( 'You take the red pill and you stay in Wonderland and I show you how deep the rabbit-hole goes.', 'impulse' ); ?>
					 </p>
				 <?php
				}
			}

			if(!function_exists('impulse_options_page')){
				/**
				 * top level menu
				 */
				function impulse_options_page() {
					 // add top level menu page
					 add_menu_page(
					 'Impulse',
					 __( 'Impulse Settings', 'impulse' ),
					 'manage_options',
					 'impulse',
					 'impulse_options_page_html'
					 );
				}
				/**
				 * register our wporg_options_page to the admin_menu action hook
				 */
				add_action( 'admin_menu', 'impulse_options_page' );
			}

			if(!function_exists('impulse_options_page_html')){
				/**
				 * top level menu:
				 * callback functions
				 */
				function impulse_options_page_html() {
					 // check user capabilities
					 if ( ! current_user_can( 'manage_options' ) ) {
					 return;
					 }
					 
					 // add error/update messages
					 
					 // check if the user have submitted the settings
					 // wordpress will add the "settings-updated" $_GET parameter to the url
					 if ( isset( $_GET['settings-updated'] ) ) {
						 // add settings saved message with the class of "updated"
						 add_settings_error( 'impulse_messages', 'impulse_message', __( 'Settings Saved', 'impulse' ), 'updated' );
					 }
					 
					 // show error/update messages
					 settings_errors( 'impulse_messages' );
					 ?>
					 <div class="wrap">
					 <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
					 <form id="impulse-form" action="options.php" method="post">
						 <?php
						 // output security fields for the registered setting "wporg"
						 settings_fields( 'impulse' );
						 // output setting sections and their fields
						 // (sections are registered for "wporg", each field is registered to a specific section)
						 do_settings_sections( 'impulse' );
						 // output save settings button
						 submit_button( __('Save Settings', 'impulse') );
						 ?>
					 </form>
					 <script>
					 	function uploadLogo(){
					 		var impulseForm = jQuery('#impulse-form').serialize();
					 		jQuery.post( 'options.php', impulseForm ).error( 
			                    function() {
			                        alert('An error occurred while saving the form.');
			                    }).success( function() {
			                        var link = jQuery('#upload-logo-button').attr('link');
			                        jQuery(location).attr('href', link);
			                    });
					 	}
					 </script>
					 </div>
				 <?php
				}
			}

			if(!function_exists('impulse_plugin_register_impulse_button')){
				// register new button in the editor
				function impulse_plugin_register_impulse_button( $buttons ) {
			            array_push( $buttons, 'impulse_button' );
			            return $buttons;
				}
			}

			if(!function_exists('impulse_plugin_add_tinymce_plugin')){
				// declare a script for the new button
				// the script will insert the shortcode on the click event
				function impulse_plugin_add_tinymce_plugin( $plugin_array ) {
			          $plugin_array['impulse_button'] = plugins_url('/assets/js/impulse_editor_button.js', __FILE__);
			          return $plugin_array;
				}
			}

			############################
			add_action( 'admin_footer', 'wp_doin_mce_popup' );
			add_action( 'media_buttons', 'wp_doin_media_buttons' );
			/**
			 * Utility to add MCE Popup fired by custom Media Buttons button
			 * 
			 * @hook admin_footer
			 */
			function wp_doin_mce_popup() {
					?>
					<script>
						var insertedTrigger = 0;

						function InsertContainer() {
							// let's obtain the values of the fields
							var buttonText = jQuery('#button_trigger').val();
							var linkTitle = jQuery('#link_text_trigger').val();
							
							var isCustom = false;
							var imageTrigger = null;
							if(document.querySelector('input[name="image-trigger"]:checked') != null){
								imageTrigger = document.querySelector('input[name="image-trigger"]:checked').value;
							}
							else if(document.querySelector('input[name="custom-image-trigger"]:checked') != null){
								isCustom = true;
								imageTrigger = document.querySelector('input[name="custom-image-trigger"]:checked').value;
							}

							//check if image alignment has been set
							var selectedImageAlignment = document.getElementById('trigger-image-alignment').value;
							
							//check if image size has been set
							var selectedImageSize = document.getElementById('trigger-image-size').value;

							//reset form
							document.getElementById('impulse-form').reset(); 

							var triggerCounter = 0;
							if(buttonText != '') triggerCounter++;
							if(linkTitle != '') triggerCounter++;
							if(imageTrigger != null) triggerCounter++;

							if(triggerCounter != 1){
								alert('Please use only 1 trigger for every post/page: ' + imageTrigger);
							}
							else{
								if(buttonText != ''){
									window.send_to_editor("<br>[impulse_trigger button='"+ buttonText +"']<br>");
								}
								
								if(linkTitle != ''){
									window.send_to_editor("<br>[impulse_trigger title='" + linkTitle + "']<br>");
								}
								if(imageTrigger != null){
									if(isCustom){
										window.send_to_editor("<br>[impulse_trigger image='"+ imageTrigger +"' custom='true' align='" + selectedImageAlignment + "' size='" + selectedImageSize + "']<br>");
									}
									else{
										window.send_to_editor("<br>[impulse_trigger image='"+ imageTrigger +"' align='" + selectedImageAlignment + "']<br>");
									}
								}
							}
						}

					</script>

					<div id="wp_doin_div_shortcode" style="display:none;">
						<div class="wrap wp_doin_shortcode">
							<div>
								<form id="impulse-form">
								<div style="padding:15px 15px 0 15px;">
									<h3 style="color:#5A5A5A!important;font-size:20px!important; font-weight:normal!important;"><?php esc_html_e( 'Impulse Trigger', 'impulse' ); ?></h3>
									<div class="field-container">
										<p class="trigger-header"><?php esc_html_e( 'Do you want one of these standard triggers?', 'impulse' ); ?></p>

										<?php
											$i = 1;
											$baseName = 'Trigger_Gold_0';
											while($i <= 6){
												$fullIconName = plugin_dir_url(__FILE__) . 'assets/icons/' . $baseName . $i . '.png';
												echo '<div class="standard-trigger"><label for="contactChoice2"><img src="' . esc_attr( $fullIconName ) . '"></label><br><input type="radio" class="radio-button" name="image-trigger" value="' . esc_attr( $fullIconName ) . '" /></div>';
												$i++;
											}

											$j = 1;
											$baseName = 'Trigger_White_0';
											while($j <= 6){
												$fullIconName = plugin_dir_url(__FILE__) . 'assets/icons/' . $baseName . $j . '.png';
												echo '<div class="standard-trigger"><label for="contactChoice2"><img src="' . esc_attr( $fullIconName ) . '"></label><br><input type="radio" class="radio-button" name="image-trigger" value="' . esc_attr( $fullIconName ) . '" /></div>';
												$j++;
											}
										?>
									</div>

									<div class="field-container">
										<p class="trigger-header"><?php esc_html_e( 'Or do you want to use a custom trigger? Please save the CONTENT before clicking the "Upload" button', 'impulse' ); ?></p>

										<?php
											$args = array('post_type' => 'custom-triggers', 'numberposts' => 6);
											$posts = get_posts($args);
											if(count($posts) > 0){
												foreach ($posts as $post) {
													$imageSrc = '';
												    $imageId = get_post_meta($post->ID, '_impulse_attachment_id', true);
												    if($imageId != ''){
												    	$imageSrc = wp_get_attachment_image_src($imageId, 'full');
												    }

												    $imageExists = is_array($imageSrc);

												    if($imageExists){
												    	echo '<div class="standard-trigger" style="margin-bottom: 25px;"><label for="logo"><img src="' . esc_attr( $imageSrc[0] ) . '" /></label><br><input type="radio" style="margin-left: 20px;" name="custom-image-trigger" value="' . esc_attr( $imageSrc[0] )  . '" /></div>';
												    }
												}
											}

											#set return link to this very post
											$protocol = is_ssl() ? 'https://' : 'http://';
											$returnUrl = base64_encode($protocol . $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
										?>
										
										<div style="clear: both; display: block;">
											<a href="<?php echo admin_url('post-new.php?post_type=custom-triggers&return_url=' . $returnUrl); ?>" style="margin-top: 10px;" class="button"><?php esc_html_e( 'Upload custom trigger', 'impulse' ); ?></a>
										</div>
										<hr>
									</div>

									<div style="clear: both; display: block;" class="field-container">
										<label for="contactChoice2"><?php esc_html_e( 'Image Size (only for custom triggers): ', 'impulse' ); ?></label><br>
										<select id="trigger-image-size">
											<option value="small"><?php esc_html_e( 'Small (width: 150px)', 'impulse' ); ?></option>
											<option value="medium"><?php esc_html_e( 'Medium (width: 300px)', 'impulse' ); ?></option>
											<option selected value="normal"><?php esc_html_e( 'Original Size (default)', 'impulse' ); ?></option>
										</select>
									</div>

									<div class="field-container">
										<label for="contactChoice2"><?php esc_html_e( 'Image Alignment :', 'impulse' ); ?></label><br>
										<select id="trigger-image-alignment">
											<option selected value="none"><?php esc_html_e( 'None', 'impulse' ); ?></option>
											<option value="left"><?php esc_html_e( 'Left', 'impulse' ); ?></option>
											<option value="right"><?php esc_html_e( 'Right', 'impulse' ); ?></option>
											<option value="center"><?php esc_html_e( 'Center', 'impulse' ); ?></option>
										</select>
									</div>
									<hr>

									<div class="field-container">
										<label for="contactChoice2"><?php esc_html_e( 'Button Trigger', 'impulse' ); ?></label>
										<br><input style="width: 350px; padding: 8px;" type="text" placeholder="<?php esc_html_e( 'button text here', 'impulse' ); ?>" id="button_trigger" name="contact" />
									</div>

									<div class="field-container">
										<label for="contactChoice2"><?php esc_html_e( 'Hyperlink Trigger', 'impulse' ); ?></label>
										<br><input style="width: 350px; padding: 8px;" type="text" placeholder="<?php esc_html_e( 'hyperlink title eg Click Here', 'impulse' ); ?>" val="" id="link_text_trigger" />
									</div>
								</div>

								<hr />
								<div style="padding:15px;">
									<input type="button" class="button-primary" value="<?php esc_html_e( 'Insert into Content', 'impulse' ); ?>" onclick="InsertContainer(); tb_remove();"/>&nbsp;&nbsp;&nbsp;
									<a class="button" href="#" onclick="tb_remove(); return false;"><?php esc_html_e( 'Cancel', 'impulse' ); ?></a>
								</div>
								</form>
							</div>
						</div>
					</div>
					<?php
				}

			/**
			 * Utility to add MCE Popup button to the Media Buttons section which lies directly 
			 * above the Visual / Text Editor
			 * 
			 * @hook media_buttons
			 */
			function wp_doin_media_buttons() {
				?>
				<style>
						.trigger-header {
							font-size: 16px !important;
							margin-top: 0 !important;
						}
						.standard-trigger{
							float: left;
							margin: 10px;
						}

						.standard-trigger img{
							height: 60px !important;
						}

						.standard-trigger input{
							margin-left: 20px !important;
						}

						.wp-core-ui a.editr_media_link {
							padding-left: 0.4em;
						}
						.label-desc {
							width: 27%;
							margin-right: 3%;
							float: left;
							font-weight: bold;
							text-align: right;
							padding-top: 2px;
						}
						.wp_doin_shortcode .content {
							float: left;
							width: 70%;
						}
						.field-container {
							margin: 5px 0;
							display: inline-block;
							width: 100%;
						}
						#TB_ajaxContent h3 {
							margin-bottom: 0;
							margin-top: 0;
						}

						#TB_ajaxContent{
							width: 100% !important;
							min-height: 800px !important;
						}
						#TB_window{
							min-height: 600px !important;
							overflow-y: scroll;
						}
					</style>
				<a href="#TB_inline?width=100%&height=1000&inlineId=wp_doin_div_shortcode" class="button thickbox wp_doin_media_link" id="add_div_shortcode" title="Impulse Trigger"><span><img style="width:19px;" src="<?php echo plugin_dir_url(__FILE__) . 'assets/js/default.png'; ?>"></span><?php esc_html_e( 'Add Impulse', 'impulse' ); ?></a>
				<?php
			}
		}
	}

	global $impulse;
	$impulse = new Impulse_Sprint_Ver_4_ImpulsePlugin();
?>