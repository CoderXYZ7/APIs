<?php
// Database configuration
$host = 'localhost';
$dbname = 'news_events_db';
$username = 'news_events_db';
$password = 'newsEventsDBPSW';

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_news':
                    $title = trim($_POST['title']);
                    $content = trim($_POST['content']);
                    $images = handleImageUpload();
                    
                    $stmt = $pdo->prepare("INSERT INTO news (title, content, images) VALUES (?, ?, ?)");
                    $stmt->execute([$title, $content, json_encode($images)]);
                    $message = "News article added successfully!";
                    break;
                    
                case 'add_event':
                    $title = trim($_POST['title']);
                    $content = trim($_POST['content']);
                    $event_date = $_POST['event_date'];
                    $images = handleImageUpload();
                    
                    $stmt = $pdo->prepare("INSERT INTO events (title, content, event_date, images) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $content, $event_date, json_encode($images)]);
                    $message = "Event added successfully!";
                    break;
                    
                case 'edit_news':
                    $id = $_POST['id'];
                    $title = trim($_POST['title']);
                    $content = trim($_POST['content']);
                    $images = handleImageUpload();
                    
                    if (!empty($images)) {
                        $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ?, images = ? WHERE id = ?");
                        $stmt->execute([$title, $content, json_encode($images), $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE news SET title = ?, content = ? WHERE id = ?");
                        $stmt->execute([$title, $content, $id]);
                    }
                    $message = "News article updated successfully!";
                    break;
                    
                case 'edit_event':
                    $id = $_POST['id'];
                    $title = trim($_POST['title']);
                    $content = trim($_POST['content']);
                    $event_date = $_POST['event_date'];
                    $images = handleImageUpload();
                    
                    if (!empty($images)) {
                        $stmt = $pdo->prepare("UPDATE events SET title = ?, content = ?, event_date = ?, images = ? WHERE id = ?");
                        $stmt->execute([$title, $content, $event_date, json_encode($images), $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE events SET title = ?, content = ?, event_date = ? WHERE id = ?");
                        $stmt->execute([$title, $content, $event_date, $id]);
                    }
                    $message = "Event updated successfully!";
                    break;
                    
                case 'delete_news':
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = "News article deleted successfully!";
                    break;
                    
                case 'delete_event':
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = "Event deleted successfully!";
                    break;
                    
                case 'link_news_event':
                    $news_id = $_POST['news_id'];
                    $event_id = $_POST['event_id'];
                    
                    $stmt = $pdo->prepare("INSERT IGNORE INTO news_events (news_id, event_id) VALUES (?, ?)");
                    $stmt->execute([$news_id, $event_id]);
                    $message = "News and event linked successfully!";
                    break;
                    
                case 'unlink_news_event':
                    $news_id = $_POST['news_id'];
                    $event_id = $_POST['event_id'];
                    
                    $stmt = $pdo->prepare("DELETE FROM news_events WHERE news_id = ? AND event_id = ?");
                    $stmt->execute([$news_id, $event_id]);
                    $message = "News and event unlinked successfully!";
                    break;
            }
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

function handleImageUpload() {
    $uploadedImages = [];
    
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = './images/';
        
        // Create images directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($_FILES['images']['name'] as $key => $filename) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $targetPath = $uploadDir . $filename;
                
                // Check if file already exists
                if (file_exists($targetPath)) {
                    throw new Exception("File '$filename' already exists!");
                }
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $targetPath)) {
                    $uploadedImages[] = $filename;
                } else {
                    throw new Exception("Failed to upload file '$filename'");
                }
            }
        }
    }
    
    return $uploadedImages;
}

// Get data for display
$news = $pdo->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$events = $pdo->query("SELECT * FROM events ORDER BY event_date ASC")->fetchAll(PDO::FETCH_ASSOC);

