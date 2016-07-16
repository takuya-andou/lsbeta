/**
 * Created by Alex on 04.05.2016.
 */
var model_testing_in_progress = false;

function startLoadingAnimation(obj, callback) // - функция запуска анимации
{

    var img = $('<img src="../../img/loader.gif" id="loading_image"/>');
    //console.log(img.height());
    var size  = ($(obj).height() > $(obj).width()) ?  $(obj).width() :  $(obj).height();

    var centerY = ($(obj).height() - 40)/2;
    var centerX = ($(obj).width() - 40)/2;


    img.css({'top': centerY, 'left': centerX});
    $(obj).append(img);
    img.fadeIn(300, function(){}).delay(500);
    if(typeof callback !== 'undefined') callback();

}

function stopLoadingAnimation(obj, callback) // - функция останавливающая анимацию
{
    var img;
    $(obj).find('#loading_image').fadeOut(500, function(){
        $(obj).find('#loading_image').remove();
        if(typeof callback !== 'undefined') callback();
    });

}

function drawChart(chart_data){
        var options = {
            width: 800,
            height: 400,
            // Don't draw the line chart points
            showPoint: false,
            // Disable line smoothing
            lineSmooth: false,
            // X-Axis specific configuration
            axisX: {
                // We can disable the grid for this axis
                showGrid: true,
                // and also don't show the label
                showLabel: true
            },
            // Y-Axis specific configuration
            axisY: {
                // Lets offset the chart a bit from the labels
                offset: 60,
                // The label interpolation function enables you to modify the values
                // used for the labels on each axis. Here we are converting the
                // values into million pound.
                labelInterpolationFnc: function(value) {
                    return value + '%';
                }
            }
        };
        new Chartist.Line('#chart', {
            series: [
                chart_data
            ]
        }
            , options);
}

$(document).ready(function(){
    /**
     * Filling model's params
     */
    if($('.param-checkbox').length > 0){
        $('.param-checkbox').each(function(){
            $(this).parent().parent().find('.param-value input').prop("disabled", !$(this).is(':checked'));;

        });
        $('.param-checkbox').change(function(){
            $(this).parent().parent().find('.param-value input').prop("disabled", !$(this).is(':checked'));;
        });
    }

    /**
     * Drawing chart
     */
    /*if(typeof chart_data !== "undefined" && $('.chart_container').length > 0) {
        drawChart();
    }*/

    /**
     * RBAC mdal form
     */
    if($('.modal_button').length > 0){
        $('.modal_button').click(function(){
            $('#modal_edit_form_container').modal('show').find('#modal_content').load($(this).attr('data-url'), function(){

            });
        })
    }

    /**
     * Running model test
     */
    if($('#run_test_button').length > 0){
        $('#run_test_button').bind('click', function(){
            if(model_testing_in_progress) return;
            startLoadingAnimation($('#model_result'));
            $('#chart').empty();
            $('#stats').empty();
            $('#message').empty();
            $('#matches').empty();
            var model_id = $('#model_selection').val();
            var bet_sizing = $('#bet_sizing').val();
            var matches_since_date = $('#matches_since_date').val();
            var matches_until_date = $('#matches_until_date').val();
            var lower_coef = $('#lower_coef').val();
            var upper_coef = $('#upper_coef').val();
            var matches_num = $('#matches_num').val();
            var bets_num = $('#bets_num').val();
            var bookie_ids = [];
            $('#bookie_selection').find('input:checked').each(function(){
                bookie_ids.push(this.value);
            });
            model_testing_active = true;
            $.ajax({
                url: 'runtest',
                method: "GET",
                data: {
                    model_id : model_id,
                    bookie_ids : JSON.stringify(bookie_ids),
                    bet_sizing: bet_sizing,
                    matches_since_date: matches_since_date,
                    matches_until_date: matches_until_date,
                    lower_coef: lower_coef,
                    upper_coef: upper_coef,
                    matches_num: matches_num,
                    bets_num: bets_num,
                },
                dataType: 'json',
                success: function(msg ){
                    model_testing_in_progress = false;
                    stopLoadingAnimation($('#model_result'), function(){
                        $.each(  msg.messages, function( key, value ) {
                            $('<div></div>').text(value).appendTo('#message');
                        });
                        if(msg.status == 0){
                            //$('#message').text('Everything\'s fine.');
                            var chart_data = [];
                            $.each(  msg.results.overall.dynamics, function( key, value ) {
                                chart_data[key] = value.bank_size;
                                $('<div></div>').text('B: ').
                                    append(value.bet_result + ' ' + value.bank_size).
                                    appendTo('#bets');
                            });
                            drawChart(chart_data);
                            $('<div></div>').text('Matches selected: ').append(msg.results.overall.matches_num).appendTo('#stats');
                            $('<div></div>').text('Bets considered: ').append(msg.results.overall.bets_num).appendTo('#stats');
                            $('<div></div>').text('Bets done: ').append(msg.results.overall.bets_done_num).appendTo('#stats');
                            $.each(  msg.results.overall.matches, function( key, value ) {
                                $('<div></div>').text(value).appendTo('#matches');
                            });
                            $.each(  msg.results.overall.bets, function( key, value ) {
                                $('<div></div>').text(value).appendTo('#bets');
                            });
                        }
                    });
                },
                error: function(msg){
                    model_testing_in_progress = false;
                    stopLoadingAnimation($('#model_result'), function(){
                        $('#message').text('Something\'s gone wrong (error ajax).');
                    });


                }
            });
        });
    }
});
