JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
        // simple data type
        if (t == "string") obj = '"'+obj+'"';
        return String(obj);
    }
    else {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n]; t = typeof(v);
            if (t == "string") v = '"'+v+'"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};

jQuery( function() {
    jQuery('#filter,#display').on('change keyup', function() {
        
        if ( jQuery("#filter option:selected").val() == 'none' ) {
            var display = 'block';
        } else {
            var display = 'none';
        }
        
        jQuery('#all_time').css('display',display);
        showStats();
    });
    jQuery('.columns-prefs, #collapse-button').on('click', function () {
        redrawDashboardWidget();
    });
    
    if ( pagenow == 'dashboard_page_wpmandrill-reports' ) {
        showStats();
    }
});

function showStats() {
    var filter = jQuery("#filter option:selected").val();
    var display = jQuery("#display option:selected").val();
    jQuery('#loading_data').css('display','block');

    jQuery.ajax({  
        type: 'POST',  
        url: ajaxurl,  
        data: {  
                action: 'get_mandrill_stats',
                filter: filter, 
                display: display
            },  
        success: function(data, textStatus, XMLHttpRequest){  
                jQuery('#loading_data').css('display','none');
                eval(data);
            },  
        error: function(MLHttpRequest, textStatus, errorThrown){  
                jQuery('#loading_data').css('display','none');
            }  
    });
    
  return false;
}

function redrawDashboardWidget() {
    jQuery('#mandrill_widget div#filtered_recent').html('<div id="ajax-icon-container"><span id="loading_data"></span></div>');
    jQuery.ajax({  
        type: 'POST',  
        url: ajaxurl,  
        data: {  
                action: 'get_dashboard_widget_stats',
                ajax: true
            },  
        success: function(data, textStatus, XMLHttpRequest){  
                eval(data);
            },  
        error: function(MLHttpRequest, textStatus, errorThrown){ 
                jQuery('#mandrill_widget div#filtered_recent').html('');
            }  
    });
    
  return false;
}
