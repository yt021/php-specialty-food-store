<?php
if (isset($indexed)) {
    if ($indexed == 1) {
        $title = getVarFromDB("admin_modules", "name", "flag", $cf);
        if (!$title || $title === "") $title = "Sitemap";
?>

<div id="main" class="middle ls_main">
    <div id="sect1" class="sect">
        <div class="middle container">
            <div class="title">
                <?php echo $title; ?>
            </div>
        </div>
    </div>

    <div class="cut w100p"></div>

    <div id="sect_sitemap" class="sect">
        <div class="middle container">
            <h2 class="tac">Sitemap Management</h2>
            <div class="tac" style="margin: 20px 0;">
                <a onclick="generateSitemap()" class="btn half fr admin_dashboard" id="generateBtn">
                    Generate New Sitemap
                </a>
                <a onclick="viewSitemap()" class="btn half fr admin_dashboard" id="viewBtn">
                    View Sitemap
                </a>
                <a onclick="validateSitemap()" class="btn half fr admin_dashboard" id="validateBtn">
                    Validate Sitemap
                </a>
                <div class="cb"></div>
            </div>
            <div id="sitemap_status" class="sitemap_status" style="display:none;">
                <div id="sitemap_message"></div>
                <div id="sitemap_details"></div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    .sitemap_status {
        margin: 20px 0;
        padding: 15px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
    }
    .sitemap_status.success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    .sitemap_status.error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
    .sitemap_status.warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }
    .sitemap_details {
        margin-top: 10px;
        font-size: 12px;
        color: #666;
    }
    .sitemap_progress {
        width: 100%;
        height: 20px;
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        overflow: hidden;
        margin: 10px 0;
    }
    .sitemap_progress_bar {
        height: 100%;
        background-color: #007cba;
        width: 0%;
        transition: width 0.3s ease;
    }
    .admin_dashboard:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<script type="text/javascript">
    function generateSitemap() {
        const generateBtn = document.getElementById('generateBtn');
        const statusDiv = document.getElementById('sitemap_status');
        const messageDiv = document.getElementById('sitemap_message');
        const detailsDiv = document.getElementById('sitemap_details');

        generateBtn.style.pointerEvents = 'none';
        generateBtn.style.opacity = '0.5';
        generateBtn.innerHTML = 'Generating...';

        statusDiv.style.display = 'block';
        statusDiv.className = 'sitemap_status';
        messageDiv.innerHTML = 'Generating a new sitemap...';
        detailsDiv.innerHTML = '<div class="sitemap_progress"><div class="sitemap_progress_bar" id="progressBar"></div></div>';

        let progress = 0;
        const progressBar = document.getElementById('progressBar');
        const progressInterval = setInterval(() => {
            progress += 10;
            progressBar.style.width = progress + '%';
            if (progress >= 90) {
                clearInterval(progressInterval);
            }
        }, 200);

        fetch('../sitemap_generator.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=generate'
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';

            if (data.success) {
                statusDiv.className = 'sitemap_status success';
                messageDiv.innerHTML = 'Sitemap generated successfully.';
                detailsDiv.innerHTML = `
                    <strong>Details:</strong><br>
                    Pages: ${data.stats.pages}<br>
                    Products: ${data.stats.products}<br>
                    Posts: ${data.stats.posts}<br>
                    Generated at: ${data.generated_at}<br>
                    File size: ${data.file_size}
                `;
            } else {
                statusDiv.className = 'sitemap_status error';
                messageDiv.innerHTML = 'Failed to generate sitemap.';
                detailsDiv.innerHTML = `<strong>Error:</strong> ${data.error}`;
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            statusDiv.className = 'sitemap_status error';
            messageDiv.innerHTML = 'Server communication error.';
            detailsDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
        })
        .finally(() => {
            generateBtn.style.pointerEvents = 'auto';
            generateBtn.style.opacity = '1';
            generateBtn.innerHTML = 'Generate New Sitemap';
        });
    }

    function viewSitemap() {
        const statusDiv = document.getElementById('sitemap_status');
        const messageDiv = document.getElementById('sitemap_message');
        const detailsDiv = document.getElementById('sitemap_details');

        statusDiv.style.display = 'block';
        statusDiv.className = 'sitemap_status';
        messageDiv.innerHTML = 'Loading sitemap...';
        detailsDiv.innerHTML = '';

        const timestamp = new Date().getTime();
        fetch('../../sitemap.xml?t=' + timestamp)
        .then(response => response.text())
        .then(data => {
            statusDiv.className = 'sitemap_status success';
            messageDiv.innerHTML = 'Sitemap content:';
            detailsDiv.innerHTML = `
                <div style="max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 12px; white-space: pre-wrap;">${data}</div>
                <div style="margin-top: 10px;">
                    <button onclick="window.open('../../sitemap.xml?t=' + new Date().getTime(), '_blank')" class="btn half fr admin_dashboard">
                        Open In New Tab
                    </button>
                    <div class="cb"></div>
                </div>
            `;
        })
        .catch(error => {
            statusDiv.className = 'sitemap_status error';
            messageDiv.innerHTML = 'Failed to load sitemap.';
            detailsDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
        });
    }

    function validateSitemap() {
        const validateBtn = document.getElementById('validateBtn');
        const statusDiv = document.getElementById('sitemap_status');
        const messageDiv = document.getElementById('sitemap_message');
        const detailsDiv = document.getElementById('sitemap_details');

        validateBtn.style.pointerEvents = 'none';
        validateBtn.style.opacity = '0.5';
        validateBtn.innerHTML = 'Validating...';

        statusDiv.style.display = 'block';
        statusDiv.className = 'sitemap_status';
        messageDiv.innerHTML = 'Validating sitemap...';
        detailsDiv.innerHTML = '';

        fetch('../sitemap_generator.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=validate'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.className = 'sitemap_status success';
                messageDiv.innerHTML = 'Sitemap is valid.';
                detailsDiv.innerHTML = `<strong>Validation Results:</strong><br>${data.validation_results}`;
            } else {
                statusDiv.className = 'sitemap_status error';
                messageDiv.innerHTML = 'Sitemap is invalid.';
                detailsDiv.innerHTML = `<strong>Errors:</strong><br>${data.errors}`;
            }
        })
        .catch(error => {
            statusDiv.className = 'sitemap_status error';
            messageDiv.innerHTML = 'Validation failed.';
            detailsDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
        })
        .finally(() => {
            validateBtn.style.pointerEvents = 'auto';
            validateBtn.style.opacity = '1';
            validateBtn.innerHTML = 'Validate Sitemap';
        });
    }
</script>

<?php
    }
}
?>
