<h2>
    Clarifai API settings
</h2>

<form id="clarifai_api_settings" method="POST">
    <label for="api_key">API key</label>

    <input type="text" name="api_key" id="api_key" value="<?php echo $apiKey; ?>">
    <input type="submit" value="Update" class="button button-primary button-large">
</form>

<div id="error-msg"></div>
