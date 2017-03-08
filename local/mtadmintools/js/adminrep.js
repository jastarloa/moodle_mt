require(['jquery'], function($) {
    $( document ).ready(function() {
        $('#mtalltenantsbillhist').click(function (){
            $.ajax({
                url: mt_wsurl,
                data: 'action=adminmt_bill_history',
                dataType: 'json',
                success: function (responseText) {
                    if (responseText.status == 'ok') {
                        window.open(responseText.link,'adminmt_bill_history');
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
    });
});