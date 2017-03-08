require(['jquery'], function($) {

    billpartsloaded = { 'diskloaded' : false, 'disk' : 0, 'usersloaded' : false, 'users' : 0 };

    // Load the Visualization API and the corechart package.
    google.charts.load('current', {'packages':['corechart']});

    // Set a callback to run when the Google Visualization API is loaded.
    google.charts.setOnLoadCallback(function () {
        getDiskdetailedCSV();
        drawDiskGlobalChart();
        drawUsersGlobalChart();
        getBillHistoryCSV();
        drawBillHistoryChart();
    });

    var getDiskdetailedCSV = function() {
        $('#mtadmintoolsfilescsv').click(function (){
            $.ajax({
                url: mt_wsurl,
                data: 'action=disk_detailed_data',
                dataType: 'json',
                success: function (responseText) {
                    if (responseText.status == 'ok') {
                        window.open(responseText.link,'disk_detailed_data');
                        console.log(responseText.link);
                    } else {
                        alert(responseText.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown + ': ' + textStatus);
                }
            });
        });
    }

    var getBillHistoryCSV = function() {
        $('#mtadmintoolsfilesbillhist').click(function (){
            $.ajax({
                url: mt_wsurl,
                data: 'action=bill_history_detailed_data',
                dataType: 'json',
                success: function (responseText) {
                    if (responseText.status == 'ok') {
                        window.open(responseText.link,'bill_history_detailed_data');
                        console.log(responseText.link);
                    } else {
                        alert(responseText.msg);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(errorThrown + ': ' + textStatus);
                }
            });
        });
    }

    var drawDiskGlobalChart = function() {
        $.ajax({
            url: mt_wsurl,
            data: 'action=disk_gral_data',
            dataType: 'json',
            success: function (responseText) {
                var chart_disk_opts_tmp = $.extend({}, chart_disk_opts);
                chart_disk_opts_tmp.title =
                    chart_disk_opts.title + "\n (" + responseText.totals.bytes + " Bytes = " +
                    responseText.totals.format + ")";
                var data = new google.visualization.DataTable(responseText);
                new google.visualization.PieChart(document.getElementById('chart_disk')).
                draw(data, chart_disk_opts_tmp);
                billpartsloaded.disk = responseText.totals.bytes;
                billpartsloaded.diskloaded = true;
                drawMonthBill();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown + ': ' + textStatus);
            }
        });
    }

    var drawUsersGlobalChart = function() {
        $.ajax({
            url: mt_wsurl,
            data: 'action=active_users_data',
            dataType: 'json',
            success: function (responseText) {
                var data = new google.visualization.DataTable(responseText);
                new google.visualization.BarChart(document.getElementById('chart_active_users')).
                draw(data, chart_active_users_opts);
                billpartsloaded.users = responseText.totals.actives;
                billpartsloaded.usersloaded = true;
                drawMonthBill();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown + ': ' + textStatus);
            }
        });
    }

    var drawMonthBill = function() {
        if (billpartsloaded.usersloaded && billpartsloaded.diskloaded) {
            var diskgb = billpartsloaded.disk / 1073741824;
            var amount = (billpartsloaded.users * 0.2) + (diskgb * 0.5);
            var currency = $("input[name='currency']").val();
            // Round 2 decimals.
            diskgb = Math.round(diskgb * 100) / 100;
            amount = Math.round(amount * 100) / 100;
            // Pricing.
            var bygb = $('#fitem_id_disk_cost div.fstatic span').data('diskcost');
            var byusr = $('#fitem_id_cost_by_user div.fstatic span').data('usercost');
            $('div#fitem_id_monthbalance div.fstatic').html(
                amount + ' ' + currency +
                ' <i>(' + diskgb + ' GB x ' + bygb + ' ' + currency + '/GB + ' + 
                billpartsloaded.users + ' ' + usersstr + ' x ' + byusr + ' ' + currency + '/' + userstr + ')</i>');
        }
    }

    var drawBillHistoryChart = function() {
        $.ajax({
            url: mt_wsurl,
            data: 'action=bill_history',
            dataType: 'json',
            success: function (responseText) {
                var data = new google.visualization.DataTable(responseText);
                new google.visualization.LineChart(document.getElementById('chart_history')).
                draw(data, chart_history_opts);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(errorThrown + ': ' + textStatus);
            }
        });
    }
});