<?php
/*
Plugin Name: LH Profile Page
Plugin URI: http://lhero.org/plugins/lh-registration-page/
Description: Enables a front end user profile page that can be updated from the front end
Author: Peter Shaw
Version: 1.0
Author URI: http://shawfactor.com/

== Changelog ==

= 1.0 =
* Initial release



License:
Released under the GPL license
http://www.gnu.org/copyleft/gpl.html

Copyright 2011  Peter Shaw  (email : pete@localhero.biz)


This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published bythe Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class LH_profile_page_plugin {

var $filename;
var $options;
var $opt_name = 'lh_profile_page-options';
var $page_id_field_name = "lh_profile_page-page_id";


private function write( $userobject, $field, $source, $label, $placeholder, $type = "text", $required = true){

return $userobject->$field;


}

private function format_results( $results){

$return_string = "";

foreach($results as $result){

if (is_wp_error($result['result'])){

$return_string .= "<p>There was an error</p>";


} else {

$return_string .= "<p>".$result['key']." updated</p>";

}

}

return $return_string;


}


private function field_input( $userobject, $field, $source, $label, $placeholder, $type = "text", $required = "true"){

if (empty($type)){

$type = "text";

}


$return_string .= "\n";

$return_string .= '<p><!--[if lt IE 10]><br/><label for="lh_profile_page-'.$source.'-'.$field.'">'.$label.'</label><br/><![endif]-->';
$return_string .= '<input name="lh_profile_page-'.$source.'-'.$field.'" id="lh_profile_page-'.$source.'-'.$field.'" type="'.$type.'" class="form-control login-field" ';


if ($source == "users"){

$return_string .= 'value="'.$userobject->$field.'"';

} elseif ($source == "usermeta"){

$return_string .= 'value="'.get_user_meta($userobject->ID, $field, true).'"';

}

$return_string .= ' placeholder="'.$placeholder.'"';


if ($required == "true"){

$return_string .= ' required="required"';

}

$return_string .= ' /></p>';

$return_string .= "\n";

return $return_string;

}


private function field_textarea( $userobject, $field, $source, $label, $placeholder, $type = "text", $required = true){

$return_string .= "\n";

$return_string .= '<p><!--[if lt IE 10]><br/><label for="lh_profile_page-'.$source.'-'.$field.'">'.$label.'</label><br/><![endif]-->';
$return_string .= '<textarea rows="4" cols="50" name="lh_profile_page-'.$source.'-'.$field.'" id="lh_profile_page-'.$source.'-'.$field.'" type="'.$type.'" class="form-control login-field" ';




$return_string .= ' placeholder="'.$placeholder.'"';


if (!$required){

$return_string .= ' required="required"';

}

$return_string .= " >";

if ($source == "users"){

$return_string .= $userobject->$field;

} elseif ($source == "usermeta"){

$return_string .= get_user_meta($userobject->ID, $field, true);



}


$return_string .= "</textarea></p>\n";

return $return_string;

}


private function field_option( $userobject, $field, $source, $label, $placeholder ="", $type = "text", $required = true){


if ($source == "taxonomy"){

$args = array(
    'hide_empty'        => false
); 

$bars = wp_get_object_terms( $userobject->ID, $field);

$stack = array();

foreach($bars as $bar){

array_push($stack, $bar->slug);

}

$terms = get_terms($field, $args);

$return_string .= '<p>'.$label.':';

if(!empty($terms)){

foreach($terms as $term){

$return_string .= '
<input type="'.$type.'" name="lh_profile_page-'.$source.'-'.$field;

if ($type == "checkbox"){

$return_string .= '[]';

}

$return_string .= '" id="lh_profile_page-'.$source.'-'.$field.'_'.$term->slug.'" value="'.$term->slug.'"';


if (in_array($term->slug, $stack)) {

$return_string .= ' checked="checked" ';

}


$return_string .= ' /><label for="lh_profile_page-'.$source.'-'.$field.'_'.$term->slug.'">'.$term->name.'</label>
';
}

$return_string .= '</p>';

}

} else {

$return_string .= '<p><!--[if lt IE 10]><br/><label for="lh_profile_page-'.$source.'-'.$field.'">'.$label.'</label><br/><![endif]-->';
$return_string .= '<input name="lh_profile_page-'.$source.'-'.$field.'" id="lh_profile_page-'.$source.'-'.$field.'" type="'.$type.'" class="form-control login-field" ';
if (isset($userobject->$field)){

$return_string .= 'value="'.$userobject->$field.'"';

}

$return_string .= ' placeholder="'.$placeholder.'"';


if (!$required){

$return_string .= ' required="required"';

}

$return_string .= ' /></p>';

}

return $return_string;

}



function filter_edit_user_link( $link ){
if(!is_admin() && get_option('lh_profile_page_page_id')) {


$parts = parse_url($link);

parse_str($parts['query'], $query);

if ($query['user_id']){

return get_permalink($this->options[$this->page_id_field_name])."?user_id=".$query['user_id'];

} else {

return get_permalink($this->options[$this->page_id_field_name]);


}

} else {

return $link;

}

}

private function profile_page_form_output($return_string, $attributes,$userobject){

extract( shortcode_atts( array(
		'fields' => 'foobar'
	), $attributes ) );



$fields = preg_split('/\\) \\(|\\(|\\)/', $fields, -1, PREG_SPLIT_NO_EMPTY);

for ($i = 0; $i < count($fields); ++$i) {
$fields[$i] = explode(',', $fields[$i]);
    }


$return_string .= "\n<noscript>Please switch on Javascript to enable this registration</noscript>\n\n";

if ($GLOBALS['lh_profile_page-update-result']){

$return_string .= $this->format_results($GLOBALS['lh_profile_page-update-result']);

}

$return_string .= '<form method="post" id="lh-profile-page-form" action="'.esc_url($_SERVER['REQUEST_URI']).'" data-lh_profile_page_nonce="'.wp_create_nonce("lh_profile_page_nonce").'">
';


foreach ($fields as $field) {
$func = $field[0];

if (method_exists($this, $func)){

$return_string .= $this->$func($userobject, $field[1],$field[2],$field[3],$field[4],$field[5],$field[6]);

}
}


$return_string .= '<input type="hidden" id="lh_profile_page_nonce" name="lh_profile_page_nonce" value="" />';

$return_string .= '<input class="btn btn-primary btn-lg btn-block" type="submit" name="lh_profile_page_submit" value="Submit"/>';

$return_string .= '</form>';

wp_enqueue_script('lh_profile_page_script', plugins_url( '/assets/lh-profile-page.js' , __FILE__ ), array(), '0.06', true  );

return apply_filters( 'lh_profile_page_form_html', $return_string );

}

private function get_referenced_user(){

if ($_GET['user_id']){

$userobject = get_userdata( $_GET['user_id'] );


} else {

$userobject = wp_get_current_user();

}

if ($userobject->ID){

return $userobject;

} else {

return false;

}

}

private function strstr_after($haystack, $needle, $case_insensitive = false) {
    $strpos = ($case_insensitive) ? 'stripos' : 'strpos';
    $pos = $strpos($haystack, $needle);
    if (is_int($pos)) {
        return substr($haystack, $pos + strlen($needle));
    }
    // Most likely false or null
    return $pos;
}

private function handle_users_change($id,$key,$value,$return){



if ($result = wp_update_user( array( 'ID' => $id, $key => $value ) )){

$var['key'] = $key;
$var['type'] = 'users';
$var['result'] = $result;

$return[] = $var;

}

return $return;


}

private function handle_usermeta_change($id,$key,$value,$return){

if ($result = update_user_meta( $id, $key, $value)){

$var['key'] = $key;
$var['type'] = 'usermeta';
$var['result'] = $result;

$return[] = $var;

}

return $return;


}

private function handle_taxonomy_change($id,$key,$value,$return){

if ($result = wp_set_object_terms($id, $value, $key)){

$var['key'] = $key;
$var['type'] = 'taxonomy';
$var['result'] = $result;

$return[] = $var;

}

return $return;


}


public function save_data(){

global $wpdb;

if ($userobject = $this->get_referenced_user()){

if (current_user_can('edit_user', $userobject->ID) ){

if ( (wp_verify_nonce( $_POST['lh_profile_page_nonce'], "lh_profile_page_nonce")) and ($_POST['lh_profile_page_submit'])) {

$return = array();

foreach($_POST as $name => $value) {

if ($after = $this->strstr_after($name,"lh_profile_page-users-")){

$return = $this->handle_users_change($userobject->ID,$after,$value,$return);

} elseif ($after = $this->strstr_after($name,"lh_profile_page-usermeta-")){

$return = $this->handle_usermeta_change($userobject->ID,$after,$value,$return);

} elseif ($after = $this->strstr_after($name,"lh_profile_page-taxonomy-")){

$return = $this->handle_taxonomy_change($userobject->ID,$after,$value,$return);

}

unset($after);


}

$GLOBALS['lh_profile_page-update-result'] = $return;

}


}

return $userobject->ID;

} 


}




public function profile_page_shortcode_output( $attributes, $content = null ) {


$return_string = '';

if ($userobject = $this->get_referenced_user()){

if (current_user_can('edit_user', $userobject->ID) ){

$return_string .= $this->profile_page_form_output($return_string, $attributes,$userobject);

} else {

$return_string .= "<p>you do not have access to edit this user</p>";

}

} else {

$return_string .= "<p>this is not a valid user</p>";

}
 
return $return_string;
}




function register_shortcodes(){

add_shortcode('lh_profile_page_form', array($this,"profile_page_shortcode_output"));

}


function login_redirect( $redirect_to, $request, $userobject ) {

if(!is_admin() && get_option('lh_profile_page_page_id')) {

return get_permalink($this->options[$this->page_id_field_name]);

} else {

return $redirect_to;


}


}

public function plugin_menu() {
add_options_page('LH Profile Page Options', 'LH Profile Page', 'manage_options', $this->filename, array($this,"plugin_options"));
}


public function plugin_options() {

if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}



    $lh_profile_page_page_id_hidden_field_name = 'lh_profile_page_page_id_submit_hidden';
   
 // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[  $lh_profile_page_page_id_hidden_field_name ]) && $_POST[  $lh_profile_page_page_id_hidden_field_name ] == 'Y' ) {

// Read their posted value
if (($_POST[ $this->page_id_field_name ] != "") and ($page = get_page(sanitize_text_field($_POST[ $this->page_id_field_name ])))){

$options[$this->page_id_field_name] = sanitize_text_field($_POST[$this->page_id_field_name]);


}

        // Put an settings updated message on the screen


if (update_option( $this->opt_name, $options )){

$this->options = get_option($this->opt_name);


?>
<div class="updated"><p><strong><?php _e('LH Profile Page settings saved', 'menu-test' ); ?></strong></p></div>
<?php

}

} 

    // Now display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h1>" . __('LH Profile Page ID', 'menu-test' ) . "</h1>";

    // settings form
    
    ?>

<form name="form1" method="post" action="">
<input type="hidden" name="<?php echo $lh_profile_page_page_id_hidden_field_name; ?>" value="Y"/>

<p><label for="<?php echo $this->page_id_field_name; ?>"><?php _e("Profile Page Id;", 'menu-test' ); ?></label>
<input type="number" id="<?php echo $this->page_id_field_name; ?>" name="<?php echo $this->page_id_field_name; ?>" value="<?php echo $this->options[$this->page_id_field_name]; ?>" size="20" required="required"/><a href="<?php echo get_permalink($this->options[$this->page_id_field_name]); ?>">Link</a>
</p>

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>

</form>



</div>



<?php

	

}

// add a settings link next to deactive / edit
public function add_settings_link( $links, $file ) {

	if( $file == $this->filename ){
		$links[] = '<a href="'. admin_url( 'options-general.php?page=' ).$this->filename.'">Settings</a>';
	}
	return $links;
}

//this function will only create a profile page if one is not already set in options

private function create_page() {

if (!$page = get_page($this->options[$this->page_id_field_name])){


$page['post_type']    = 'page';

$page['post_content'] = '[lh_profile_page_form fields="
(write,user_email,users,foo,your@email.com)
(field_input,first_name,usermeta,First Name,first name,text,true)
(field_input,last_name,usermeta,Last Name,last name,text,true)
(field_textarea,description,usermeta,Description,Description,text,false)"]';

$page['post_status']  = 'publish';
$page['post_title']   = 'Profile Page';

if ($pageid = wp_insert_post($page)){

$options = $this->options;

$options[$this->page_id_field_name] = $pageid;

if (update_option($this->opt_name, $options )){

$this->options = get_option($this->opt_name);

}

}
}
}

public function on_activate() {

$this->create_page();

}

public function __construct() {

$this->filename = plugin_basename( __FILE__ );
$this->options = get_option($this->opt_name);

add_filter( 'get_edit_user_link', array($this,"filter_edit_user_link"));
add_action( 'init', array($this,"register_shortcodes"));
add_filter( 'login_redirect', array($this,"login_redirect"), 10, 3);
add_action('admin_menu', array($this,"plugin_menu"));
add_filter('plugin_action_links', array($this,"add_settings_link"), 10, 2);
add_action( 'wp', array($this,"save_data"));



}


}


$lh_profile_page_instance = new LH_profile_page_plugin();
register_activation_hook(__FILE__, array($lh_profile_page_instance,'on_activate') );


function lh_profile_page_uninstall(){

delete_option('lh_profile_page-options');

}


register_uninstall_hook( __FILE__, 'lh_profile_page_uninstall' );


?>