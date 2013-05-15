<?php
/*
Plugin Name: mlsplatinum
Plugin URI: http://lpmdev.us/platinum/
Description: A plugin for listing the mls with RETS in Platinum site
Version:  1.0
Author: Navya
Author URI: http://lpmdev.us/platinum/
License: GPL2
*/
?><?php
/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
global $wpdb, $wp_version;
//$pluginData = get_plugin_data(__FILE__);
define("MLS_API_URL", "http://174.121.152.3/~blhadmin/rets/test_rets.php");
 define("MLS_PLUGIN_URL", WP_PLUGIN_URL ."/rets_plugin/");
 //define("MLS_PLUGIN_VERSION", $pluginData["Version"]);
// sometimes dirname( __FILE__ ) gives us a bad location, but sometimes require_once(...) doesn't require from the correct directory.
// so we're splitting the difference here and seeing if dirname( __FILE__ ) is valid by checking the existence of a well-known file,
// then falling back to an empty path name if it's invalid.
if(file_exists(dirname( __FILE__ ) . "/platinum_mls.php")){
	$require_prefix = dirname( __FILE__ ) . "/";
} else {
	$require_prefix = "";
}

function my_plugin_install() {

    global $wpdb;

    $the_page_title = 'Whatever You Want';
    $the_page_name = 'whatever-you-want';

    // the menu entry...
    delete_option("my_plugin_page_title");
    add_option("my_plugin_page_title", $the_page_title, '', 'yes');
    // the slug...
    delete_option("my_plugin_page_name");
    add_option("my_plugin_page_name", $the_page_name, '', 'yes');
    // the id...
    delete_option("my_plugin_page_id");
    add_option("my_plugin_page_id", '0', '', 'yes');

    $the_page = get_page_by_title( $the_page_title );

    if ( ! $the_page ) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_content'] = "This text may be overridden by the plugin. You shouldn't edit it.";
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';
        $_p['post_category'] = array(1); // the default 'Uncatrgorised'

        // Insert the post into the database
        $the_page_id = wp_insert_post( $_p );

    }
    else {
        // the plugin may have been previously active and the page may just be trashed...

        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post( $the_page );

    }

    delete_option( 'my_plugin_page_id' );
    add_option( 'my_plugin_page_id', $the_page_id );

}

function my_plugin_remove() {

    global $wpdb;

    $the_page_title = get_option( "my_plugin_page_title" );
    $the_page_name = get_option( "my_plugin_page_name" );

    //  the id of our page...
    $the_page_id = get_option( 'my_plugin_page_id' );
    if( $the_page_id ) {

        wp_delete_post( $the_page_id ); // this will trash, not delete

    }

    delete_option("my_plugin_page_title");
    delete_option("my_plugin_page_name");
    delete_option("my_plugin_page_id");

}
function my_plugin_query_parser( $q ) {

$the_page_name = get_option( "my_plugin_page_name" );
$the_page_id = get_option( 'my_plugin_page_id' );

$qv = $q->query_vars;

// have we NOT used permalinks...?
if( !$q->did_permalink AND ( isset( $q->query_vars['page_id'] ) ) AND ( intval($q->query_vars['page_id']) == $the_page_id ) ) {

$q->set('my_plugin_page_is_called', TRUE );
return $q;

}
elseif( isset( $q->query_vars['pagename'] ) AND ( ($q->query_vars['pagename'] == $the_page_name) OR ($_pos_found = strpos($q->query_vars['pagename'],$the_page_name.'/') === 0) ) ) {

$q->set('my_plugin_page_is_called', TRUE );
return $q;

}
else {

$q->set('my_plugin_page_is_called', FALSE );
return $q;

}
}



function call_api($community)
{	//echo $community;
	if( str_word_count($community)>1)
		 $community=str_replace(" ","%20",$community);
	$result=file_get_contents("http://174.121.152.3/~blhadmin/rets/test_rets.php?title=$community");
   	return $result;
}
function call_shortcode($atts){
   $atts= extract(shortcode_atts( array(
		'comm' => 'community',
		
	), $atts )) ;
	$content=call_api($comm);
	echo $content;
	//call_api($atts['comm']);
  
}
function mls_InitWidgets() {
	register_widget("mlsSearchAgent_SearchWidget");
	
	//register_widget("dsSearchAgent_ListAreasWidget");
	//register_widget("dsSearchAgent_ListingsWidget");
}
function fetch_data_this_page() {
   include 'mlslisting.php';
}

