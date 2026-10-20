<?php
/**
 * MaintenanceRequest.php
 * OOP + PDO model for the maintenance_requests table (the system's main entity)
 * plus its close relations: request_assignments and activity_logs.
 */

class MaintenanceRequest
{
    private PDO $conn;
    private string $table = 'maintenance_requests';

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getAll(array $filters = []): array
    {
        $sql = "SELECT mr.*,
                       u.full_name  AS requester_name,
                       r.room_number,
                       r.floor_level,
                       b.building_id,
                       b.building_name,
                       c.category_name,
                       (SELECT tech.full_name
                          FROM request_assignments ra
                          JOIN users tech ON ra.technician_id = tech.user_id
                         WHERE ra.request_id = mr.request_id
                         ORDER BY ra.assignment_id DESC LIMIT 1) AS technician_name
                FROM {$this->table} mr
                LEFT JOIN users u       ON mr.requester_id = u.user_id
                LEFT JOIN rooms r       ON mr.room_id = r.room_id
                LEFT JOIN buildings b   ON r.building_id = b.building_id
                LEFT JOIN categories c  ON mr.category_id = c.category_id
                WHERE 1=1";

        $params = [];

        if (isset($filters['building_id']) && $filters['building_id'] !== '' && $filters['building_id'] !== null) {
            $sql .= " AND b.building_id = :building_id";
            $params[':building_id'] = (int) $filters['building_id'];
        }
        if (isset($filters['category_id']) && $filters['category_id'] !== '' && $filters['category_id'] !== null) {
            $sql .= " AND c.category_id = :category_id";
            $params[':category_id'] = (int) $filters['category_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND mr.status = :status";
            $params[':status'] = (string) $filters['status'];
        }
        if (!empty($filters['priority_level'])) {
            $sql .= " AND mr.priority_level = :priority_level";
            $params[':priority_level'] = (string) $filters['priority_level'];
        }
        if (!empty($filters['search'])) {
            $search = '%' . trim((string) $filters['search']) . '%';
            $sql .= " AND (mr.description LIKE :search_desc OR u.full_name LIKE :search_name OR r.room_number LIKE :search_room)";
            $params[':search_desc'] = $search;
            $params[':search_name'] = $search;
            $params[':search_room'] = $search;
        }

        $sql .= " ORDER BY mr.date_submitted DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT mr.*,
                       u.full_name AS requester_name,
                       r.room_number, r.floor_level,
                       b.building_id, b.building_name,
                       c.category_id, c.category_name
                FROM {$this->table} mr
                LEFT JOIN users u      ON mr.requester_id = u.user_id
                LEFT JOIN rooms r      ON mr.room_id = r.room_id
                LEFT JOIN buildings b  ON r.building_id = b.building_id
                LEFT JOIN categories c ON mr.category_id = c.category_id
                WHERE mr.request_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table}
                    (requester_id, room_id, category_id, description, priority_level, status, date_submitted)
                VALUES
                    (:requester_id, :room_id, :category_id, :description, :priority_level, 'Pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':requester_id'   => $data['requester_id'],
            ':room_id'        => $data['room_id'],
            ':category_id'    => $data['category_id'],
            ':description'    => $data['description'],
            ':priority_level' => $data['priority_level'],
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table}
                   SET room_id        = :room_id,
                       category_id    = :category_id,
                       description    = :description,
                       priority_level = :priority_level,
                       status         = :status,
                       date_resolved  = CASE
                                            WHEN :status_check IN ('Completed','Cancelled')
                                            THEN COALESCE(date_resolved, NOW())
                                            ELSE NULL
                                         END
                 WHERE request_id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':room_id'        => $data['room_id'],
            ':category_id'    => $data['category_id'],
            ':description'    => $data['description'],
            ':priority_level' => $data['priority_level'],
            ':status'         => $data['status'],
            ':status_check'   => $data['status'],
            ':id'             => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $this->conn->beginTransaction();
        try {
            $this->conn->prepare("DELETE FROM feedback WHERE request_id = :id")->execute([':id' => $id]);
            $this->conn->prepare("DELETE FROM activity_logs WHERE request_id = :id")->execute([':id' => $id]);
            $this->conn->prepare("DELETE FROM request_assignments WHERE request_id = :id")->execute([':id' => $id]);
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE request_id = :id");
            $stmt->execute([':id' => $id]);
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function countByStatus(): array
    {
        $counts = ['Pending' => 0, 'In Progress' => 0, 'Completed' => 0, 'Cancelled' => 0];
        $stmt = $this->conn->query("SELECT status, COUNT(*) AS total FROM {$this->table} GROUP BY status");
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public function assignTechnician(int $requestId, int $technicianId): bool
    {
        $check = $this->conn->prepare(
            "SELECT assignment_id FROM request_assignments WHERE request_id = :rid ORDER BY assignment_id DESC LIMIT 1"
        );
        $check->execute([':rid' => $requestId]);
        $existing = $check->fetch();

        if ($existing) {
            $stmt = $this->conn->prepare(
                "UPDATE request_assignments SET technician_id = :tid, assigned_date = NOW() WHERE assignment_id = :aid"
            );
            return $stmt->execute([':tid' => $technicianId, ':aid' => $existing['assignment_id']]);
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO request_assignments (request_id, technician_id, assigned_date) VALUES (:rid, :tid, NOW())"
        );
        return $stmt->execute([':rid' => $requestId, ':tid' => $technicianId]);
    }

    public function getAssignedTechnicianId(int $requestId): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT technician_id FROM request_assignments WHERE request_id = :rid ORDER BY assignment_id DESC LIMIT 1"
        );
        $stmt->execute([':rid' => $requestId]);
        $row = $stmt->fetch();
        return $row ? (int) $row['technician_id'] : null;
    }

    public function logActivity(int $requestId, ?int $userId, string $action): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO activity_logs (request_id, user_id, action_taken, timestamp) VALUES (:rid, :uid, :action, NOW())"
        );
        return $stmt->execute([':rid' => $requestId, ':uid' => $userId, ':action' => $action]);
    }

    public function getActivityLogs(int $requestId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT al.*, u.full_name
               FROM activity_logs al
               LEFT JOIN users u ON al.user_id = u.user_id
              WHERE al.request_id = :rid
              ORDER BY al.log_id DESC"
        );
        $stmt->execute([':rid' => $requestId]);
        return $stmt->fetchAll();
    }
}
