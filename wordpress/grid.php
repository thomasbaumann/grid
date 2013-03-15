<?
/*
Plugin Name: The Grid
Plugin URI: 
Description: Allows to create complex Centerpages, composed of Widgets, Plain Content and Post-References
Author: Benjamin Birkenhake
Version: 0.1
Author URI: http://birkenhake.org
*/

/* Adding all the Actions */

// register Grid_Container_Get_Posts
add_action( 'widgets_init', create_function( '', 'register_widget("Grid_Container_Get_Posts");' ) );
/* Define the custom box */
add_action( 'add_meta_boxes', 'grid_add_custom_box' );

add_action( 'save_post', 'grid_save_postdata' );

add_action('admin_head', 'grid_move_widget_javascript');

add_action('wp_ajax_grid_move_widget', 'grid_move_widget_callback');


/**
 * Grid_Container_Get_Posts Class
 */
class Grid_Container_Get_Posts extends WP_Widget {
	/** constructor */
	function __construct() {
		parent::WP_Widget( /* Base ID */'grid_container_get_posts', /* Name */'Grid Container Get Posts', array( 'description' => 'A Grid Container to get Posts' ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title; 
			// Do the Main Stuff here.
			echo "Hello Grid!";
			echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php 
	}

} // class Grid_Container_Get_Posts




/* Main function to load the Grid for a given Centerpage */
function grid_get_grid($post_id){
  global $wpdb;
  $grid = array();
  //print_r($slug);
  $content = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = '".$post_id."' and meta_key LIKE '_grid_%' ORDER BY 'meta_key' ASC", 'ARRAY_N');
  //print_r($content);
  foreach($content as $container){  
    $my_container = array();    
    // Explode the Container-ID and separate the Parts
    $parts = explode("_", $container[2]);
    $no_region = $parts[3];
    $no_container = $parts[5];
    $type = $parts[6];    
    // Build the Container Array.
    $my_container["meta_id"] = $container[0];
    $my_container["post_id"] = $container[1];
    $my_container["meta_key"] = $container[2];
    $my_container["type"]  = $type;    
    $my_container["content"] = $container[3];
    // Put the Container in the Grid-Array
    $grid[$no_region][$no_container] = $my_container;
    // Sort the region, so that the Containers are in correct order
    ksort($grid[$no_region]);
  }
  // Sort the Grid, so that the regions are in correct order
  ksort($grid);
  //print_r($grid);
  return $grid;
}


/* Container Functions 
  The Container Functions take the data that are stored within the meta,
  and collect all other for rendering neccessary data.*/

function grid_container_get_posts($container){
  $arguments = explode(";", $container["content"]);
  $args = array();
  foreach($arguments as $argument){
    $parts = explode(",", $argument);
    $args[$parts[0]] = $parts[1];
  }
  $posts = get_posts($args);
  $container["posts"] = $posts;
}


/* Function to update the key of a given Meta-Data-Set */
function grid_update_meta_key($meta_id, $meta_key){
  global $wpdb;
  $grid = array();
  //print_r($slug);
  $content = $wpdb->get_results("UPDATE $wpdb->postmeta SET meta_key='".$meta_key."' WHERE meta_id = '".$meta_id."' ", 'ARRAY_N');
  
}




/* Adds a box to the main column on the Post and Page edit screens */
function grid_add_custom_box() {
  
    add_meta_box( 
        'grid_sectionid',
        __( 'The Grid', 'grid_textdomain' ),
        'grid_inner_custom_box',
        'post' 
    );
    
    add_meta_box(
        'grid_sectionid',
        __( 'The Grid', 'grid_textdomain' ), 
        'grid_inner_custom_box',
        'page'
    );
}

/* Add the Javascript and CSS to the Admin Header */
function grid_move_widget_javascript() {  
  print '<link rel="stylesheet" id="grid-css"  href="' . plugin_dir_url(__FILE__) . '/grid.css" type="text/css" media="all" />';
  print '<script type="text/javascript" src="' . plugin_dir_url(__FILE__) . '/grid.js"> </script> '; 
}


/* Prints the box content */
function grid_inner_custom_box( $post ) {

  // Use nonce for verification
  wp_nonce_field(plugin_basename( __FILE__ ), 'grid_noncename' );
  
  $grid = grid_get_grid($post->ID);
  
  if(count($grid)>0){     
    echo '<div id="grid">';           
    foreach($grid as $no_region => $region){
      echo "<div id='gridsortable$no_region' class='region region-no-$no_region connectedGridSortable' meta-region='$no_region'>";   
        foreach($region as $no_container => $container){                                  
            print '<div class="container container-'.$container["type"].' container-'.$no_container.' widget " meta-id="'.$container["meta_id"].'" meta-type="'.$container["type"].'">'."\n";
            print "<div class='widget-top'>\n";            
            print "<div class='widget-title'>".$container["type"]."</div>\n";
            print "</div>\n";
            print "<div class='widget-inside'>\n";
            print $container["content"];
            print "</div>\n";
            print "</div> <!-- // Container No. $no_container  -->\n";                        
        }
      echo "</div> <!-- // Region No. $no_region -->\n";	      
    }  
    echo "</div> <!-- // The Grid-->\n";	  
  }
  
  
}

function grid_move_widget_callback(){
  $old_meta_id = $_POST['old_meta_id'];
  $new_meta_key = $_POST['new_meta_key'];
  print $old_meta_id."-".$new_meta_key;
  grid_update_meta_key($old_meta_id, $new_meta_key);
  die;
}


/* When the post is saved, saves our custom data */
function grid_save_postdata( $post_id ) {
  // verify if this is an auto save routine. 
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['grid_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  $mydata = $_POST['grid_new_field'];

  // Do something with $mydata 
  // probably using add_post_meta(), update_post_meta(), or 
  // a custom table (see Further Reading section below)
}

function grid_position_container(){
  if($_GET["meta-id"]!="" and $_GET["meta-key"]!=""){
    $meta_id = $_GET["meta-id"];
    $meta_key = $_GET["meta-key"];
    print "Got meta_id [".$_GET["meta-id"]."] and meta_key [".$_GET["meta-key"]."]";
    grid_update_meta_key($meta_id, $meta_key);
  }
}

?>