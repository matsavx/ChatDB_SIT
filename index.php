<?php

class UserData {
    private $login;
    private $password;

    public function __construct($login, $password) {
        $this->login = $login;
        $this->password = $password;
    }
    public function getLogin() {
        return $this->login;
    }
    public function getPassword() {
        return $this->password;
    }
    public function save ($db) {
        $stmt = $db->prepare('INSERT INTO `user_data` (`login`, `password`) values (?, ?)');
        $stmt->exequte([$this->login, $this->password]);
    }
    public function getById($db) {
        /*не понял*/
        /*нет id*/
    }
    public function remove ($db) {
        $stmt = $db->prepare('DELETE FROM `user_data` where `login` = ?');
        $stmt->execute([$this->login]);
    }
    public function all($db) {
        $stmt = $db->prepare('SELECT * FROM `user_data`');
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getByLogin($db, $login) {
        $stmt = $db->prepare('SELECT * FROM `user_data` where `login` = ?');
        $stmt->execute([$login]);
        $curr = $stmt->fetchAll();
        if (count($curr) != 0)
            return new UserData($curr[0]["login"], $curr[0]["password"]);
        else
            return new UserData(null, null);
    }
}

class Message {
    private $login;
    private $text;
    private $date;

    public function __construct($login,$text,$date) {
        $this->login = $login;
        $this->text = $text;
        $this->date = $date;
    }
    public function getLogin()
    {
        return $this->login;
    }
    public function getText()
    {
        return $this->text;
    }
    public function getDate()
    {
        return $this->date;
    }
//    public function all($db) {
//        $stmt = $db->prepare('SELECT * FROM `messages`');
//        $stmt->execute();
//        return $stmt->fetchAll();
//    }
//    public function save($db) {
//        $stmt = $db->prepare('INSERT INTO `messages` (`login`, `text`, `date`) values (?, ?, ?)');
//        $stmt->execute([$this->login, $this->text, $this->date]);
//    }
}

class DataMapper {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }
    public function save($mess) {
        $stmt = $this->db->prepare('INSERT INTO `messages` (`login`, `text`, `date`) values (?, ?, ?)');
        $stmt->execute([$mess->getLogin(), $mess->getText(), $mess->getDate()]);
    }
    public function all($db) {
        $stmt = $db->prepare('SELECT * FROM `messages`');
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getMessageByLogin($login) {
        $stmt = $this->db->prepare("SELECT * FROM `messages` WHERE text = ?");
        $stmt->execute([$login]);
        return new Message($stmt->fetchAll['login'], $stmt->fetchAll['text'], $stmt->fetchAll['date']);
    }
    public function remove($mess) {
        $stmt = $this->db->prepare('DELETE * FROM `messages` (`login`, `text`, `date`) values (?, ?, ?)');
        $stmt->execute([$mess->getLogin(), $mess->getText(), $mess->getDate()]);
    }
}

require_once dirname(__DIR__).'/vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__).'/chatdb/src');
$twig = new \Twig\Environment($loader);
$template = $twig->load('index.twig');

$host = 'localhost';
$dbname = 'chatdb';
$true_login = 'admin';
$true_password = 'admin';
$user = new UserData(null, null);
$db = new PDO("mysql:host=$host;dbname=$dbname", $true_login, $true_password);
$data_mapper = new DataMapper($db);

$login = $_GET['login'];
$password = $_GET['password'];
$text= $_GET['text'];

$login_err = true;
if (isset($login) && isset($password)) {
    $user = new UserData($login, $password);
    if ($user->getByLogin($db, $login)->getLogin() == $login && $user->getByLogin($db, $login)->getLogin() == $password)
        $login_err = false;
    else
        $login_err = true;
}

if ($login_err == false && isset($text)) {
    $message = new Message($login, $text, date('Y-m-d\TH:i:s.u'));
    $data_mapper->save($message);
}

echo $template->render(['login'=>$user->getLogin(), 'messages'=>$data_mapper->all($db), 'err'=>$login_err]);

