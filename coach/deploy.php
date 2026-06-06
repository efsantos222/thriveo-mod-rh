<?php
/**
 * Deployment Script
 * This script helps deploy the application to the production server
 */

// Configuration
$config = [
    'ftp_host' => 'ftp.proftest.com.br',
    'ftp_user' => 'your_ftp_username',  // Replace with actual FTP username
    'ftp_pass' => 'your_ftp_password',  // Replace with actual FTP password
    'ftp_path' => '/public_html/coach/',
    'local_path' => __DIR__,
    'exclude' => [
        '.git',
        '.gitignore',
        'deploy.php',
        'README.md',
        'logs',
        '.htaccess'  // We'll upload this separately to avoid permission issues
    ]
];

// Create logs directory if it doesn't exist
if (!file_exists($config['local_path'] . '/logs')) {
    mkdir($config['local_path'] . '/logs', 0755, true);
}

// Start logging
$log = fopen($config['local_path'] . '/logs/deployment.log', 'a');
$log_message = "\n\n=== Deployment Started: " . date('Y-m-d H:i:s') . " ===\n";
fwrite($log, $log_message);

try {
    // Connect to FTP
    $conn = ftp_connect($config['ftp_host']);
    if (!$conn) {
        throw new Exception("Could not connect to FTP server");
    }

    // Login
    if (!ftp_login($conn, $config['ftp_user'], $config['ftp_pass'])) {
        throw new Exception("FTP login failed");
    }

    // Enable passive mode
    ftp_pasv($conn, true);

    // Function to upload directory
    function uploadDirectory($conn, $local_dir, $remote_dir, $exclude, $log) {
        if (!is_dir($local_dir)) {
            return;
        }

        // Create remote directory if it doesn't exist
        @ftp_mkdir($conn, $remote_dir);

        $files = scandir($local_dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..' || in_array($file, $exclude)) {
                continue;
            }

            $local_path = $local_dir . '/' . $file;
            $remote_path = $remote_dir . '/' . $file;

            if (is_dir($local_path)) {
                uploadDirectory($conn, $local_path, $remote_path, $exclude, $log);
            } else {
                if (ftp_put($conn, $remote_path, $local_path, FTP_BINARY)) {
                    $message = "Uploaded: $remote_path\n";
                    fwrite($log, $message);
                } else {
                    $message = "Failed to upload: $remote_path\n";
                    fwrite($log, $message);
                }
            }
        }
    }

    // Upload files
    uploadDirectory($conn, $config['local_path'], $config['ftp_path'], $config['exclude'], $log);

    // Upload .htaccess separately
    if (ftp_put($conn, $config['ftp_path'] . '.htaccess', $config['local_path'] . '/.htaccess', FTP_ASCII)) {
        fwrite($log, "Uploaded: .htaccess\n");
    } else {
        fwrite($log, "Failed to upload: .htaccess\n");
    }

    // Set permissions
    ftp_site($conn, "CHMOD 644 " . $config['ftp_path'] . ".htaccess");
    
    // Close connection
    ftp_close($conn);
    
    $message = "=== Deployment Completed Successfully ===\n";
    fwrite($log, $message);
    echo "Deployment completed successfully. Check logs/deployment.log for details.\n";

} catch (Exception $e) {
    $message = "Error: " . $e->getMessage() . "\n";
    fwrite($log, $message);
    echo "Deployment failed. Check logs/deployment.log for details.\n";
    if (isset($conn) && $conn) {
        ftp_close($conn);
    }
}

fclose($log);
