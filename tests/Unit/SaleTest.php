<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Sale;
use App\Models\User;
use App\Models\PlantingLocation;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

/**
 * Unit Test untuk Model Sale
 * 
 * Test ini menguji semua method dan relasi yang ada di model Sale
 */
class SaleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Membuat sale baru dengan field sesuai input
     * 
     * Menguji bahwa sale dapat dibuat dengan semua field yang diisi
     */
    public function test_can_create_sale_with_all_fields(): void
    {
        // Menyiapkan data user dan planting location
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_bbi',
        ]);

        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Test',
        ]);

        // Menyiapkan data sale untuk diuji
        $saleData = [
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'buyer_name' => 'Pembeli Test',
            'buyer_contact' => '081234567890',
            'planting_location_id' => $plantingLocation->id,
            'total_amount' => 1000000,
            'payment_method' => 'cash',
            'payment_status' => 'lunas',
            'user_id' => $user->id,
        ];

        // Membuat sale baru
        $sale = Sale::create($saleData);

        // Memverifikasi bahwa sale berhasil dibuat dengan field sesuai input
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'receipt_number' => 'PJ-2024-001',
            'buyer_name' => 'Pembeli Test',
            'total_amount' => 1000000,
        ]);
    }

    /**
     * Test: Relasi user mengembalikan user yang membuat sale
     * 
     * Menguji bahwa relasi belongs-to antara sale dan user berfungsi
     */
    public function test_user_relationship(): void
    {
        // Membuat user dan sale
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'petugas_bbi',
        ]);

        $sale = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'total_amount' => 1000000,
            'user_id' => $user->id,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($user->id, $sale->user->id);
    }

    /**
     * Test: Relasi plantingLocation mengembalikan planting location tempat penjualan
     * 
     * Menguji bahwa relasi belongs-to antara sale dan planting location berfungsi
     */
    public function test_planting_location_relationship(): void
    {
        // Membuat planting location dan sale
        $plantingLocation = PlantingLocation::create([
            'name' => 'Lahan Test',
        ]);

        $sale = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'planting_location_id' => $plantingLocation->id,
            'total_amount' => 1000000,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertEquals($plantingLocation->id, $sale->plantingLocation->id);
    }

    /**
     * Test: Relasi items mengembalikan semua item dalam sale
     * 
     * Menguji bahwa relasi one-to-many antara sale dan sale item berfungsi
     */
    public function test_items_relationship(): void
    {
        // Membuat sale dan beberapa items
        $sale = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'total_amount' => 1000000,
        ]);

        $item1 = SaleItem::create([
            'sale_id' => $sale->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);

        $item2 = SaleItem::create([
            'sale_id' => $sale->id,
            'quantity' => 20,
            'unit_price' => 25000,
            'subtotal' => 500000,
        ]);

        // Memverifikasi bahwa relasi berfungsi dengan benar
        $this->assertTrue($sale->items->contains($item1));
        $this->assertTrue($sale->items->contains($item2));
        $this->assertEquals(2, $sale->items->count());
    }

    /**
     * Test: Method getPaymentMethodLabel mengembalikan label metode pembayaran
     * 
     * Menguji bahwa setiap metode pembayaran memiliki label yang sesuai
     */
    public function test_get_payment_method_label_returns_label(): void
    {
        // Membuat sale dengan payment method cash
        $sale1 = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'payment_method' => 'cash',
            'total_amount' => 1000000,
        ]);
        $this->assertEquals('Cash', $sale1->payment_method_label);

        // Membuat sale dengan payment method transfer_bank
        $sale2 = Sale::create([
            'receipt_number' => 'PJ-2024-002',
            'sale_date' => Carbon::now(),
            'payment_method' => 'transfer_bank',
            'total_amount' => 2000000,
        ]);
        $this->assertEquals('Transfer Bank', $sale2->payment_method_label);
    }

    /**
     * Test: Method getPaymentStatusLabel mengembalikan label status pembayaran
     * 
     * Menguji bahwa setiap status pembayaran memiliki label yang sesuai
     */
    public function test_get_payment_status_label_returns_label(): void
    {
        // Membuat sale dengan payment status lunas
        $sale1 = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'payment_status' => 'lunas',
            'total_amount' => 1000000,
        ]);
        $this->assertEquals('LUNAS', $sale1->payment_status_label);

        // Membuat sale dengan payment status belum_lunas
        $sale2 = Sale::create([
            'receipt_number' => 'PJ-2024-002',
            'sale_date' => Carbon::now(),
            'payment_status' => 'belum_lunas',
            'total_amount' => 2000000,
        ]);
        $this->assertEquals('BELUM LUNAS', $sale2->payment_status_label);
    }

    /**
     * Test: Method getPaymentStatusColor mengembalikan warna badge untuk status pembayaran
     * 
     * Menguji bahwa setiap status pembayaran memiliki warna badge yang sesuai
     */
    public function test_get_payment_status_color_returns_color(): void
    {
        // Membuat sale dengan payment status lunas
        $sale1 = Sale::create([
            'receipt_number' => 'PJ-2024-001',
            'sale_date' => Carbon::now(),
            'payment_status' => 'lunas',
            'total_amount' => 1000000,
        ]);
        $this->assertEquals('success', $sale1->payment_status_color);

        // Membuat sale dengan payment status belum_lunas
        $sale2 = Sale::create([
            'receipt_number' => 'PJ-2024-002',
            'sale_date' => Carbon::now(),
            'payment_status' => 'belum_lunas',
            'total_amount' => 2000000,
        ]);
        $this->assertEquals('warning', $sale2->payment_status_color);
    }

    /**
     * Test: Method generateReceiptNumber menghasilkan nomor receipt baru
     * 
     * Menguji bahwa method dapat menghasilkan nomor receipt dengan format yang benar
     */
    public function test_generate_receipt_number_returns_new_receipt_number(): void
    {
        // Memanggil method static generateReceiptNumber
        $receiptNumber = Sale::generateReceiptNumber();

        // Memverifikasi bahwa nomor receipt memiliki format yang benar
        $this->assertStringStartsWith('PJ-', $receiptNumber);
        $this->assertStringContainsString(date('Y'), $receiptNumber);
    }

    /**
     * Test: Method generateReceiptNumber menghasilkan nomor receipt yang increment
     * 
     * Menguji bahwa method dapat menghasilkan nomor receipt yang berurutan
     */
    public function test_generate_receipt_number_increments_correctly(): void
    {
        // Membuat sale pertama untuk tahun ini
        $sale1 = Sale::create([
            'receipt_number' => 'PJ-' . date('Y') . '-001',
            'sale_date' => Carbon::now(),
            'total_amount' => 1000000,
        ]);

        // Memanggil method generateReceiptNumber
        $receiptNumber = Sale::generateReceiptNumber();

        // Memverifikasi bahwa nomor receipt adalah increment dari yang terakhir
        $this->assertStringContainsString('002', $receiptNumber);
    }
}