// Get linked news and events
$links = $pdo->query("
    SELECT ne.*, n.title as news_title, e.title as event_title 
    FROM news_events ne 
    JOIN news n ON ne.news_id = n.id 
    JOIN events e ON ne.event_id = e.id
    ORDER BY ne.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get single item for editing
$edit_news = null;
$edit_event = null;

if (isset($_GET['edit_news'])) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$_GET['edit_news']]);
    $edit_news = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_GET['edit_event'])) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$_GET['edit_event']]);
    $edit_event = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News and Events Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .section {
            background: white;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        .images-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .images-list img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ddd;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        
        .tab {
            padding: 12px 24px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: white;
            border-bottom-color: #667eea;
            color: #667eea;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .table {
                font-size: 12px;
            }
            
            .btn {
                padding: 8px 16px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>News and Events Management</h1>
            <p>Manage your news articles and events</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="section">
            <div class="tabs">
                <button class="tab active" onclick="showTab('add-news')">Add News</button>
                <button class="tab" onclick="showTab('add-event')">Add Event</button>
                <button class="tab" onclick="showTab('link-items')">Link Items</button>
                <button class="tab" onclick="showTab('view-all')">View All</button>
            </div>

            <!-- Add News Tab -->
            <div id="add-news" class="tab-content active">
                <h2><?= $edit_news ? 'Edit News Article' : 'Add News Article' ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?= $edit_news ? 'edit_news' : 'add_news' ?>">
                    <?php if ($edit_news): ?>
                        <input type="hidden" name="id" value="<?= $edit_news['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="news-title">Title:</label>
                        <input type="text" id="news-title" name="title" value="<?= $edit_news ? htmlspecialchars($edit_news['title']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="news-content">Content:</label>
                        <textarea id="news-content" name="content" required><?= $edit_news ? htmlspecialchars($edit_news['content']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="news-images">Images:</label>
                        <input type="file" id="news-images" name="images[]" multiple accept="image/*">
                        <small>Select multiple images to upload. Files will be saved to ./images/ folder.</small>
                        <?php if ($edit_news && $edit_news['images']): ?>
                            <div class="images-list">
                                <?php foreach (json_decode($edit_news['images']) as $image): ?>
                                    <img src="./images/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($image) ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?= $edit_news ? 'Update' : 'Add' ?> News Article</button>
                    <?php if ($edit_news): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Add Event Tab -->
            <div id="add-event" class="tab-content">
                <h2><?= $edit_event ? 'Edit Event' : 'Add Event' ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?= $edit_event ? 'edit_event' : 'add_event' ?>">
                    <?php if ($edit_event): ?>
                        <input type="hidden" name="id" value="<?= $edit_event['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="event-title">Title:</label>
                        <input type="text" id="event-title" name="title" value="<?= $edit_event ? htmlspecialchars($edit_event['title']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event-content">Content:</label>
                        <textarea id="event-content" name="content" required><?= $edit_event ? htmlspecialchars($edit_event['content']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="event-date">Event Date:</label>
                        <input type="datetime-local" id="event-date" name="event_date" value="<?= $edit_event ? date('Y-m-d\TH:i', strtotime($edit_event['event_date'])) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event-images">Images:</label>
                        <input type="file" id="event-images" name="images[]" multiple accept="image/*">
                        <small>Select multiple images to upload. Files will be saved to ./images/ folder.</small>
                        <?php if ($edit_event && $edit_event['images']): ?>
                            <div class="images-list">
                                <?php foreach (json_decode($edit_event['images']) as $image): ?>
                                    <img src="./images/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($image) ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?= $edit_event ? 'Update' : 'Add' ?> Event</button>
                    <?php if ($edit_event): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Link Items Tab -->
            <div id="link-items" class="tab-content">
                <h2>Link News and Events</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="link_news_event">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="news-select">Select News Article:</label>
                            <select id="news-select" name="news_id" required>
                                <option value="">Choose a news article...</option>
                                <?php foreach ($news as $item): ?>
                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="event-select">Select Event:</label>
                            <select id="event-select" name="event_id" required>
                                <option value="">Choose an event...</option>
                                <?php foreach ($events as $item): ?>
                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?> (<?= date('Y-m-d', strtotime($item['event_date'])) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Link Items</button>
                </form>

                <h3 style="margin-top: 30px;">Current Links</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>News Article</th>
                            <th>Event</th>
                            <th>Linked Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($links as $link): ?>
                            <tr>
                                <td><?= htmlspecialchars($link['news_title']) ?></td>
                                <td><?= htmlspecialchars($link['event_title']) ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($link['created_at'])) ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="unlink_news_event">
                                        <input type="hidden" name="news_id" value="<?= $link['news_id'] ?>">
                                        <input type="hidden" name="event_id" value="<?= $link['event_id'] ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to unlink these items?')">Unlink</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- View All Tab -->
            <div id="view-all" class="tab-content">
                <h2>All News Articles</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content Preview</th>
                            <th>Images</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= htmlspecialchars(substr($item['content'], 0, 100)) ?>...</td>
                                <td>
                                    <?php if ($item['images']): ?>
                                        <div class="images-list">
                                            <?php foreach (json_decode($item['images']) as $image): ?>
                                                <img src="./images/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($image) ?>">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        No images
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <a href="?edit_news=<?= $item['id'] ?>" class="btn btn-secondary">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_news">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this news article?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h2 style="margin-top: 40px;">All Events</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content Preview</th>
                            <th>Event Date</th>
                            <th>Images</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                <td><?= htmlspecialchars($item['title']) ?></td>
                                <td><?= htmlspecialchars(substr($item['content'], 0, 100)) ?>...</td>
                                <td><?= date('Y-m-d H:i', strtotime($item['event_date'])) ?></td>
                                <td>
                                    <?php if ($item['images']): ?>
                                        <div class="images-list">
                                            <?php foreach (json_decode($item['images']) as $image): ?>
                                                <img src="./images/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($image) ?>">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        No images
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y-m-d H:i', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <a href="?edit_event=<?= $item['id'] ?>" class="btn btn-secondary">Edit</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
    </script>
</body>
</html>