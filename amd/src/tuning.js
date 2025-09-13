/* eslint-env es6 */
/* eslint no-trailing-spaces: "off", no-unused-vars: "off" */
define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    return {
        init: function() {

            var isLoading = false; // Czy trwa zapytanie

            function sendToGpt(promptText) {
                if (isLoading) {
                    return; // Jeśli ładowanie, ignoruj kliknięcie
                }

               // console.log('🚀 Wywołano sendToGpt z promptem:', promptText);

                var original = $('.original-content-store').html();

                if (!promptText || promptText.length < 5) {
                    Notification.alert('Prompt too short', 'Please provide a prompt with at least 5 characters.');
                    return;
                }

                var docid = $('input[name="docid"]').val();

                isLoading = true;
                $('.generated-content-preview').html(
                    '<div class="d-flex justify-content-center my-3">' +
                        '<div class="spinner-border" role="status">' +
                            '<span class="visually-hidden">Loading...</span>' +
                        '</div>' +
                    '</div>'
                );
                $('#tuningprompt').val('');

                Ajax.call([{
                    methodname: 'mod_valuemapdoc_tune_content_api',
                    args: {
                        originaltext: original,
                        prompt: promptText,
                        docid: docid
                    }
                }])[0]
                .done(function(response) {
                    $('.generated-content-preview').html(response.tunedtext);
                    $('input[name="tunedresult"]').val(response.tunedtext); 
                })
                .fail(function(error) {
                    Notification.exception(error);
                })
                .always(function() {
                    isLoading = false;
                });
            } //sendToGpt

            // Attach events
            $('#send_prompt_button').on('click', function(e) {
                e.preventDefault();
                sendToGpt($('#tuningprompt').val());
            });

            $('.prompt-preset').on('click', function(e) {
                e.preventDefault();
                var prompt = $(this).data('prompt');
                sendToGpt(prompt);
            });
        }
    };
});