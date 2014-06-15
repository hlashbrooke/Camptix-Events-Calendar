<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'CampTix_Events_Calendar' ) ) {

	class CampTix_Events_Calendar {

		/**
		 * The single instance of CampTix_Events_Calendar.
		 * @var 	object
		 * @access  private
		 * @since 	1.0.0
		 */
		private static $_instance = null;

		/**
		 * The version number.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $_version;

		/**
		 * The token.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $_token;

		/**
		 * The taxonomy object name.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $_taxonomy;

		/**
		 * The main plugin file.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $file;

		/**
		 * The main plugin directory.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $dir;

		/**
		 * The plugin assets directory.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $assets_dir;

		/**
		 * The plugin assets URL.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $assets_url;

		/**
		 * Suffix for Javascripts.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $script_suffix;

		/**
		 * Constructor function.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function __construct ( $file = '', $version = '1.0.0' ) {
			$this->_version = $version;
			$this->_token = 'camptix_events_calendar';
			$this->_taxonomy = 'event_sponsor';

			$this->file = $file;
			$this->dir = dirname( $this->file );
			$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
			$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

			$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register installation function
			register_activation_hook( $this->file, array( $this, 'install' ) );

			// Register sponsor taxonomy
			add_action('init', array( $this, 'register_taxonomy' ) );

			// Add custom fields to taxonomy
			add_action( $this->_taxonomy . '_add_form_fields', array( $this, 'add_taxonomy_fields' ), 2, 1 );
			add_action( $this->_taxonomy . '_edit_form_fields', array( $this, 'edit_taxonomy_fields' ), 2, 1 );
			add_action( 'edited_' . $this->_taxonomy , array( $this , 'save_taxonomy_fields' ) , 10 , 2 );
	        add_action( 'created_' . $this->_taxonomy , array( $this , 'save_taxonomy_fields' ) , 10 , 2 );

	        // Add tickets option field to event posts
	        add_action( 'tribe_events_cost_table', array( $this, 'select_tickets' ), 1, 1 );
	        add_action( 'save_post', array( $this, 'save_tickets' ), 10, 1 );

	        // Display sponsor and attendee boxes on single event page
	        add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'display_sponsors' ) );
	        add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'display_attendees' ) );

			// Load admin JS & CSS
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ), 10, 1 );

			// Handle localisation
			$this->load_plugin_textdomain();
			add_action( 'init', array( $this, 'load_localisation' ), 0 );
		} // End __construct ()

		/**
		 * Regsiter 'event_sponsor' taxonomy
		 * @return void
		 */
		public function register_taxonomy () {

	        $labels = array(
	            'name' => __( 'Sponsors' , 'camptix-events-calendar' ),
	            'singular_name' => __( 'Sponsor', 'camptix-events-calendar' ),
	            'menu_name' => __( 'Sponsors' , 'camptix-events-calendar' ),
	            'search_items' =>  __( 'Search Sponsors' , 'camptix-events-calendar' ),
	            'all_items' => __( 'All Sponsors' , 'camptix-events-calendar' ),
	            'parent_item' => __( 'Parent Sponsor' , 'camptix-events-calendar' ),
	            'parent_item_colon' => __( 'Parent Sponsor:' , 'camptix-events-calendar' ),
	            'edit_item' => __( 'Edit Sponsor' , 'camptix-events-calendar' ),
	            'update_item' => __( 'Update Sponsor' , 'camptix-events-calendar' ),
	            'add_new_item' => __( 'Add New Sponsor' , 'camptix-events-calendar' ),
	            'new_item_name' => __( 'New Sponsor Name' , 'camptix-events-calendar' ),
	            'separate_items_with_commas' => __( 'Separate sponsors with commas' , 'camptix-events-calendar' ),
	            'add_or_remove_items' => __( 'Add or remove sponsors' , 'camptix-events-calendar' ),
	            'choose_from_most_used' => __( 'Choose from the most used sponsors' , 'camptix-events-calendar' ),
	            'not_found' => __( 'No sponsors found.' , 'camptix-events-calendar' ),
	        );

	        $args = array(
	            'public' => true,
	            'hierarchical' => true,
	            'rewrite' => true,
	            'sort' => true,
	            'labels' => $labels,
	        );

	        register_taxonomy( $this->_taxonomy, 'tribe_events', $args );
	    } // Edn register_taxonomy ()

	    /**
	     * Get custom fields for sponsors
	     * @return void
	     */
	    public function sponsor_fields () {

	    	$fields = array(
	    		array(
	    			'id' => 'logo',
	    			'label' => __( 'Logo', 'camptix-events-calendar' ),
	    			'description' => __( 'The sponsor\'s company logo.', 'camptix-events-calendar' ),
	    			'type' => 'image',
	    			'default' => '',
	    			'placeholder' => '',
				),
				array(
					'id' => 'url',
					'label' => __( 'Website', 'camptix-events-calendar' ),
					'description' => __( 'The sponsor\'s website.', 'camptix-events-calendar' ),
	    			'type' => 'url',
	    			'default' => '',
	    			'placeholder' => __( 'Website URL', 'camptix-events-calendar' ),
				),
			);

	    	return apply_filters( $this->_token . '_' . $this->_taxnomy . '_fields', $fields );
	    } // Edn sponsor_fields ()

	    /**
	     * Add fields to sponsor taxonomy (add form)
	     * @param string $taxonomy Taxonomy name
	     * @return void
	     */
	    public function add_taxonomy_fields ( $taxonomy ) {

	    	$fields = $this->sponsor_fields();

	    	foreach( $fields as $field ) {
	    		?><div class="form-field"><?php
	    			$this->display_field( $field );
	    		?></div><?php
	    	}
	    } // End add_taxonomy_fields ()

	    /**
	     * Add fields to sponsor taxonomy (edit form)
	     * @param  object $sponsor Taxonomy term object
	     * @return void
	     */
	    public function edit_taxonomy_fields ( $sponsor ) {

	    	$fields = $this->sponsor_fields();

	    	$sponsor_id = $sponsor->term_id;

	    	foreach( $fields as $field ) {
	    		?>
	    		<tr class="form-field">
			        <th scope="row" valign="top"><label for="<?php esc_attr_e( $field['id'] ); ?>"><?php esc_attr_e( $field['label'] ); ?></label></th>
			        <td>
			        	<?php $this->display_field( $field, $sponsor_id ); ?>
			        </td>
			    </tr><?php
	    	}
	    } // Edn edit_taxonomy_fields ()

	    /**
	     * Save sponsor taxonomy fields
	     * @param  integer $sponsor_id ID of sponsor
	     * @return void
	     */
	    public function save_taxonomy_fields ( $sponsor_id = 0 ) {

	        $sponsor_data = get_option( $this->_taxonomy . '_' . $sponsor_id, array() );

	        $fields = $this->sponsor_fields();

	        foreach ( $fields as $field ){
	        	$field_name = $this->_taxonomy . '_' . $field['id'];
	            if ( isset( $_POST[ $field_name ] ) ) {
	                $sponsor_data[ $field['id'] ] = $_POST[ $field_name ];
	            }
	        }

	        // Update sponsor
	        update_option( $this->_taxonomy . '_' . $sponsor_id, $sponsor_data );

	    } // End save_taxonomy_fields ()

	    /**
	     * Add ticket selection box to event edit screen
	     * @param  integer $post_id Event ID
	     * @return void
	     */
	    public function select_tickets ( $post_id = 0 ) {

	    	$existing = get_post_meta( $post_id, '_event_tickets', true );

	    	$args = array(
	    		'post_type' => 'tix_ticket',
	    		'posts_per_page' => -1,
	    		'post_status' => 'any',
			);

			$tickets = get_posts( $args );

			$ticket_options = '';
			foreach( $tickets as $ticket ) {
				$selected = false;
				if( $existing && in_array( $ticket->ID, (array) $existing ) ) {
					$selected = true;
				}
				$ticket_options .= '<option value="' . esc_attr( $ticket->ID ) . '" ' . selected( true, $selected, false ) . '>' . esc_html( $ticket->post_title ) . '</option>' . "\n";
			}
	    	?>
			<tr>
				<td colspan="2" class="tribe_sectionheader"><h4><?php _e( 'Event Tickets', 'camptix-events-calendar' ); ?></h4></td>
			</tr>
			<tr>
				<td><?php _e( 'Tickets:','camptix-events-calendar' ); ?></td>
				<td>
					<select class="chosen ticket-dropdown" name="event_tickets[]" multiple="multiple">
						<?php echo $ticket_options; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><small><?php _e( 'Select all the tickets for this event - these are used to display the attendees on the event page. Leave blank to hide the attendee list.', 'camptix-events-calendar' ); ?></small></td>
			</tr>
			<?php
	    } // End select_tickets ()

	    /**
	     * Save tickets for single event
	     * @param  integer $post_id Event ID
	     * @return void
	     */
	    public function save_tickets ( $post_id = 0 ) {

	    	if ( ! isset( $_POST['post_type'] ) || 'tribe_events' != $_POST['post_type'] ) {
		        return;
		    }

		    if( ! isset( $_POST['event_tickets'] ) ) {
		    	return;
		    }

		    update_post_meta( $post_id, '_event_tickets', $_POST['event_tickets'] );
	    } // End save_tickets ()

	    /**
		 * Generate HTML for displaying fields
		 * @param  array $args Field data
		 * @return void
		 */
		public function display_field ( $field = '', $sponsor_id = 0 ) {

			$html = '';

			$data = $field['default'];
			if( $sponsor_id ) {
				$sponsor = get_option( $this->_taxonomy . '_' . $sponsor_id, array() );
				if( isset( $sponsor[ $field['id'] ] ) ) {
					$data = $sponsor[ $field['id'] ];
				}
			}

			if( ! $sponsor_id ) {
				$html .= '<label for="' . esc_attr( $field['id'] ) . '"></label>' . "\n";
			}

			$field_name = $this->_taxonomy . '_' . $field['id'];

			switch( $field['type'] ) {

				case 'text':
				case 'url':
					$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="text" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $data . '"/>' . "\n";
				break;

				case 'password':
					$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $data . '"/>' . "\n";
				break;

				case 'number':
					$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $data . '" min="' . esc_attr( $field['min'] ) . '" max="' . esc_attr( $field['max'] ) . '" />' . "\n";
				break;

				case 'textarea':
					$html .= '<textarea id="' . esc_attr( $field['id'] ) . '" rows="5" cols="50" name="' . esc_attr( $field_name ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . $data . '</textarea><br/>'. "\n";
				break;

				case 'checkbox':
					$checked = '';
					if( $option && 'on' == $option ){
						$checked = 'checked="checked"';
					}
					$html .= '<input id="' . esc_attr( $field['id'] ) . '" type="' . $field['type'] . '" name="' . esc_attr( $field_name ) . '" ' . $checked . '/>' . "\n";
				break;

				case 'checkbox_multi':
					foreach( $field['options'] as $k => $v ) {
						$checked = false;
						if( in_array( $k, $data ) ) {
							$checked = true;
						}
						$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="checkbox" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $field_name ) . '[]" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
					}
				break;

				case 'radio':
					foreach( $field['options'] as $k => $v ) {
						$checked = false;
						if( $k == $data ) {
							$checked = true;
						}
						$html .= '<label for="' . esc_attr( $field['id'] . '_' . $k ) . '"><input type="radio" ' . checked( $checked, true, false ) . ' name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $k ) . '" id="' . esc_attr( $field['id'] . '_' . $k ) . '" /> ' . $v . '</label> ';
					}
				break;

				case 'select':
					$html .= '<select name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field['id'] ) . '">';
					foreach( $field['options'] as $k => $v ) {
						$selected = false;
						if( $k == $data ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '">' . $v . '</option>';
					}
					$html .= '</select> ';
				break;

				case 'select_multi':
					$html .= '<select name="' . esc_attr( $field_name ) . '[]" id="' . esc_attr( $field['id'] ) . '" multiple="multiple">';
					foreach( $field['options'] as $k => $v ) {
						$selected = false;
						if( in_array( $k, $data ) ) {
							$selected = true;
						}
						$html .= '<option ' . selected( $selected, true, false ) . ' value="' . esc_attr( $k ) . '" />' . $v . '</label> ';
					}
					$html .= '</select> ';
				break;

				case 'image':
					$image_thumb = '';
					if( $data ) {
						$image_thumb = wp_get_attachment_thumb_url( $data );
					}
					$html .= '<div>';
					$html .= '<img id="' . $field_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
					$html .= '<input id="' . $field_name . '_button" type="button" data-uploader_title="' . __( 'Upload an image' , 'wordpress-plugin-template' ) . '" data-uploader_button_text="' . __( 'Use image' , 'wordpress-plugin-template' ) . '" class="image_upload_button button" value="'. __( 'Upload new image' , 'wordpress-plugin-template' ) . '" style="width:auto;" />' . "\n";
					$html .= '<input id="' . $field_name . '_delete" type="button" class="image_delete_button button" value="'. __( 'Remove image' , 'wordpress-plugin-template' ) . '" style="width:auto;" />' . "\n";
					$html .= '<input id="' . $field_name . '" class="image_data_field" type="hidden" name="' . $field_name . '" value="' . $data . '"/><br/>' . "\n";
					$html .= '</div>';
				break;

				case 'color':
					?><div class="color-picker" style="position:relative;">
				        <input type="text" name="<?php esc_attr_e( $field_name ); ?>" class="color" value="<?php esc_attr_e( $data ); ?>" />
				        <div style="position:absolute;background:#FFF;z-index:99;border-radius:100%;" class="colorpicker"></div>
				    </div>
				    <?php
				break;

			}

			$html .= '<br/><span class="description">' . $field['description'] . '</span>';

			echo $html;
		} // End display_field ()

		/**
		 * Display sponsor on single event page
		 * @return void
		 */
		public function display_sponsors () {
			global $post;

			$sponsors = wp_get_post_terms( $post->ID, $this->_taxonomy );

			if( 0 < count( $sponsors ) ) {

				$heading = __( 'Sponsor', 'camptix-events-calendar' );
				if( 1 < count( $sponsors ) ) {
					$heading = __( 'Sponsors', 'camptix-events-calendar' );
				}

				$heading = apply_filters( $this->_token . '_single_event_sponsors_heading', $heading, $post->ID );

				?>
				<div class="tribe-events-single-section tribe-events-sponsors tribe-events-event-meta tribe-clearfix">
					<div class="tribe-events-meta-group tribe-events-meta-group-details" style="width:100%;">
						<h3 class="tribe-events-single-section-title"><?php echo esc_html( $heading ); ?></h3>
						<?php
						foreach( $sponsors as $sponsor ) {
							$sponsor_data = get_option( $this->_taxonomy . '_' . $sponsor->term_id, array() );
							?>
							<div class="event-sponsor" style="margin-bottom:50px;">
								<?php

								do_action( $this->_token . '_single_event_sponsor_before', $sponsor->term_id, $post->ID );

								// Sponsor logo
								if( isset( $sponsor_data['logo'] ) && $sponsor_data['logo'] ) {

									$image = wp_get_attachment_image( $sponsor_data['logo'], 'medium', '', array( 'align' => 'left', 'style' => 'padding-right:20px;' ) );
									$image = apply_filters( $this->_token . '_single_event_sponsor_image', $image, $sponsor->term_id, $post->ID );

									echo $image;
								}

								// Sponsor name
								echo '<h3>' . apply_filters( $this->_token . '_single_event_sponsor_title', $sponsor->name, $sponsor->term_id, $post->ID ) . '</h3>';

								// Sponsor description
								if( isset( $sponsor->description ) && $sponsor->description ) {
									echo apply_filters( $this->_token . '_single_event_sponsor_description', wpautop( $sponsor->description ), $sponsor->term_id, $post->ID );
								}

								// Sponsor website
								if( isset( $sponsor_data['url'] ) && $sponsor_data['url'] ) {

									$website = sprintf( __( 'Website: %1$s', 'camptix-events-calendar' ), '<a href=" ' . esc_url( $sponsor_data['url'] ) . '">' . $sponsor_data['url'] . '</a>' );
									$website = apply_filters( $this->_token . '_single_event_sponsor_website', $website, $sponsor->term_id, $post->ID );

									echo '<p><em>' . $website . '</em></p>';
								}

								do_action( $this->_token . '_single_event_sponsor_after', $sponsor->term_id, $post->ID );

								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
		} // End display_sponsors ()

		/**
		 * Display attendees on single event page
		 * @return void
		 */
		function display_attendees () {
			global $post;

			$tickets = '';
			$ticket_array = get_post_meta( $post->ID, '_event_tickets', true );
			if( $ticket_array && 0 < count( (array) $ticket_array ) ) {
				$tickets = implode( ',', $ticket_array );
			}

			$tickets = apply_filters( $this->_token . '_single_event_tickets', $tickets, $post->ID );

			if( $tickets ) {

				$heading = apply_filters( $this->_token . '_single_event_attendees_heading', __( 'Attendees', 'camptix-events-calendar' ), $post->ID );

				?>
				<div class="tribe-events-single-section tribe-events-attendees tribe-events-event-meta tribe-clearfix">
					<div class="tribe-events-meta-group tribe-events-meta-group-details" style="width:100%;">
						<h3 class="tribe-events-single-section-title"><?php echo esc_html( $heading ); ?></h3>

						<?php do_action( $this->_token . '_single_event_attendees_before', $post->ID ); ?>

						<?php echo do_shortcode( '[camptix_attendees tickets="' . $tickets . '"]' ); ?>

						<?php do_action( $this->_token . '_single_event_attendees_after', $post->ID ); ?>

					</div>
				</div>
				<?php
			}
		} // End display_attendees ()

		/**
		 * Load admin Javascript.
		 * @access  public
		 * @since   1.0.0
		 * @return void
		 */
		public function admin_enqueue_assets ( $hook = '' ) {

			wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
			wp_register_style( $this->token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );

			$screen = get_current_screen();

			if( 'edit-' . $this->_taxonomy == $screen->id ) {
				wp_enqueue_script( $this->_token . '-admin' );
			}

			if( 'tribe_events' == $screen->id ) {
				wp_enqueue_style( $this->token . '-admin' );
			}

		} // End admin_enqueue_scripts()

		/**
		 * Load plugin localisation
		 * @access  public
		 * @since   1.0.0
		 * @return void
		 */
		public function load_localisation () {
			load_plugin_textdomain( 'camptix-events-calendar' , false , dirname( plugin_basename( $this->file ) ) . '/lang/' );
		} // End load_localisation()

		/**
		 * Load plugin textdomain
		 * @access  public
		 * @since   1.0.0
		 * @return void
		 */
		public function load_plugin_textdomain () {
		    $domain = 'camptix-events-calendar';

		    $locale = apply_filters( 'plugin_locale' , get_locale() , $domain );

		    load_textdomain( $domain , WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		    load_plugin_textdomain( $domain , FALSE , dirname( plugin_basename( $this->file ) ) . '/lang/' );
		} // End load_plugin_textdomain()

		/**
		 * Main CampTix_Events_Calendar Instance
		 *
		 * Ensures only one instance of CampTix_Events_Calendar is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see CampTix_Events_Calendar()
		 * @return Main CampTix_Events_Calendar instance
		 */
		public static function instance ( $file = '', $version = '1.0.0' ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $file, $version );
			}
			return self::$_instance;
		} // End instance()

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
		} // End __clone()

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
		} // End __wakeup()

		/**
		 * Installation. Runs on activation.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function install () {
			$this->_log_version_number();
		} // End install()

		/**
		 * Log the plugin version number.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		private function _log_version_number () {
			update_option( $this->_token . '_version', $this->_version );
		}

	}

}