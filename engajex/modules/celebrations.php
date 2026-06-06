<?php
require_once '../config.php';
require_once '../includes/flash_toast.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$companyId = $_SESSION['company_id'] ?? null;
$message = '';
$error = '';

/**
 * Helper to process Media Upload
 * Returns [url, type] or null
 */
function handleUpload($file)
{
    if ($file['error'] !== UPLOAD_ERR_OK)
        return null;

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes))
        return null;

    // Simple upload to a local 'uploads' directory
    $uploadDir = '../assets/uploads/';
    if (!is_dir($uploadDir))
        mkdir($uploadDir, 0755, true);

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('cel_') . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['url' => 'assets/uploads/' . $filename, 'type' => 'image'];
    }
    return null;
}

// Handle Post Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'post') {
    $text = trim($_POST['message']);
    $videoUrl = trim($_POST['video_url']);

    // Check for image upload
    $mediaData = null;
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $uploadResult = handleUpload($_FILES['image']);
        if ($uploadResult) {
            $mediaData = $uploadResult;
        } else {
            $error = "Erro ao enviar imagem. Verifique o formato.";
        }
    } elseif (!empty($videoUrl)) {
        // Simple embed logic: if it's youtube, convert to embed. Else just link.
        // For simplicity, we just store the URL and type 'video'
        $mediaData = ['url' => $videoUrl, 'type' => 'video'];
    }

    if ($text || $mediaData) {
        try {
            $stmt = $pdo->prepare("INSERT INTO celebrations (company_id, user_id, message, media_url, media_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $companyId,
                $userId,
                $text,
                $mediaData['url'] ?? null,
                $mediaData['type'] ?? 'none'
            ]);
            // Redirect to avoid resubmission
            header("Location: celebrations.php");
            exit;
        } catch (PDOException $e) {
            $error = "Erro ao publicar.";
        }
    } else {
        $error = "Escreva uma mensagem ou adicione uma mídia.";
    }
}

// Fetch Feed
$stmt = $pdo->prepare("
    SELECT c.*, u.name as user_name, u.area as user_area 
    FROM celebrations c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.company_id = ? 
    ORDER BY c.created_at DESC 
    LIMIT 20
");
$stmt->execute([$companyId]);
$feed = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Celebrações - TestProf Engaja</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .app-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }


        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-color);
        }

        .feed-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .create-post-card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .post-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .post-header {
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .post-content {
            padding: 1rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #e2e8f0;
            white-space: pre-wrap;
        }

        .post-media {
            width: 100%;
            display: block;
        }

        .post-footer {
            padding: 0.75rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            gap: 1rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="app-layout">
        <?php include '../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="feed-container">
                <h1 style="margin-bottom: 2rem; text-align: center;">Mural de Celebrações 🎉</h1>

                <?php if ($error): flashToast($error, 'error'); endif; ?>

                <!-- Creator -->
                <div class="create-post-card">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="post">

                        <textarea name="message" class="form-control" rows="3" placeholder="O que vamos comemorar hoje?"
                            style="margin-bottom: 1rem;"></textarea>

                        <!-- Collapsible Input for Video -->
                        <div id="video-input" style="display: none; margin-bottom: 1rem;">
                            <input type="url" name="video_url" class="form-control"
                                placeholder="Cole o link do YouTube/Vimeo aqui...">
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; gap: 0.5rem;">
                                <label class="btn btn-outline"
                                    style="padding: 0.4rem 0.8rem; font-size: 0.9rem; cursor: pointer;">
                                    📷 Foto
                                    <input type="file" name="image" accept="image/*" style="display: none;">
                                </label>
                                <button type="button" class="btn btn-outline"
                                    style="padding: 0.4rem 0.8rem; font-size: 0.9rem;"
                                    onclick="document.getElementById('video-input').style.display='block'">
                                    🎥 Vídeo
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary">Postar</button>
                        </div>
                    </form>
                </div>

                <!-- Feed -->
                <?php foreach ($feed as $post): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="post-avatar"><?php echo strtoupper(substr($post['user_name'], 0, 1)); ?></div>
                            <div>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($post['user_name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($post['user_area']); ?> •
                                    <?php echo date('d/m H:i', strtotime($post['created_at'])); ?></div>
                            </div>
                        </div>

                        <?php if ($post['message']): ?>
                            <div class="post-content"><?php echo htmlspecialchars($post['message']); ?></div>
                        <?php endif; ?>

                        <?php if ($post['media_type'] === 'image'): ?>
                            <img src="../<?php echo htmlspecialchars($post['media_url']); ?>" class="post-media" loading="lazy">
                        <?php elseif ($post['media_type'] === 'video'): ?>
                            <?php
                            // Basic Embed Logic (YouTube support)
                            $url = $post['media_url'];
                            if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                                // Extract ID simple regex
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
                                if (isset($matches[1])) {
                                    echo '<iframe width="100%" height="300" src="https://www.youtube.com/embed/' . $matches[1] . '" frameborder="0" allowfullscreen></iframe>';
                                } else {
                                    echo '<div style="padding:1rem;"><a href="' . $url . '" target="_blank" style="color:var(--primary-color);">Ver Vídeo Externo</a></div>';
                                }
                            } else {
                                echo '<div style="padding:1rem;"><a href="' . $url . '" target="_blank" style="color:var(--primary-color);">Ver Link de Vídeo</a></div>';
                            }
                            ?>
                        <?php endif; ?>

                        <div class="post-footer">
                            <span>❤️ 0 Curtidas (Em breve)</span>
                            <span>💬 Comentar (Em breve)</span>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </main>
    </div>
</body>

</html>