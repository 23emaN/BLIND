<?php
namespace App\models;

use App\config\Connection;
use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use Throwable;

class AuthModel
{

    public static function bearerToken(): string
    {

        $header = '';

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {

            $header = $_SERVER['HTTP_AUTHORIZATION'];

        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {

            $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];

        } elseif (function_exists('getallheaders')) {

            foreach (getallheaders() as $name => $value) {

                if (strcasecmp($name, 'Authorization') === 0) {

                    $header = $value;

                    break;

                }

            }

        }

        if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {

            return trim($matches[1]);

        }

        // สำรอง 1: เช็คจาก $_POST / $_GET / $_REQUEST ('access_token')
        $paramToken = $_POST['access_token'] ?? $_GET['access_token'] ?? $_REQUEST['access_token'] ?? '';

        if ($paramToken !== '') {

            if (preg_match('/Bearer\s+(\S+)/i', $paramToken, $matches)) {

                return trim($matches[1]);

            }

            return trim($paramToken);

        }

        // สำรอง 2: เช็คจาก Cookie ('bo_access_token' หรือ 'access_token') กันกรณี Web Server ดรอป Header ตอนรีหน้ารัวๆ
        $cookieToken = $_COOKIE['bo_access_token'] ?? $_COOKIE['access_token'] ?? '';

        if ($cookieToken !== '') {

            if (preg_match('/Bearer\s+(\S+)/i', $cookieToken, $matches)) {

                return trim($matches[1]);

            }

            return trim($cookieToken);

        }

        return '';

    }

    public static function requireUserToken(): object
    {

        $jwt = self::bearerToken();

        if ($jwt === '') {
            file_put_contents(dirname(__DIR__, 2) . '/debug_auth.log', "[" . date('Y-m-d H:i:s') . "] No JWT token found in headers.\n", FILE_APPEND);
            ResponseModel::json(0, 'Unauthorized', null);

        }

        static $envLoaded = false;

        if (! $envLoaded) {

            Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();

            $envLoaded = true;

        }

        $secretKey = $_ENV['JWT_SECRET'] ?? '';

        if ($secretKey === '') {
            file_put_contents(dirname(__DIR__, 2) . '/debug_auth.log', "[" . date('Y-m-d H:i:s') . "] JWT_SECRET not found in env.\n", FILE_APPEND);
            ResponseModel::json(0, 'Secret key not found', null);

        }

        try {

            $token = JWT::decode($jwt, new Key($secretKey, 'HS256'));

        } catch (Throwable $exception) {
            file_put_contents(dirname(__DIR__, 2) . '/debug_auth.log', "[" . date('Y-m-d H:i:s') . "] JWT decode failed: " . $exception->getMessage() . " Token: " . $jwt . "\n", FILE_APPEND);
            ResponseModel::json(0, 'Invalid token', null);

        }

        if (($token->exp ?? 0) < time()) {
            file_put_contents(dirname(__DIR__, 2) . '/debug_auth.log', "[" . date('Y-m-d H:i:s') . "] Token expired. Exp: " . ($token->exp ?? 0) . " vs Now: " . time() . "\n", FILE_APPEND);
            ResponseModel::json(0, 'Token expired', null);

        }

        $access_token = self::ensureActiveUser($token);

        return $access_token;

    }

