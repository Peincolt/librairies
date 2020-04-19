/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');
const $ = require('jquery');
// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

document.addEventListener('DOMContentLoaded',function() {
    /* USER PART */
    var inputsUsername = document.querySelectorAll('input[id$="_username"]');
    var inputsEmail = document.querySelectorAll('input[id$="_email"]');
    var inputsPasswordsFirst = document.querySelectorAll('input[id$="_password_first"]');
    var inputsPasswordSecond = document.querySelectorAll('input[id$="_password_second"]');
    var checkboxRoles = document.querySelectorAll('input[id^="user_roles_"]');

    if (inputsUsername.length > 0) {
        for (var i=0; i<inputsUsername.length; i++) {
            inputsUsername[i].onblur = checkField;
        }
    }

    if (inputsEmail.length > 0) {
        for (var i=0; i<inputsEmail.length; i++) {
            inputsEmail[i].onblur = checkField;
        }
    }

    if (inputsPasswordsFirst.length > 0) {
        for (var i=0; i<inputsPasswordsFirst.length;i++) {
            inputsPasswordsFirst[i].oninput = checkPassword;
        }
    }

    if (inputsPasswordSecond.length > 0) {
        for (var i=0; i<inputsPasswordSecond.length;i++) {
            inputsPasswordSecond[i].oninput = checkSame;
        }
    }

    if (checkboxRoles.length > 0) {
        for (var i=0;i<checkboxRoles.length;i++) {
            checkboxRoles[i].onclick = selectRole;
        }
    }

},false);

/* USER FUNCTIONS */

function checkField(event)
{
    console.log('check');
    var value = event.target.value;
    var split = event.target.id.split('_');
    var field = split[split.length-1];
    if (split[1] == "demand") {
        var demand = true;
    } else {
        var demand = false;
    }

    if (value) {
        $.post('/ajax/user/isFieldTaken',
            {
                'field' : field,
                'value' : value,
                'demand' : demand
            }
        ).done(function(data) {
            var submit = document.getElementById('submit');
            var divError = document.getElementById('error_'+data.field);
            divError.innerHTML = "";
            if (data.error_message) {
                if (!divError.classList.contains('alert-danger')) {
                    divError.classList.add('alert','alert-danger');
                    if (data.block != undefined) {
                        submit.disabled = true;
                    }
                }
                divError.innerHTML = data.error_message;
            } else {
                divError.classList.remove('alert','alert-danger');
                submit.disabled = false;
            }
        }).fail(function(data) {
            document.getElementById('submit').disabled = false;
            /*var divError = document.getElementById('error_'+data.field);
            divError.innerHTML = "";
            divError.appendChild(document.createTextNode('Erreur lors de la récupération des événements. Veuillez réessayer plus tard ou contacter un admin en lui transmettant les messages d\'erreurs siutés juste en dessous : '));
            divError.appendChild(document.createElement('br'));
            divError.appendChild(document.createTextNode('Code d\'erreur : '+data.status+' . Message d\'erreur : '+data.statusText));
            if (!divError.classList.contains('alert-danger')) {
                divError.classList.add('alert','alert-danger');
            }*/
        });
    }
}

function checkPassword(event)
{
    var id = event.target.id;
    var explode = id.split('_');
    explode[explode.length - 1] = 'second';
    var second = document.getElementById(explode.join('_'));
    var submit = document.getElementById('submit');
    var divError = document.getElementById('error_password');
    divError.innerHTML = "";

    if (second.value && this.value != second.value) {
        divError.innerHTML = "Les deux mots de passe doivent correspondre";
        divError.classList.add('alert','alert-danger');
        submit.disabled = true;
    } else {
        var message = "";
        var lengthRegex = new RegExp("^.{8,}$");
        var majRegex = new RegExp("[A-Z]+");
        var numberRegex = new RegExp("[0-9]+");
        var specialCharRegex = new RegExp("\\W+");
        if (!lengthRegex.test(this.value)) {
            message = "Votre mot de passe doit contenir au moins 8 caractéres";
        }

        if (!majRegex.test(this.value)) {
            if (message == "") {
                message = "Votre mot de passe doit contenir au moins une lettre majuscule";
            } else {
                message += " et une lettre majuscule";
                var length = true;
            }
        }

        if (!numberRegex.test(this.value)) {
            if (message == "") {
                message = "Votre mot de passe doit contenir au moins un chiffre";
            } else {
                number = true;
                if (length != undefined) {
                    message += ", un chiffre";
                } else {
                    message += " et un chiffre";
                }
            }
        }

        if (!specialCharRegex.test(this.value)) {
            if (message == "") {
                message = "Votre mot de passe doit au moins contenir un caractére spécial";
            } else {
                if (length || number) {
                    message += ", un caractére spécial";
                } else {
                    message += " et un caractére spécial";
                }
            }
        }

        if (message != "") {
            divError.classList.add('alert','alert-danger');
            divError.innerHTML = message;
            submit.disabled = true;
        } else {
            divError.classList.remove('alert-danger','alert');
            submit.disabled = false;
        }
    }
}

function checkSame(event)
{
    var id = event.target.id;
    var explode = id.split('_');
    explode[explode.length - 1] = 'first';
    var first = document.getElementById(explode.join('_'));
    var submit = document.getElementById('submit');

    var divError = document.getElementById('error_password');
    divError.innerHTML = "";
    if (first.value && this.value != first.value) {
        divError.innerHTML = "Les deux mots de passe doivent être identiques";
        divError.classList.add('alert','alert-danger');
        submit.disabled = true;
    } else {
        divError.classList.remove('alert-danger','alert');
        submit.disabled = false;
    }
}

function selectRole(event)
{
    var id = event.target.id;
    var checkboxRoles = document.querySelectorAll('input[id^="user_roles_"]');

    if (checkboxRoles.length > 0) {
        for (var i=0;i<checkboxRoles.length;i++) {
            if (checkboxRoles[i].id != id) {
                checkboxRoles[i].checked = false;
            }
        }
    }
}