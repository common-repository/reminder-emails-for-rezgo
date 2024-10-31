<?php
/*
Plugin Name: Rezgo Email Reminders
Plugin URI: http://wordpress.org/plugins/reminder-emails-for-rezgo/
Description: Send custom text or html reminder email messages to customers when they make a booking through your Rezgo online booking engine.
Version: 0.1
Author: alexvp
Author URI: http://alexvp.elance.com
*/

$RezgoReminderObj= new RezgoReminder();
//init 
register_activation_hook( __FILE__, array($RezgoReminderObj,'install') );
register_deactivation_hook( __FILE__, array($RezgoReminderObj,'uninstall') );
//Admin
add_action('admin_menu', array($RezgoReminderObj,'admin_menu') );
add_action('wp_ajax_rezgo_reminder', array($RezgoReminderObj, 'ajax_rezgo_reminder') );
add_action('admin_head', array($RezgoReminderObj,'admin_head') );
// for html email
add_action('phpmailer_init', array($RezgoReminderObj,'set_email_text_body') );
add_action('init', array($RezgoReminderObj,'try_call_webhook') );

class RezgoReminder {
	var $text_domain = "RezgoReminder";
	var $api_endpoint = "http://xml.rezgo.com/xml";
	var $setting_names = "rezgo_reminder_account_cid|rezgo_reminder_api_key|rezgo_reminder_from_email|rezgo_reminder_from_name|rezgo_reminder_last_updated|rezgo_reminder_run_manual";
	var $max_days = 31; //for dropdown
	var $cron_var = "rezgo-reminders-run";
	var $per_page = 30;
	
	function RezgoReminder() {
		global $wpdb;
		
		$this->load_settings();
		$this->plugin_base_url = plugins_url("/", __FILE__);
		
		$this->table_tours = $wpdb->prefix."rezgo_reminder_tours";
		$this->table_reminders = $wpdb->prefix."rezgo_reminder_reminders";
		$this->table_log = $wpdb->prefix."rezgo_reminder_log";
		
		$this->statuses = array (
			0 => 'All',
			1 => 'Confirmed',
			2 => ' Pending',
			3 => ' Cancelled',
		);

	}
	function load_settings() {
		$this->settings=array();
		foreach( explode("|",$this->setting_names) as $key)
			$this->settings[$key]= get_option($key,"");
	}
	
