<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Booking Reminders For Rezgo &gt; Settings', $domain ) ?></h2>

<form method="post" action="" class="rezgo_reminders_settings">

  <div class="field_frame">

    <fieldset>
        <legend><?php _e( 'Rezgo Account Settings', $domain ) ?></legend>
        <div class="field_contents">
        
          <dl>
            <dt><label for="account_cid"><?php _e( 'Account CID', $domain ) ?></label></dt>
            <dd><input id="account_cid" size=10 type="text" value="<?php echo $this->settings['rezgo_reminder_account_cid']; ?>" /></dd>
            <dt><label for="api_key"><?php _e( 'API key', $domain ) ?></label></dt>
            <dd><input id="api_key" size=20 type="text" value="<?php echo $this->settings['rezgo_reminder_api_key']; ?>" /></dd>
          </dl>
        

          <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', $domain ) ?>" id='submitKeys' />
          
        <div id="submitKey_successDiv">
          <div class="submitKey_Icon">
            <img src="<?php echo $this->plugin_base_url?>images/success.gif"><br />
            <?php _e( 'Connected', $domain ) ?>
          </div>
          <div class="submitKey_Msg">
            <?php _e( 'The API Connection is working', $domain ) ?><br /><br />
            <a id="company_website" href="" target=_blank></a>
          </div>
        </div>
           
        <div id="submitKey_failedDiv">
        
        <div class="submitKey_Icon">
          <img src="<?php echo $this->plugin_base_url?>images/failure.png"><br />
          <?php _e( 'Failed', $domain ) ?>
        </div>
        <div class="submitKey_Msg">
          <?php _e( 'The API Connection is NOT working', $domain ) ?><br /><br />
          <div id="connect_problem"></div>
        </div>
        
        </div>
      
      </div>
    </fieldset>
    
    </div>
    
    <div class="field_frame">

    <fieldset>
        <legend><?php _e( 'Advanced Settings', $domain ) ?></legend>
        <div class="field_contents">
        
          <dl>
            <dt><label for="from_email"><?php _e( 'From Email Address', $domain ) ?></label></dt>
            <dd><input id="from_email" size=30 type="text" value="<?php echo $this->settings['rezgo_reminder_from_email']; ?>" />
            <div class="email_notes"><?php _e( 'This is the email that all notifications will be sent from. It will also be the reply email so that when people reply to the email, you will receive it directly.', $domain ) ?></div> 
            </dd>
            <dt><label for="from_name"><?php _e( 'From Email Name', $domain ) ?></label></dt>
            <dd><input id="from_name" size=40 type="text" value="<?php echo $this->settings['rezgo_reminder_from_name']; ?>" />
            <div class="email_notes"><?php _e( 'This is the name assosicated with email address and will appear in the customers email client.', $domain ) ?></div>
            </dd>
          </dl>      
            
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', $domain ) ?>" id="submitFromEmail" />
        </div>
    </fieldset>
    
    </div>
    
    <div class="field_frame">
    	<fieldset>
        <legend><?php _e( 'Manual or Automatic  Reminders', $domain ) ?></legend>
        <div class="field_contents">
        <?php _e( 'If you would like the plugin to automatically send reminders on daily basis, you will need to add the following CRON JOB to crontab on your host. If you are unsure about how to setup this up, please contact your webhost', $domain ) ?>
        <br />
        <input type="text" style="width:90%" id="cron_job" readonly value="30 1 * * * /usr/bin/curl <?php echo home_url("/?{$this->cron_var}=true"); ?>" />
        <br />
        <br />
        <input type="checkbox" id="run_manual" /><?php _e( 'Send Reminders Manually', $domain ) ?>
        <br />
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', $domain ) ?>" id="submitRunMode" />
        </div>
    </fieldset>
    
    </div>
    
    <div class="field_frame">
    <fieldset>
        <legend><?php _e( 'Syncronize Tour/Option List', $domain ) ?></legend>
        <div class="field_contents">
        <?php _e( 'Notifications are only sent for tour/options that are active in your Rezgo account. If you have deleted or updated your tours or options in Rezgo, you should update your tour/option list', $domain ) ?>
	<div id="lastUpdatedDiv">
           <?php _e( 'Last Updated', $domain ) ?> : <span id="last_updated"><?php echo $this->settings['rezgo_reminder_last_updated'] ?></span>
	</div>
        <br /><br />
        <input type="submit" class="button-primary" value="<?php _e( 'Update Tour/Option List', $domain ) ?>" id="submitSyn" />
        </div>
    </fieldset>
    
    </div>
    
</form>

<script type="text/javascript" >
jQuery(document).ready(function($) {
	
    <?php if($this->settings['rezgo_reminder_last_updated']) { ?>
      $( "#lastUpdatedDiv" ).show();
    <?php } ?>
    
    
    <?php if($this->settings['rezgo_reminder_run_manual']) { ?>
      $( "#cron_job" ).css('color', 'gray');
      $( "#run_manual" ).attr('checked',true);
    <?php } ?>
    
    $("#run_manual").click(function() {
		if($(this).is(":checked"))
			$( "#cron_job" ).css('color', 'gray');
		else
			$( "#cron_job" ).css('color', 'black');
    });
    
    // keys
    $( "#submitKeys" ).click(function() {
		$('#submitKey_failedDiv').hide();
		$('#submitKey_successDiv').hide();
		var data = { action: 'rezgo_reminder','method': 'ajax_set_keys','account_cid':$('#account_cid').val(), 'api_key':$('#api_key').val()}
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
				$('#submitKey_successDiv').show();
				$('#company_website').text(response.company_website);
				$('#company_website').attr('href',response.company_website);
			}
			else
			{
				$('#submitKey_failedDiv').show();
				$('#connect_problem').html(response.connect_problem);
			}
		}
		,"json"
		);
		return false;
    });	
    
    $( "#submitFromEmail" ).click(function() {
		var data = { action: 'rezgo_reminder','method': 'ajax_set_from_email','from_email':$('#from_email').val(),'from_name':$('#from_name').val()}
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
				alert(response.message);
			}
			else
			{
				alert(response.message);
			}
		}
		,"json"
		);
		return false;
    });	

    $( "#submitRunMode" ).click(function() {
		var data = { action: 'rezgo_reminder','method': 'ajax_set_run_manual','manual':$('#run_manual').is(':checked')}
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
				alert(response.message);
			}
			else
			{
				alert(response.message);
			}
		}
		,"json"
		);
		return false;
    });	
    
    $( "#submitSync" ).click(function() {
		var data = { action: 'rezgo_reminder','method': 'ajax_sync_tours'}
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
			$('#last_updated').text(response.last_updated);
			$("#lastUpdatedDiv").show();
			alert(response.message);
			}
			else
			{
				alert(response.message);
			}
		}
		,"json"
		);
		return false;
    });	
    
});
</script>