function my_plugin_page_filter( $posts ) {
 include 'pagination.class.php';
 require_once("phRets.php");
 $rets = new phRETS;
global $wp_query;
//echo get_option( "my_plugin_page_name" );
// echo get_permalink(); 
if( $wp_query->get('my_plugin_page_is_called') ) {
	//$community=@$_POST['idx-q-Communities'];
	//$minprice=@$_POST['idx-q-PriceMin'];	
	//$maxprice=@$_POST['idx-q-PriceMax'];	
	$community='Mirabel';
         $maxprice ='No limit';
	$minprice='Any';	
	if( str_word_count($community)>1)
		$community=str_replace(" ","%20",$community);
		if($community)
			$url=MLS_API_URL."?title=$community";
		
		if($community && $minprice)
			$url=MLS_API_URL."?title=$community&minprice=$minprice";
		if($community && $maxprice)
			$url=MLS_API_URL."?title=$community&maxprice=$maxprice";
		if($community && $minprice && $maxprice)
			 $url=MLS_API_URL."?title=$community&minprice=$maxprice&maxprice=$maxprice";


		$response = wp_remote_retrieve_body( wp_remote_post( $url, array(
		'method' => 'POST',
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => array(),
		'body' => array( 'username' => 'bob', 'password' => '1234xyz' ),
		'cookies' => array()
	)
	));
	
	if( is_wp_error( $response ) ) {
		echo 'Something went wrong!';
	} else {
		$data=json_decode($response);
                 // If we have an array with items
        if (count($data)) {
        
         // Create the pagination object
          $pagination = new pagination($data, (isset($_GET['page']) ? $_GET['page'] : 1), 5);
           // Decide if the first and last links should show
          $pagination->setShowFirstAndLast(false);
          // You can overwrite the default seperator
          $pagination->setMainSeperator(' | ');
          // Parse through the pagination class
         $dataPages = $pagination->getResults();
           if (count($dataPages) != 0) {
            // Create the page numbers
            echo $pageNumbers = '<div class="numbers">'.$pagination->getLinks($_GET).'</div>';
             echo '<table>';
               foreach ($dataPages as $listing) {
                 echo "<tr><td><strong>".$listing->LIST_31." ".$listing->LIST_34.",Unit ".$listing->LIST_35.",".$listing->LIST_39."</strong></td></tr>" ;
       // echo "<tr><td>Sub Street name:".$listing['LIST_131']."</td></tr>" ;
     //  echo "<tr><td>Unit Number:".$listing['LIST_35']."</td></tr>" ;
        // echo "<tr><td>City:".$listing['LIST_39']."</td></tr>" ;
         //  echo "<tr><td>Country:".$listing['LIST_40']."</td></tr>" ;
         echo "<tr><td>".$listing['LIST_66']."beds,".$listing['LIST_67']."baths</td></tr>";
           echo "<tr><td>Home size:".$listing['LIST_48']."sq ft</td></tr>";
             echo "<tr><td>Lot size:".$listing['LIST_56']."sq ft</td></tr>";
               echo "<tr><td>Year Built:".$listing['LIST_53']."</td></tr>";
  echo '</table>';         
           echo '</td>';
           echo '</tr>'; 
            }
echo '</table>';

           }//close count
         }//close count
	}//close else
}
?>
<script type="text/css">
#loading-image {
background-color: #333;
width: 55px;
height: 55px;
position: fixed;
top: 20px;
right: 20px;
z-index: 1;
-moz-border-radius: 10px;
-webkit-border-radius: 10px;
border-radius: 10px; /* future proofing */
-khtml-border-radius: 10px;
}
</script>
<div id="loading-image">
<img src="<?php bloginfo('template_url'); ?>/ajax-loader.gif" alt="Loading..." />
</div>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script type="text/javascript" >
$(window).load(function() {

$('#loading-image').hide();
});
</script>
<?php
}

register_activation_hook(__FILE__,'my_plugin_install'); 
register_deactivation_hook( __FILE__, 'my_plugin_remove' );
add_filter( 'parse_query', 'my_plugin_query_parser' );
//add_shortcode( 'mls_listing', 'fetch_data_this_page' );
//add_filter( 'the_posts', 'my_plugin_page_filter' );
add_filter( 'the_content', 'my_plugin_page_filter');
//add_shortcode( 'mls_listing', 'fetch_data_this_page' );

?>
