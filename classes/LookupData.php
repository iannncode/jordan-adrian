<?php
/**
 * LookupData.php
 * OOP + PDO helper for reference/dropdown data: buildings, rooms, categories, users, roles.
 */

class LookupData
{
    private PDO $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getBuildings(): array
    {
        return $this->conn->query("SELECT * FROM buildings ORDER BY building_name")->fetchAll();
    }

    public function getRooms(?int $buildingId = null): array
    {
        if ($buildingId) {
            $stmt = $this->conn->prepare(
                "SELECT * FROM rooms WHERE building_id = :bid ORDER BY room_number"
            );
            $stmt->execute([':bid' => $buildingId]);
            return $stmt->fetchAll();
        }

        return $this->conn->query(
            "SELECT r.*, b.building_name
               FROM rooms r
               JOIN buildings b ON r.building_id = b.building_id
              ORDER BY b.building_name, r.room_number"
        )->fetchAll();
    }

    public function getCategories(): array
    {
        return $this->conn->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();
    }

    public function getRoles(): array
    {
        return $this->conn->query("SELECT * FROM roles ORDER BY role_id")->fetchAll();
    }

    /**
     * @param int|null $roleId Filter by role (1 = Requester, 2 = Technician, 3 = Admin by seed data)
     */
    public function getUsers(?int $roleId = null): array
    {
        if ($roleId) {
            $stmt = $this->conn->prepare(
                "SELECT * FROM users WHERE role_id = :rid ORDER BY full_name"
            );
            $stmt->execute([':rid' => $roleId]);
            return $stmt->fetchAll();
        }

        return $this->conn->query("SELECT * FROM users ORDER BY full_name")->fetchAll();
    }

    public function getRoleIdByName(string $roleName): ?int
    {
        $stmt = $this->conn->prepare("SELECT role_id FROM roles WHERE role_name = :name LIMIT 1");
        $stmt->execute([':name' => $roleName]);
        $row = $stmt->fetch();
        return $row ? (int) $row['role_id'] : null;
    }
}
