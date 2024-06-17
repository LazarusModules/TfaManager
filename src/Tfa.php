<?php
namespace LazarusPhp\TfaManager;

use DateTime;
use LazarusPhp\DatabaseManager\Database;
use App\System\Classes\Required\Date;
use PDO;
use PDOException;

class Tfa
{

    private $db;
    private $date;
    private $session_id;
    private $table;
    public function __construct()
    {
        $this->db = new Database();
        $this->date = new Date();
        $this->table = "tfa";
        if($_SESSION['id'])
        {
            $this->session_id = $_SESSION['id'];
        }

        
    }
    public  function VerifyTfa($input)
    {
        $now = $this->date->AddDate("now")->format("Y-m-d H:i:s");
        $query = "SELECT id FROM ".$this->table." WHERE user_id=:id && code=:code && expires > :expires";
        $tfa = $this->db->GenerateQuery($query, [":id" => $this->session_id, ":code"=>$input, ":expires" => $now]);
        $result = $tfa->fetch();
        $count = $tfa->rowCount();
        if($count > 0) 
        {
            $this->DeleteTfaRequest($result->id);
            return true;
        }
        else
        {
            return false;
        }
    }


    public function DeleteTfaRequest($id)
    {
        $query = "DELETE FROM ".$this->table." WHERE id=:id";
        if(!$this->db->GenerateQuery($query, [":id"=>$id]))
        {
            echo "submission failed";
        }
    }
    


    public  function GenerateTfa()
    {
        $this->ClearOldTfa();
        isset($_SESSION['id']) ? $this->session_id = $_SESSION['id'] : $this->session_id = '';
        $code = random_int(100000, 999999);
        $now = $this->date->AddDate("now")->format("Y-m-d H:i:s");
        $query = "SELECT * FROM ".$this->table." WHERE user_id=:id && expires > :expires ";
        $tfa = $this->db->GenerateQuery($query, [":id" => $this->session_id, ":expires" => $now]);
        $count = $tfa->rowCount();
        if ($count == 0) {
                $date = $this->date->AddDate("now")->add(new \DateInterval("PT5M"))->format("Y-m-d H:i:s");
                $query = "INSERT INTO tfa (user_id,code,expires) VALUES(:user_id,:code,:expires)";
                if(!$this->db->GenerateQuery($query, [":user_id" => $this->session_id, ":code" => $code, ":expires" => $date]))
                {
                    echo "Submission Failed";
                }
            }
         
    }

    private function ClearOldTfa()
    {
        $now = $this->date->AddDate("now")->format("Y-m-d H:i:s");
        $query = "DELETE FROM ".$this->table." WHERE user_id=:id && expires < :expires";
            if(!$this->db->GenerateQuery($query, [":id" => $this->session_id, ":expires" => $now]))
            {
                echo "Submission Failed";
            }
        
    }
    public  function TfaInput()
    {
        for($i=0;$i<6;$i++)
        {
            echo "<input name='code[]' type='textbox' maxlength='1' size='1'>";
        }
    }
}