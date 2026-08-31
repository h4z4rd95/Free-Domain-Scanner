<?php
/**
 * Domain Model
 * Handles domain search and Whois operations
 */

namespace App\Models;

use App\Core\Database;
use App\Core\Logger;

class Domain
{
    private Database $db;
    private Logger $logger;
    private array $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
        $this->config = require BASE_PATH . '/config/api.php';
    }

    /**
     * Check domain availability via Namecheap API
     */
    public function checkAvailability(string $domainName, string $extension): array
    {
        $fullDomain = $domainName . '.' . $extension;
        
        try {
            // Call Namecheap API
            $result = $this->callNamecheapApi('domain.check', [
                'DomainName' => $fullDomain,
            ]);

            if ($result['success']) {
                $status = $result['data']['Available'] === 'true' ? 'available' : 'registered';
                
                // Log the search
                $this->logger->info("Domain checked: {$fullDomain}", [
                    'status' => $status,
                    'extension' => $extension
                ]);

                return [
                    'success' => true,
                    'domain' => $fullDomain,
                    'status' => $status,
                    'price' => $this->getDomainPrice($extension),
                    'message' => $status === 'available' 
                        ? 'دامنه آزاد است و قابل ثبت می‌باشد' 
                        : 'دامنه قبلاً ثبت شده است'
                ];
            }

            throw new \Exception('API request failed');

        } catch (\Exception $e) {
            $this->logger->error("Domain check failed: {$fullDomain}", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'domain' => $fullDomain,
                'status' => 'unknown',
                'message' => 'خطا در بررسی وضعیت دامنه. لطفاً مجدداً تلاش کنید.'
            ];
        }
    }

    /**
     * Get Whois information for a domain
     */
    public function getWhois(string $domainName, string $extension): array
    {
        $fullDomain = $domainName . '.' . $extension;
        
        try {
            $result = $this->callNamecheapApi('domain.getwhois', [
                'DomainName' => $fullDomain,
            ]);

            if ($result['success']) {
                $whoisData = $result['data'];
                
                // Extract relevant information
                $registrar = $whoisData['RegistrarName'] ?? 'Unknown';
                $registrationDate = $whoisData['CreatedDate'] ?? null;
                $expiryDate = $whoisData['ExpiredDate'] ?? null;
                
                // Check if owner info is hidden
                $ownerHidden = isset($whoisData['RegistrantOrganization']) && 
                              $whoisData['RegistrantOrganization'] === 'REDACTED FOR PRIVACY';

                return [
                    'success' => true,
                    'domain' => $fullDomain,
                    'registrar' => $registrar,
                    'registration_date' => $registrationDate,
                    'expiry_date' => $expiryDate,
                    'owner_hidden' => $ownerHidden,
                    'raw_data' => $whoisData
                ];
            }

            throw new \Exception('Whois lookup failed');

        } catch (\Exception $e) {
            $this->logger->error("Whois lookup failed: {$fullDomain}", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'خطا در دریافت اطلاعات Whois'
            ];
        }
    }

    /**
     * Get suggested alternative domains
     */
    public function getSuggestions(string $domainName, string $extension): array
    {
        $suggestions = [];
        
        // Common alternative extensions
        $alternativeExtensions = ['com', 'net', 'org', 'ir', 'io', 'co'];
        
        foreach ($alternativeExtensions as $altExt) {
            if ($altExt !== $extension) {
                $checkResult = $this->checkAvailability($domainName, $altExt);
                if ($checkResult['success'] && $checkResult['status'] === 'available') {
                    $suggestions[] = [
                        'domain' => $domainName . '.' . $altExt,
                        'price' => $this->getDomainPrice($altExt)
                    ];
                }
            }
        }

        // Add variations with hyphens or numbers
        $variations = [
            str_replace(' ', '-', $domainName),
            $domainName . '-online',
            $domainName . '-site',
        ];

        foreach ($variations as $variation) {
            if ($variation !== $domainName) {
                $checkResult = $this->checkAvailability($variation, $extension);
                if ($checkResult['success'] && $checkResult['status'] === 'available') {
                    $suggestions[] = [
                        'domain' => $variation . '.' . $extension,
                        'price' => $this->getDomainPrice($extension)
                    ];
                }
            }
        }

        return array_slice(array_unique($suggestions, SORT_REGULAR), 0, 5);
    }

    /**
     * Get all active domain extensions
     */
    public function getExtensions(?string $category = null): array
    {
        // Fallback data if database is not available
        $fallbackExtensions = [
            ['extension' => 'com', 'category' => 'general', 'price_usd' => 10.00, 'priority' => 1, 'is_active' => 1],
            ['extension' => 'net', 'category' => 'general', 'price_usd' => 12.00, 'priority' => 2, 'is_active' => 1],
            ['extension' => 'org', 'category' => 'organization', 'price_usd' => 11.00, 'priority' => 3, 'is_active' => 1],
            ['extension' => 'ir', 'category' => 'country', 'price_usd' => 2.00, 'priority' => 4, 'is_active' => 1],
            ['extension' => 'io', 'category' => 'tech', 'price_usd' => 35.00, 'priority' => 5, 'is_active' => 1],
        ];

        try {
            $sql = "SELECT * FROM domain_extensions WHERE is_active = 1";
            $params = [];

            if ($category) {
                $sql .= " AND category = :category";
                $params['category'] = $category;
            }

            $sql .= " ORDER BY priority ASC, extension ASC";

            return $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            // Return fallback data if database is not available
            error_log("Failed to fetch extensions from DB: " . $e->getMessage());
            return $fallbackExtensions;
        }
    }

    /**
     * Get domain categories
     */
    public function getCategories(): array
    {
        try {
            $sql = "SELECT DISTINCT category FROM domain_extensions WHERE is_active = 1";
            $results = $this->db->fetchAll($sql);
            
            return array_column($results, 'category');
        } catch (\Exception $e) {
            // Fallback categories
            return ['general', 'organization', 'country', 'tech'];
        }
    }

    /**
     * Get price for a specific extension
     */
    public function getDomainPrice(string $extension): float
    {
        try {
            $sql = "SELECT price_usd FROM domain_extensions WHERE extension = :ext AND is_active = 1";
            $result = $this->db->fetch($sql, ['ext' => $extension]);
            
            return $result ? (float) $result['price_usd'] : 10.00; // Default price
        } catch (\Exception $e) {
            // Fallback prices
            $prices = [
                'com' => 10.00,
                'net' => 12.00,
                'org' => 11.00,
                'ir' => 2.00,
                'io' => 35.00,
            ];
            return $prices[$extension] ?? 10.00;
        }
    }

    /**
     * Call Namecheap API
     */
    private function callNamecheapApi(string $command, array $params): array
    {
        $apiConfig = $this->config['namecheap'];
        
        $baseParams = [
            'ApiUsername' => $apiConfig['username'],
            'ApiKey' => $apiConfig['api_key'],
            'UserName' => $apiConfig['username'],
            'ClientIp' => $apiConfig['client_ip'],
            'Command' => $command,
        ];

        $queryParams = array_merge($baseParams, $params);
        $url = $apiConfig['sandbox'] ? $apiConfig['api_url'] : $apiConfig['production_url'];
        
        // Build query string
        $queryString = http_build_query($queryParams);
        $fullUrl = $url . '?' . $queryString;

        // Make API request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200) {
            return [
                'success' => false,
                'error' => $error ?: "HTTP {$httpCode}"
            ];
        }

        // Parse XML response
        $xml = simplexml_load_string($response);
        
        if (!$xml) {
            return [
                'success' => false,
                'error' => 'Invalid XML response'
            ];
        }

        // Check for errors in response
        if (isset($xml->errors)) {
            $errorMsg = (string) $xml->errors->error;
            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }

        // Process successful response based on command
        $data = [];
        
        if ($command === 'domain.check') {
            $domainCheck = $xml->DomainCheckResponse->Domain[0];
            $data = [
                'DomainName' => (string) $domainCheck['Name'],
                'Available' => (string) $domainCheck['Available'],
            ];
        } elseif ($command === 'domain.getwhois') {
            $whoisResponse = $xml->DomainGetWhoisResponse;
            $data = [
                'RegistrarName' => (string) $whoisResponse->RegistrarInfo->Name,
                'CreatedDate' => (string) $whoisResponse->CreatedDate,
                'ExpiredDate' => (string) $whoisResponse->ExpiredDate,
                'RegistrantOrganization' => (string) $whoisResponse->RegistrantContact->Organization,
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Save search to history
     */
    public function saveSearch(string $domainName, string $extension, string $status, ?int $userId = null, ?array $whoisData = null): void
    {
        $fullDomain = $domainName . '.' . $extension;
        $sessionId = session_id();

        if ($userId) {
            // Save to database for logged-in users
            $this->db->insert('search_history', [
                'user_id' => $userId,
                'domain_name' => $domainName,
                'extension' => $extension,
                'full_domain' => $fullDomain,
                'status' => $status,
                'whois_data' => $whoisData ? json_encode($whoisData) : null,
            ]);
        } else {
            // Save to cookie for guests (handled by helper function)
            saveGuestSearch([
                'domain' => $fullDomain,
                'status' => $status,
                'timestamp' => time()
            ]);
        }
    }
}
