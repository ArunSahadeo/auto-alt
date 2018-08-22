window.addEventListener( 'load', initApp );

var self = this;

self.form   = '';
self.input  = '';

function initApp ()
{
    var form = document.getElementById('clarifai_api_settings');

    if ( form.length === 0 )
    {
        return;
    }

    self.form = form;

    var input = document.getElementById('api_key');
    self.input = input;

    self.input.onchange = checkDynamicInput;

    self.form.addEventListener( 'submit', handleSubmitEvent );
}

function checkDynamicInput ()
{
    var input = document.getElementById('api_key'),
        pattern = /^[a-z0-9]+$/i,
        message = 'You are not entering a valid API key value'
    ;

    if (!input.value.match(pattern))
    {
        input.setCustomValidity(message); 
    }
}

function handleSubmitEvent ()
{
    if (!self.input.checkValidity())
    {
        document.getElementById('error-msg').innerHTML = "You did not enter a valid API key";
        return false;
    }

    return true;
}
