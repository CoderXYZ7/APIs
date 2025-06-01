<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$dbname = 'news_events_db';
$username = 'news_events_db';
$password = 'newsEventsDBPSW';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get request parameters
$action = $_GET['action'] ?? '';
$type = $_GET['type'] ?? '';

// Main routing
switch($action) {
    case 'get':
        if($type === 'news') {
            getNews($pdo);
        } elseif($type === 'events') {
            getEvents($pdo);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid type. Use news or events']);
        }
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action. Available: get']);
}

function getNews($pdo) {
    $id = $_GET['id'] ?? null;
    $name = $_GET['name'] ?? null;
    $limit = $_GET['limit'] ?? null;
    $from_date = $_GET['from_date'] ?? null;
    $to_date = $_GET['to_date'] ?? null;
    $with_events = $_GET['with_events'] ?? 'false';
    
    $sql = "SELECT n.*, 
            DATE_FORMAT(n.created_at, '%Y-%m-%d %H:%i:%s') as created_at_formatted,
            DATE_FORMAT(n.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at_formatted";
    
    if($with_events === 'true') {
        $sql .= ", GROUP_CONCAT(DISTINCT ne.event_id) as connected_event_ids";
    }
    
    $sql .= " FROM news n";
    
    if($with_events === 'true') {
        $sql .= " LEFT JOIN news_events ne ON n.id = ne.news_id";
    }
    
    $conditions = [];
    $params = [];
    
    if($id) {
        $conditions[] = "n.id = :id";
        $params['id'] = $id;
    }
    
    if($name) {
        $conditions[] = "n.title LIKE :name";
        $params['name'] = "%$name%";
    }
    
    if($from_date) {
        $conditions[] = "n.created_at >= :from_date";
        $params['from_date'] = $from_date;
    }
    
    if($to_date) {
        $conditions[] = "n.created_at <= :to_date";
        $params['to_date'] = $to_date;
    }
    
    if(!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    if($with_events === 'true') {
        $sql .= " GROUP BY n.id";
    }
    
    $sql .= " ORDER BY n.created_at DESC";
    
    if($limit && is_numeric($limit)) {
        $sql .= " LIMIT :limit";
        $params['limit'] = (int)$limit;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        
        foreach($params as $key => $value) {
            if($key === 'limit') {
                $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process results
        foreach($results as &$result) {
            // Parse images JSON
            if($result['images']) {
                $result['images'] = json_decode($result['images'], true);
            }
            
            // Convert connected_event_ids to array if present
            if(isset($result['connected_event_ids']) && $result['connected_event_ids']) {
                $result['connected_event_ids'] = array_map('intval', explode(',', $result['connected_event_ids']));
            } elseif(isset($result['connected_event_ids'])) {
                $result['connected_event_ids'] = [];
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $results,
            'count' => count($results)
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
}

function getEvents($pdo) {
    $id = $_GET['id'] ?? null;
    $name = $_GET['name'] ?? null;
    $limit = $_GET['limit'] ?? null;
    $from_date = $_GET['from_date'] ?? null;
    $to_date = $_GET['to_date'] ?? null;
    $future_only = $_GET['future_only'] ?? 'false';
    $past_only = $_GET['past_only'] ?? 'false';
    $with_news = $_GET['with_news'] ?? 'false';
    
    $sql = "SELECT e.*, 
            DATE_FORMAT(e.event_date, '%Y-%m-%d %H:%i:%s') as event_date_formatted,
            DATE_FORMAT(e.created_at, '%Y-%m-%d %H:%i:%s') as created_at_formatted,
            DATE_FORMAT(e.updated_at, '%Y-%m-%d %H:%i:%s') as updated_at_formatted";
    
    if($with_news === 'true') {
        $sql .= ", GROUP_CONCAT(DISTINCT ne.news_id) as connected_news_ids";
    }
    
    $sql .= " FROM events e";
    
    if($with_news === 'true') {
        $sql .= " LEFT JOIN news_events ne ON e.id = ne.event_id";
    }
    
    $conditions = [];
    $params = [];
    
    if($id) {
        $conditions[] = "e.id = :id";
        $params['id'] = $id;
    }
    
    if($name) {
        $conditions[] = "e.title LIKE :name";
        $params['name'] = "%$name%";
    }
    
    if($from_date) {
        $conditions[] = "e.event_date >= :from_date";
        $params['from_date'] = $from_date;
    }
    
    if($to_date) {
        $conditions[] = "e.event_date <= :to_date";
        $params['to_date'] = $to_date;
    }
    
    if($future_only === 'true') {
        $conditions[] = "e.event_date > NOW()";
    }
    
    if($past_only === 'true') {
        $conditions[] = "e.event_date < NOW()";
    }
    
    if(!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    if($with_news === 'true') {
        $sql .= " GROUP BY e.id";
    }
    
    // Order by event date (future events first, then by date)
    if($future_only === 'true') {
        $sql .= " ORDER BY e.event_date ASC";
    } else {
        $sql .= " ORDER BY e.event_date DESC";
    }
    
    if($limit && is_numeric($limit)) {
        $sql .= " LIMIT :limit";
        $params['limit'] = (int)$limit;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        
        foreach($params as $key => $value) {
            if($key === 'limit') {
                $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(":$key", $value);
            }
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process results
        foreach($results as &$result) {
            // Parse images JSON
            if($result['images']) {
                $result['images'] = json_decode($result['images'], true);
            }
            
            // Convert connected_news_ids to array if present
            if(isset($result['connected_news_ids']) && $result['connected_news_ids']) {
                $result['connected_news_ids'] = array_map('intval', explode(',', $result['connected_news_ids']));
            } elseif(isset($result['connected_news_ids'])) {
                $result['connected_news_ids'] = [];
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $results,
            'count' => count($results)
        ]);
        
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }
}
?>