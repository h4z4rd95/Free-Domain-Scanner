<?php
/**
 * Order Controller
 * Handles order creation and submission
 */

namespace App\Controllers;

use App\Models\Order;
use App\Core\Logger;

class OrderController
{
    private Order $orderModel;
    private Logger $logger;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->logger = new Logger();
    }

    public function create(): void
    {
        requireLogin();
        
        $domain = sanitize($_GET['domain'] ?? '');
        if (empty($domain)) {
            redirect(baseUrl() . '/search');
        }

        $cards = $this->orderModel->getActiveCards();
        $pageTitle = 'ثبت سفارش دامنه';
        
        require BASE_PATH . '/app/Views/pages/order.php';
    }

    public function submit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'متد درخواست نامعتبر است'], 405);
        }

        requireLogin();

        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        $domain = sanitize($input['domain'] ?? '');
        $extension = sanitize($input['extension'] ?? 'com');
        $period = (int) ($input['period'] ?? 1);
        $paymentMethod = $input['payment_method'] ?? 'manual_transfer';
        
        if (empty($domain)) {
            jsonResponse(['success' => false, 'message' => 'نام دامنه معتبر نیست']);
        }

        // Get price
        $priceUsd = $this->orderModel->getDomainPrice($extension);
        $priceIrt = usdToToman($priceUsd * $period);
        $currencyRate = getCurrencyRate();

        // Handle receipt upload for manual transfer
        $receiptPath = null;
        if ($paymentMethod === 'manual_transfer') {
            if (!empty($_FILES['receipt'])) {
                $uploadResult = uploadFile($_FILES['receipt'], BASE_PATH . '/public/uploads/receipts/');
                if ($uploadResult['success']) {
                    $receiptPath = 'receipts/' . $uploadResult['filename'];
                }
            }
            
            $transferDate = sanitize($input['transfer_date'] ?? '');
            $transferTime = sanitize($input['transfer_time'] ?? '');
            
            if (!$receiptPath && empty($transferDate)) {
                jsonResponse([
                    'success' => false, 
                    'message' => 'لطفاً تصویر رسید واریز را آپلود کنید یا تاریخ و ساعت واریز را وارد نمایید'
                ]);
            }
        }

        // Create order
        $orderNumber = generateOrderNumber();
        $userId = getCurrentUserId();
        
        $orderId = $this->orderModel->create([
            'order_number' => $orderNumber,
            'user_id' => $userId,
            'domain_name' => $domain,
            'extension' => $extension,
            'full_domain' => $domain . '.' . $extension,
            'service_type' => 'domain',
            'period_years' => $period,
            'price_usd' => $priceUsd * $period,
            'price_irt' => $priceIrt,
            'currency_rate' => $currencyRate,
            'payment_method' => $paymentMethod,
            'payment_receipt' => $receiptPath,
            'status' => 'pending',
            'notes' => sanitize($input['notes'] ?? ''),
        ]);

        if ($orderId) {
            $this->logger->info("Order created: {$orderNumber}", [
                'user_id' => $userId,
                'domain' => $domain . '.' . $extension,
                'amount' => $priceIrt
            ]);

            jsonResponse([
                'success' => true,
                'message' => 'سفارش شما با موفقیت ثبت شد. پس از بررسی و تأیید پرداخت، فرآیند ثبت دامنه انجام خواهد شد.',
                'order_number' => $orderNumber,
                'redirect_url' => baseUrl() . '/orders/' . $orderId
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'خطا در ثبت سفارش. لطفاً مجدداً تلاش کنید.'
            ]);
        }
    }

    public function index(): void
    {
        requireLogin();
        
        $userId = getCurrentUserId();
        $orders = $this->orderModel->getUserOrders($userId);
        $pageTitle = 'سفارشات من';
        
        require BASE_PATH . '/app/Views/pages/orders.php';
    }
}
