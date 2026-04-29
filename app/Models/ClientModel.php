<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientModel extends Model
{
    use HasFactory;

    // Optional constant, good for consistency
    const TABLE = 'clients';

    protected $table = self::TABLE;

    // Fillable fields allow mass-assignment
    protected $fillable = [
        // Basic Info
        'cleint_name',
        'mobile_no',
        'email_id',

        // Bank Details
        'bank_account_no',
        'ifce_code',
        'bank_name_branch',

        // Identity
        'gst_in',
        'pan',
        'aadhar',
        'firm_name',

        // GST Login
        'gst_login_id',
        'gst_login_password',

        // Income Tax Login
        'income_tax_login_id',
        'income_tax_login_password',

        // E-Way Bill
        'e_way_bill_id',
        'e_way_bill_password',

        // E-Invoice
        'e_invoice_id',
        'e_invoice_password',

    ];

    // Enable created_at and updated_at
    public $timestamps = true;

    // Optional: cast registration date to Carbon
    protected $casts = [
        'gst_registration_date' => 'date',
    ];
}
