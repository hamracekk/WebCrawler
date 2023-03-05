<?php

//EXTERNS:
require __DIR__ . '/../vendor/autoload.php';
//END OF EXTERNS

// Class which abstract CRUD database operations
class Database 
{
    //ATRIBUTES:
    private $server = 'localhost';
    private $login = 'root';
    private $password = '';
    private $database = 'webcrawler';
    private $table_name = 'webcrawler';
    private $whole_dataset = [];
    private $connection; 
    private $already_connected = false; 
    private $logger; // Logger
    //END OF ATRIBUTES
    
    //METHODS:

    //PUBLIC:
    public function __construct($server = 'localhost', $login = 'root', $password = '', $database = "webcrawler", $table_name = 'webcrawler') 
    {
        $logger = new Monolog\Logger('WebCrawlerDatabseLogger');
        $logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ .'/../logs/database.log', Monolog\Logger::WARNING));

        $this->server = $server;
        $this->login = $login;
        $this->password = $password;
        $this->database = $database;
        $this->table_name = $table_name;

        if(!$this->already_connected)
        {
            // Creating connection to database
            $this->connection = new mysqli($this->server, $this->login, $this->password, $this->database);
            // Connection checking
            if ($this->connection->connect_error)  { 
                $logger->error('Failed to connect databse.');
                die("Connection failed: " .  $this->connection->connect_error); 
            }
            else { 
                $logger->error('Databse is loaded');
                $this->already_connected = true; 
            }
        }
    }

    public function __destruct()
    {
        $this->close_connection();
    }

    public function InsertRecord(&$URL, &$regExp, $periodicity, $Label, $activity, $tags) : bool | array
    {
        //Sanitization of insertion 
        $stmt = $this->connection->prepare("INSERT INTO " . $this->table_name . " (url,boundary_regex,periodicity,label,active,tag) VALUES (?, ?)");
        $stmt->bind_param("ssisbs", $URL, $regExp, $periodicity, $Label, $activity, $tags);
        $stmt->execute();

        $result = true;
        if ($stmt->affected_rows === 1) { 
            $stmt->insert_id;
            $result = [$stmt->insert_id, $URL, $regExp, $periodicity, $Label, $activity, $tags]; // return created record
        } 
        else { $result = false; } // return false if not created 

        if($stmt)
            $stmt->close();
        return $result;
    }

    public function DeleteRecord($id) // odobratie čláanku z databaze s určitým ID
    {
        //sanitizace dotazu
        $stmt = $this->connection->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->affected_rows === 1;
        if($stmt)
            $stmt->close();
        return $result;
    }
    //END OF PUBLIC SECTION 

    //PRIVATE:
    private function close_connection() // uzavriem spojenie
    { 
        $this->connection->close();  
        $this->already_connected = false;
    }
    //END OF PRIVATE SECTION
}