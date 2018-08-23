<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Clarifai\API\ClarifaiClient;
use Clarifai\DTOs\Inputs\ClarifaiFileImage;
use Clarifai\DTOs\Outputs\ClarifaiOutput;
use Clarifai\DTOs\Predictions\Concept;

add_action( 'add_attachment', 'addDefaultAlt' );

function addDefaultAlt ($postID)
{
    if ( wp_attachment_is_image($postID) )
    {
        $uploadImage = get_post($postID);
        $defaultAlt;
        $imageMeta = array(
            'ID'    => $postID
        );

        $defaultAlt = fetchImageDefinition($uploadImage);

        if ( !isset($defaultAlt) )
        {
            $defaultAlt = parseImageMetadata($uploadImage);
        }

        error_log( "Default alt: $defaultAlt" );

        $imageMeta['post_title'] = $defaultAlt; 
        $imageMeta['post_excerpt'] = $defaultAlt; 
        $imageMeta['post_content'] = $defaultAlt; 

        update_post_meta( $postID, '_wp_attachment_image_alt', $defaultAlt );
        wp_update_post( $imageMeta );

    }
}

function fetchImageDefinition ($uploadImage)
{
    $clarifaiAPIKey = get_option('api_key');

    if ( !isset($clarifaiAPIKey) )
    {
        error_log( 'Clarifai API key not set' );
        return;
    }

    $imageURI = $uploadImage->guid;

    if ( !isset($imageURI) )
    {
        error_log( 'Image GUID not set!' );
        return;
    }

    $clarifai = new ClarifaiClient($clarifaiAPIKey);

    $clarifaiAPIResponse = $clarifai->publicModels()->generalModel()->predict(
        new ClarifaiFileImage( file_get_contents($imageURI) ) 
    )->executeSync();

    if ( $clarifaiAPIResponse->isSuccessful() )
    {
        $clarifaiAPIOutput = $clarifaiAPIResponse->get();
        $concepts = $clarifaiAPIOutput->data();
        $firstConcept = $concepts[0];
    
        return $firstConcept->name();
    }

    else
    {
        set_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_description',
            __( $clarifaiAPIResponse->status()->description(), 'textdomain' )
        );
        set_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_details',
            __( $clarifaiAPIResponse->status()->errorDetails(), 'textdomain' )
        );

        switch ( $clarifaiAPIResponse->status()->statusCode() )
        {
            case 10020:
                set_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_status',
                    __( 'This image has been previously uploaded to the WordPress Media Library and is being treated as a duplicate entry.', 'textdomain' )
                );
            break;

            default:
                set_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_status',
                    __( 'Error status: ' . $clarifaiAPIResponse->status()->statusCode(), 'textdomain' )
                );

            break;
        }

        error_log('Error description: ' . $clarifaiAPIResponse->status()->description());
        error_log('Error details: ' . $clarifaiAPIResponse->status()->errorDetails());
        error_log('Error status: ' . $clarifaiAPIResponse->status()->statusCode());

        $postID = url_to_postid( $_SERVER['REQUEST_URI'] );

        if ( isset($postID) )
        {
            redirectToSelf( $postID );
            return;
        }

    }
}

function redirectToSelf ($postID)
{
    wp_redirect( $postID );
    exit(); 
}
add_action( 'admin_notices', 'clarifaiErrors' );

function clarifaiErrors ()
{
    $clarifaiErrorDescription = get_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_description');
    $clarifaiErrorDetails = get_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_details');
    $clarifaiErrorStatus = get_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_status');

    $hasErrors = ( $clarifaiErrorDescription && $clarifaiErrorDetails && $clarifaiErrorStatus );
    
    if ( !$hasErrors )
    {
        return;
    }
    
    delete_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_description');
    delete_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_error_details');
    delete_transient(AUTO_ALT_PLUGIN_NAME . '_clarifai_eror_status');
    
    printf( '<div class="%1$s"><p>Error description: %2$s</p></div><div class="%1$s"><p>Error details: %3$s</p></div><div class="%1$s"><p>%4$s</p></div>', 'notice notice-error error-message notice-alt is-dismissible', $clarifaiErrorDescription, $clarifaiErrorDetails, $clarifaiErrorStatus);

}

function parseImageMetadata ($uploadImage)
{

}
