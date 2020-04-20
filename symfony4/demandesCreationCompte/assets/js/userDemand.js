const $ = require('jquery');

document.addEventListener('DOMContentLoaded',function() {
    var checkboxValider = document.getElementsByName('checkbox-valider');
    var checkboxRefuser = document.getElementsByName('checkbox-refuser');

    if (checkboxValider.length > 0) {
        for(var i=0;i<checkboxValider.length;i++) {
            checkboxValider[i].onclick = editDemand;
        }
    }

    if (checkboxRefuser.length > 0) {
        for(var i=0;i<checkboxRefuser.length;i++) {
            checkboxRefuser[i].onclick = editDemand;
        }
    }

},false);

function editDemand(event)
{
    var id = event.target.value
    var name = event.target.name;
    var url = "/user/demand/";
    var regexValider = new RegExp('.*valider.*');

    if (regexValider.test(name)) {
        url += "valid";
    } else {
        url += "decline";
    }

    if (id) {
        $.post(url,
            {
            'id' : id
            }
        ).done(function(data){
            if (data.error_message) {
                createAlert(data.error_message,'danger');
            } else {
                var checkboxs = document.getElementsByName('checkbox-'+data.action);
                for(var i=0;i<checkboxs.length;i++) {
                    if (checkboxs[i].value == data.id) {
                        document.getElementById('array-demands').childNodes[3].removeChild(checkboxs[i].parentNode.parentNode);
                    }
                }

                createAlert(data.message,'success');
            }
        }).fail(function(data) {
            console.log(data);
        });
    }
}

function createAlert(message,type)
{
    var nav = document.getElementsByTagName('nav');
    var divAlertGeneral = document.getElementById('div-alert');

    if (divAlertGeneral == undefined) {
        var divAlertGeneral = document.createElement('div');
        divAlertGeneral.id = 'div-alert';
        nav[0].after(divAlertGeneral);
    }

    divAlertGeneral.innerHTML = "";

    var divAlert = document.createElement('div');
    divAlert.classList.add('container','alert','alert-'+type);
    divAlert.innerHTML = message;
    divAlertGeneral.appendChild(divAlert);
}