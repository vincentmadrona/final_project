<?php
require 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json");

// Utility to respond in JSON or XML
function respond($data, $accept) {
    if (strpos($accept, 'xml') !== false) {
        header("Content-Type: application/xml");
        $xml = new SimpleXMLElement('<response/>');
        array_walk_recursive($data, function($value, $key) use ($xml) {
            $xml->addChild($key, htmlspecialchars($value)); // Escape special characters
        });
        echo $xml->asXML(); // Output the XML response
    } else {
        header("Content-Type: application/json");
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}


$method = $_SERVER['REQUEST_METHOD'];
$accept = $_SERVER['HTTP_ACCEPT'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM org_event WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($event) {
                    respond($event, $accept);
                } else {
                    respond(["error" => "Event not found"], $accept);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM org_event");
                $org_event = $stmt->fetchAll(PDO::FETCH_ASSOC);
                respond($org_event, $accept);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents("php://input"), true);
            if (isset($input['name'], $input['start_date'], $input['end_date'], $input['venue_id'], $input['organization_id'])) {
                $stmt = $pdo->prepare("INSERT INTO org_event (name, start_date, end_date, venue_id, organization_id)
                 VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$input['name'], $input['start_date'], $input['end_date'], $input['venue_id'], $input['organization_id']]);
                respond(["message" => "Event created successfully"], $accept);
            } else {
                respond(["error" => "Invalid input"], $accept);
            }
            break;

        case 'PUT':
            parse_str(file_get_contents("php://input"), $input); // Parse PUT data
            if (isset($input['id'], $input['name'], $input['start_date'], $input['end_date'], $input['venue_id'], $input['organization_id']) && is_numeric($input['id'])) {
                $stmt = $pdo->prepare("
                    UPDATE org_event 
                    SET name = ?, start_date = ?, end_date = ?, venue_id = ?, organization_id = ? 
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['name'], 
                    $input['start_date'], 
                    $input['end_date'], 
                    $input['venue_id'], 
                    $input['organization_id'], 
                    $input['id']
                ]);
        
                if ($stmt->rowCount() > 0) {
                    respond(["message" => "Event updated successfully"], $accept);
                } else {
                    respond(["error" => "Event not found or no changes made"], $accept);
                }
            } else {
                respond(["error" => "Invalid input"], $accept);
            }
            break;
        

        case 'DELETE':
            parse_str(file_get_contents("php://input"), $input);
            if (isset($input['id']) && is_numeric($input['id'])) {
                $stmt = $pdo->prepare("DELETE FROM org_event WHERE id = ?");
                $stmt->execute([$input['id']]);
                if ($stmt->rowCount() > 0) {
                    respond(["message" => "Event deleted successfully"], $accept);
                } else {
                    respond(["error" => "Event not found"], $accept);
                }
            } else {
                respond(["error" => "Invalid input"], $accept);
            }
            break;

        default:
            respond(["error" => "Invalid request method"], $accept);
            break;
    }
} catch (PDOException $e) {
    respond(["error" => "Database error: " . $e->getMessage()], $accept);
} catch (Exception $e) {
    respond(["error" => "Server error: " . $e->getMessage()], $accept);
}
?>
