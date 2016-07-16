/**
 * Created by Alex on 13.07.2016.
 */
function getBetsByMatchId(match_id){
    if(match_id.length == 0) return [];
    $.ajax({
        url: '/lionshotbeta/web/betting/default/getbetsbymatchid',
        method: "GET",
        data: {
            match_id: match_id
        },
        dataType: 'json',
        success: function(msg ){
            $('#bet_selection').empty();
            if(msg.length == 0)
                $('<option></option>').val(null).text('No bets found.').appendTo('#bet_selection');
            else
                $.each(  msg, function( key, value ) {
                $('<option></option>').val(key).text(value).appendTo('#bet_selection');
            });
        },
        error: function(msg){
            console.log(msg);
        }
    });
}

$(document).ready(function(){
    $('#match_selection').bind('change', function(){
        getBetsByMatchId($(this).val());
    });
});