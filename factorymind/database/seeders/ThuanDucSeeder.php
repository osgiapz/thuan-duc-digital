<?php

namespace Database\Seeders;

use App\Models\MasterData\Bom;
use App\Models\MasterData\BomItem;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Product;
use App\Models\MasterData\ProductCategory;
use App\Models\MasterData\Routing;
use App\Models\MasterData\RoutingStep;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\WarehouseBin;
use App\Models\MasterData\WarehouseRack;
use App\Models\MasterData\WarehouseZone;
use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\Organization\Machine;
use App\Models\Organization\MachineCategory;
use App\Models\Organization\Plant;
use App\Models\Organization\ProductionLine;
use App\Models\Organization\Workshop;
use App\Models\User;
use App\Models\UserWorkContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ThuanDucSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $company = $this->seedCompany();
        $nm4     = $this->seedPlant($company);
        $this->seedOrgStructure($company, $nm4);
        $this->seedUsers($company, $nm4);
        $this->seedMasterData($company, $nm4);
    }

    // ─── Roles & Permissions ─────────────────────────────────────────────
    private function seedRolesAndPermissions(): void
    {
        $roles = [
            'super_admin',
            'group_executive',
            'company_director',
            'plant_manager',
            'production_manager',
            'quality_manager',
            'warehouse_manager',
            'finance_director',
            'hr_manager',
            'supervisor',
            'operator',
            'qc_inspector',
            'warehouse_staff',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $permissions = [
            'companies.view', 'companies.manage',
            'plants.view', 'plants.manage',
            'users.view', 'users.manage',
            'products.view', 'products.manage',
            'boms.view', 'boms.manage',
            'routings.view', 'routings.manage',
            'warehouses.view', 'warehouses.manage',
            'work_orders.view', 'work_orders.create', 'work_orders.approve',
            'inventory.view', 'inventory.transact',
            'qc.view', 'qc.record',
            'finance.view', 'finance.manage',
            'hr.view', 'hr.manage',
            'payroll.view', 'payroll.approve',
            'ai.view', 'ai.interact',
            'control_tower.view', 'control_tower.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        Role::findByName('super_admin')->givePermissionTo(Permission::all());
        Role::findByName('plant_manager')->givePermissionTo([
            'plants.view', 'products.view', 'boms.view',
            'work_orders.view', 'work_orders.create', 'work_orders.approve',
            'inventory.view', 'inventory.transact',
            'qc.view', 'qc.record',
            'control_tower.view',
            'ai.view', 'ai.interact',
        ]);
    }

    // ─── Company (Thuận Đức Group) ────────────────────────────────────────
    private function seedCompany(): Company
    {
        return Company::firstOrCreate(
            ['code' => 'TDG'],
            [
                'name'             => 'Công ty Cổ phần Thuận Đức',
                'legal_name'       => 'CÔNG TY CỔ PHẦN THUẬN ĐỨC',
                'tax_code'         => '0100234567',
                'company_type'     => 'group',
                'currency_code'    => 'VND',
                'fiscal_year_start' => 1,
                'status'           => 'active',
                'address'          => [
                    'street'   => '123 Đường Công Nghiệp',
                    'district' => 'Bình Dương',
                    'city'     => 'Bình Dương',
                    'country'  => 'Vietnam',
                ],
                'contact' => [
                    'phone' => '0274-3800-000',
                    'email' => 'info@thuanduc.com',
                ],
            ]
        );
    }

    // ─── Plant NM4 ────────────────────────────────────────────────────────
    private function seedPlant(Company $company): Plant
    {
        return Plant::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'NM4'],
            [
                'name'       => 'Nhà máy 4 — Tráng/In/May',
                'plant_type' => 'manufacturing',
                'status'     => 'active',
                'address'    => [
                    'street'  => 'Lô C5, KCN Thuận Đức',
                    'city'    => 'Bình Dương',
                    'country' => 'Vietnam',
                ],
                'coordinates' => ['lat' => 10.9805, 'lng' => 106.6549],
            ]
        );
    }

    // ─── Organization Structure ───────────────────────────────────────────
    private function seedOrgStructure(Company $company, Plant $nm4): void
    {
        // Departments
        $deptProd = Department::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'SX-NM4'],
            ['name' => 'Phòng Sản xuất NM4', 'plant_id' => $nm4->id, 'status' => 'active']
        );
        $deptQc = Department::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'QC-NM4'],
            ['name' => 'Phòng QC NM4', 'plant_id' => $nm4->id, 'status' => 'active']
        );
        $deptKho = Department::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'KHO-NM4'],
            ['name' => 'Phòng Kho NM4', 'plant_id' => $nm4->id, 'status' => 'active']
        );

        // Workshops
        $wsTrang = Workshop::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'WS-TRANG'],
            ['company_id' => $company->id, 'department_id' => $deptProd->id, 'name' => 'Phân xưởng Tráng', 'status' => 'active']
        );
        $wsIn = Workshop::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'WS-IN'],
            ['company_id' => $company->id, 'department_id' => $deptProd->id, 'name' => 'Phân xưởng In', 'status' => 'active']
        );
        $wsMay = Workshop::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'WS-MAY'],
            ['company_id' => $company->id, 'department_id' => $deptProd->id, 'name' => 'Phân xưởng May', 'status' => 'active']
        );

        // Machine categories
        $catTrang = MachineCategory::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'TRANG'],
            ['name' => 'Máy Tráng']
        );
        $catIn = MachineCategory::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'MAY_IN'],
            ['name' => 'Máy In']
        );
        $catMay = MachineCategory::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'MAY_KHAU'],
            ['name' => 'Máy Khâu/May']
        );

        // Lines
        $lineTrang1 = ProductionLine::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'LINE-TRANG-1'],
            ['company_id' => $company->id, 'workshop_id' => $wsTrang->id, 'name' => 'Dây chuyền Tráng 1', 'capacity_per_hour' => 500, 'capacity_uom' => 'tấm', 'status' => 'active']
        );
        $lineIn1 = ProductionLine::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'LINE-IN-1'],
            ['company_id' => $company->id, 'workshop_id' => $wsIn->id, 'name' => 'Dây chuyền In 1', 'capacity_per_hour' => 300, 'capacity_uom' => 'tấm', 'status' => 'active']
        );

        // Machines
        Machine::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'TRANG-01'],
            ['company_id' => $company->id, 'workshop_id' => $wsTrang->id, 'line_id' => $lineTrang1->id, 'machine_category_id' => $catTrang->id, 'name' => 'Máy tráng số 1', 'status' => 'active', 'theoretical_capacity' => 500, 'capacity_uom' => 'tấm/giờ']
        );
        Machine::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'TRANG-02'],
            ['company_id' => $company->id, 'workshop_id' => $wsTrang->id, 'line_id' => $lineTrang1->id, 'machine_category_id' => $catTrang->id, 'name' => 'Máy tráng số 2', 'status' => 'active', 'theoretical_capacity' => 500, 'capacity_uom' => 'tấm/giờ']
        );
        Machine::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'IN-01'],
            ['company_id' => $company->id, 'workshop_id' => $wsIn->id, 'line_id' => $lineIn1->id, 'machine_category_id' => $catIn->id, 'name' => 'Máy in số 1', 'status' => 'active', 'theoretical_capacity' => 300, 'capacity_uom' => 'tấm/giờ']
        );
        Machine::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'IN-02'],
            ['company_id' => $company->id, 'workshop_id' => $wsIn->id, 'line_id' => $lineIn1->id, 'machine_category_id' => $catIn->id, 'name' => 'Máy in số 2', 'status' => 'breakdown', 'theoretical_capacity' => 300, 'capacity_uom' => 'tấm/giờ']
        );
    }

    // ─── Users ────────────────────────────────────────────────────────────
    private function seedUsers(Company $company, Plant $nm4): void
    {
        $users = [
            ['email' => 'admin@thuanduc.com',      'display_name' => 'System Admin',           'role' => 'super_admin'],
            ['email' => 'ceo@thuanduc.com',         'display_name' => 'Nguyễn Văn Giám Đốc',   'role' => 'group_executive'],
            ['email' => 'nm4-manager@thuanduc.com', 'display_name' => 'Trần Văn Quản Lý NM4',  'role' => 'plant_manager'],
            ['email' => 'qc@thuanduc.com',          'display_name' => 'Lê Thị QC',             'role' => 'qc_inspector'],
            ['email' => 'kho@thuanduc.com',         'display_name' => 'Phạm Văn Kho',          'role' => 'warehouse_staff'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'display_name' => $data['display_name'],
                    'password'     => Hash::make('password'),
                    'status'       => 'active',
                ]
            );
            $user->syncRoles([$data['role']]);

            UserWorkContext::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_id'    => $company->id,
                    'plant_id'      => $nm4->id,
                    'role_name'     => $data['role'],
                    'context_label' => $nm4->name,
                ]
            );
        }
    }

    // ─── Master Data ──────────────────────────────────────────────────────
    private function seedMasterData(Company $company, Plant $nm4): void
    {
        // Product categories
        $catNVL = ProductCategory::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'NVL'],
            ['name' => 'Nguyên vật liệu']
        );
        $catBTP = ProductCategory::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'BTP'],
            ['name' => 'Bán thành phẩm']
        );
        $catTP = ProductCategory::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'TP'],
            ['name' => 'Thành phẩm']
        );

        // Raw materials
        $hatNhua = Product::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'NVL-HN-001'],
            ['category_id' => $catNVL->id, 'name' => 'Hạt nhựa PE trắng', 'product_type' => 'raw_material', 'base_uom' => 'kg', 'standard_cost' => 25000, 'is_active' => true]
        );
        $mucIn = Product::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'NVL-MUC-001'],
            ['category_id' => $catNVL->id, 'name' => 'Mực in xanh dương', 'product_type' => 'raw_material', 'base_uom' => 'lít', 'standard_cost' => 450000, 'is_active' => true]
        );

        // Semi-finished (BTP)
        $btpTrang = Product::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'BTP-TRANG-001'],
            ['category_id' => $catBTP->id, 'name' => 'Tấm tráng 120cm (chưa in)', 'product_type' => 'semi_finished', 'base_uom' => 'tấm', 'standard_cost' => 12000, 'is_active' => true]
        );

        // Finished goods
        $tpTuiPE = Product::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'TP-TUI-001'],
            ['category_id' => $catTP->id, 'name' => 'Túi PE in 2 màu 30×40cm', 'product_type' => 'finished_good', 'base_uom' => 'túi', 'standard_cost' => 850, 'list_price' => 1200, 'is_active' => true]
        );

        // BOM for finished product
        $bom = Bom::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'BOM-TP-TUI-001', 'version' => '1.0'],
            [
                'product_id'     => $tpTuiPE->id,
                'name'           => 'BOM Túi PE in 2 màu',
                'uom'            => 'túi',
                'quantity'       => 1000,
                'effective_from' => '2026-01-01',
                'is_active'      => true,
            ]
        );

        BomItem::firstOrCreate(
            ['bom_id' => $bom->id, 'material_id' => $hatNhua->id],
            ['company_id' => $company->id, 'sequence' => 10, 'quantity' => 2.5, 'uom' => 'kg', 'scrap_pct' => 2.0, 'operation_step' => 'Tráng']
        );
        BomItem::firstOrCreate(
            ['bom_id' => $bom->id, 'material_id' => $mucIn->id],
            ['company_id' => $company->id, 'sequence' => 20, 'quantity' => 0.05, 'uom' => 'lít', 'scrap_pct' => 5.0, 'operation_step' => 'In']
        );

        // Routing
        $routing = Routing::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'RT-TP-TUI-001', 'version' => '1.0'],
            ['product_id' => $tpTuiPE->id, 'name' => 'Quy trình Tráng → In → May → Đóng gói', 'effective_from' => '2026-01-01', 'is_active' => true]
        );

        $wsTrang = Workshop::where('plant_id', $nm4->id)->where('code', 'WS-TRANG')->first();
        $wsIn    = Workshop::where('plant_id', $nm4->id)->where('code', 'WS-IN')->first();
        $wsMay   = Workshop::where('plant_id', $nm4->id)->where('code', 'WS-MAY')->first();
        $catTrang = MachineCategory::where('company_id', $company->id)->where('code', 'TRANG')->first();
        $catIn    = MachineCategory::where('company_id', $company->id)->where('code', 'MAY_IN')->first();

        RoutingStep::firstOrCreate(
            ['routing_id' => $routing->id, 'step_number' => 10],
            ['company_id' => $company->id, 'name' => 'Tráng màng PE', 'workshop_id' => $wsTrang?->id, 'machine_category_id' => $catTrang?->id, 'std_time_minutes' => 0.12, 'output_product_id' => $btpTrang->id, 'yield_pct' => 98]
        );
        RoutingStep::firstOrCreate(
            ['routing_id' => $routing->id, 'step_number' => 20],
            ['company_id' => $company->id, 'name' => 'In 2 màu', 'workshop_id' => $wsIn?->id, 'machine_category_id' => $catIn?->id, 'std_time_minutes' => 0.20, 'yield_pct' => 97]
        );
        RoutingStep::firstOrCreate(
            ['routing_id' => $routing->id, 'step_number' => 30],
            ['company_id' => $company->id, 'name' => 'May và đóng gói', 'workshop_id' => $wsMay?->id, 'std_time_minutes' => 0.10, 'yield_pct' => 99]
        );

        // Warehouses
        $khoRM = Warehouse::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'KHO-NVL'],
            ['company_id' => $company->id, 'name' => 'Kho Nguyên liệu NM4', 'warehouse_type' => 'raw_material', 'is_active' => true]
        );
        $khoWIP = Warehouse::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'KHO-BTP'],
            ['company_id' => $company->id, 'name' => 'Kho Bán thành phẩm NM4', 'warehouse_type' => 'wip', 'is_active' => true]
        );
        $khoFG = Warehouse::firstOrCreate(
            ['plant_id' => $nm4->id, 'code' => 'KHO-TP'],
            ['company_id' => $company->id, 'name' => 'Kho Thành phẩm NM4', 'warehouse_type' => 'finished_goods', 'is_active' => true]
        );

        // Warehouse structure (zones + racks + bins)
        foreach ([$khoRM, $khoWIP, $khoFG] as $warehouse) {
            $zone = WarehouseZone::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'code' => 'ZONE-A'],
                ['name' => 'Khu A']
            );
            $rack = WarehouseRack::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'code' => 'RACK-A1'],
                ['zone_id' => $zone->id, 'name' => 'Kệ A1']
            );

            foreach (['A1-01', 'A1-02', 'A1-03', 'A1-04', 'A1-05'] as $binCode) {
                $bin = WarehouseBin::firstOrCreate(
                    ['warehouse_id' => $warehouse->id, 'code' => $binCode],
                    [
                        'company_id'  => $company->id,
                        'zone_id'     => $zone->id,
                        'rack_id'     => $rack->id,
                        'is_active'   => true,
                    ]
                );
                if (! $bin->qr_code) {
                    $bin->generateQrCode();
                }
            }
        }

        // Customers
        Customer::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'KH-001'],
            ['name' => 'Công ty TNHH XYZ Việt Nam', 'customer_type' => 'domestic', 'payment_days' => 30, 'status' => 'active']
        );
        Customer::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'KH-EX-001'],
            ['name' => 'ABC Trading Co. Ltd (Japan)', 'customer_type' => 'export', 'payment_days' => 45, 'status' => 'active']
        );

        // Suppliers
        Supplier::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'NCC-001'],
            ['name' => 'Công ty TNHH Hóa Chất Bình Dương', 'supplier_type' => 'material', 'lead_time_days' => 5, 'status' => 'active']
        );
    }
}
