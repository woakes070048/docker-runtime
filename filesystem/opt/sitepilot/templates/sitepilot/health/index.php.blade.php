{!! '<?php' !!}

try {
    $load = checkLoad();

    @if(!empty($environment['DB_USERNAME']) !empty($environment['DB_PASSWORD']))
    checkMySQL();
    @endif
    
    checkRedis();

    @if(!empty($smtp['user']) && !empty($smtp['password']))
    checkMail();
    @endif

    http_response_code(200);
    echo "[OK] - Load: $load";
} catch (Exception $e) {
    http_response_code(500);
    echo "[ERROR] - " . $e->getMessage();
}

/**
 * Check MySQL connection.
 *
 * @return void
 * @throws Exception
 */
function checkMySQL()
{
    $servername = "{{ $environment['DB_HOST'] }}:{{ $environment['DB_PORT'] }}";
    $username = "{{ $environment['DB_USERNAME'] }}";
    $password = "{{ $environment['DB_PASSWORD'] }}";

    $conn = new mysqli($servername, $username, $password);

    if ($conn->connect_error) {
        throw new Exception("Could not connect to MySQL server.");
    }
}

/**
 * Check Redis connection.
 *
 * @return void
 * @throws Exception
 */
function checkRedis()
{
    $client = new TinyRedisClient('127.0.0.1:6379');
    $client->set('sitepilot_redis_check', 'OK');
    $value = $client->get('sitepilot_redis_check');

    if ($value != "OK") {
        throw new Exception("Could not connect to Redis server.");
    }
}

/**
 * Check average load (5 min).
 *
 * @return void
 * @throws Exception
 */
function checkLoad()
{
    $load = sys_getloadavg();

    return $load[1];
}

/**
 * Check if scripts can send e-mail.
 * 
 * @return void 
 * @throws Exception 
 */
function checkMail()
{
    $client = new TinyRedisClient('127.0.0.1:6379');
    $lastState = $client->get('sitepilot_mail_check_state');
    $lastCheck = $client->get('sitepilot_mail_check_timestamp');

    if (((time() - $lastCheck) > 3600) || $lastState == "NOK") {
        $from = "health@app.io";
        $to = "health@sitepilot.io";

        $subject = "Test mail function";
        $message = "This is a test to check if php mail function sends out the email.";
        $headers = "From:" . $from;

        $client->set('sitepilot_mail_check_timestamp', time());

        if (!mail($to, $subject, $message, $headers)) {
            $client->set('sitepilot_mail_check_state', 'NOK');
            throw new Exception("Could not send email.");
        } else {
            $client->set('sitepilot_mail_check_state', 'OK');
        }
    }
}

/**
 * Redis client.
 */
class TinyRedisClient
{
    private $server;
    private $socket;

    public function __construct($server = 'localhost:6379')
    {
        $this->server = $server;
    }

    public function __call($method, array $args)
    {
        array_unshift($args, $method);
        $cmd = '*' . count($args) . "\r\n";
        foreach ($args as $item) {
            $cmd .= '$' . strlen($item) . "\r\n" . $item . "\r\n";
        }
        fwrite($this->getSocket(), $cmd);

        return $this->parseResponse();
    }

    private function getSocket()
    {
        return $this->socket
            ? $this->socket
            : ($this->socket = stream_socket_client($this->server));
    }

    private function parseResponse()
    {
        $line = fgets($this->getSocket());
        list($type, $result) = array($line[0], substr($line, 1, strlen($line) - 3));
        if ($type == '-') {
            throw new Exception($result);
        } elseif ($type == '$') {
            if ($result == -1) {
                $result = null;
            } else {
                $line = fread($this->getSocket(), $result + 2);
                $result = substr($line, 0, strlen($line) - 2);
            }
        } elseif ($type == '*') {
            $count = (int) $result;
            for ($i = 0, $result = array(); $i < $count; $i++) {
                $result[] = $this->parseResponse();
            }
        }

        return $result;
    }
}
