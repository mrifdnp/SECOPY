<?php

namespace App\Services;

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CustomerObject;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\NotificationPreference;
use Xendit\Invoice\NotificationChannel;
use Exception;

class XenditService
{
    protected $invoiceApi;

    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
        $this->invoiceApi = new InvoiceApi();
    }

    /**
     * Create Xendit Invoice
     *
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    public function createInvoice(array $data)
    {
        try {
            $params = new CreateInvoiceRequest([
                'external_id'      => $data['external_id'],
                'description'      => $data['description'] ?? 'Payment Invoice',
                'amount'           => $data['amount'],
                'invoice_duration' => (int) config('services.xendit.invoice_duration'),
                'payer_email'      => $data['payer_email'] ?? null,
                'customer' => new CustomerObject([
                    'customer_id'  => $data['customer_id'],
                    'given_names'  => $data['customer_name'] ?? 'Guest',
                    'email'        => $data['payer_email'] ?? null,
                    'mobile_number'=> $data['customer_phone'] ?? null,
                    'address'      => $data['address'] ?? null
                ]),
                'customer_notification_preference' => new NotificationPreference([
                    'invoice_created' => [
                        NotificationChannel::EMAIL,
                        NotificationChannel::WHATSAPP
                    ],
                    'invoice_reminder' => [
                        NotificationChannel::EMAIL,
                        NotificationChannel::WHATSAPP
                    ],
                    'invoice_expired' => [
                        NotificationChannel::EMAIL,
                        NotificationChannel::WHATSAPP
                    ],
                ]),
                'success_redirect_url' => config('services.xendit.success_redirect'),
                'failure_redirect_url' => config('services.xendit.failure_redirect'),
            ]);

            $for_user_id = config('services.xendit.xenplatform_user_id');

            // dd($params, $for_user_id);

            return $this->invoiceApi->createInvoice($params, $for_user_id);
        } catch (Exception $e) {
            \Log::error('Xendit createInvoice failed: ' . $e->getMessage());
            throw $e;
        }
    }


    public function cancelInvoice(string $invoiceId)
    {
        try {
            return $this->invoiceApi->expireInvoice($invoiceId);
        } catch (\Exception $e) {
            \Log::error('Xendit cancelInvoice failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getInvoiceById(string $invoiceId)
    {
        $for_user_id = config('services.xendit.xenplatform_user_id');

        try {
            return $this->invoiceApi->getInvoiceById($invoiceId, $for_user_id);
        } catch (\Exception $e) {
            \Log::error('Xendit cancelInvoice failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
