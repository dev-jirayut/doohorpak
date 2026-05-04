<?php

namespace App\Http\Controllers;

use App\Models\LineSetting;
use App\Models\Property;
use App\Models\UtilityRate;
use App\Services\LineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function rates(Request $request)
    {
        $rates   = UtilityRate::orderByDesc('effective_from')->paginate(10);
        $current = UtilityRate::current();
        return view('settings.rates', compact('rates', 'current'));
    }

    public function storeRate(Request $request)
    {
        $request->validate([
            'electricity_rate' => 'required|numeric|min:0',
            'water_rate'       => 'required|numeric|min:0',
            'effective_from'   => 'required|date',
        ]);

        UtilityRate::where('is_active', true)->update(['is_active' => false]);

        UtilityRate::create([
            'electricity_rate' => $request->electricity_rate,
            'water_rate'       => $request->water_rate,
            'effective_from'   => $request->effective_from,
            'is_active'        => true,
        ]);

        return redirect()->route('settings.rates')->with('success', 'บันทึกอัตราค่าสาธารณูปโภคสำเร็จ');
    }

    public function line(Request $request)
    {
        $property = $request->get('current_property');
        $properties = $this->accessibleProperties($request);
        $setting  = $property ? LineSetting::firstOrNew(['property_id' => $property->id]) : null;
        return view('settings.line', compact('setting', 'property', 'properties'));
    }

    public function storeLine(Request $request)
    {
        $property = $request->get('current_property');

        if (! $property) {
            $request->validate([
                'property_id' => ['required', Rule::exists('properties', 'id')],
            ]);

            $property = Property::findOrFail($request->property_id);
            abort_unless($request->user()->canAccessProperty($property->id), 403);
        }

        $data = $request->validate([
            'notify_token'             => 'nullable|string',
            'oa_channel_secret'        => 'nullable|string',
            'oa_channel_access_token'  => 'nullable|string',
            'webhook_url'              => 'nullable|url|max:500',
            'admin_line_user_ids'      => 'nullable|string',
            'notify_on_invoice'        => 'boolean',
            'notify_on_overdue'        => 'boolean',
            'notify_on_maintenance'    => 'boolean',
            'notify_on_new_tenant'     => 'boolean',
            'reminder_time'            => 'required|date_format:H:i',
        ]);

        $payload = [
            'admin_line_user_ids'   => collect(preg_split('/\R+/', (string) $request->input('admin_line_user_ids')))
                ->map(fn ($id) => trim($id))
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'notify_on_invoice'     => $request->boolean('notify_on_invoice'),
            'notify_on_overdue'     => $request->boolean('notify_on_overdue'),
            'notify_on_maintenance' => $request->boolean('notify_on_maintenance'),
            'notify_on_new_tenant'  => $request->boolean('notify_on_new_tenant'),
            'webhook_url'           => $data['webhook_url'] ?? route('webhooks.line', $property->id),
            'reminder_time'         => $data['reminder_time'],
        ];

        foreach (['notify_token', 'oa_channel_secret', 'oa_channel_access_token'] as $secretField) {
            if (filled($data[$secretField] ?? null)) {
                $payload[$secretField] = $data[$secretField];
            }
        }

        LineSetting::updateOrCreate(['property_id' => $property->id], $payload);

        return back()->with('success', 'บันทึกการตั้งค่า LINE สำเร็จ');
    }

    private function accessibleProperties(Request $request)
    {
        $user = $request->user();

        return match (true) {
            $user->isSuperAdmin() => Property::where('is_active', true)->orderBy('name')->get(),
            $user->isOwner()      => $user->ownedProperties()->where('is_active', true)->orderBy('name')->get(),
            default               => $user->properties()->where('is_active', true)->orderBy('name')->get(),
        };
    }

    public function richMenu(Request $request)
    {
        $property = $request->get('current_property');
        abort_unless($property, 404);

        $setting = LineSetting::firstOrNew(['property_id' => $property->id]);
        $defaultActions = $this->defaultRichMenuActions($property);

        return view('settings.line-rich-menu', compact('setting', 'defaultActions'));
    }

    public function storeRichMenu(Request $request, LineService $line)
    {
        $property = $request->get('current_property');
        abort_unless($property, 404);

        $data = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:1024',
            'name' => 'required|string|max:300',
            'chat_bar_text' => 'required|string|max:14',
            'invoice' => 'required|string|max:300',
            'parcel' => 'required|string|max:300',
            'maintenance' => 'required|string|max:300',
            'contract' => 'required|string|max:300',
            'contact' => 'required|string|max:300',
        ]);

        try {
            $richMenuId = $line->createRichMenu($property, $request->file('image'), $data);
        } catch (\Throwable $e) {
            Log::error("LINE rich menu create error: {$e->getMessage()}");
            return back()->withInput()->with('error', 'สร้าง Rich Menu ไม่สำเร็จ: ' . $e->getMessage());
        }

        $imagePath = $request->file('image')->store('line-rich-menus', config('filesystems.default'));

        LineSetting::updateOrCreate(
            ['property_id' => $property->id],
            [
                'rich_menu_id' => $richMenuId,
                'rich_menu_image_path' => $imagePath,
                'rich_menu_actions' => collect($data)->except('image')->all(),
                'rich_menu_created_at' => now(),
            ]
        );

        return back()->with('success', 'สร้าง Rich Menu และตั้งเป็นเมนูหลักของ LINE OA เรียบร้อย');
    }

    private function defaultRichMenuActions(Property $property): array
    {
        return [
            'name' => 'Dormitory tenant rich menu',
            'chat_bar_text' => 'หอพักของคุณ',
            'invoice' => 'ตรวจสอบใบแจ้งหนี้',
            'parcel' => 'นัดรับพัสดุ',
            'maintenance' => 'แจ้งซ่อม',
            'contract' => 'ดูสัญญาหอ',
            'contact' => 'ติดต่อหอพัก',
        ];
    }

    public function payment(Request $request)
    {
        $property = $request->get('current_property');
        return view('settings.payment', compact('property'));
    }

    public function storePayment(Request $request)
    {
        $property = $request->get('current_property');
        abort_unless($property, 404);

        $data = $request->validate([
            'bank_account_name'        => 'nullable|string|max:100',
            'bank_account_number'      => 'nullable|string|max:30',
            'bank_name'                => 'nullable|string|max:50',
            'promptpay_id'             => 'nullable|string|max:20',
            'revenue_model'            => 'required|in:percentage,package',
            'revenue_percentage'       => 'required_if:revenue_model,percentage|nullable|numeric|min:0|max:100',
            'revenue_package_per_room' => 'required_if:revenue_model,package|nullable|numeric|min:0',
        ]);

        $property->update($data);

        return back()->with('success', 'บันทึกการตั้งค่าการชำระเงินสำเร็จ');
    }
}
