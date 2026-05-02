<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Charge;
use App\Models\Contract;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LineConversation;
use App\Models\LineMessage;
use App\Models\LineSetting;
use App\Models\MaintenanceRequest;
use App\Models\MeterReading;
use App\Models\OmiseTransaction;
use App\Models\Parcel;
use App\Models\Payment;
use App\Models\PlatformRevenue;
use App\Models\Property;
use App\Models\Rental;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtilityRate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Wipe existing data (PostgreSQL, cascade) ─────────────────────
        DB::statement('TRUNCATE TABLE
            line_messages, line_conversations, parcels,
            maintenance_requests, contracts, invoice_items, payments, omise_transactions,
            platform_revenues, invoices, meter_readings, charge_rooms, charges,
            rentals, tenants, rooms, floors, buildings, utility_rates, room_types,
            line_settings, property_users, properties, users
            RESTART IDENTITY CASCADE'
        );

        // ─── Users ────────────────────────────────────────────────────────
        $superAdmin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@dormitory.app',
            'password' => 'password',
            'role'     => 'super_admin',
        ]);

        $owner1 = User::create([
            'name'     => 'สมชาย เจ้าของหอ',
            'email'    => 'owner1@dormitory.app',
            'password' => 'password',
            'role'     => 'owner',
            'phone'    => '081-000-0001',
            'line_user_id' => 'Udemo_owner_prop1',
        ]);

        $owner2 = User::create([
            'name'     => 'สมหญิง เจ้าของหอ',
            'email'    => 'owner2@dormitory.app',
            'password' => 'password',
            'role'     => 'owner',
            'phone'    => '081-000-0002',
            'line_user_id' => 'Udemo_owner_prop2',
        ]);

        $staff1 = User::create([
            'name'     => 'วิภา พนักงาน',
            'email'    => 'staff@dormitory.app',
            'password' => 'password',
            'role'     => 'staff',
            'line_user_id' => 'Udemo_staff_prop1',
        ]);

        // ─── Properties ───────────────────────────────────────────────────
        $prop1 = Property::create([
            'owner_id'         => $owner1->id,
            'name'             => 'หอพักสุขใจ',
            'address'          => '123 ถ.รัชดา แขวงดินแดง เขตดินแดง กทม. 10400',
            'phone'            => '02-123-4567',
            'type'             => 'dormitory',
            'is_active'        => true,
            'bank_name'        => 'กสิกรไทย',
            'bank_account_name'   => 'สมชาย เจ้าของหอ',
            'bank_account_number' => '040-1-23456-7',
            'promptpay_id'     => '0810000001',
            'revenue_model'    => 'percentage',
            'revenue_percentage' => 5.00,
        ]);

        $prop2 = Property::create([
            'owner_id'         => $owner2->id,
            'name'             => 'ลลิล เรสซิเดนซ์',
            'address'          => '456 ถ.ลาดพร้าว แขวงลาดยาว เขตจตุจักร กทม. 10900',
            'phone'            => '02-987-6543',
            'type'             => 'dormitory',
            'is_active'        => true,
            'revenue_model'    => 'package',
            'revenue_package_per_room' => 50.00,
        ]);

        // Assign staff to prop1
        $staff1->properties()->attach($prop1->id);

        // LINE Settings
        LineSetting::create([
            'property_id'           => $prop1->id,
            'notify_token'          => 'demo-token-prop1',
            'oa_channel_secret'     => 'demo-channel-secret-prop1',
            'oa_channel_access_token'=> 'demo-channel-access-token-prop1',
            'admin_line_user_ids'   => ['Udemo_owner_prop1', 'Udemo_staff_prop1'],
            'notify_on_invoice'     => true,
            'notify_on_overdue'     => true,
            'notify_on_maintenance' => true,
            'notify_on_new_tenant'  => true,
            'reminder_time'         => '09:00',
        ]);

        // ─── Utility Rate ─────────────────────────────────────────────────
        UtilityRate::create([
            'electricity_rate' => 7.00,
            'water_rate'       => 18.00,
            'effective_from'   => '2024-01-01',
            'is_active'        => true,
        ]);

        // ─── Property 1: Buildings + Floors ───────────────────────────────
        $buildingA = Building::create(['property_id' => $prop1->id, 'name' => 'อาคาร A', 'is_active' => true]);
        $buildingB = Building::create(['property_id' => $prop1->id, 'name' => 'อาคาร B', 'is_active' => true]);

        foreach ([1, 2, 3] as $f) {
            Floor::create(['building_id' => $buildingA->id, 'floor_number' => $f]);
            Floor::create(['building_id' => $buildingB->id, 'floor_number' => $f]);
        }

        // ─── Room Types ───────────────────────────────────────────────────
        $single = RoomType::create(['name' => 'ห้องเดี่ยว',   'base_price' => 3500]);
        $double = RoomType::create(['name' => 'ห้องคู่',      'base_price' => 4500]);
        $suite  = RoomType::create(['name' => 'ห้องสวีท',     'base_price' => 6000]);

        // ─── Rooms ────────────────────────────────────────────────────────
        $roomData = [
            ['A101', 1, $single->id, 'occupied', $buildingA->id],
            ['A102', 1, $single->id, 'occupied', $buildingA->id],
            ['A103', 1, $double->id, 'occupied', $buildingA->id],
            ['A104', 1, $single->id, 'available', $buildingA->id],
            ['A201', 2, $single->id, 'occupied', $buildingA->id],
            ['A202', 2, $double->id, 'occupied', $buildingA->id],
            ['A203', 2, $suite->id,  'occupied', $buildingA->id],
            ['A204', 2, $single->id, 'available', $buildingA->id],
            ['A301', 3, $suite->id,  'occupied', $buildingA->id],
            ['A302', 3, $double->id, 'maintenance', $buildingA->id],
            ['B101', 1, $single->id, 'occupied', $buildingB->id],
            ['B102', 1, $single->id, 'available', $buildingB->id],
            ['B201', 2, $double->id, 'occupied', $buildingB->id],
            ['B202', 2, $double->id, 'available', $buildingB->id],
        ];

        $rooms = [];
        foreach ($roomData as [$num, $floor, $typeId, $status, $buildingId]) {
            $rooms[$num] = Room::create([
                'property_id'     => $prop1->id,
                'building_id'     => $buildingId,
                'room_number'     => $num,
                'floor'           => $floor,
                'room_type_id'    => $typeId,
                'status'          => $status,
                'electricity_type' => 'unit',
                'electricity_rate' => 7.00,
                'water_type'      => 'unit',
                'water_rate'      => 18.00,
                'has_internet'    => true,
                'internet_fee'    => 200,
            ]);
        }

        // ─── Tenants ──────────────────────────────────────────────────────
        $tenantData = [
            ['สมชาย ใจดี',      '3100100000001', '081-111-1111', 'somchai@test.com'],
            ['สมหญิง รักดี',    '3100100000002', '082-222-2222', 'somying@test.com'],
            ['มานะ ทำดี',       '3100100000003', '083-333-3333', null],
            ['วิชัย เก่งดี',    '3100100000004', '084-444-4444', null],
            ['ประเสริฐ ดีมาก',  '3100100000005', '085-555-5555', null],
            ['นิภา สวยดี',      '3100100000006', '086-666-6666', null],
            ['ธนา รวยดี',       '3100100000007', '087-777-7777', null],
            ['อนุชา ดีจริง',    '3100100000008', '088-888-8888', null],
            ['ลดาวัลย์ น่ารัก', '3100100000009', '089-999-9999', null],
        ];

        $tenants = [];
        foreach ($tenantData as [$name, $id, $phone, $email]) {
            $tenants[] = Tenant::create([
                'property_id' => $prop1->id,
                'name'        => $name,
                'id_card'     => $id,
                'phone'       => $phone,
                'email'       => $email,
            ]);
        }

        // ─── Rentals ──────────────────────────────────────────────────────
        $occupiedRooms = ['A101','A102','A103','A201','A202','A203','A301','B101','B201'];
        $rentals = [];
        foreach ($occupiedRooms as $i => $roomNum) {
            $tenant = $tenants[$i];
            $room   = $rooms[$roomNum];
            $rentals[$roomNum] = Rental::create([
                'property_id'    => $prop1->id,
                'room_id'        => $room->id,
                'tenant_id'      => $tenant->id,
                'monthly_rent'   => $room->roomType->base_price,
                'deposit_amount' => $room->roomType->base_price * 2,
                'start_date'     => Carbon::now()->subMonths(rand(3, 12))->startOfMonth(),
                'status'         => 'active',
            ]);
        }

        // ─── Contracts ────────────────────────────────────────────────────
        foreach ($rentals as $roomNum => $rental) {
            Contract::create([
                'rental_id'       => $rental->id,
                'property_id'     => $prop1->id,
                'contract_number' => 'CT-' . strtoupper(Str::random(8)),
                'start_date'      => $rental->start_date,
                'end_date'        => $rental->start_date->copy()->addYear(),
                'status'          => 'active',
                'owner_signature' => 'signed',
                'owner_signed_at' => $rental->start_date,
                'tenant_signature'=> 'signed',
                'tenant_signed_at'=> $rental->start_date,
            ]);
        }

        // ─── Meter Readings (this month) ──────────────────────────────────
        $month = now()->month;
        $year  = now()->year;

        $meterData = [
            'A101' => [1200, 1250, 50, 56],
            'A102' => [870,  920,  30, 38],
            'A103' => [2100, 2175, 80, 90],
            'A201' => [560,  610,  20, 27],
            'A202' => [1800, 1855, 60, 70],
            'A203' => [3200, 3280, 100, 112],
            'A301' => [4500, 4580, 150, 162],
            'B101' => [950,  1010, 35, 43],
            'B201' => [1400, 1470, 55, 65],
        ];

        foreach ($meterData as $roomNum => [$elPrev, $elCurr, $wPrev, $wCurr]) {
            MeterReading::create([
                'room_id'              => $rooms[$roomNum]->id,
                'month'                => $month,
                'year'                 => $year,
                'electricity_previous' => $elPrev,
                'electricity_current'  => $elCurr,
                'water_previous'       => $wPrev,
                'water_current'        => $wCurr,
            ]);
        }

        // ─── Invoices (previous month — some paid, some pending, one overdue) ──
        $prevMonth = now()->subMonth()->month;
        $prevYear  = now()->subMonth()->year;
        $rate      = UtilityRate::current();
        $invNum    = 1;

        $invoiceStatuses = ['paid', 'paid', 'paid', 'paid', 'pending', 'pending', 'overdue', 'pending', 'pending'];

        $statusIndex = 0;
        foreach ($rentals as $roomNum => $rental) {
            $room    = $rooms[$roomNum];
            $elUnits = rand(40, 120);
            $wUnits  = rand(5, 20);
            $elCharge = $elUnits * $rate->electricity_rate;
            $wCharge  = $wUnits  * $rate->water_rate;
            $total    = $rental->monthly_rent + $elCharge + $wCharge + 200; // +200 internet

            $status    = $invoiceStatuses[$statusIndex++] ?? 'pending';
            $invoiceNo = sprintf('INV-%d%02d-%04d', $prevYear, $prevMonth, $invNum++);

            Invoice::create([
                'property_id'        => $prop1->id,
                'invoice_number'     => $invoiceNo,
                'rental_id'          => $rental->id,
                'month'              => $prevMonth,
                'year'               => $prevYear,
                'due_date'           => Carbon::create($prevYear, $prevMonth, 1)->addMonth()->day(5),
                'room_charge'        => $rental->monthly_rent,
                'electricity_units'  => $elUnits,
                'electricity_rate'   => $rate->electricity_rate,
                'electricity_charge' => $elCharge,
                'water_units'        => $wUnits,
                'water_rate'         => $rate->water_rate,
                'water_charge'       => $wCharge,
                'other_charge'       => 200,
                'total_amount'       => $total,
                'status'             => $status,
                'paid_at'            => $status === 'paid' ? Carbon::create($prevYear, $prevMonth, rand(5, 25)) : null,
            ]);
        }

        // ─── Maintenance Requests ─────────────────────────────────────────
        $maintenanceData = [
            ['A101', $tenants[0], 'ก๊อกน้ำในห้องน้ำรั่ว',   'plumbing', 'high',   'pending'],
            ['A202', $tenants[4], 'แอร์ไม่เย็น',             'general',  'normal', 'in_progress'],
            ['A301', $tenants[6], 'หลอดไฟในห้องดับ 2 ดวง',  'electrical','normal','done'],
            ['B101', $tenants[7], 'ประตูห้องน้ำปิดไม่สนิท', 'furniture', 'low',   'pending'],
            ['A103', $tenants[2], 'น้ำไม่ไหล',               'plumbing', 'urgent', 'in_progress'],
        ];

        foreach ($maintenanceData as [$roomNum, $tenant, $title, $cat, $prio, $status]) {
            MaintenanceRequest::create([
                'request_number' => 'MR-' . strtoupper(Str::random(8)),
                'property_id'    => $prop1->id,
                'room_id'        => $rooms[$roomNum]->id,
                'tenant_id'      => $tenant->id,
                'title'          => $title,
                'description'    => "ผู้เช่าแจ้งว่า: {$title} กรุณาดำเนินการโดยด่วน",
                'category'       => $cat,
                'priority'       => $prio,
                'status'         => $status,
                'resolved_at'    => $status === 'done' ? now()->subDays(1) : null,
            ]);
        }

        // ─── Charges / Invoice items / Payments / Revenue for Property 1 ──
        $internetCharge = Charge::create([
            'property_id' => $prop1->id,
            'name'        => 'ค่าอินเทอร์เน็ต',
            'amount'      => 200,
            'type'        => 'monthly',
            'description' => 'อินเทอร์เน็ตไร้สายรายเดือน',
            'is_active'   => true,
        ]);

        $cleaningCharge = Charge::create([
            'property_id' => $prop1->id,
            'name'        => 'ค่าทำความสะอาดส่วนกลาง',
            'amount'      => 120,
            'type'        => 'monthly',
            'description' => 'ดูแลพื้นที่ส่วนกลางและทางเดิน',
            'is_active'   => true,
        ]);

        $keycardCharge = Charge::create([
            'property_id' => $prop1->id,
            'name'        => 'ค่าคีย์การ์ดสำรอง',
            'amount'      => 150,
            'type'        => 'one_time',
            'description' => 'สำหรับออกบัตรใหม่หรือบัตรสำรอง',
            'is_active'   => true,
        ]);

        foreach ($occupiedRooms as $roomNum) {
            $internetCharge->rooms()->attach($rooms[$roomNum]->id, ['active_from' => now()->subMonths(6)->toDateString()]);
            $cleaningCharge->rooms()->attach($rooms[$roomNum]->id, ['active_from' => now()->subMonths(6)->toDateString()]);
        }
        $keycardCharge->rooms()->attach($rooms['A103']->id, ['active_from' => now()->subMonth()->toDateString()]);

        foreach (Invoice::where('property_id', $prop1->id)->get() as $invoice) {
            InvoiceItem::insert([
                [
                    'invoice_id'   => $invoice->id,
                    'description'  => 'ค่าเช่าห้อง',
                    'quantity'     => 1,
                    'unit_price'   => $invoice->room_charge,
                    'amount'       => $invoice->room_charge,
                    'sort_order'   => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
                [
                    'invoice_id'   => $invoice->id,
                    'description'  => 'ค่าไฟฟ้า',
                    'quantity'     => $invoice->electricity_units,
                    'unit_price'   => $invoice->electricity_rate,
                    'amount'       => $invoice->electricity_charge,
                    'sort_order'   => 2,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
                [
                    'invoice_id'   => $invoice->id,
                    'description'  => 'ค่าน้ำ',
                    'quantity'     => $invoice->water_units,
                    'unit_price'   => $invoice->water_rate,
                    'amount'       => $invoice->water_charge,
                    'sort_order'   => 3,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
                [
                    'invoice_id'   => $invoice->id,
                    'description'  => 'ค่าอินเทอร์เน็ต',
                    'quantity'     => 1,
                    'unit_price'   => $invoice->other_charge,
                    'amount'       => $invoice->other_charge,
                    'sort_order'   => 4,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ],
            ]);

            if ($invoice->status === 'paid') {
                Payment::create([
                    'invoice_id'        => $invoice->id,
                    'amount'            => $invoice->total_amount,
                    'payment_date'      => $invoice->paid_at?->toDateString() ?? now()->toDateString(),
                    'payment_method'    => 'transfer',
                    'reference_number'  => 'TRF' . now()->format('ym') . str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT),
                    'note'              => 'ข้อมูลตัวอย่าง: โอนผ่านบัญชีธนาคาร',
                ]);

                $omise = OmiseTransaction::create([
                    'invoice_id'       => $invoice->id,
                    'property_id'      => $prop1->id,
                    'omise_charge_id'  => 'chrg_demo_' . Str::lower(Str::random(12)),
                    'payment_method'   => 'promptpay',
                    'amount'           => (int) round($invoice->total_amount * 100),
                    'currency'         => 'thb',
                    'status'           => 'successful',
                    'metadata'         => ['invoice_number' => $invoice->invoice_number],
                    'charged_at'       => $invoice->paid_at ?? now(),
                ]);

                $fee = round($invoice->total_amount * ($prop1->revenue_percentage / 100), 2);
                PlatformRevenue::create([
                    'property_id'          => $prop1->id,
                    'omise_transaction_id' => $omise->id,
                    'invoice_id'           => $invoice->id,
                    'type'                 => 'percentage_fee',
                    'payment_channel'      => 'online',
                    'billing_month'        => $invoice->month,
                    'billing_year'         => $invoice->year,
                    'gross_amount'         => $invoice->total_amount,
                    'fee_amount'           => $fee,
                    'net_amount'           => $invoice->total_amount - $fee,
                    'status'               => $invoice->id % 2 === 0 ? 'transferred' : 'pending',
                    'transferred_at'       => $invoice->id % 2 === 0 ? now()->subDays(5) : null,
                    'transfer_ref'         => $invoice->id % 2 === 0 ? 'PAYOUT-' . str_pad((string) $invoice->id, 5, '0', STR_PAD_LEFT) : null,
                ]);
            }
        }

        // ─── Parcels / LINE chat for Property 1 ──────────────────────────
        $parcelSeed = [
            ['A101', $tenants[0], 'parcel', 'Shopee', 'Flash', 'TH' . rand(100000000, 999999999), 'notified', now()->subHours(4)],
            ['A202', $tenants[4], 'letter', 'ธนาคารกสิกรไทย', 'ไปรษณีย์ไทย', null, 'waiting', now()->subDay()],
            ['B101', $tenants[7], 'food', 'GrabFood', 'Grab', null, 'collected', now()->subDays(2)],
            ['A301', $tenants[6], 'document', 'บริษัทประกัน', 'EMS', 'EE' . rand(100000000, 999999999) . 'TH', 'returned', now()->subDays(5)],
        ];

        foreach ($parcelSeed as [$roomNum, $tenant, $type, $sender, $carrier, $tracking, $status, $receivedAt]) {
            Parcel::create([
                'parcel_number'   => 'PKG-' . strtoupper(Str::random(8)),
                'property_id'     => $prop1->id,
                'room_id'         => $rooms[$roomNum]->id,
                'tenant_id'       => $tenant->id,
                'type'            => $type,
                'sender'          => $sender,
                'carrier'         => $carrier,
                'tracking_number' => $tracking,
                'status'          => $status,
                'received_by'     => $staff1->name,
                'received_at'     => $receivedAt,
                'notified_at'     => in_array($status, ['notified', 'collected', 'returned'], true) ? $receivedAt->copy()->addMinutes(8) : null,
                'collected_at'    => $status === 'collected' ? $receivedAt->copy()->addHours(2) : null,
                'note'            => 'ข้อมูลตัวอย่างสำหรับทดสอบตารางพัสดุ',
            ]);
        }

        foreach ([$tenants[0], $tenants[3], $tenants[6]] as $i => $tenant) {
            $conv = LineConversation::create([
                'property_id'     => $prop1->id,
                'line_user_id'    => 'Udemo_prop1_' . ($i + 1),
                'display_name'    => $tenant->name,
                'tenant_id'       => $tenant->id,
                'last_message_at' => now()->subMinutes(30 + ($i * 70)),
                'has_unread'      => $i === 0,
            ]);

            LineMessage::create([
                'conversation_id'  => $conv->id,
                'line_message_id'  => 'line_' . Str::lower(Str::random(12)),
                'direction'        => 'inbound',
                'type'             => 'text',
                'content'          => ['ขอแจ้งซ่อมแอร์ครับ', 'รับพัสดุได้ถึงกี่โมงคะ', 'ขอใบเสร็จค่าเช่าเดือนก่อนครับ'][$i],
                'created_at'       => now()->subMinutes(40 + ($i * 70)),
                'updated_at'       => now()->subMinutes(40 + ($i * 70)),
            ]);

            LineMessage::create([
                'conversation_id'  => $conv->id,
                'direction'        => 'outbound',
                'type'             => 'text',
                'content'          => 'รับทราบครับ ทีมงานจะตรวจสอบและแจ้งกลับอีกครั้ง',
                'sent_by_user_id'  => $staff1->id,
                'created_at'       => now()->subMinutes(30 + ($i * 70)),
                'updated_at'       => now()->subMinutes(30 + ($i * 70)),
            ]);
        }

        // ─── Property 2 mock data, so switching property never shows empty tables ──
        LineSetting::create([
            'property_id'           => $prop2->id,
            'notify_token'          => 'demo-token-prop2',
            'oa_channel_secret'     => 'demo-channel-secret-prop2',
            'oa_channel_access_token'=> 'demo-channel-access-token-prop2',
            'admin_line_user_ids'   => ['Udemo_owner_prop2'],
            'notify_on_invoice'     => true,
            'notify_on_overdue'     => true,
            'notify_on_maintenance' => true,
            'notify_on_new_tenant'  => false,
            'reminder_time'         => '10:00',
        ]);

        $prop2Building = Building::create(['property_id' => $prop2->id, 'name' => 'อาคารหลัก', 'is_active' => true]);
        foreach ([1, 2] as $f) {
            Floor::create(['building_id' => $prop2Building->id, 'floor_number' => $f]);
        }

        $prop2Rooms = [];
        foreach ([
            ['101', 1, $single->id, 'occupied'],
            ['102', 1, $double->id, 'occupied'],
            ['103', 1, $single->id, 'available'],
            ['201', 2, $suite->id, 'occupied'],
            ['202', 2, $double->id, 'maintenance'],
            ['203', 2, $single->id, 'available'],
        ] as [$num, $floor, $typeId, $status]) {
            $prop2Rooms[$num] = Room::create([
                'property_id'     => $prop2->id,
                'building_id'     => $prop2Building->id,
                'room_number'     => $num,
                'floor'           => $floor,
                'room_type_id'    => $typeId,
                'status'          => $status,
                'electricity_type' => 'unit',
                'electricity_rate' => 7.50,
                'water_type'      => 'unit',
                'water_rate'      => 20.00,
                'has_internet'    => true,
                'internet_fee'    => 250,
            ]);
        }

        $prop2Tenants = [];
        foreach ([
            ['กานต์ วิวดี', '091-111-2222', 'Utenant_p2_1'],
            ['พราว ฟ้าใส', '092-222-3333', 'Utenant_p2_2'],
            ['นที ใจเย็น', '093-333-4444', 'Utenant_p2_3'],
        ] as $i => [$name, $phone, $lineId]) {
            $prop2Tenants[$i] = Tenant::create([
                'property_id'  => $prop2->id,
                'name'         => $name,
                'id_card'      => '320010000000' . ($i + 1),
                'phone'        => $phone,
                'email'        => 'tenant-p2-' . ($i + 1) . '@example.test',
                'line_user_id' => $lineId,
            ]);
        }

        $prop2Rentals = [];
        foreach (['101', '102', '201'] as $i => $roomNum) {
            $room = $prop2Rooms[$roomNum];
            $rental = Rental::create([
                'property_id'    => $prop2->id,
                'room_id'        => $room->id,
                'tenant_id'      => $prop2Tenants[$i]->id,
                'monthly_rent'   => $room->roomType->base_price,
                'deposit_amount' => $room->roomType->base_price * 2,
                'start_date'     => now()->subMonths(2 + $i)->startOfMonth(),
                'status'         => 'active',
            ]);
            $prop2Rentals[$roomNum] = $rental;

            Contract::create([
                'rental_id'       => $rental->id,
                'property_id'     => $prop2->id,
                'contract_number' => 'CT-' . strtoupper(Str::random(8)),
                'start_date'      => $rental->start_date,
                'end_date'        => $rental->start_date->copy()->addYear(),
                'status'          => 'active',
                'owner_signature' => 'signed',
                'owner_signed_at' => $rental->start_date,
                'tenant_signature'=> 'signed',
                'tenant_signed_at'=> $rental->start_date,
            ]);

            MeterReading::create([
                'room_id'              => $room->id,
                'month'                => $month,
                'year'                 => $year,
                'electricity_previous' => 300 + ($i * 250),
                'electricity_current'  => 350 + ($i * 275),
                'water_previous'       => 18 + ($i * 12),
                'water_current'        => 25 + ($i * 14),
            ]);

            $elUnits = 45 + ($i * 12);
            $wUnits = 7 + ($i * 3);
            $elCharge = $elUnits * 7.5;
            $wCharge = $wUnits * 20;
            $other = 250;
            $total = $rental->monthly_rent + $elCharge + $wCharge + $other;
            $status = ['paid', 'pending', 'overdue'][$i];

            $invoice = Invoice::create([
                'property_id'        => $prop2->id,
                'invoice_number'     => sprintf('INV-P2-%d%02d-%04d', $prevYear, $prevMonth, $i + 1),
                'rental_id'          => $rental->id,
                'month'              => $prevMonth,
                'year'               => $prevYear,
                'due_date'           => Carbon::create($prevYear, $prevMonth, 1)->addMonth()->day(5),
                'room_charge'        => $rental->monthly_rent,
                'electricity_units'  => $elUnits,
                'electricity_rate'   => 7.5,
                'electricity_charge' => $elCharge,
                'water_units'        => $wUnits,
                'water_rate'         => 20,
                'water_charge'       => $wCharge,
                'other_charge'       => $other,
                'total_amount'       => $total,
                'status'             => $status,
                'paid_at'            => $status === 'paid' ? now()->subDays(12) : null,
            ]);

            InvoiceItem::insert([
                ['invoice_id' => $invoice->id, 'description' => 'ค่าเช่าห้อง', 'quantity' => 1, 'unit_price' => $invoice->room_charge, 'amount' => $invoice->room_charge, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['invoice_id' => $invoice->id, 'description' => 'ค่าไฟฟ้า', 'quantity' => $invoice->electricity_units, 'unit_price' => $invoice->electricity_rate, 'amount' => $invoice->electricity_charge, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['invoice_id' => $invoice->id, 'description' => 'ค่าน้ำ', 'quantity' => $invoice->water_units, 'unit_price' => $invoice->water_rate, 'amount' => $invoice->water_charge, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['invoice_id' => $invoice->id, 'description' => 'ค่าอินเทอร์เน็ต', 'quantity' => 1, 'unit_price' => $invoice->other_charge, 'amount' => $invoice->other_charge, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ]);

            if ($status === 'paid') {
                $payment = Payment::create([
                    'invoice_id'       => $invoice->id,
                    'amount'           => $invoice->total_amount,
                    'payment_date'     => now()->subDays(12)->toDateString(),
                    'payment_method'   => 'cash',
                    'reference_number' => 'CASH-P2-' . str_pad((string) $invoice->id, 4, '0', STR_PAD_LEFT),
                    'note'             => 'ข้อมูลตัวอย่าง: ชำระเงินสดที่สำนักงาน',
                ]);

                PlatformRevenue::firstOrCreate(
                    [
                        'property_id'    => $prop2->id,
                        'type'           => 'package_fee',
                        'billing_month'  => $invoice->month,
                        'billing_year'   => $invoice->year,
                    ],
                    [
                        'invoice_id'      => $invoice->id,
                        'payment_id'      => $payment->id,
                        'payment_channel' => 'cash',
                        'gross_amount'    => 0,
                        'fee_amount'      => $prop2->revenue_package_per_room * $prop2->rooms()->count(),
                        'net_amount'      => 0,
                        'status'          => 'unpaid',
                        'note'            => 'แพ็กเกจรายเดือนจากข้อมูลตัวอย่าง: รับเงินผู้เช่าเป็นเงินสด',
                    ]
                );
            }
        }

        $prop2Internet = Charge::create([
            'property_id' => $prop2->id,
            'name'        => 'ค่าอินเทอร์เน็ตพรีเมียม',
            'amount'      => 250,
            'type'        => 'monthly',
            'description' => 'แพ็กเกจอินเทอร์เน็ตสำหรับทุกห้อง',
            'is_active'   => true,
        ]);
        foreach (['101', '102', '201'] as $roomNum) {
            $prop2Internet->rooms()->attach($prop2Rooms[$roomNum]->id, ['active_from' => now()->subMonths(3)->toDateString()]);
        }

        MaintenanceRequest::create([
            'request_number' => 'MR-' . strtoupper(Str::random(8)),
            'property_id'    => $prop2->id,
            'room_id'        => $prop2Rooms['102']->id,
            'tenant_id'      => $prop2Tenants[1]->id,
            'title'          => 'เครื่องทำน้ำอุ่นไม่ทำงาน',
            'description'    => 'ผู้เช่าแจ้งว่าน้ำไม่ร้อนตั้งแต่เมื่อคืน',
            'category'       => 'electrical',
            'priority'       => 'high',
            'status'         => 'pending',
        ]);

        Parcel::create([
            'parcel_number'   => 'PKG-' . strtoupper(Str::random(8)),
            'property_id'     => $prop2->id,
            'room_id'         => $prop2Rooms['201']->id,
            'tenant_id'       => $prop2Tenants[2]->id,
            'type'            => 'parcel',
            'sender'          => 'Lazada',
            'carrier'         => 'Kerry',
            'tracking_number' => 'KR' . rand(100000000, 999999999),
            'status'          => 'waiting',
            'received_by'     => $owner2->name,
            'received_at'     => now()->subHours(2),
            'note'            => 'กล่องขนาดกลาง',
        ]);

        $prop2Conv = LineConversation::create([
            'property_id'     => $prop2->id,
            'line_user_id'    => 'Udemo_prop2_1',
            'display_name'    => $prop2Tenants[1]->name,
            'tenant_id'       => $prop2Tenants[1]->id,
            'last_message_at' => now()->subMinutes(18),
            'has_unread'      => true,
        ]);
        LineMessage::create([
            'conversation_id' => $prop2Conv->id,
            'line_message_id' => 'line_' . Str::lower(Str::random(12)),
            'direction'       => 'inbound',
            'type'            => 'text',
            'content'         => 'วันนี้มีช่างเข้ามากี่โมงคะ',
            'created_at'      => now()->subMinutes(18),
            'updated_at'      => now()->subMinutes(18),
        ]);

        $this->command->info('✅ Seeded successfully!');
        $this->command->table(['Role', 'Email', 'Password'], [
            ['Super Admin', 'admin@dormitory.app', 'password'],
            ['Owner 1',     'owner1@dormitory.app', 'password'],
            ['Owner 2',     'owner2@dormitory.app', 'password'],
            ['Staff',       'staff@dormitory.app',  'password'],
        ]);
    }
}
