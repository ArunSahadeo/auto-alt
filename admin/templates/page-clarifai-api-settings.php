<h2>
    Clarifai API settings
</h2>

<form id="clarifai_api_settings" method="POST">
    <label for="clarifai_api_key">API key</label>

    <input type="text" name="clarifai_api_key" id="clarifai_api_key" value="<?php echo $apiKey; ?>">
    <input type="submit" value="Update" class="button button-primary button-large">
</form>

<div id="error-msg"></div>