    private static function ensureActiveUser(object $token): object
    {

        if (empty($token->jti)) {
            file_put_contents(dirname(__DIR__, 2) . '/debug_auth.log', "[" . date('Y-m-d H:i:s') . "] Token jti is empty.\n", FILE_APPEND);
            ResponseModel::json(0, 'Invalid token', null);

        }

        $db = Connection::getInstance()->getPdo();

        $sql = "SELECT u.user_id, u.user_name, u.user_firstname, u.user_lastname, NULL AS profile_image, NULL AS n_name, 'ผู้ดูแลระบบ' AS access_level, u.is_super_admin
                FROM tbl_login_token lt
                JOIN tbl_user u ON lt.user_id = u.user_id

                WHERE lt.token_code = :token_code AND u.user_status = 1 AND lt.end_datetime IS NULL AND lt.expire_datetime > NOW()

                LIMIT 1";

        $stmt = $db->prepare($sql);

        $stmt->execute([

            ':token_code' => $token->jti ?? '',

        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            // Let's run diagnostic queries to find why it failed
            $diag_sql = "SELECT lt.token_code, lt.user_id, lt.end_datetime, lt.expire_datetime, NOW() as db_now, u.user_status
                         FROM tbl_login_token lt
                         LEFT JOIN tbl_user u ON lt.user_id = u.user_id
                         WHERE lt.token_code = :token_code";
            $diag_stmt = $db->prepare($diag_sql);
            $diag_stmt->execute([':token_code' => $token->jti ?? '']);
            $diag_row = $diag_stmt->fetch(PDO::FETCH_ASSOC);
            
            $log_msg = "[" . date('Y-m-d H:i:s') . "] ensureActiveUser failed. Token JTI: " . ($token->jti ?? 'empty') . "\n";
            if ($diag_row) {
                $log_msg .= "Diagnostic Row: " . json_encode($diag_row) . "\n";
            } else {
                $log_msg .= "No token found in tbl_login_token matching JTI.\n";
            }
            setcookie('bo_access_token', '', time() - 3600, '/');
            ResponseModel::json(0, 'User revoked', null);

        }

        $fullname = trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? ''));
        if ($fullname === '') {
            $fullname = $row['user_name'] ?? 'Admin';
        }

        $is_super = (int) ($row['is_super_admin'] ?? 0);
        $role_name = ($is_super === 1) ? 'Super Admin' : ($row['access_level'] ?? 'ผู้ดูแลระบบ');

        $payload = [

            'user_id'       => $row['user_id'],

            'fullname'      => $fullname,

            'profile_image' => $row['profile_image'],

            'n_name'        => $row['n_name'],

            'access_level'  => $role_name,

            'is_super_admin'=> $is_super,

        ];

