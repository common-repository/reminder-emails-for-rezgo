<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Custom Reminders For Rezgo &gt; Reminders', $domain ) ?></h2>
<?php if($_GET['delete_ok']){ ?> 
  <div class=rezgoreminders_reminder_deleted><?php _e( 'Reminder deleted', $domain ) ?></div>
<?php } ?>

<input type="submit" class="button-primary" value="<?php _e( 'Add Reminder', $domain ) ?>" id='submitAddReminder'/>

<?php if ( $this->reminders  AND $this->settings['rezgo_reminder_run_manual']) : ?>
    <fieldset class="rezgoreminders_run_now_layer">
        <legend><?php _e( 'Send Reminders Now', $domain ) ?></legend>
        <br />
        <?php _e( 'You have opted to send reminders manually. To send remindes now, click the button.', $domain ) ?>
        <input type="submit" class="button-secondary" value="<?php _e( 'Send Reminders Now', $domain ) ?>" id='submitRunNow' style="float:right"/>
        <br />
    </fieldset>
<?php endif; ?>





<?php if ( $this->reminders ) : ?>
<br /><br />
<?php _e( 'Your active reminders are listed below. You can have as many reminders as you like', $domain ) ?>
<br /><br />
<table class="rezgoreminders_reminders_table">
		<thead>
			<tr>
				<th class="rezgo_reminder_tour"><span class="nobr"><?php _e( 'Tour/Option', $domain ); ?></span></th>
				<th class="rezgo_reminder_days_before"><span class="nobr"><?php _e( 'When', $domain ); ?></span></th>
				<th class="rezgo_reminder_status"><span class="nobr"><?php _e( 'Status', $domain ); ?></span></th>
				<th class="rezgo_reminder_emails_delete"><span class="nobr"><?php _e( 'Delete', $domain ); ?></span></th>
			</tr>
		</thead>

		<tbody><?php
			foreach ( $this->reminders as $reminder) {
				?><tr class="reminder">
					<td class="rezgo_reminder_tour">
						<a href="<?php echo add_query_arg( 'edit', $reminder->id, $this->page_url ) ?>"><?php echo $reminder->tour_name; ?></a>
					</td>
					<td class="rezgo_reminder_days_before">
						<?php echo $reminder->days_before?> day(s)
					</td>
					<td class="rezgo_reminder_status">
						<?php echo $this->statuses[$reminder->status]?>
					</td>
					<td class="rezgo_reminder_delete">
						<?php
							echo '<a class="rezgo_reminder_delete_link" href="'.$this->page_url.'&delete='.$reminder->id.'">'.__("Delete",$domain).'</a>';
						?>
					</td>
				</tr><?php
			}
		?></tbody>

	</table>
<?php endif; ?>

<script type="text/javascript" >
jQuery(document).ready(function($) {
    
    
    // keys
    $( "#submitRunNow" ).click(function() {
		window.open('<?php echo home_url("/?{$this->cron_var}=true"); ?>');
		return false;
    });	
    
    $( "#submitAddReminder" ).click(function() {
		window.location= "<?php echo $this->page_url?>&edit=0";
		return false;
    });	
    
    $( ".rezgo_reminder_delete_link" ).click(function() {
		return confirm("<?php _e( 'Are you sure?', $domain )?>");
    });	
    
});
</script>