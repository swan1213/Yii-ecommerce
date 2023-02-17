var timeOutObj = null;
function getIntervalstatus() {
    var currentUser = $('#connected_user').val();
    var getItemKey = "importing_completed_" + currentUser;
    var currentStatus = localStorage.getItem(getItemKey);
    console.log("currentStatus = " + currentStatus);
    if ( currentStatus == 'importing' ){
        timeOutObj = setTimeout(getIntervalstatus, 5000);

    } else {
        if ( timeOutObj !== null ) {
            clearTimeout(timeOutObj);
            window.location = '/';
        }

    }

}

function getStatusImporting (){

    var currentUser = $('#connected_user').val();
    $.ajax({
        type: 'post',
        url: '/dashboard/import-status',
        data: {
            user_id: currentUser,
        },
        success: function (result) {
            console.log(result);
            var response = $.parseJSON(result);
            var status_completed = response.completed;
            var setItemKey = "importing_completed_" + currentUser;
            if ( status_completed == true ){
                localStorage.setItem(setItemKey, 'completed');
            } else {
                localStorage.setItem(setItemKey, 'importing');
                setTimeout(getStatusImporting, 3000);
                if ( timeOutObj === null ){
                    getIntervalstatus();
                }
            }
        }
    });

}

$(document).ready(function(){
    getStatusImporting();

});