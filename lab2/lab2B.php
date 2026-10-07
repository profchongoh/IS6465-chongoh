<?php

//calling a function
function formatName($first, $middle, $last)
{
    $first = ucfirst(strtolower($first));
    $middle = ucfirst(strtolower($middle));
    $last = ucfirst(strtolower($last));

    return $first . ' ' . $middle . ' ' . $last;
}

$displayName = formatName('mARIA', 'lee', 'CHEN');
echo $displayName;  // Maria Lee Chen
echo '<br>';

//return an array
function cleanNameParts($first, $middle, $last)
{
    return [
        ucfirst(strtolower($first)),
        ucfirst(strtolower($middle)),
        ucfirst(strtolower($last))
    ];
}

$nameParts = cleanNameParts('mARIA', 'lee', 'CHEN');
echo $nameParts[0];  // Maria
echo '<br>';

//local and global variables
$first = 'Maria';
$last = 'Chen';

function buildFullName()
{
    global $first, $last;
    return "$first $last";
}

echo buildFullName();
echo '<br>';

function buildFullName2($first, $last)
{
    return "$first $last";
}

echo buildFullName2($first, $last);


//static variables
function nextTicketNumber()
{
    static $number = 0;
    $number++;
    return $number;
}

echo nextTicketNumber();  // 1
echo nextTicketNumber();  // 2
echo '<br>';

//class
class User
{
    public $name;
    public $email;

    public function display()
    {
        return 'User: ' . $this->name;
    }
}

$user = new User();
$user->name = 'Maria Chen';
$user->email = 'maria@example.com';

echo $user->display();
echo '<br>';

//constructor
class User2
{
    public $name;
    private $passwordHash;

    public function __construct($name, $password)
    {
        $this->name = $name;
        $this->passwordHash =
            password_hash($password, PASSWORD_DEFAULT);
    }
}

$user = new User2('Maria Chen', 'temporary-password');
print_r($user);
echo '<br>';




?>