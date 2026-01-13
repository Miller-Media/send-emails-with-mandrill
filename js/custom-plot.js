var bounces_recent = JSON.parse(plotData.bounces_recent);
var opens_recent = JSON.parse(plotData.opens_recent);
var unopens_recent = JSON.parse(plotData.unopens_recent);
var lit_bounced = plotData.lit_bounced;
var lit_opened = plotData.lit_opened;
var lit_unopened = plotData.lit_unopened;
var lit_today = plotData.lit_today;
var lit_last7days = plotData.lit_last7days;
var tickFormatter = plotData.tickFormatter;

function emailFormatter(v, axis) {
    return v.toFixed(axis.tickDecimals) + ' emails';
}

function percentageFormatter(v, axis) {
    return v.toFixed(axis.tickDecimals) + '%';
}

function wpm_showTooltip(x, y, contents) {
    jQuery('<div id="wpm_tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y + 5,
        left: x + 5,
        border: '1px solid #fdd',
        padding: '2px',
        'background-color': '#fee',
        opacity: 0.80
    }).appendTo('body').fadeIn(200);
}

var previousPoint = null;
jQuery('#filtered_recent').on('plothover', function (event, pos, item) {
    if (item) {
        if (previousPoint != item.dataIndex) {
            previousPoint = item.dataIndex;
            jQuery('#wpm_tooltip').remove();
            var x = item.datapoint[0].toFixed(0);
            var y = (tickFormatter == 'emailFormatter') ? item.datapoint[1].toFixed(0) : item.datapoint[1].toFixed(2);
            wpm_showTooltip(item.pageX, item.pageY, item.series.label + ' = ' + y + ((tickFormatter == 'emailFormatter') ? ' emails' : '%'));
        }
    } else {
        jQuery('#wpm_tooltip').remove();
        previousPoint = null;
    }
});

// Clear the 'Loading...' text
jQuery('#filtered_recent').html('');

jQuery.plot(jQuery('#filtered_recent'), [
    { data: bounces_recent, label: lit_bounced },
    { data: opens_recent, label: lit_opened },
    { data: unopens_recent, label: lit_unopened }
], {
    series: {
        stack: false,
        bars: { show: true, barWidth: 0.6, align: 'center' },
        points: { show: false },
        lines: { show: false },
        shadowSize: 4
    },
    grid: {
        hoverable: true,
        aboveData: true,
        borderWidth: 0,
        minBorderMargin: 10,
        margin: { top: 10, left: 10, bottom: 15, right: 10 }
    },
    xaxes: [{ ticks: [[0, lit_today], [1, lit_last7days]] }],
    yaxes: [{ min: 0, tickFormatter: tickFormatter }],
    legend: { position: 'ne', margin: [20, 10] }
});