	function uninstall() {
		global $wpdb;
		foreach( explode("|",$this->setting_names) as $key)
			delete_option($key);
		$wpdb->query("DROP TABLE IF EXISTS `{$this->table_bookings}`,`{$this->table_log}`,`{$this->table_tours}`,`{$this->table_reminders}`");
	}
	function install() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE IF NOT EXISTS `{$this->table_log}` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`sent_timestamp` int(10) unsigned NOT NULL,
			`email` varchar(255) NOT NULL,
			`tour_name` varchar(255) NOT NULL,
			`option_name` varchar(255) NOT NULL,
			`booking_timestamp` int(10) unsigned NOT NULL,
			`trans_num` varchar(255) NOT NULL,
  			PRIMARY KEY (`id`)
			)";
		dbDelta( $sql );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$this->table_reminders}` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`tour_uid` int(10) unsigned NOT NULL,
			`tour_name` varchar(255) NOT NULL,
			`author_userid` int(10) unsigned NOT NULL,
			`last_updated` datetime NOT NULL,
			`emails_sent` int(10) unsigned NOT NULL,
			`days_before` int(10) unsigned NOT NULL,
			`status`  varchar(255) NOT NULL,
			`subject` varchar(255) NOT NULL,
			`message_text` text NOT NULL,
			`message_html` text NOT NULL,
			`cc_email`  varchar(255) NOT NULL,
  			PRIMARY KEY (`id`)
			)";
		dbDelta( $sql );

		$sql = "CREATE TABLE IF NOT EXISTS `{$this->table_tours}` (
			`uid` int(10) unsigned NOT NULL,
			`name` varchar(255) NOT NULL,
			PRIMARY KEY (`uid`)
			)";
		dbDelta( $sql );
	}
	
	function admin_menu() {
		
		
		$version = get_bloginfo('version');
		$vparts = explode('.', $version);
		if ((int)$vparts[0] >= 3 && (int)$vparts[1] >= 8) {
			$plugin_icon = 'dashicons-clock';
		} else {
			$plugin_icon = plugins_url( 'images/menu.png', __FILE__ );
		}
		
		
		add_menu_page(
		'Custom Reminders For Rezgo', 
		__('Rezgo Reminders', $this->text_domain), 
		'manage_options',
		'rezgo-reminder-menu', 
		array(&$this, 'settings_page'),
		$plugin_icon
		);

		add_submenu_page(
		'rezgo-reminder-menu',
		'Custom Reminders For Rezgo', 
		__('Settings', $this->text_domain),
		'manage_options',
		'rezgo-reminder-menu', 
		array(&$this, 'settings_page'));

		add_submenu_page(
		'rezgo-reminder-menu',
		'Custom Reminders For Rezgo', 
		__('Reminders', $this->text_domain), 
		'manage_options',
		'rezgo-reminder-reminders', 
		array(&$this, 'reminders_page'));

		add_submenu_page(
		'rezgo-reminder-menu',
		'Custom Reminders For Rezgo', 
		__('Log', $this->text_domain), 
		'manage_options',
		'rezgo-reminder-log', 
		array(&$this, 'log_page'));
	}
	
	function admin_head() {
		wp_register_style( 'rezgo-reminder-style', plugins_url('style.css', __FILE__) );
		wp_enqueue_style( 'rezgo-reminder-style' );
	}
	// load html pages for menu
	// $domain is text domain
	// for http://codex.wordpress.org/Function_Reference/_e
	function show_page($page) {
		$domain = $this->text_domain;
		include "html/$page.php";
	}
	function settings_page() {
		$this->show_page("settings");
	}
	function reminders_page() {
		global $wpdb;
		$this->page_url=admin_url("admin.php?page=rezgo-reminder-reminders");
	
		if( @$_GET['delete'] ) {
			$wpdb->delete( $this->table_reminders, array( 'id' => $_GET['delete'] ), array( '%d' )); 
			wp_redirect( add_query_arg( 'delete_ok','1',$this->page_url));
		}
		if( isset($_GET['edit']) ) {
			$this->reminder = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_reminders} WHERE id=%d", $_GET['edit']) );
			if(!$this->reminder ) {
				$this->reminder = new stdClass;
				$this->reminder->id =0;
			}
			$this->tours = $wpdb->get_results("SELECT * FROM {$this->table_tours}");
			$this->show_page("edit_reminder");
		}
		else
		{
			$this->reminders = $wpdb->get_results("SELECT * FROM {$this->table_reminders} ORDER BY tour_name");
			$this->tours = $wpdb->get_results("SELECT * FROM {$this->table_tours}");
			$this->show_page("reminders");
		}
	}	
	
	function log_page() {
		global $wpdb;
		
		$limit = $this->per_page;
		$total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_log}");
		$total_pages = ceil($total/$limit);
		
		$page = intval(@$_GET['num']);
		if(!$page)
			$page = 1;
		if($page > $total_pages)
			$page = $total_pages;
		$ofs = ($page-1) * $limit;
		
		$this->total_pages = $total_pages;
		$this->page = $page;
		$this->pagination_url = "admin.php?page=rezgo-reminder-log&num=";
		$this->logs = $wpdb->get_results("SELECT * FROM {$this->table_log} ORDER BY id DESC LIMIT $ofs,$limit");
		$this->show_page("log");
	}
	
	//ajax route to methods
	function ajax_reply($is_success,$args) {
		$args['result'] = $is_success ? 'success': 'failed';
		echo json_encode($args);
		die();
	}

	function ajax_rezgo_reminder() {
		if(!empty($_POST['method']) AND method_exists($this,$_POST['method']))
			$this->$_POST['method']();
		else
			_e('non-valid method', $this->text_domain );
	}

	function ajax_get_reminder() {
		global $wpdb;
		
		$reminder= $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_reminders} WHERE tour_uid=%d", $_POST['tour_uid']) );
		$this->ajax_reply(true, array('days_before'=>$reminder->days_before, 'status'=>$reminder->status, 'subject'=>$reminder->subject, 'message_html'=>$reminder->message_html, 'message_text'=>$reminder->message_text,'cc_email'=>$reminder->cc_email) );
	}
	function ajax_save_reminder() {
		global $wpdb;
		
		$reminder = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_reminders} WHERE id=%d", $_POST['id']) );
		$tour = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_tours} WHERE uid=%d", $_POST['tour_uid']) );
		
		$data = array();
		$data['tour_uid'] = $_POST['tour_uid'];
		$data['tour_name'] =$tour->name;
		$data['author_userid'] = get_current_user_id();
		$data['last_updated'] =current_time('mysql');
		$data['days_before'] =$_POST['days_before'];
		$data['status'] =$_POST['status'];
		$data['subject'] =$_POST['subject'];
		$data['message_text'] =$_POST['message_text'];
		$data['message_html'] =$_POST['message_html'];
		$data['cc_email'] =$_POST['cc_email'];
		$format = array( '%d','%s','%d','%s','%d','%d','%s','%s','%s','%s');
		if($reminder)
			$wpdb->update( $this->table_reminders,$data , array('id' => $reminder->id ),$format,array('%d'));
		else {
			$wpdb->insert( $this->table_reminders,$data , $format);
			$_POST['id'] = $wpdb->insert_id;
		}
		$this->ajax_reply(true,array('reminder_id'=>$_POST['id']));
	}

	function ajax_set_keys() {
		$url = $this->api_endpoint . '?transcode=' . $_POST['account_cid'] . '&key=' . $_POST['api_key'] . '&i=company' ;
		$reply= wp_remote_get( $url );
		if ( is_wp_error( $reply) ) 
			$this->ajax_reply(false, array('connect_problem'=>$reply->get_error_message()) );

		$xml = simplexml_load_string($reply['body']);
		if(empty($xml->domain))// we get only string with error message
			$this->ajax_reply(false, array('connect_problem'=>(string)$xml) );

		update_option( 'rezgo_reminder_account_cid', $_POST['account_cid']);
		update_option( 'rezgo_reminder_api_key', $_POST['api_key']);
		$this->ajax_reply(true, array('company_website'=>"http://{$xml->domain}.rezgo.com") );
	}

	function ajax_set_from_email() {
		update_option( 'rezgo_reminder_from_email', $_POST['from_email']);
		update_option( 'rezgo_reminder_from_name', $_POST['from_name']);
		$this->ajax_reply(true, array('message'=>__('name/email updated', $this->text_domain )) );
	}
	
	function ajax_set_run_manual() {
		update_option( 'rezgo_reminder_run_manual', $_POST['manual']?1:0);
		$this->ajax_reply(true, array('message'=>__('Mode switched', $this->text_domain )) );
	}
	

	function ajax_sync_tours() {
		global $wpdb;
		$url = $this->api_endpoint . '?transcode=' . get_option( 'rezgo_reminder_account_cid') . '&key=' . get_option( 'rezgo_reminder_api_key') . '&i=search_items' ;
		$reply= wp_remote_get( $url );
		if ( is_wp_error( $reply) ) 
			$this->ajax_reply(false, array('message'=>$reply->get_error_message()) );

		$xml = simplexml_load_string($reply['body']);
		if(empty($xml->item))// we get only string with error message
			$this->ajax_reply(false, array('message'=>__( sprintf("No active tours? API reply: %s",(string)$xml ), $this->text_domain )) );

		$wpdb->query("TRUNCATE {$this->table_tours}");
		foreach($xml->item as $i) {
			$name = trim( (string)$i->name ." @ ". (string)$i->time );
			$wpdb->insert( $this->table_tours, array( 'uid' => (string)$i->uid, 'name' => $name), array( '%d', '%s' ) );
			//we update tour's title in reminder rules
			$wpdb->update( $this->table_reminders, array( 'tour_name' => $name), array('tour_uid' => (string)$i->uid ), array( '%s' ),array('%d'));
		}

		$last_updated = date_i18n('j F Y @ H:i A', time()); 
		update_option( 'rezgo_reminder_last_updated', $last_updated );
		$this->ajax_reply(true, array('message'=>count($xml->item )." " .__("tours imported", $this->text_domain), 'last_updated'=>$last_updated) );
	}
	
	function log_message ( $msg ) {
		echo $msg . "<br />";
		flush();
	}
	
	function try_call_webhook() {
		if(!isset($_GET[$this->cron_var])) 
			return ; 
			
		global $wpdb;
		
		$reminders = $wpdb->get_results("SELECT * FROM {$this->table_reminders}");
		
		foreach($reminders  as $r) {
			$books = $this->find_bookings($r);
			$this->log_message( $r->tour_name. " [" . $r->days_before . "] [". $this->statuses[$r->status]."] -> ".count($books ));
			
			foreach($books as $b) {
				$success=$this->send_notification($r,$b,$error);
				if(!$success) {
					$this->log_message($error);
					continue;
				}
				
				$this->log_message(__("Sent email to ", $this->text_domain). " " . $b->email_address);
				
				//add log on success only
				$log = array("sent_timestamp"=>time(),"email"=>$b->email_address,
							"tour_name"=>$b->tour_name,"option_name"=>$b->option_name,
							'booking_timestamp'=>$b->date,"trans_num"=>$b->trans_num);
				$log = array_map("strval", $log);
				$wpdb->insert($this->table_log,$log);

				$wpdb->query($wpdb->prepare("UPDATE {$this->table_reminders} SET emails_sent=emails_sent+1 WHERE id=%d", $r->id) );
			}
		}
		die();
	}
	
	// by reminder tour ID and status!
	function find_bookings($r) {
		$results = array();
		$url = $this->api_endpoint . '?transcode=' . $this->settings['rezgo_reminder_account_cid'] . '&key=' . $this->settings['rezgo_reminder_api_key'] . '&i=search_bookings' ;
		$url .= '&t=date';
		
		if(false) //debug 
			$url .= '&q=2013-10-01,2013-12-31';
		else {
			$date_remind = date("Y-m-d", strtotime("+ {$r->days_before} days"));
			$url .= '&q='.$date_remind;
		}
		$reply = wp_remote_get( $url );
		if ( is_wp_error( $reply) ) {
			$this->log_message($reply->get_error_message());
			return $results;
		}
		
		$xml = simplexml_load_string($reply['body']);
		if(!isset($xml->total) ) {
			$this->log_message(__("API Error : ", $this->text_domain) . strip_tags($reply['body']));
			return $results;
		}
		if(empty($xml->booking)){
			//$this->log_message( __("No bookings", $this->text_domain) );
			return $results;
		}
		
		//SINGLE
		if(!is_array($xml->booking)) {
			if( $xml->booking->item_id == $r->tour_uid AND  ($xml->booking->status == $r->status OR  $r->status==0) )
				$results [] = $xml->booking;
			return $results;
		} 
		
		foreach($xml->booking as $b) {
			if( $b->item_id == $r->tour_uid AND  ($b->status == $r->status OR  $r->status==0) )
				$results [] = $b;
		} 
		return $results;
	}
	
	function send_notification($r,$b,&$reason) {
		if(empty($r->subject)) {
			$reason = __('Notification subject is empty', $this->text_domain);
			return false;
		}
		
		$from_email= get_option('rezgo_reminder_from_email');
		if(!$from_email) {
			$reason = __('From Email is not defined in Settings', $this->text_domain);
			return false;
		}
		$from_name= get_option('rezgo_reminder_from_name');
		if(!$from_name) {
			$reason = __('From Name is not defined in Settings', $this->text_domain);
			return false;
		}

		//get fields for replacement
		$t = array();
		$t['trans_num']=$b->trans_num;
		$t['tour_uid']=$b->item_id;
		$t['tour_name']=$b->tour_name;
		$t['option_name']=$b->option_name;
		$t['first_name']=$b->first_name;
		$t['last_name']=$b->last_name;
		$t['email']=$b->email_address;
		$t= array_map("strval", $t);
		$t['booking_timestamp']= (int)$b->date;

		// make subst map
		$subst=array();
		foreach($t as $k=>$v)
			$subst['{'.$k.'}']=(string)$v;
		$subst['{booking_date}']= date_i18n(get_option('date_format'),$t['booking_timestamp']);
		
		$subject = strtr($r->subject,$subst);
		$message_html = strtr($r->message_html,$subst);
		//will send as html 
		add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));

		//BCC?
		$headers = array("From: $from_name<$from_email>");
		if($r->cc_email)
			$headers[] = "Bcc: " . $r->cc_email;
		
		// we remember plain version and set it in hook!
		$this->message_text = strtr($r->message_text,$subst);
		$result = wp_mail($t['email'], $subject, $message_html, $headers );
		unset($this->message_text);
		
		if(!$result )
			$reason = __("WP_mail failed!", $this->text_domain);

		//return true;//debug 
		return $result;
	}
	
	// carefull, we set alt/text body for html emails only !
	function set_email_text_body($phpmailer) {
		if( $phpmailer->ContentType == 'text/html' AND !empty($this->message_text)) {
			$phpmailer->AltBody = $this->message_text;
			//print_r($phpmailer);die();
		}
	}
}