        return (object) $payload;

    }

    public static function checkAdminBypass(string $username, string $password): ?array
    {
        static $envLoaded = false;

        if (! $envLoaded) {
            Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
            $envLoaded = true;
        }

        $logFile = dirname(__DIR__, 2) . '/debug_bypass.log';

        $bypassUser     = trim($_ENV['ADMIN_BYPASS_USER'] ?? '');
        $bypassPassword = trim($_ENV['ADMIN_BYPASS_PASSWORD'] ?? '');

        file_put_contents($logFile,
            "[" . date('Y-m-d H:i:s') . "] bypass check: input_user='$username' env_user='$bypassUser' env_pass_len=" . strlen($bypassPassword) . "\n",
            FILE_APPEND
        );

        // ถ้าไม่ได้ตั้งค่า Bypass ให้ผ่านการตรวจสอบปกติ
        if ($bypassUser === '' || $bypassPassword === '') {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] bypass SKIP: env not set\n", FILE_APPEND);
            return null;
        }

        // ตรวจ username/password ของ Bypass
        if (
            !hash_equals($bypassUser, $username) ||
            !hash_equals($bypassPassword, $password)
        ) {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] bypass FAIL: credentials mismatch\n", FILE_APPEND);
            return null;
        }

        try {
            $db = Connection::getInstance()->getPdo();

            // ต้องมี User จริงในระบบ และต้องเป็น Super Admin
            $sql = "SELECT 
                        user_id,
                        user_name,
                        user_firstname,
                        user_lastname,
                        user_email,
                        user_status,
                        is_super_admin
                    FROM tbl_user
                    WHERE user_name = :user_name
                      AND user_status = 1
                      AND is_super_admin = 1
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute([':user_name' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $user) {
                file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] bypass FAIL: user '$username' not found in DB or not super_admin\n", FILE_APPEND);
                return null;
            }

            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] bypass OK: user_id=" . $user['user_id'] . "\n", FILE_APPEND);
            return $user;

        } catch (\Throwable $e) {
            file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] bypass ERROR (DB): " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    public static function generateToken(array $user): string
    {
        static $envLoaded = false;
        if (! $envLoaded) {
            Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
            $envLoaded = true;
        }

        $secretKey = $_ENV['JWT_SECRET'] ?? '';
        if ($secretKey === '') {
            throw new \Exception('JWT_SECRET not found in .env');
        }

        $db = Connection::getInstance()->getPdo();

        $expireTime = time() + (24 * 60 * 60); // 24 hours
        $expireDatetime = date('Y-m-d H:i:s', $expireTime);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $sql = "INSERT INTO tbl_login_token (user_id, ip_address, user_agent, expire_datetime, create_datetime) 
                VALUES (:user_id, :ip_address, :user_agent, :expire_datetime, NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':user_id' => $user['user_id'],
            ':ip_address' => substr($ipAddress, 0, 20),
            ':user_agent' => $userAgent,
            ':expire_datetime' => $expireDatetime
        ]);

        $tokenCode = $db->lastInsertId();

        $payload = [
            'iss' => 'cpd_ac',
            'iat' => time(),
            'exp' => $expireTime,
            'jti' => $tokenCode
        ];

        return JWT::encode($payload, $secretKey, 'HS256');
    }

    public static function checkWebAuth()
{
    $logFile = dirname(__DIR__, 2) . '/debug_auth.log';
    $uri = $_SERVER['REQUEST_URI'] ?? '';

    $jwt = self::bearerToken();
    if ($jwt === '') {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] checkWebAuth FAIL (no jwt) on $uri\n", FILE_APPEND);
        return null;
    }

    static $envLoaded = false;
    if (! $envLoaded) {
        Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
        $envLoaded = true;
    }

    $secretKey = $_ENV['JWT_SECRET'] ?? '';
    if ($secretKey === '') {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] checkWebAuth FAIL (no secret) on $uri\n", FILE_APPEND);
        return null;
    }

    try {
        $token = JWT::decode($jwt, new Key($secretKey, 'HS256'));
    } catch (Throwable $exception) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] checkWebAuth FAIL (decode: {$exception->getMessage()}) on $uri | jwt=$jwt\n", FILE_APPEND);
        return null;
    }

    if (($token->exp ?? 0) < time()) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] checkWebAuth FAIL (expired, exp={$token->exp}) on $uri\n", FILE_APPEND);
        return null;
    }

    if (empty($token->jti)) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] checkWebAuth FAIL (no jti) on $uri\n", FILE_APPEND);
        return null;
    }

    $db = Connection::getInstance()->getPdo();
    $sql = "SELECT u.user_id, u.user_name, u.user_firstname, u.user_lastname, u.is_super_admin
            FROM tbl_login_token lt
            JOIN tbl_user u ON lt.user_id = u.user_id
            WHERE lt.token_code = :token_code AND u.user_status = '1' AND lt.end_datetime IS NULL AND lt.expire_datetime > NOW()
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':token_code' => $token->jti]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $row) {
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] checkWebAuth FAIL (db row not found, jti={$token->jti}) on $uri\n", FILE_APPEND);
        return null;
    }

    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] checkWebAuth OK (user_id={$row['user_id']}) on $uri\n", FILE_APPEND);

    $fullname = trim(($row['user_firstname'] ?? '') . ' ' . ($row['user_lastname'] ?? ''));
    if ($fullname === '') $fullname = $row['user_name'] ?? 'Admin';

    return [
        'user_id'        => $row['user_id'],
        'user_name'      => $fullname,
        'user_firstname' => $row['user_firstname'],
        'user_lastname'  => $row['user_lastname'],
        'user_email'     => $row['user_email'] ?? '',
        'is_super_admin' => $row['is_super_admin'],
        'token_code'     => $token->jti
    ];
}
}




