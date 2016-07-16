/**
 * Created by Alex on 11.07.2016.
 */
function getTeamsByPattern(pattern){
    if(pattern.length == 0) return [];
    $.ajax({
        url: '/lionshotbeta/web/soccer/team/getteamsbypattern',
        method: "GET",
        data: {
            pattern: pattern
        },
        dataType: 'json',
        success: function(msg ){
            $('#teams_available').empty();
            $.each(  msg, function( key, value ) {
                $('<option></option>').val(key).text(value).appendTo('#teams_available');
            });
        },
        error: function(msg){
            console.log(msg);
        }
    });
}
$(document).ready(function(){
    $('#team_name_pattern').bind('change', function(){
        //console.log($(this).val());
        getTeamsByPattern($(this).val());
        //alert(1);
    })
});