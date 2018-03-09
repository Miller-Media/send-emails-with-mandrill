<?php if (!current_user_can('manage_options')) wp_die( __('You do not have sufficient permissions to access this page.') ); ?>
<?php wpMandrill::getConnected(); ?>
<div class="wrap">
<div class="icon32" style="background: url('<?php echo plugins_url('images/mandrill-head-icon.png',__FILE__); ?>');"><br /></div>
<h2><?php _e('Mandrill Service Report', 'wpmandrill'); ?></h2><?php

$stats = self::getCurrentStats();
if ( empty($stats) ) {
    echo '<p>' . __('There was a problem retrieving statistics.', 'wpmandrill') . '</p>';
    echo '</div>';
    return;
}

$delivered  = $stats['general']['stats']['sent'] -
                $stats['general']['stats']['hard_bounces'] - 
                $stats['general']['stats']['soft_bounces'] -
                $stats['general']['stats']['rejects'];

$lit = array();

$lit['hourly']['title']   = __('Hourly Sending Volume and Open/Click Rate','wpmandrill');
$lit['hourly']['Xtitle']  = __('Hours','wpmandrill');
$lit['hourly']['tooltip'] = __('Hour','wpmandrill');

$lit['daily']['title']    = __('Daily Sending Volume and Open/Click Rate','wpmandrill');
$lit['daily']['Xtitle']   = __('Days','wpmandrill');
$lit['daily']['tooltip']  = __('Day','wpmandrill');

$lit['subtitle']    = __('in the last 30 days','wpmandrill');
$lit['Ytitle']      = __('Open & Click Rate','wpmandrill');
$lit['SerieName']   = __('Volume','wpmandrill');
$lit['emails']      = __('emails','wpmandrill');
$lit['openrate']    = __('Open Rate','wpmandrill');
$lit['clickrate']   = __('Click Rate','wpmandrill');

?>
<div id="alltime_report">
    <h3><?php echo sprintf(__('All-time statistics since %s: ', 'wpmandrill'),date('m/d/Y',strtotime($stats['general']['created_at']))); ?></h3>
    
    <div id="alltime_report_canvas">
        <div class="stat_box"><?php _e('Reputation:', 'wpmandrill'); ?><br/><span><?php echo $stats['general']['reputation']?>%</span></div>
        <div class="stat_box"><?php _e('Quota:', 'wpmandrill'); ?><br/><span><?php echo $stats['general']['hourly_quota']?> <?php _e('sends/hour', 'wpmandrill'); ?></span></div>
        <div class="stat_box"><?php _e('Emails sent:', 'wpmandrill'); ?><br/><span><?php echo $stats['general']['stats']['sent']?></span></div>
        <div class="stat_box"><?php _e('Emails delivered:', 'wpmandrill'); ?><br/><span><?php echo $delivered?> (<?php echo number_format(  $delivered*100 / ( ($stats['general']['stats']['sent'])?$stats['general']['stats']['sent']:1 ) ,2); ?>%)</span></div>
        <div class="stat_box"><?php _e('Tracked opens:', 'wpmandrill'); ?><br/><span><?php echo $stats['general']['stats']['opens']?></span></div>
        <div class="stat_box"><?php _e('Tracked clicks:', 'wpmandrill'); ?><br/><span><?php echo $stats['general']['stats']['clicks']?></span></div>
        <?php
            if ( $stats['general']['stats']['rejects'] ) echo '<div class="stat_box warning">'.__('Rejects:', 'wpmandrill').'<br/><span>'.$stats['general']['stats']['rejects'].'</span></div>';
            if ( $stats['general']['stats']['complaints'] ) echo '<div class="stat_box warning">'.__('Complaints:', 'wpmandrill').'<br/><span>'.$stats['general']['stats']['complaints'].'</span></div>';
            if ( $stats['general']['backlog'] ) echo '<div class="stat_box warning">'.__('Current backlog:', 'wpmandrill').'<br/><span>'.$stats['general']['backlog'].' emails</span></div>';
        ?>
    </div>
</div>

