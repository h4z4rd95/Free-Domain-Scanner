<?php
/**
 * Search Controller
 * Handles domain search operations
 */

namespace App\Controllers;

use App\Models\Domain;
use App\Core\Logger;

class SearchController
{
    private Domain $domainModel;
    private Logger $logger;

    public function __construct()
    {
        $this->domainModel = new Domain();
        $this->logger = new Logger();
    }

    public function index(): void
    {
        $extensions = $this->domainModel->getExtensions();
        $categories = $this->domainModel->getCategories();
        $guestHistory = getGuestSearchHistory();
        
        $pageTitle = 'جستجوی دامنه';
        require BASE_PATH . '/app/Views/pages/search.php';
    }

    public function apiSearch(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'متد درخواست نامعتبر است'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $domainName = sanitize($input['domain'] ?? '');
        $extension = sanitize($input['extension'] ?? 'com');

        // Validation
        if (empty($domainName)) {
            jsonResponse(['success' => false, 'message' => 'نام دامنه را وارد کنید']);
        }

        if (!preg_match('/^[a-z0-9-]+$/', strtolower($domainName))) {
            jsonResponse(['success' => false, 'message' => 'نام دامنه فقط می‌تواند شامل حروف، اعداد و خط تیره باشد']);
        }

        // Check availability
        $result = $this->domainModel->checkAvailability($domainName, $extension);

        // Get Whois if registered
        $whoisData = null;
        if ($result['status'] === 'registered') {
            $whoisResult = $this->domainModel->getWhois($domainName, $extension);
            if ($whoisResult['success']) {
                $whoisData = [
                    'registrar' => $whoisResult['registrar'],
                    'registration_date' => $whoisResult['registration_date'],
                    'expiry_date' => $whoisResult['expiry_date'],
                    'owner_hidden' => $whoisResult['owner_hidden']
                ];
            }
        }

        // Get suggestions if registered
        $suggestions = [];
        if ($result['status'] === 'registered') {
            $suggestions = $this->domainModel->getSuggestions($domainName, $extension);
        }

        // Save to history
        $userId = getCurrentUserId();
        $this->domainModel->saveSearch($domainName, $extension, $result['status'], $userId, $whoisData);

        // Convert price to Toman
        $priceToman = usdToToman($result['price'] ?? 0);

        jsonResponse([
            'success' => true,
            'data' => [
                'domain' => $result['domain'],
                'status' => $result['status'],
                'message' => $result['message'],
                'price_usd' => $result['price'] ?? 0,
                'price_toman' => $priceToman,
                'whois' => $whoisData,
                'suggestions' => $suggestions
            ]
        ]);
    }
}
