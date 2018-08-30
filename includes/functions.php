<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Clarifai\API\ClarifaiClient;
use Clarifai\DTOs\Inputs\ClarifaiFileImage;
use Clarifai\DTOs\Outputs\ClarifaiOutput;
use Clarifai\DTOs\Predictions\Concept;

$stopwords = array(
    "I'm",
);

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
            $defaultAlt = '';
        }

        $imageMeta['post_title'] = $defaultAlt; 
        $imageMeta['post_excerpt'] = $defaultAlt; 
        $imageMeta['post_content'] = $defaultAlt; 

        update_post_meta( $postID, '_wp_attachment_image_alt', $defaultAlt );
        wp_update_post( $imageMeta );

    }
}

function fetchImageDefinition ($uploadImage)
{
    $clarifaiAPIKey = get_option('clarifai_api_key');

    if ( !isset($clarifaiAPIKey) )
    {
        error_log( 'Clarifai API key not set' );
        return;
    }

    $imageURI = $uploadImage->guid;
    $slugParts;
    $extractedText;

    if ( !isset($imageURI) )
    {
        error_log( 'Image GUID not set!' );
        return;
    }

    try {
        $extractedText = extractImageText($imageURI);
    } catch (Exception $e) {
        wp_send_json('Error is: ' . $e->getMessage(), 400);
    }

    $imageFileName = preg_replace('/\d/', '', $uploadImage->post_name);

    if ( substr_count($imageFileName, '-') > 0 )
    {
        $slugParts = explode('-', $imageFileName);
    }

    $clarifai = new ClarifaiClient($clarifaiAPIKey);

    $clarifaiAPIResponse = $clarifai->publicModels()->generalModel()->predict(
        new ClarifaiFileImage( file_get_contents($imageURI) ) 
    )->executeSync();

    if ( $clarifaiAPIResponse->isSuccessful() )
    {
        $clarifaiAPIOutput = $clarifaiAPIResponse->get();
        $finalConcept = '';
        $concepts = $clarifaiAPIOutput->data();

        foreach ( $concepts as $index => $concept )
        {
            if ( isset($slugParts) )
            {
                $pattern = '/' . preg_quote($concept->name(), '/') . '/i'; 

                if ( preg_grep($pattern, $slugParts) )
                {
                    $finalConcept = $concept->name();
                    break;
                }
                else
                {
                    continue;
                }
            }
            elseif ( substr_count($imageFileName, $concept->name()) > 0 )
            {
                $finalConcept = $concept->name();
                break;
            }
            elseif ( isset($extractedText) )
            {
                $finalConcept = $extractedText;
                break;
            }
            else
            {
                if ( (intval($index) + 1) != count($concepts) )
                {
                    continue;
                }

                $finalConcept = $concepts[0]->name();
                break;
            }
        }
    
        return $finalConcept;
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

function extractImageText ($imageURI)
{
    $microsoftAPIKey = get_option('microsoftcognitiveservices_api_key'); 

    if ( !isset($microsoftAPIKey) )
    {
        return;
    }

    $microsoftAPIEndpoint = '/recognizeText';
    $reqParams = '?mode=Printed';
    $microsoftAPIBase = MICROSOFT_REGION_DOMAIN . $microsoftAPIEndpoint . $reqParams;

    $postArgs = array(
        'headers'   => array(
            'Content-Type'  => 'application/octet-stream',
            'Ocp-Apim-Subscription-Key' => $microsoftAPIKey,
        ),
        'body'  => file_get_contents($imageURI)
    );

    $response = wp_safe_remote_post(
        $microsoftAPIBase,
        $postArgs
    );

    $responseStatus = wp_remote_retrieve_response_code( $response );
    $responseMessage = wp_remote_retrieve_response_message( $response );

    if ( !in_array($responseStatus, array(200, 201, 202)) )
    {
        error_log("The response code: $responseStatus");
        error_log("The response message: $responseMessage");
        return;
    }

    $operationLocation = wp_remote_retrieve_header($response, 'Operation-Location');

    if ( !isset($operationLocation) )
    {
        return;
    }

    $extractedText = fetchText($operationLocation, $microsoftAPIKey);

    return $extractedText;
}

function fetchText ($operationURI, $microsoftAPIKey)
{
    $args = array(
        'headers'   => array(
            'Content-Type'  => 'application/json',
            'Ocp-Apim-Subscription-Key' => $microsoftAPIKey,
        ),
    );

    $response = wp_remote_get(
        $operationURI,
        $args
    );

    $responseStatus = wp_remote_retrieve_response_code( $response );
    $responseMessage = wp_remote_retrieve_response_message( $response );

    if ( !in_array($responseStatus, array(200, 201, 202)) )
    {
        error_log("The response code: $responseStatus");
        error_log("The response message: $responseMessage");
        return;
    }

    $apiResponse = json_decode( wp_remote_retrieve_body($response), true );

    error_log( print_r($apiResponse, TRUE) );

    if ( isset($apiResponse['recognitionResult']['lines']['words']) )
    {
        $extractedKeyword;
        $extractedKeywords = $apiResponse['recognitionResult']['lines']['words'];

        foreach ( $extractedKeywords as $keyword )
        {
            $keyword = $keyword['text'];

            if ( in_array(strtolower($keyword), $stopwords) )
            {
                continue;
            }

            $extractedKeyword = $keyword;
            break;
        }

        return $extractedKeyword;
    }

    return;
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