<div style="clear: both;"></div>
<div id="filtered_reports">
    <h3><?php _e('Filtered statistics:', 'wpmandrill'); ?></h3>
    <label for="filter"><?php _e('Filter by:', 'wpmandrill'); ?> </label>
    <select id="filter" name="filter">
        <option value="none" selected="selected" ><?php _e('No filter', 'wpmandrill'); ?></option>
        <optgroup label="<?php _e('Sender:', 'wpmandrill'); ?>">
            <?php 
                foreach ( array_keys($stats['stats']['hourly']['senders']) as $sender) {
                    echo '<option value="s:'.$sender.'">'.$sender.'</option>';
                }
            ?>            
        </optgroup>
        <optgroup label="<?php _e('Tag:', 'wpmandrill'); ?>">
            <?php 
                if ( isset($stats['stats']['hourly']['tags']['detailed_stats']) 
                     && is_array($stats['stats']['hourly']['tags']['detailed_stats']) ) {
                     
                    foreach ( array_keys($stats['stats']['hourly']['tags']['detailed_stats']) as $tag) {
                        echo '<option value="'.$tag.'">'.$tag.'</option>';
                    }
                    
                }
            ?>            
        </optgroup>        
    </select>
    <label for="display"><?php _e('Display:', 'wpmandrill'); ?> </label>
    <select id="display" name="display">
        <option value="volume"><?php _e('Total Volume per Period', 'wpmandrill'); ?></option>
        <option value="average"><?php _e('Average Volume per Period', 'wpmandrill'); ?></option>
    </select><div id="ajax-icon-container"><span id="loading_data" class="hidden"></span></div>
    <div id="filtered_reports_canvas">
        <div id="filtered_recent" style="width: 50%;height: 300px; float: left;"></div>
        <div id="filtered_oldest" style="width: 50%;height: 300px; float: left;"></div>
    </div>
    <div style="clear: both;"></div>
</div>
<br/><br/>
<div id="hourly_report"></div>
<script type="text/javascript">
function emailFormatter(v, axis) {
    return v.toFixed(axis.tickDecimals) +" emails";
}
function percentageFormatter(v, axis) {
    return v.toFixed(axis.tickDecimals) +"%";
}
function wpm_showTooltip(x, y, contents) {
	jQuery('<div id="wpm_tooltip">' + contents + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y + 5,
        left: x + 5,
        border: '1px solid #fdd',
        padding: '2px',
        'background-color': '#fee',
        opacity: 0.80
    }).appendTo("body").fadeIn(200);
}
<?php
	// hourly stats data
	$hticks = array_keys($stats['graph']['hourly']['delivered']);
	array_walk($hticks, 'wpMandrill_transformJSArray');
	
	$hvolume = $stats['graph']['hourly']['delivered'];
	$horate  = $stats['graph']['hourly']['open_rate'];
	$hcrate  = $stats['graph']['hourly']['click_rate'];
	
	array_walk($hvolume,'wpMandrill_transformJSArray');
	array_walk($horate, 'wpMandrill_transformJSArray');
	array_walk($hcrate, 'wpMandrill_transformJSArray');

	// daily stats data
	$dticks 	= array_keys($stats['graph']['daily']['delivered']);
	array_walk($dticks, 'wpMandrill_transformJSArray');
	
	$day_keys 	= array();
	foreach(array_keys($stats['graph']['daily']['delivered']) as $day_index => $day_key) {
		$day_keys[$day_index] = $day_key;
	}
		
	$dvolume = $stats['graph']['daily']['delivered'];
	$dorate  = $stats['graph']['daily']['open_rate'];
	$dcrate  = $stats['graph']['daily']['click_rate'];
	
	array_walk($dvolume,'wpMandrill_transformJSArray', array(1, $day_keys));
	array_walk($dorate, 'wpMandrill_transformJSArray', array(1, $day_keys));
	array_walk($dcrate, 'wpMandrill_transformJSArray', array(1, $day_keys));
	
	
?>
var hvolume     = [<?php echo implode(',',$hvolume);?>];
var hopenrates  = [<?php echo implode(',',$horate);?>];
var hclickrates = [<?php echo implode(',',$hcrate);?>]
		
