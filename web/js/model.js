function ajaxGetModels(type_id, event_id, callback){
    return $.ajax({
        url: '/lionshotbeta/web/admin/model/ajaxgetparamsandmodels',
        data: { type_id: type_id, event_id: event_id},
        dataType : "json",
        success: function (data, textStatus) {
            if(callback)
            callback(data);
        },
        error: function(){
            if(callback)
            callback(null);
        }
    });
}

function ajaxCalculateMatches(model_id, match_ids, params, callback){
    return $.ajax({
        url: '/lionshotbeta/web/admin/model/ajaxcalculate',
        data: { model_id: model_id, match_ids: JSON.stringify(match_ids), params: JSON.stringify(params)},
        dataType : "json",
        success: function (data, textStatus) {
            console.log(data);
            if(callback)
            callback(data);
        },
        error: function(){
            if(callback)
            callback(null);
        }
    });
}

function updateModelContainer(data){
    if(data != null){
        if(data.status == 'success'){
            if(data.models_count > 0){
                $('#model_selection').fadeIn(0);
                for (var k in data.models){
                    if (data.models.hasOwnProperty(k)) {
                        $('#model_selection')
                            .append($("<option></option>")
                                .attr("value",k)
                                .addClass('model_option')
                                .text(data.models[k].name + '(' + data.models[k].status + ')'));
                    }
                }
            }
            else{
                $('#model_selection').find('.model_option').remove();
            }
        }
        else{
            $('#model_selection').fadeOut(0);
        }
    }
    else{
        $('#model_selection').fadeOut(0);
    }

    updateCalculateButton();
}

function updateParamContainer(data){
    if(data != null){
        if(data.status == 'success'){
            if(data.params_count > 0){
                $('#params_container').empty();
                $('#params_container').fadeIn(0);
                for (var k in data.params){
                    if (data.params.hasOwnProperty(k)) {
                        $('#params_container')
                            .append($("<input>")
                                .attr("type",data.params[k].type)
                                .attr("name",data.params[k].name)
                                .attr("id",data.params[k].id));
                    }
                }
            }
            else{
                $('#params_container').empty().fadeOut();
            }
        }
        else{
            $('#params_container').empty().fadeOut();
        }
    }
    else{
        $('#params_container').empty().fadeOut();
    }
}

function updateCalculateButton(){
    if( $('#model_selection').val() != ''){
        $('#calculate_button').fadeIn(0);
    }
    else{
        $('#calculate_button').fadeOut(0);
    }
}

$(document).ready(function(){
    $('#type_selection').change(function(){
        ajaxGetModels($('#type_selection').val(),$('#event_selection').val(), function(data) {
            updateModelContainer(data);
            updateParamContainer(data);
        });
    });
    $('#event_selection').change(function(){
        ajaxGetModels($('#type_selection').val(),$('#event_selection').val(), function(data) {
            updateModelContainer(data);
            updateParamContainer(data);
        });
    });
    $('#model_selection').change(function(){
        updateCalculateButton();
    });
    $('#calculate_button').bind('click', function(){
        var model_id = $('#model_selection').val();
        var matches_ids = [];
        $('.to_calculate_checkbox').each(function(){
            matches_ids.push($(this).data('match-id'));
        });
        var params =[];
        $('#params_container').find('input').each(function(){
            params.push($(this).val());
        });
        ajaxCalculateMatches(model_id, matches_ids, params);
        //var model_id = $('#model_selection').val();
    });
});