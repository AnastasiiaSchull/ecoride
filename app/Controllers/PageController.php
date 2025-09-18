<?php
class PageController {
    public function __construct(private PDO $pdo) {}
    public function contact(): void { include ROOT.'/app/Views/static/contact.php'; }
    
}