var dvolume     = [<?php echo implode(',',$dvolume);?>];
var dopenrates  = [<?php echo implode(',',$dorate);?>];
var dclickrates = [<?php echo implode(',',$dcrate);?>]
var dticks	    = [<?php echo implode(',',array_keys($stats['graph']['daily']['delivered']));?>]
jQuery(function () {
	var previousPoint = null;
	jQuery("#hourly_report_canvas").bind("plothover", function (event, pos, item) {
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                jQuery("#wpm_tooltip").remove();
                var x = item.datapoint[0].toFixed(0);	                

                if ( item.seriesIndex == 0 ) {
                	var y = item.datapoint[1].toFixed(0);
                	wpm_showTooltip(item.pageX, item.pageY, item.series.label + " (at hour " + x + ") = " + y + " emails");
                } else {
                	var y = item.datapoint[1].toFixed(2);
                	wpm_showTooltip(item.pageX, item.pageY, item.series.label + " (at hour " + x + ") = " + y + "%");
                }
            }
        }
        else {
        	jQuery("#wpm_tooltip").remove();
            previousPoint = null;            
        }
	});
	jQuery("#daily_report_canvas").bind("plothover", function (event, pos, item) {
        if (item) {
            if (previousPoint != item.dataIndex) {
                previousPoint = item.dataIndex;
                
                jQuery("#wpm_tooltip").remove();
                var x = dticks[item.dataIndex];
                	
                if ( item.seriesIndex == 0 ) {
                	var y = item.datapoint[1].toFixed(0);
                	wpm_showTooltip(item.pageX, item.pageY, "Day " + x + ": " + y + " emails");
                } else {
                	var y = item.datapoint[1].toFixed(2);
                	wpm_showTooltip(item.pageX, item.pageY, item.series.label + " for " + x + ": " + y + "%");
                }
            }
        }
        else {
        	jQuery("#wpm_tooltip").remove();
            previousPoint = null;            
        }
	});
	jQuery.plot(jQuery("#hourly_report_canvas"),
	           [ { data: hvolume, label: "Volume", yaxis: 2, bars: {show: true, barWidth: 0.6, align: "center"}, lines: { show: true }},
	             { data: hopenrates, label: "Open Rate"  },
	             { data: hclickrates, label: "Click Rate" }],
	           {
	        	   series: {
	 	   			   points: { show: true },
					   lines: { show: true },
					   shadowSize: 7
	 	           },
	        	   grid: {
	 	        	  hoverable: true,
	 	        	  aboveData: true,
	 	        	  borderWidth: 0,
	 	        	  minBorderMargin: 10,
	 	        	  margin: {
	 	        		    top: 10,
	 	        		    left: 10,
	 	        		    bottom: 15,
	 	        		    right: 100
	 	        		}
	 	           },
	               xaxes: [ { ticks: [<?php echo implode(',',$hticks);?>] } ],
	               yaxes: [ { min: 0, tickFormatter: percentageFormatter },
	                        {
	            	   			min: 0, 
	            	   			alignTicksWithAxis: 1, //1=right, null=left
	                          	position: 'sw',
	                          	tickFormatter: emailFormatter
	                        } ],
	               legend: { position: 'ne', margin: [20, 10]}
		});
	jQuery.plot(jQuery("#daily_report_canvas"),
	           [ { data: dvolume, label: "Volume", yaxis: 2, bars: {show: true, barWidth: 0.6, align: "center"}, lines: { show: true }},
	             { data: dopenrates, label: "Open Rate"  },
	             { data: dclickrates, label: "Click Rate" }],
	           {
	        	   series: {
	 	   			   points: { show: true },
					   lines: { show: true },
					   shadowSize: 7
	 	           },
	        	   grid: {
	 	        	  hoverable: true,
	 	        	  aboveData: true,
	 	        	  borderWidth: 0,
	 	        	  minBorderMargin: 10,
	 	        	  margin: {
	 	        		    top: 10,
	 	        		    left: 10,
	 	        		    bottom: 15,
	 	        		    right: 10
	 	        		}
	 	           },
	               xaxes: [ { ticks: [<?php echo implode(',', $dticks);?>] } ],
	               yaxes: [ { min: 0, tickFormatter: percentageFormatter },
	                        {
			     	   		  min: 0, 
	                          alignTicksWithAxis: 1, //1=right, null=left
	                          position: 'sw',
	                          tickFormatter: emailFormatter
	                        } ],
	               legend: { position: 'ne', margin: [20, 10]}
		});
});
</script>
<h3><?php echo $lit['hourly']['title']; ?></h3>
<h4><?php echo $lit['subtitle']; ?></h4>
    <div id="hourly_report_canvas" style="height: 400px;"></div><br/><br/>
<h3><?php echo $lit['daily']['title']; ?></h3>
<h4><?php echo $lit['subtitle']; ?></h4>
    <div id="daily_report_canvas" style="height: 400px;"></div>
    <h3><a href="http://mandrillapp.com/" target="_target"><?php _e('For more detailed statistics, please visit your Mandrill Dashboard','wpmandrill'); ?></a>.</h3>

		<?php
		wpMandrill::$stats = $stats;
?>