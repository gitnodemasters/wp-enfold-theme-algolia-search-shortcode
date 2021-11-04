<?php
/**
 * Search form as Avia Layout Builder element
 * Displays a search field that stretches across the available space
 * Option: select the post types that should be included in the search
 * Option: enable/disable ajax
 * Option: Display results either on a separate page (classic search page) or on the same page below the search form
 *
 *
 * @author tinabillinger
 * @since 4.4
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {  die('-1');  }


if( ! class_exists('avia_sc_algolia_search')) 
{
    class avia_sc_algolia_search extends aviaShortcodeTemplate
    {

        /**
         * Create the config array for the shortcode button
         */
        function shortcode_insert_button()
        {
			$this->config['version']		= '1.0';
            $this->config['self_closing']	= 'yes';

            $this->config['name']		= __( 'Algolia Search', 'avia_framework');
            $this->config['tab']		= __( 'Content Elements', 'avia_framework');
            // $this->config['icon']		= AviaBuilder::$path['imagesURL'] . 'algolia.png';
			$this->config['icon']		= AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/algolia_search/src/algolia.png';
            $this->config['order']		= 2;
            $this->config['shortcode']	= 'avia_sc_algolia_search';
            $this->config['tooltip']	= __( 'Displays a Algolia search form', 'avia_framework');
            $this->config['target']		= 'avia-target-insert';
            $this->config['tinyMCE']	= array( 'disable' => true) ;
            // $this->config['preview']	= 1;
            $this->config['disabling_allowed'] = true;
			$this->config['id_name']	= 'id';
			$this->config['id_show']	= 'yes';
        }

        function extra_assets()
        {
            wp_enqueue_style('avia-sc-algolia-search', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/algolia_search/src/algolia_search.css', array('avia-layout'), false);
			wp_enqueue_script( 'avia-sc-algolia-search', AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/algolia_search/src/algolia_search.js', array( 'avia-shortcodes' ), false, true );
		}

        /**
         * Popup Elements
         *
         * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
         * opens a modal window that allows to edit the element properties
         *
         * @return void
         */
        function popup_elements()
        {
            

            $this->elements = array(
				
				array(
						'type' 	=> 'tab_container', 
						'nodescription' => true
					),

				array(
						'type' 	=> 'tab',
						'name'  => __( 'Setting', 'avia_framework' ),
						'nodescription' => true
					),
					array(
						'type'			=> 'template',
						'template_id'	=> 'toggle_container',
						'templates_include'	=> array( 
													$this->popup_key( 'algolia_creds_toggle' )
											),
						'nodescription' => true
					),
				
				
				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				array(
						'type' 	=> 'tab',
						'name'  => __( 'Content', 'avia_framework' ),
						'nodescription' => true
					),
				
					array(
							'type'			=> 'template',
							'template_id'	=> 'toggle_container',
							'templates_include'	=> array( 
													$this->popup_key( 'content_type' ),
													// $this->popup_key( 'content_form' ),
												),
							'nodescription' => true
						),

				array(
						'type' 	=> 'tab_close',
						'nodescription' => true
					),
				
				// array(
				// 		'type' 	=> 'tab',
				// 		'name'  => __( 'Layout', 'avia_framework' ),
				// 		'nodescription' => true
				// 	),
				
				// 	array(
				// 			'type'			=> 'template',
				// 			'template_id'	=> $this->popup_key( 'layout_result' )
				// 		),
				
				// array(
				// 		'type' 	=> 'tab_close',
				// 		'nodescription' => true
				// 	),
				
				// array(
				// 		'type' 	=> 'tab',
				// 		'name'  => __( 'Styling', 'avia_framework' ),
				// 		'nodescription' => true
				// 	),
				
				// 	array(
				// 			'type'			=> 'template',
				// 			'template_id'	=> 'toggle_container',
				// 			'templates_include'	=> array( 
				// 									$this->popup_key( 'styling_fonts' ),
				// 									// $this->popup_key( 'styling_form' ),
				// 									// $this->popup_key( 'styling_result' ),
				// 									$this->popup_key( 'styling_colors_form' ),
				// 									$this->popup_key( 'styling_colors_result' )
				// 								),
				// 			'nodescription' => true
				// 		),
				
				// array(
				// 		'type' 	=> 'tab_close',
				// 		'nodescription' => true
				// 	),

				array(
						'type' 	=> 'tab_container_close',
						'nodescription' => true
					)
                

            );
        }
		
		/**
		 * Create and register templates for easier maintainance
		 * 
		 * @since 4.6.4
		 */
		protected function register_dynamic_templates()
		{
			
			// assemble available post types
            $pt_args = array(
							'public'				=> true,
							'exclude_from_search'	=> false,
						);

            $pt_list = get_post_types( $pt_args, 'objects' );
            $select_list = array();

            if( ! empty( $pt_list ) ) 
			{
                foreach( $pt_list as $pk => $pt )
				{
                    $exclude = array( 'avia_framework_post', 'attachment', 'tribe-ea-record' );
                    if( ! in_array( $pk, $exclude ) ) 
					{
                        $select_list[ $pt->labels->name ] = $pk;
                    }
                }
            }
			
			
			/**
			 * Content Tab
			 * ===========
			 */
			
			$c = array(
						 array(
							'name' 	=> __( 'Placeholder', 'avia_framework' ),
							'desc' 	=> __( 'Enter a placeholder text for the input field', 'avia_framework' ) ,
							'id' 	=> 'placeholder',
							'std' 	=> __( 'Search the site ...', 'avia_framework' ),
							'type' 	=> 'input'
						),

						array(
							'name' 	=> __( 'Label Text', 'avia_framework' ),
							'desc' 	=> __( 'Enter a label text for the button', 'avia_framework' ) ,
							'id' 	=> 'label_text',
							'std' 	=> __( 'Find', 'avia_framework' ),
							'type' 	=> 'input',
						),

						array(
							'name' => __( 'Icon Display', 'avia_framework'),
							'desc' => __( 'Where should the icon be displayed?', 'avia_framework' ),
							'id' => 'icon_display',
							'type' => 'select',
							'std' => '',
							'subtype'	=> array(
												__( 'No Icon', 'avia_framework' )		=> '',
												__( 'Input Field', 'avia_framework' )	=> 'input',
												__( 'Button', 'avia_framework' )		=> 'button',
											)
						),

						array(
							'name'  => __( 'Search Icon', 'avia_framework' ),
							'desc'  => __( 'Select an Icon below', 'avia_framework' ),
							'id'    => 'icon',
							'type'  => 'iconfont',
							'required'	=> array( 'icon_display', 'not', '' )
						),
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Search Form', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_form' ), $template );
			
			$c = array(
						array(
							'name'	=> __( 'Content Types', 'avia_framework' ),
							'desc'	=> __( 'Which content types should be included in the search', 'avia_framework' ),
							'id'	=> 'post_types',
							'type'	=> 'select',
							'std'	=> '',
							'subtype'	=> array(
												__( 'Resource', 'avia_framework' )		=> 'resource',
												__( 'Blog', 'avia_framework' )			=> 'blog',
												__( 'Event', 'avia_framework' )			=> 'event',
												__( 'News', 'avia_framework' )			=> 'news',
											)
						),				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Content Type', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'content_type' ), $template );
			
			/**
			 * Layout Tab
			 * ===========
			 */
			
			$c = array(
						array(
							'name' 	=> __( 'Search results container', 'avia_framework' ),
							'desc' 	=> __( 'Enter the ID of a container that will hold the search results.<br/>It has to be on the same page as this search form.', 'avia_framework' ),
							'id' 	=> 'ajax_container',
							'std' 	=> __( '#my_container', 'avia_framework' ),
							'type' 	=> 'input',
							'required'	=> array( 'ajax_location', 'equals', 'custom' )
						),
				);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'layout_result' ), $c );

			/**
			 * Setting Tab
			 * ===========
			 */
			

			$c = array(
						 array(
							'name' 	=> __( 'Application ID', 'avia_framework' ),
							'desc' 	=> __( 'Enter a application ID for the algolia search', 'avia_framework' ) ,
							'id' 	=> 'algolia_app_id',
							'std' 	=> __( 'DEQBBMYTK4', 'avia_framework' ),
							'type' 	=> 'input'
						),

						array(
							'name' 	=> __( 'Search-only API key', 'avia_framework' ),
							'desc' 	=> __( 'Enter a API Key for the algolia search', 'avia_framework' ) ,
							'id' 	=> 'algolia_search_api_key',
							'std' 	=> __( '64269e35845d3aaa18bc8f701a49f323', 'avia_framework' ),
							'type' 	=> 'input',
						),

						array(
							'name' 	=> __( 'Index name prefix', 'avia_framework' ),
							'desc' 	=> __( 'Enter a prefix for the algolia index', 'avia_framework' ) ,
							'id' 	=> 'algolia_index_pre',
							'std' 	=> __( 'wp_', 'avia_framework' ),
							'type' 	=> 'input',
						)
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Creds Form', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'algolia_creds_toggle' ), $template );
			
			/**
			 * Styling Tab
			 * ===========
			 */
			
			$c = array(
						array(
							'name' 	=> __( 'Input Font Size', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font size for the input. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_input_size',
							'type' 	=> 'select',
							'std' 	=> '',
							'container_class' => 'av_half av_half_first',
							'subtype'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Default Size', 'avia_framework' ) => '' ), 'px' ),
						),

						array(
							'name' 	=> __( 'Button Font Size', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font size for the button. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_button_size',
							'type' 	=> 'select',
							'std' 	=> '',
							'container_class' => 'av_half',
							'subtype'	=> AviaHtmlHelper::number_array( 10, 50, 1, array( __( 'Default Size', 'avia_framework' ) => '' ), 'px' ),
						),

				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Fonts', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_fonts' ), $template );
				
			$c = array(
						
						array(
							'name' 	=> __( 'Height', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom height for the search input and button', 'avia_framework' ),
							'id' 	=> 'custom_height',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype' => AviaHtmlHelper::number_array( 40, 100, 1 , array( __( 'Default Height', 'avia_framework' ) => '' ), 'px' ),
						),

						array(
							'name' 	=> __( 'Border Radius', 'avia_framework' ),
							'desc' 	=> __( 'Set the border radius of the search form', 'avia_framework' ),
							'id' 	=> 'radius',
							'type' 	=> 'multi_input',
							'std' 	=> '0px',
							'sync' 	=> true,
							'multi' => array(
											'top' 	=> __( 'Top-Left-Radius', 'avia_framework' ),
											'right'	=> __( 'Top-Right-Radius', 'avia_framework' ),
											'bottom'=> __( 'Bottom-Right-Radius', 'avia_framework' ),
											'left'	=> __( 'Bottom-Left-Radius', 'avia_framework' ),
										)
						),

						array(
							'name' 	=> __( 'Border Width', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom border width for the search input and button', 'avia_framework' ),
							'id' 	=> 'border_width',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype' => AviaHtmlHelper::number_array( 0, 30, 1, array( __( 'Default Width', 'avia_framework' ) => '' ), 'px' ),
						),

						
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Form', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_form' ), $template );
			
			
			
			$c = array(
						array(
							'name' 	=> __( 'Search Results Container Padding', 'avia_framework' ),
							'desc' 	=> __( 'Set the padding for the search results container', 'avia_framework' ),
							'id' 	=> 'results_padding',
							'type' 	=> 'multi_input',
							'std' 	=> '0px',
							'sync' 	=> true,
							'multi'	=> array(
											'top'		=> __( 'Top-Left-Padding', 'avia_framework' ),
											'right'		=> __( 'Top-Right-Padding', 'avia_framework' ),
											'bottom'	=> __( 'Bottom-Right-Padding', 'avia_framework' ),
											'left'		=> __( 'Bottom-Left-Padding', 'avia_framework' ),
										)
						),

						array(
							'name' 	=> __( 'Search Results Container Margin', 'avia_framework' ),
							'desc' 	=> __( 'Set the margin for the search results container', 'avia_framework' ),
							'id' 	=> 'results_margin',
							'type' 	=> 'multi_input',
							'std' 	=> '0px',
							'sync' 	=> true,
							'multi' => array(
											'top'		=> __( 'Top-Left-Margin', 'avia_framework' ),
											'right'		=> __( 'Top-Right-Margin', 'avia_framework' ),
											'bottom'	=> __( 'Bottom-Right-Margin', 'avia_framework' ),
											'left'		=> __( 'Bottom-Left-Margin', 'avia_framework' ),
										)
						)
				
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Result', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_result' ), $template );
			
			
			$c = array(
						array(
							'name' 	=> __( 'Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a border color for the input and button', 'avia_framework' ),
							'id' 	=> 'border_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype' => array( 
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Border Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom border color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_border_color',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'border_color', 'equals', 'custom' )
						),
				
						array(
							'name' 	=> __( 'Input Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a font color for the input', 'avia_framework' ),
							'id' 	=> 'input_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array( 
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Input Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_input_color',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'input_color', 'equals', 'custom' )
						),
				
						array(
							'name' 	=> __( 'Input Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a background color for the input', 'avia_framework' ),
							'id' 	=> 'input_bg',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array( 
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework'	)=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Input Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_input_bg',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required' => array( 'input_bg', 'equals', 'custom' )
						),
				
						array(
							'name' 	=> __( 'Button Font/Icon Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a font or icon color for the button', 'avia_framework' ),
							'id' 	=> 'button_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array( 
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Button Font/Icon Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom font color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_button_color',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'button_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Button Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a background color for the button', 'avia_framework' ),
							'id' 	=> 'button_bg',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array( 
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Button Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_button_bg',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'button_bg', 'equals', 'custom' )
						),
				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Form Colors', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors_form' ), $template );
			
			$c = array(
						array(
							'name' 	=> __( 'Search Results Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a font color for the input', 'avia_framework' ),
							'id' 	=> 'results_color',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array( 
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Search Results Font Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom results color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_results_color',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required' => array( 'results_color', 'equals', 'custom' )
						),

						array(
							'name' 	=> __( 'Search Results Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a background color for the search results container', 'avia_framework' ),
							'id' 	=> 'results_bg',
							'type' 	=> 'select',
							'std' 	=> '',
							'subtype'	=> array( 
												__( 'Default', 'avia_framework' )				=> '',
												__( 'Define Custom Colors', 'avia_framework' )	=> 'custom'
											),
						),

						array(
							'name' 	=> __( 'Custom Search Results Background Color', 'avia_framework' ),
							'desc' 	=> __( 'Select a custom background color. Leave empty to use the default', 'avia_framework' ),
							'id' 	=> 'custom_results_bg',
							'type' 	=> 'colorpicker',
							'rgba'  => true,
							'std' 	=> '',
							'required'	=> array( 'results_bg', 'equals', 'custom' )
						),

				);
			
			$template = array(
							array(	
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Result Colors', 'avia_framework' ),
								'content'		=> $c 
							),
					);
			
			AviaPopupTemplates()->register_dynamic_template( $this->popup_key( 'styling_colors_result' ), $template );
			
		}

        /**
         * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
         * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
         * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
         *
         *
         * @param array $params this array holds the default values for $content and $args.
         * @return $params the return array usually holds an innerHtml key that holds item specific markup.
         */
        function editor_element( $params )
        {
			$params = parent::editor_element( $params );
			return $params;

        }

        /**
         * Frontend Shortcode Handler
         *
         * @param array $atts array of attributes
         * @param string $content text within enclosing form of shortcode element
         * @param string $shortcodename the shortcode found, when == callback name
         * @return string $output returns the modified html string
         */
        function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
        {
            extract( AviaHelper::av_mobile_sizes( $atts ) ); //return $av_font_classes, $av_title_font_classes and $av_display_classes

            extract( shortcode_atts( array(
						'placeholder'   => '',
						'label_text'    => '',
						'font'  => '',
						'icon_display' => '',
						'icon'  => '',
						'post_types'    => '',
						'post_types_custom'  => '',
						'display'   => '',
						'ajax_location' => '',
						'ajax_container' => '',
						'numberposts' => 5,

						'border_color' => '',
						'custom_border_color' => '',

						'input_bg' => '',
						'custom_input_bg' => '',

						'button_bg' => '',
						'custom_button_bg' => '',

						'results_bg' => '',
						'custom_results_bg' => '',

						'input_color' => '',
						'custom_input_color' => '',

						'button_color' => '',
						'custom_button_color' => '',

						'results_color' => '',
						'custom_results_color' => '',

						'custom_input_size' => '',
						'custom_button_size' => '',

						'custom_height' => '',
						'radius' => '',
						'border_width' => '',

						'results_hide_titles' => '',
						'results_hide_meta' => '',
						'results_hide_image' => '',
						'results_padding' => '',
						'results_margin' => '',

						'algolia_app_id' => '',
						'algolia_search_api_key' => '',
						'algolia_index_pre' => ''

					), $atts, $this->config['shortcode'] ) );

            $icon = av_icon( $icon, $font, false );
            $button_val = '';
            $button_class = '';
            $input_icon = false;
            $submit_icon = false;
            $input_class = '';
            $button_wrapper_class = '';
            $form_class = '';
            $results_container_attr = '';

            $form_style = '';
            $input_style = '';
            $button_style = '';
            $button_icon_style = '';
            $input_icon_style = '';
            $button_wrapper_style = '';
            $form_wrapper_style = '';

			$results_style = array();
            $spacer_img = '';


            $explode_radius = explode( ',', $atts['radius'] );
            if( count( $explode_radius ) > 1 )
            {
                $atts['radius'] = '';
                foreach( $explode_radius as $kv => $value )
                {
                    if( empty( $value ) ) 
					{
						$value = '0';
					}
					
                    $atts['radius'] .= $value .' ';
                }
            }

            // padding results container
            if(array_key_exists('results_padding',$atts) ) 
			{
                $explode_padding = explode(',',$atts['results_padding']);
                if(count($explode_padding) > 1)
                {
                    $atts['results_padding'] = '';
                    foreach($explode_padding as $value)
                    {
                        if(empty($value)) $value = '0';
                        $atts['results_padding'] .= $value .' ';
                    }
                }

                if($atts['results_padding'] == '0px' && $atts['results_padding'] == '0' && $atts['results_padding'] == '0%' && $atts['results_padding'] == null) {
                    $atts['results_padding'] = '';
                }
            }


            // margin results container
            if(array_key_exists('results_margin',$atts) ) 
			{
                $explode_margin = explode(',',$atts['results_margin']);
                if(count($explode_margin) > 1)
                {
                    $atts['results_margin'] = '';
                    foreach($explode_margin as $value)
                    {
                        if(empty($value)) $value = '0';
                        $atts['results_margin'] .= $value .' ';
                    }
                }

                if($atts['results_margin'] == '0px' || $atts['results_margin'] == '0' || $atts['results_margin'] == '0%' || $atts['results_margin'] == null) 
				{
                    $atts['results_margin'] = '';
                }
            }


            $button_val = $label_text;

            if( $icon_display == 'button' && $icon !== '' )
			{

                // submit button with icon only
                if( $label_text == '' ) 
				{
                    $button_val = $icon;
                    $button_class .= ' av-submit-hasicon avia-font-'.$font;
                    $button_wrapper_class .= ' av-submit-hasicon';
                    $spacer_img = "<img src='" . get_template_directory_uri() . "/images/layout/blank.png' />";
                }
                // submit button with label and icon
                else 
				{
                    $submit_icon = true;
                    $button_wrapper_class .= ' av-submit-hasiconlabel';
                }

            }

            else if( $icon_display == 'input' && $icon !== '' )
			{
                $input_icon = true;
                $input_class .= ' av-input-hasicon';
            }

            // results location
            if( $display == 'classic' ) 
			{
                $form_class = 'av_disable_ajax_search';
            }
            else 
			{
                if( $ajax_location == 'custom' && $ajax_container !== '' ) 
				{
                    $results_container_attr = " data-ajaxcontainer='{$ajax_container}'";
                }
                if( $ajax_location == 'form_absolute' ) 
				{
                    $form_class .= 'av_results_container_fixed';
                }
            }

            // search params
            $form_action = home_url( '/' );
            $search_id = 's';
            $search_val = ! empty( $_GET['s'] ) ? get_search_query() : '';
            // radius style
            if( $radius !== '' && $radius !== '0px' ) 
			{
                $input_style .= AviaHelper::style_string( $atts, 'radius', 'border-radius' );
                $button_wrapper_style .= AviaHelper::style_string( $atts, 'radius', 'border-radius' );
                $button_style .= AviaHelper::style_string( $atts, 'radius', 'border-radius' );
                $form_wrapper_style .= AviaHelper::style_string( $atts, 'radius', 'border-radius' );
            }

            // border style
            if( $border_width !== '' ) 
			{
                $form_wrapper_style .= AviaHelper::style_string( $atts, 'border_width', 'border-width','px' );
            }

            if( $border_color == 'custom' && $custom_border_color !== '' ) 
			{
                $form_wrapper_style .= AviaHelper::style_string( $atts, 'custom_border_color', 'border-color' );
                $form_wrapper_style .= AviaHelper::style_string( $atts, 'custom_border_color', 'background-color' );
            }


            // input style
            if( $input_bg == 'custom' && $custom_input_bg !== '' ) 
			{
                $input_style .= AviaHelper::style_string( $atts, 'custom_input_bg', 'background-color' );
            }

            if( $input_color == 'custom' && $custom_input_color !== '' ) 
			{
                $input_style .= AviaHelper::style_string( $atts, 'custom_input_color', 'color' );
                $input_icon_style .= AviaHelper::style_string( $atts, 'custom_input_color', 'color' );
            }

            if( $custom_height !== '' ) 
			{
                $input_style .= AviaHelper::style_string( $atts, 'custom_height', 'line-height', 'px' );
                $input_style .= AviaHelper::style_string( $atts, 'custom_height', 'height', 'px' );
            }

            if( $custom_input_size !== '' ) 
			{
                $input_style .= AviaHelper::style_string( $atts, 'custom_input_size', 'font-size','px' );
                $input_icon_style .= AviaHelper::style_string( $atts, 'custom_input_size', 'font-size','px' );
            }


            // button style
            if( $button_bg == 'custom' && $custom_button_bg ) 
			{
                $button_style .= AviaHelper::style_string( $atts, 'custom_button_bg', 'background-color' );
            }

            if( $button_color == 'custom' && $custom_button_color ) 
			{
               $button_style .= AviaHelper::style_string( $atts, 'custom_button_color', 'color' );
               $button_wrapper_style .= AviaHelper::style_string( $atts, 'custom_button_color', 'color' );
            }

            if( $custom_button_size !== '' ) 
			{
                $button_style .= AviaHelper::style_string( $atts, 'custom_button_size', 'font-size', 'px' );
                $button_icon_style .= AviaHelper::style_string( $atts, 'custom_button_size', 'font-size', 'px' );
            }

            // button wrapper style
            if( $button_bg == 'custom' && $custom_button_bg ) 
			{
                $button_wrapper_style .= AviaHelper::style_string( $atts, 'custom_button_bg', 'background-color' );
            }

            // results style
            if( $results_bg == 'custom' && $custom_results_bg !== '' )
			{
                $results_style['background-color'] = $custom_results_bg;
            }

            if( $results_color == 'custom' && $custom_results_color !== '' )
			{
                $results_style['color'] = $custom_results_color;
            }

            if( $results_padding !== '' && $results_padding !== '0px' ) 
			{
                $results_style['padding'] = $atts['results_padding'];
            }

            if( $results_margin !== '' && $results_margin !== '0px' ) 
			{
                $results_style['margin'] = $atts['results_margin'];
            }

            $form_style = AviaHelper::style_string( $form_style );
            $input_style = AviaHelper::style_string( $input_style );
            $button_style = AviaHelper::style_string( $button_style );
            $button_icon_style = AviaHelper::style_string( $button_icon_style );
            $input_icon_style = AviaHelper::style_string( $input_icon_style );
            $button_wrapper_style = AviaHelper::style_string( $button_wrapper_style );
            $form_wrapper_style = AviaHelper::style_string( $form_wrapper_style );

            $results_style_attr = '';

            if( ! empty( $results_style ) ) 
			{
                $results_style_str = json_encode( $results_style );
                $results_style_attr = " data-results_style='{$results_style_str}'";
            }

            $output = '';
            // $output .= "<div {$meta['custom_el_id']} class='avia_search_element {$av_display_classes} {$meta['el_class']}'>";

            // $output .= "<form action='{$form_action}' id='searchform_element' method='get' class='{$form_class}'{$results_container_attr} {$form_style}{$results_style_attr}>";
            // $output .= "<div class='av_searchform_wrapper' {$form_wrapper_style}>";
            // $output .= "<input type='text' value='{$search_val}' id='s' name='{$search_id}' placeholder='{$placeholder}' {$input_style} class='{$input_class}' />";
           
			// if( $input_icon ) 
			// {
			// 	$output .= "<span class='av-search-icon avia-font-{$font}' {$input_icon_style}>{$icon}</span>";
			// }

            // $output .= "<div class='av_searchsubmit_wrapper{$button_wrapper_class}' {$button_wrapper_style}>";
            
			// if( $submit_icon )
			// {
			// 	$output .= "<span class='av-search-icon avia-font-{$font}' {$button_icon_style}>{$icon}</span>";
			// }
            
			// $output .= "<input type='submit' value='{$button_val}' id='searchsubmit' class='button{$button_class}' {$button_style} />";

            // // layout helper IE
            // $output .= $spacer_img;
            // $output .= '</div>';
            // $output .= "<input type='hidden' name='numberposts' value='{$numberposts}' />";

            // if( $post_types == 'custom' )
			// {

            //     if( $post_types_custom ) 
			// 	{
            //         $post_types_custom = explode( ',', $post_types_custom );

            //         foreach( $post_types_custom as $ptc ) 
			// 		{
            //             $output .= "<input type='hidden' name='post_type[]' value='{$ptc}' />";
            //         }
            //     }
            // }


            // // results display options
            // $results_hide_fields = array();
            // $results_hide_str = '';
			
            // if( $results_hide_titles ) 
			// {
			// 	$results_hide_fields[] = 'post_titles';
			// }
			
            // if( $results_hide_meta ) 
			// {
			// 	$results_hide_fields[] = 'meta';
			// }
            // if( $results_hide_image ) 
			// {
			// 	$results_hide_fields[] = 'image';
			// }

            // if( ! empty( $results_hide_fields ) ) 
			// {
            //     $results_hide_str = implode( ',', $results_hide_fields );
            // }

            // $output .= "<input type='hidden' name='results_hide_fields' value='{$results_hide_str}' />";

            // $output .= '</div>';
            // $output .= '</form>';
            // $output .= '</div>';

			// $output .= '<!DOCTYPE html>';
			// $output .= '<html lang="en">';
			// $output .= '<head>';
			// $output .= '<meta charset="utf-8" />';
			// $output .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />';
			// $output .= '<meta name="theme-color" content="#000000" />';

			// $output .= '<link rel="manifest" href="./manifest.webmanifest" />';
			// $output .= '<link rel="shortcut icon" href="./favicon.png" />';

			$output .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/instantsearch.css@7/themes/satellite-min.css" />';
			$output .= '<link rel="stylesheet" href="'.AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/algolia_search/src/Dropdown.css" />';
			$output .= '<link rel="stylesheet" href="'.AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/algolia_search/src/algolia_search.css" />';
			$output .= '<link rel="stylesheet" href="'.AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/algolia_search/src/index.css" />';

			// $output .= '<title>instantsearch.js-app</title>';
			// $output .= '</head>';

			// $output .= '<body>'; 
			$output .= "<input type='hidden' id='algolia_app_id' value='{$algolia_app_id}' />";
			$output .= "<input type='hidden' id='algolia_search_api_key' value='{$algolia_search_api_key}' />";
			$output .= "<input type='hidden' id='algolia_index_pre' value='{$algolia_index_pre}' />";
			$output .= "<input type='hidden' id='post_type' value='{$post_types}' />";
			$output .= '<div class="container1 '.$post_types.'">';
			// $output .= '<div class="filter-tags cf clearfix" style="display: block;">';
			// $output .= '<div class="populate-tags"></div>';
			// $output .= '<div class="clear-selected">&nbsp;Clear Selected</div>';
			// $output .= '</div>';
			$output .= '<div class="search-panel__filters">';
			$output .= '<div id="topics" class="col-md-4 col-sm-12"></div>';
			$output .= '<div id="types" class="col-md-4 col-sm-12"></div>';
			$output .= '<div id="searchbox" class="col-md-4 col-sm-12"></div>';
			$output .= '</div>';
			
			$output .= "<div id='hits' class='{$post_types}'></div>";
			$output .= "<div id='pagination'></div>";
			$output .= '</div>';
			$output .= '<script src="https://cdn.jsdelivr.net/algoliasearch/3.32.0/algoliasearchLite.min.js"></script>';
			$output .= '<script src="https://cdn.jsdelivr.net/npm/instantsearch.js@4.9.1"></script>';
			$output .= '<script src='.AviaBuilder::$path['pluginUrlRoot'] . 'avia-shortcodes/algolia_search/src/algolia_search.js'.'></script>';
			// $output .= '</body>';
			// $output .= '</html>';

            return $output;

        }

    }
}
