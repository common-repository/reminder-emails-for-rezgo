<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Booking Reminders For Rezgo &gt; Log', $domain ) ?></h2>
<h3><?php _e( 'The following is a list of all reminders sent using the reminder plugin', $domain ) ?></h3>
<center>
<br />
<table class="rezgoreminders_log_table">
		<thead>
			<tr>
				<th class="rezgoreminders_log_sentdate"><span class="nobr"><?php _e( 'Date', $domain ); ?></span></th>
				<th class="rezgoreminders_log_email"><span class="nobr"><?php _e( 'To', $domain ); ?></span></th>
				<th class="rezgoreminders_log_date"><span class="nobr"><?php _e( 'Booked For Date', $domain ); ?></span></th>
				<th class="rezgoreminders_log_tour"><span class="nobr"><?php _e( 'Tour/Option', $domain ); ?></span></th>
				<th class="rezgoreminders_log_trans_num"><span class="nobr"><?php _e( 'Booking #', $domain ); ?></span></th>
			</tr>
		</thead>

		<tbody><?php
			foreach ( $this->logs as $log) {
				?><tr class="log_record">
					<td class="rezgoreminders_log_sent">
						<?php echo date_i18n( get_option( 'date_format' ), $log->sent_timestamp ); ?>
					</td>
					<td class="rezgoreminders_log_email">
						<?php echo $log->email?>
					</td>
					<td class="rezgoreminders_log_date">
						<?php echo date_i18n( get_option( 'date_format' ), $log->booking_timestamp ); ?>
					</td>
					<td class="rezgoreminders_log_tour">
						<?php echo $log->tour_name?> <?php echo $log->option_name?>
					</td>
					<td class="rezgoreminders_log_trans_num">
						<?php echo $log->trans_num?>
					</td>
				</tr><?php
			}
		?></tbody>

	</table>

	<?php if($this->total_pages>1) { ?>
		<?php if($this->page>1) { ?>
			<a href="<?php echo $this->pagination_url."1"; ?>">&lt;&lt;</a>
		<?php } else { ?>
			1
		<?php } ?>
		&nbsp;
		
		<?php for($i=max($this->page-2,2);$i<min($this->page+3,$this->total_pages);$i++) { ?>
			<?php if($this->page==$i) { ?>
				<?php echo $i?>
			<?php } else { ?>
				<a href="<?php echo $this->pagination_url.$i; ?>"><?php echo $i?></a>
			<?php } ?>
			&nbsp;
		<?php } ?>
		
		<?php if($this->page!=$this->total_pages) { ?>
			<a href="<?php echo $this->pagination_url.$this->total_pages; ?>">&gt;&gt;</a>
		<?php } else { ?>
			<?php echo $this->page?>
		<?php } ?>
		
	<?php } ?>
	
<script type="text/javascript" >
jQuery(document).ready(function($) {
    
    
    // keys
    $( "#submitAddNotify" ).click(function() {
	window.location= "<?php echo $this->pagination_url?>&edit=0";
	return false;
    });	
    
    $( ".deleteAction" ).click(function() {
	return confirm("<?php _e( 'Are you sure?', $domain )?>");
    });	
    
});
</script>