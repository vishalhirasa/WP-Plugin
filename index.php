<?php
/**
Plugin Name: Latest Notification plugin
Author: Netsparked 
Description: Plugin to show latest notifications on frontend.
version: 1.0
**/

add_action('admin_menu', 'notification_plugin_setup_menu');
 
function notification_plugin_setup_menu(){
  add_menu_page( 'Notifications', 'Notification Plugin', 'manage_options', 'notification-plugin', 'test_init' );
}
 
function test_init(){
	global $wpdb;

	$table_name = 'wp_blob';

	// SQL to create table
	$sql = "CREATE TABLE $table_name (
		id INT AUTO_INCREMENT PRIMARY KEY,
		title VARCHAR(255) NOT NULL,
		name VARCHAR(255) NOT NULL,
		links TEXT NOT NULL,
		mime VARCHAR(50) NOT NULL,
		create_date DATETIME NOT NULL
	)";

	// Execute the query
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

// function that runs when shortcode is called
function insert_notification_shortcode() { 
  
// Things that you want to do.
$message = '<button class="upd" id="upd">Upload Documents</button>'; 
  
// Output needs to be return
if(current_user_can('administrator') && is_user_logged_in()){
return $message;
}
}
// register shortcode
add_shortcode('insert_notification', 'insert_notification_shortcode');

function insert_popup_modal(){

global $wpdb;

$upload_dir   = wp_upload_dir();
 $dir = $upload_dir['basedir'];

if(isset($_POST['btn'])){
	$name ='';
	$type ='';
	$title = isset($_POST['title']) ? $_POST['title'] : '';
	$links = isset($_POST['links']) ? $_POST['links'] : '';
	$name = isset($_FILES['myfile']['name']) ? $_FILES['myfile']['name'] : '';
	$type = isset($_FILES['myfile']['type']) ? $_FILES['myfile']['type'] : '';
	if (file_exists($_FILES['myfile']['tmp_name'])){
		
		$folder = $dir.'/latest_notifications/'.$_FILES["myfile"]["name"];
		if(!move_uploaded_file($_FILES["myfile"]["tmp_name"], $folder)){
			echo "Can't Upload File.. Incorrect path.";
		}
	}
	$stmt = $wpdb->insert('wp_blob', array(
		'title' => $title,
		'name' =>  $name,
		'links' =>  $links,
		'mime' =>  $type,
		'create_date' => date('Y-m-d h:i:s')
	));
	if($stmt == true){
		?>
		<script>window.location.replace("https://www.bbncollege.co.in/");</script>
	<?php exit; }
}
?>
<style>
.upd{
	color: #FFFFFF !important;
    background-color: #EA4D44 !important;
}
/* The Modal (background) */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1; /* Sit on top */
  padding-top: 100px; /* Location of the box */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.modal-content {
  background-color: #fefefe;
  margin: auto;
  padding: 20px;
  border: 1px solid #888;
  width: 50%;
}

/* The Close Button */
.close {
  color: #aaaaaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
  
}
.btn-upload{
	color: #FFFFFF !important;
    background-color: #EA4D44 !important;
	
}
.four-main p a {
    color: black;
    padding-left: 20px;
}

.four-main p {
    margin-bottom: 10px;
}
a.trash {
    padding-left: 0px !important;
    color: #ff0000 !important;
}
</style>


<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close">&times;</span>
    <div class="inner-post">
	<h4 style="text-align: center;">Please Upload Your Document Here</h4><br/>
		<form method="post" enctype="multipart/form-data">
		<input type="text" class="file-upload" name="title" placeholder="Your Title" required ><br /><br />
		<input type="file" class="file-uplo" name="myfile">OR
		<input type="text" class="file-uplo" name="links" placeholder="Paste your link here">
		<div class="btn-main">
		<button name="btn" type="submit" class="btn-upload">Upload</button>
		</div>
		</form>
    </div>
  </div>

</div>
<script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("upd");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
<script>
   jQuery( ".trash" ).on( "click", function() {
   	//alert('hello');
   	   var msg = confirm('Are you sure delete this file?');
   	   if (msg){
   	   var del_id= jQuery(this).attr('data-id');
          var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
          if(del_id) {
   				  jQuery.ajax({
   					url : ajaxurl,
   					type: 'POST',
   					data : {
   					  'action' : 'delete_new_notification',
   					  'del_id': del_id,
   					}
   				}).done(function ( response ) {
   					alert("Deleted Successfully");
   					window.location.reload();
   				}).fail(function ( err ) {
   					console.log(err+"-error");
   				});
   		}
   		}
   		else{
   		   //alert("cancel is click")
   			}
   });
</script>
<?php
}
add_action('wp_footer','insert_popup_modal');

function showLatestNotifications() { 
global $wpdb;
 $result = $wpdb->get_results("SELECT * FROM wp_blob ORDER BY create_date DESC");
 echo "<div class='four-main' style='margin-top: -18px !important;'>";
  $i = 1;
  if(empty($result)){
	  echo "<p style='padding-left:10px;'>No New Notification</p>";
  }
  
	foreach($result as $results){
		 if($i == '6'){
			  break;
		  }
		  $dltbtn = "";
		  if(current_user_can('administrator') && is_user_logged_in()){
			  $dltbtn = " | <a href='' class='trash' data-id='".$results->id."'>Delete</a>";
		  }
		  if(!empty($results->links)){
			  echo "
		<p><a target='_blank' href='".$results->links."'>".$results->title."</a>".$dltbtn."</p>";
		  }else{
		echo "
		<p><a target='_blank' href='".site_url()."/wp-content/uploads/latest_notifications/".$results->name."'>".$results->title."</a>".$dltbtn."</p>";
	}
	 $i++; }
	
 echo "</div>";
 } 
add_shortcode('showLatestNotifications','showLatestNotifications');

// File delete with ajax
function delete_new_notification() {
global $wpdb;
 $id = $_POST['del_id'];
//echo $music_number;
$table = 'wp_blob';
$wpdb->delete( $table, array( 'id' => $id ));
exit();
  }
add_action( 'wp_ajax_delete_new_notification', 'delete_new_notification' );    // If called from admin panel
add_action( 'wp_ajax_nopriv_delete_new_notification', 'delete_new_notification' );

?>



