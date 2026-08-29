<?php
/**
 * Home Controller
 */

namespace App\Controllers;

class HomeController
{
    public function index(): void
    {
        $pageTitle = 'صفحه اصلی - جستجوی دامنه';
        require BASE_PATH . '/app/Views/pages/home.php';
    }
}
