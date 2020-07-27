
<?php


require_once __DIR__."/../vendor/autoload.php";

use Core\Mail\Mail;
use Core\View\View;
use App\Models\User;

session();

$view = new View;
$view->addPath("root", "")->addPath("site", "site");
echo $view->render("site::index");
