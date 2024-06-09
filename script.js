const code = document.querySelector('#code');
const result = document.querySelector('#result');
const saveButton = document.querySelector('#save');

function run() {
    const codeValue = code.value;

    localStorage.setItem('code', codeValue);

    const htmlMatch = codeValue.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
    const cssMatch = codeValue.match(/<style[^>]*>([\s\S]*?)<\/style>/i);
    const jsMatch = codeValue.match(/<script[^>]*>([\s\S]*?)<\/script>/i);
    const javaMatch = codeValue.match(/<script[^>]* type="text\/java"[^>]*>([\s\S]*?)<\/script>/i);
    const phpMatch = codeValue.match(/<\?php([\s\S]*?)\?>/i);

    const htmlContent = htmlMatch ? htmlMatch[1] : '';
    const cssContent = cssMatch ? cssMatch[1] : '';
    const jsContent = jsMatch ? jsMatch[1] : '';
    const javaContent = javaMatch ? javaMatch[1] : '';
    const phpContent = phpMatch ? phpMatch[1] : '';

    const content = `
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Result</title>
                <style>${cssContent}</style>
                <script>${jsContent}</script>
                <script type="text/java">${javaContent}</script>
            </head>
            <body>
                ${htmlContent}
                <?php ${phpContent} ?>
            </body>
        </html>
    `;

    const doc = result.contentDocument || result.contentWindow.document;
    doc.open();
    doc.write(content);
    doc.close();
}

code.value = localStorage.getItem('code') || '';
run();
code.onkeyup = run;

saveButton.addEventListener('click', () => {
    const codeValue = code.value;
    fetch('save_code.php?project_id=22', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ code: codeValue }),
    })    
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Code saved successfully!');
        } else {
            alert('Failed to save code.');
        }
    })
    .catch(error => {
        console.error('Error saving code:', error);
        alert('Failed to save code.');
    });
});


