<?php
// api/index.php

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

// Récupérer la méthode et le chemin de la requête
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Enlever le chemin jusqu'à /api
$apiPath = '/api';
if (strpos($uri, $apiPath) === 0) {
    $uri = substr($uri, strlen($apiPath));
}
$uri = trim($uri, '/');
$segments = explode('/', $uri);

// Exemple de route simple : GET /api/ping
if ($method === 'GET' && $segments[0] === 'ping') {
    echo json_encode(['status' => 'ok', 'message' => 'pong']);
    exit;
}

// Exemple de route : GET /api/menus/{branch}/{type}
if ($method === 'GET' && $segments[0] === 'menus' && isset($segments[1], $segments[2])) {
    $branchName = $segments[1];
    $type = $segments[2];
    try {
        $pdo = getPDOConnection();

        // Récupérer l'ID de la succursale à partir de son nom
        $stmt = $pdo->prepare('SELECT id FROM branches WHERE name = ?');
        $stmt->execute([$branchName]);
        $branchId = $stmt->fetchColumn();

        if (!$branchId) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Succursale non trouvée']);
            exit;
        }

        $stmt = $pdo->prepare('SELECT mi.image, mi.name, mi.description, mi.price, mi.allergens, mi.is_available FROM menus m JOIN menu_items mi ON mi.menu_id = m.id WHERE m.branch_id = ? AND m.type = ? AND m.is_active = 1 AND mi.is_available = 1');
        $stmt->execute([$branchId, $type]);
        $items = $stmt->fetchAll();
        echo json_encode(['status' => 'ok', 'data' => $items]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- MENUS ---
// POST /api/menus (ajouter un nouvel item)
if ($method === 'POST' && $segments[0] === 'menus') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['menu_id'], $input['name'], $input['description'], $input['price'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Champs requis manquants']);
        exit;
    }
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('INSERT INTO menu_items (menu_id, name, description, price, allergens, is_available, image) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $input['menu_id'],
            $input['name'],
            $input['description'],
            $input['price'],
            $input['allergens'] ?? null,
            $input['is_available'] ?? 1,
            $input['image'] ?? null
        ]);
        echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// PUT /api/menus/:id (modifier un item)
if ($method === 'PUT' && $segments[0] === 'menus' && isset($segments[1])) {
    $id = $segments[1];
    $input = json_decode(file_get_contents('php://input'), true);
    try {
        $pdo = getPDOConnection();
        $fields = [];
        $params = [];
        foreach (['name','description','price','allergens','is_available','image'] as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Aucune donnée à mettre à jour']);
            exit;
        }
        $params[] = $id;
        $sql = 'UPDATE menu_items SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// DELETE /api/menus/:id (supprimer un item)
if ($method === 'DELETE' && $segments[0] === 'menus' && isset($segments[1])) {
    $id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- RESERVATIONS ---
// POST /api/reservations (créer une nouvelle réservation)
if ($method === 'POST' && $segments[0] === 'reservations') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['branch_id'], $input['reservation_date'], $input['reservation_time'], $input['guests'], $input['name'], $input['phone'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Champs requis manquants']);
        exit;
    }
    try {
        $pdo = getPDOConnection();
        // Vérifier s'il existe déjà une réservation pour ce téléphone ou e-mail à la même date et succursale
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM reservations WHERE branch_id = ? AND reservation_date = ? AND (phone = ? OR (email IS NOT NULL AND email = ?))');
        $stmt->execute([
            $input['branch_id'],
            $input['reservation_date'],
            $input['phone'],
            $input['email'] ?? ''
        ]);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Vous avez déjà une réservation pour cette date à cette succursale.']);
            exit;
        }
        $stmt = $pdo->prepare('INSERT INTO reservations (branch_id, reservation_date, reservation_time, guests, name, phone, email, special_requests, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $input['branch_id'],
            $input['reservation_date'],
            $input['reservation_time'],
            $input['guests'],
            $input['name'],
            $input['phone'],
            $input['email'] ?? null,
            $input['special_requests'] ?? null,
            $input['status'] ?? 'pending'
        ]);
        $reservationId = $pdo->lastInsertId();
        // Envoi de l'e-mail de confirmation
        $to = $input['email'] ?? '';
        $branchName = $input['branch_id'] == 1 ? 'Mutanga' : 'Mutakura';
        $subject = 'Confirmation de votre réservation - NYABUNGO ' . $branchName;
        $message = "Bonjour " . htmlspecialchars($input['name']) . ",\n\n" .
            "Votre réservation au NYABUNGO $branchName a bien été enregistrée.\n" .
            "Date : " . $input['reservation_date'] . "\n" .
            "Heure : " . $input['reservation_time'] . "\n" .
            "Nombre de personnes : " . $input['guests'] . "\n" .
            (isset($input['occasion']) && $input['occasion'] ? ("Occasion : " . $input['occasion'] . "\n") : "") .
            (isset($input['special_requests']) && $input['special_requests'] ? ("Demandes spéciales : " . $input['special_requests'] . "\n") : "") .
            "\nNous avons hâte de vous accueillir !\n\nL'équipe NYABUNGO";
        $headers = "From: reservation@nyabungo.com\r\nContent-Type: text/plain; charset=utf-8";
        if ($to) {
            if (!@mail($to, $subject, $message, $headers)) {
                error_log('Erreur envoi mail client pour réservation #' . $reservationId);
            }
        }
        // Email au restaurant (exemple générique)
        $toResto = 'contact@nyabungo.com';
        $subjectResto = 'Nouvelle réservation - ' . $branchName;
        $messageResto = "Nouvelle réservation :\n" .
            "Nom : " . $input['name'] . "\n" .
            "Téléphone : " . $input['phone'] . "\n" .
            "Email : " . ($input['email'] ?? '') . "\n" .
            "Date : " . $input['reservation_date'] . "\n" .
            "Heure : " . $input['reservation_time'] . "\n" .
            "Personnes : " . $input['guests'] . "\n" .
            (isset($input['occasion']) && $input['occasion'] ? ("Occasion : " . $input['occasion'] . "\n") : "") .
            (isset($input['special_requests']) && $input['special_requests'] ? ("Demandes spéciales : " . $input['special_requests'] . "\n") : "");
        if (!@mail($toResto, $subjectResto, $messageResto, $headers)) {
            error_log('Erreur envoi mail resto pour réservation #' . $reservationId);
        }
        echo json_encode(['status' => 'ok', 'id' => $reservationId]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// GET /api/reservations/:branch_id (lister les réservations d'une succursale)
if ($method === 'GET' && $segments[0] === 'reservations' && isset($segments[1])) {
    $branch_id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('SELECT * FROM reservations WHERE branch_id = ? ORDER BY reservation_date DESC, reservation_time DESC');
        $stmt->execute([$branch_id]);
        $reservations = $stmt->fetchAll();
        echo json_encode(['status' => 'ok', 'data' => $reservations]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// GET /api/reservations/slots/:branch_id/:date (créneaux déjà réservés)
if ($method === 'GET' && $segments[0] === 'reservations' && $segments[1] === 'slots' && isset($segments[2], $segments[3])) {
    $branch_id = $segments[2];
    $date = $segments[3];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('SELECT reservation_time FROM reservations WHERE branch_id = ? AND reservation_date = ?');
        $stmt->execute([$branch_id, $date]);
        $slots = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['status' => 'ok', 'slots' => $slots]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- EVENTS ---
// POST /api/events (soumettre une demande d'événement)
if ($method === 'POST' && $segments[0] === 'events') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['branch_id'], $input['event_type'], $input['requested_date'], $input['guests'], $input['name'], $input['phone'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Champs requis manquants']);
        exit;
    }
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('INSERT INTO events (branch_id, event_type, requested_date, guests, name, phone, email, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $input['branch_id'],
            $input['event_type'],
            $input['requested_date'],
            $input['guests'],
            $input['name'],
            $input['phone'],
            $input['email'] ?? null,
            $input['message'] ?? null,
            $input['status'] ?? 'pending'
        ]);
        echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// GET /api/events/:branch_id (lister les demandes d'événements d'une succursale)
if ($method === 'GET' && $segments[0] === 'events' && isset($segments[1])) {
    $branch_id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('SELECT * FROM events WHERE branch_id = ? ORDER BY requested_date DESC');
        $stmt->execute([$branch_id]);
        $events = $stmt->fetchAll();
        echo json_encode(['status' => 'ok', 'data' => $events]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- GALLERIES ---
// GET /api/galleries/:branch_id (liste des images d'une succursale)
if ($method === 'GET' && $segments[0] === 'galleries' && isset($segments[1])) {
    $branch_id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('SELECT * FROM galleries WHERE branch_id = ? AND is_active = 1 ORDER BY display_order ASC, created_at DESC');
        $stmt->execute([$branch_id]);
        $galleries = $stmt->fetchAll();
        echo json_encode(['status' => 'ok', 'data' => $galleries]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// POST /api/galleries
if ($method === 'POST' && $segments[0] === 'galleries') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['image'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Image requise']);
        exit;
    }
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('INSERT INTO galleries (branch_id, image, caption, is_active, display_order) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $input['branch_id'] ?? null,
            $input['image'],
            $input['caption'] ?? null,
            $input['is_active'] ?? 1,
            $input['display_order'] ?? 0
        ]);
        echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// PUT /api/galleries/:id
if ($method === 'PUT' && $segments[0] === 'galleries' && isset($segments[1])) {
    $id = $segments[1];
    $input = json_decode(file_get_contents('php://input'), true);
    try {
        $pdo = getPDOConnection();
        $fields = [];
        $params = [];
        foreach (['image','caption','is_active','display_order','branch_id'] as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Aucune donnée à mettre à jour']);
            exit;
        }
        $params[] = $id;
        $sql = 'UPDATE galleries SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// DELETE /api/galleries/:id
if ($method === 'DELETE' && $segments[0] === 'galleries' && isset($segments[1])) {
    $id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('DELETE FROM galleries WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- TESTIMONIALS ---
// GET /api/testimonials/:branch_id
if ($method === 'GET' && $segments[0] === 'testimonials' && isset($segments[1])) {
    $branch_id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('SELECT * FROM testimonials WHERE branch_id = ? AND is_active = 1 ORDER BY created_at DESC');
        $stmt->execute([$branch_id]);
        $testimonials = $stmt->fetchAll();
        echo json_encode(['status' => 'ok', 'data' => $testimonials]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// POST /api/testimonials
if ($method === 'POST' && $segments[0] === 'testimonials') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Contenu requis']);
        exit;
    }
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('INSERT INTO testimonials (branch_id, author, content, is_active) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $input['branch_id'] ?? null,
            $input['author'] ?? null,
            $input['content'],
            $input['is_active'] ?? 1
        ]);
        echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// PUT /api/testimonials/:id
if ($method === 'PUT' && $segments[0] === 'testimonials' && isset($segments[1])) {
    $id = $segments[1];
    $input = json_decode(file_get_contents('php://input'), true);
    try {
        $pdo = getPDOConnection();
        $fields = [];
        $params = [];
        foreach (['author','content','is_active','branch_id'] as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Aucune donnée à mettre à jour']);
            exit;
        }
        $params[] = $id;
        $sql = 'UPDATE testimonials SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// DELETE /api/testimonials/:id
if ($method === 'DELETE' && $segments[0] === 'testimonials' && isset($segments[1])) {
    $id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- USERS ---
// GET /api/users
if ($method === 'GET' && $segments[0] === 'users') {
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->query('SELECT id, username, email, is_active, role, created_at FROM users');
        $users = $stmt->fetchAll();
        echo json_encode(['status' => 'ok', 'data' => $users]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// POST /api/users
if ($method === 'POST' && $segments[0] === 'users') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['username'], $input['password'], $input['email'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Champs requis manquants']);
        exit;
    }
    try {
        $pdo = getPDOConnection();
        $hash = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, email, is_active, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $input['username'],
            $hash,
            $input['email'],
            $input['is_active'] ?? 1,
            $input['role'] ?? 'editor'
        ]);
        echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// PUT /api/users/:id
if ($method === 'PUT' && $segments[0] === 'users' && isset($segments[1])) {
    $id = $segments[1];
    $input = json_decode(file_get_contents('php://input'), true);
    try {
        $pdo = getPDOConnection();
        $fields = [];
        $params = [];
        foreach (['username','email','is_active','role'] as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }
        if (isset($input['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
        }
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Aucune donnée à mettre à jour']);
            exit;
        }
        $params[] = $id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// DELETE /api/users/:id
if ($method === 'DELETE' && $segments[0] === 'users' && isset($segments[1])) {
    $id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// --- SITE CONTENT ---
// GET /api/site_content/:page
if ($method === 'GET' && $segments[0] === 'site_content' && isset($segments[1])) {
    $page = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('SELECT * FROM site_content WHERE page = ?');
        $stmt->execute([$page]);
        $content = $stmt->fetch();
        echo json_encode(['status' => 'ok', 'data' => $content]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// POST /api/site_content
if ($method === 'POST' && $segments[0] === 'site_content') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['page'], $input['content'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Champs requis manquants']);
        exit;
    }
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('INSERT INTO site_content (page, content) VALUES (?, ?)');
        $stmt->execute([
            $input['page'],
            $input['content']
        ]);
        echo json_encode(['status' => 'ok', 'id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// PUT /api/site_content/:id
if ($method === 'PUT' && $segments[0] === 'site_content' && isset($segments[1])) {
    $id = $segments[1];
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Contenu requis']);
        exit;
    }
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('UPDATE site_content SET content = ? WHERE id = ?');
        $stmt->execute([
            $input['content'],
            $id
        ]);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
// DELETE /api/site_content/:id
if ($method === 'DELETE' && $segments[0] === 'site_content' && isset($segments[1])) {
    $id = $segments[1];
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare('DELETE FROM site_content WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// POST /api/contact (formulaire de contact)
if ($method === 'POST' && $segments[0] === 'contact') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['name'], $input['email'], $input['message'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Champs requis manquants']);
        exit;
    }
    $to = 'contact@nyabungo.com';
    $subject = 'Nouveau message via le formulaire de contact';
    $message = "Nom : " . $input['name'] . "\n" .
               "Email : " . $input['email'] . "\n" .
               "Message :\n" . $input['message'];
    $headers = "From: " . $input['email'] . "\r\nContent-Type: text/plain; charset=utf-8";
    if (@mail($to, $subject, $message, $headers)) {
        echo json_encode(['status' => 'ok']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi de l\'e-mail.']);
    }
    exit;
}

// Route non trouvée
http_response_code(404);
echo json_encode(['status' => 'error', 'message' => 'Endpoint non trouvé']); 