/* eslint-env es6 */
define(['jquery', 'core/ajax'], function() {
    return {
        init: function() {
                const copyButton = document.getElementById('id_copytoclipboard');
                const textContainer = document.getElementById('document-content-text');
                const spinner = document.getElementById('copy-spinner');

                if (!copyButton || !textContainer) {
                    alert('⚠️ Copy button or text container not found.');
                    return;
                }

                copyButton.addEventListener('click', async () => {
                    if (spinner) {
                        spinner.classList.remove('d-none'); // Show spinner
                    }

                    try {
                        const textToCopy = textContainer.innerText.trim();
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(textToCopy);
                        } else {
                            const textarea = document.createElement('textarea');
                            textarea.value = textToCopy;
                            document.body.appendChild(textarea);
                            textarea.select();
                            document.execCommand('copy');
                            document.body.removeChild(textarea);
                        }

                        alert('✅ Text copied to clipboard!');
                    } catch (err) {
                        alert('❌ Failed to copy text: ' + err.message);
                    } finally {
                        if (spinner) {
                            spinner.classList.add('d-none'); // Hide spinner
                        }
                    }
                });
        }
    };
});
