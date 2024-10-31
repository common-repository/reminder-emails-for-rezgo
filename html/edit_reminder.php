<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Booking Reminders For Rezgo &gt; Reminders', $domain ) ?></h2>
   <div id=rezgo_saved_success><img src="<?php echo $this->plugin_base_url?>images/success.gif"><?php _e( 'Your changes have been saved', $domain ) ?></div>
   <form method="post" action="" class="rezgo_reminders_settings">
   
   <div class="field_frame">

    <fieldset>
        <legend><?php _e( 'Reminder Details', $domain ) ?></legend>
        <div class="field_contents">
        <?php _e( 'Tour / Option', $domain ) ?> <select class="modified"  id="rezgo_tour_uid">
	  <?php foreach($this->tours as $tour){?>
	    <option value="<?php echo $tour->uid?>" <?php selected($tour->uid, $this->reminder->tour_uid, 1);?> ><?php echo $tour->name?></option>
	  <?php } ?>
        </select>
        <br />
        <br />
        
        <?php _e( 'When ?', $domain ) ?> <select class="modified"  id="rezgo_days_before">
	  <?php for($i=1;$i<=$this->max_days;$i++) {?>
	    <option value="<?php echo $i?>" <?php selected($i, $this->reminder->days_before);?> ><?php echo $i?></option>
	  <?php } ?>
        </select>
        <?php _e( 'Day before the booked for date of the tour', $domain ) ?> 
        <br />
        <br />
        
        <?php _e( 'With Status?', $domain ) ?> <select class="modified"  id="rezgo_status">
	  <?php foreach($this->statuses as $id=>$text) {?>
	    <option value="<?php echo $id?>" <?php selected($id, $this->reminder->status);?> ><?php echo $text?></option>
	  <?php } ?>
        </select>
        <?php _e( '(Select the status for the bookings for this notification)', $domain ) ?> 
        <br />
        <br />
        
        <?php _e( 'Subject:', $domain ) ?><input class="modified" id="rezgo_subject" type="text" size="80" value="<?php echo esc_html($this->reminder->subject)?>"><br />
        <br />
        <br />
        
        <?php _e( 'The subject/message can contain variables. Here are a list of the variables that are supported', $domain ) ?>
        <ul>
        <li>{tour_name} {option_name}</li>
        <li>{first_name} {last_name} {email}</li>
        <li>{trans_num} {booking_date}</li>
        </ul>
        <label><?php _e( 'TEXT message:', $domain ) ?></label><br />
        <textarea class="modified" id="rezgo_text"><?php echo esc_html($this->reminder->message_text)?></textarea><br /><br />
        <label><?php _e( 'HTML message:', $domain ) ?></label><br />
         <?php wp_editor( $this->reminder->message_html, "rezgo_html", array("editor_class"=>"modified")) ?>
         <br />
         
        <strong><?php _e( 'Send a copy of this reminder to:', $domain ) ?></strong><input class="modified" id="rezgo_cc_email" type="text" size="40" value="<?php echo esc_html($this->reminder->cc_email)?>"><br />
        <input type="submit" class="button-primary" value="<?php _e( 'Save Reminder', $domain ) ?>" id='submitSaveReminder'/>
        </div>
    </fieldset>
    </div>
    </form>
    
<script type="text/javascript" >
var reminder_id=<?php echo $this->reminder->id; ?>;
var rezgo_modified_flag=0;
jQuery(document).ready(function($) {
    
    $( "#submitSaveReminder" ).click(function() {
		regzo_save_notification();
		return false;
    });	
    
    
    function regzo_save_notification()
    {
		var inputid = "rezgo_html";
		var content;
		tinyMCE.triggerSave();
		var editor = tinyMCE.get(inputid);
		if (editor) {
			// Ok, the active tab is Visual
			content = editor.getContent();
		} else {
			// The active tab is HTML, so just query the textarea
			content = $('#'+inputid).val();
		}    
    
		var data = { action: 'rezgo_reminder','method': 'ajax_save_reminder',
				'id':reminder_id,
				'tour_uid':$("#rezgo_tour_uid").val(),
				'days_before':$("#rezgo_days_before").val(),
				'status':$("#rezgo_status").val(),
				'subject':$("#rezgo_subject").val(),
				'message_text':$("#rezgo_text").val(),
				'message_html':content,
				'cc_email':$("#rezgo_cc_email").val()}
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
				rezgo_modified_flag=0;
				reminder_id = response.reminder_id;
				$('#rezgo_saved_success').show();
			}
			else
			{
				alert(response.message);
			}
		}
		,"json"
		);
    }
    
});
</script>