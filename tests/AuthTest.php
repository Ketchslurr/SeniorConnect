<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../views/authFunctions.php';

class AuthTest extends TestCase {
    private $pdo;

    protected function setUp(): void {
        $this->pdo = new PDO('mysql:host=mainline.proxy.rlwy.net;port=11430;dbname=senior_connect', 'root', 'gcBgyXeCUfVihoktdJsHDFyahPMcNvzC');
        // $this->pdo = new PDO('mysql:host=localhost;dbname=senior_connect', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function testValidAdminLogin() {
        $result = authenticateUser($this->pdo, 'admin@gmail.com', 'ValidAdminPassword');
        $this->assertIsArray($result);
        $this->assertEquals('admin', $result['role']);
    }

    public function testValidSeniorCitizenLogin() {
        $result = authenticateUser($this->pdo, 'elly@gmail.com', 'ValidPassword');
        $this->assertIsArray($result);
        $this->assertEquals(2, $result['role']);
    }

    public function testInvalidLogin() {
        $result = authenticateUser($this->pdo, 'admin@gmail.com', 'wrongpassword');
        $this->assertFalse($result);
    }
}
?>
