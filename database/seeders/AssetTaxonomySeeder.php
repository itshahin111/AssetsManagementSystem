<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            $this->computerEquipmentCategory(),
            $this->furnitureCategory(),
            $this->electricalCategory(),
            $this->accessoryCategory(),
            $this->networkCategory(),
            $this->otherCategory(),
        ];

        foreach ($categories as $catData) {
            $types = $catData['types'];
            unset($catData['types']);

            $category = AssetCategory::firstOrCreate(
                ['code' => $catData['code']],
                $catData
            );

            foreach ($types as $typeData) {
                AssetType::firstOrCreate(
                    [
                        'asset_category_id' => $category->id,
                        'name'              => $typeData['name'],
                    ],
                    $typeData
                );
            }
        }
    }

    private function computerEquipmentCategory(): array
    {
        return [
            'name'        => 'Computer Equipment',
            'code'        => 'COMP',
            'description' => 'Desktops, laptops, monitors, printers, servers, and related IT hardware.',
            'status'      => 'active',
            'types'       => [
                ['name' => 'Desktop Computer', 'code' => 'COMP-DSK', 'tracking_type' => 'individual', 'description' => 'Standard desktop workstation.'],
                ['name' => 'Laptop',            'code' => 'COMP-LAP', 'tracking_type' => 'individual', 'description' => 'Portable laptop computer.'],
                ['name' => 'Monitor',           'code' => 'COMP-MON', 'tracking_type' => 'individual', 'description' => 'External display monitor.'],
                ['name' => 'Printer',           'code' => 'COMP-PRN', 'tracking_type' => 'individual', 'description' => 'Laser or inkjet printer.'],
                ['name' => 'Server',            'code' => 'COMP-SRV', 'tracking_type' => 'individual', 'description' => 'Rack or tower server.'],
                ['name' => 'UPS',               'code' => 'COMP-UPS', 'tracking_type' => 'quantity',   'description' => 'Uninterruptible power supply unit.'],
            ],
        ];
    }
    private function furnitureCategory(): array
    {
        return [
            'name'        => 'Furniture',
            'code'        => 'FURN',
            'description' => 'Office furniture including chairs, desks, cabinets, and storage.',
            'status'      => 'active',
            'types'       => [
                ['name' => 'Office Chair',  'code' => 'FURN-CHR', 'tracking_type' => 'quantity', 'description' => 'Ergonomic or standard office chair.'],
                ['name' => 'Office Desk',   'code' => 'FURN-DSK', 'tracking_type' => 'quantity', 'description' => 'Standard or standing desk.'],
                ['name' => 'Filing Cabinet', 'code' => 'FURN-CAB', 'tracking_type' => 'quantity', 'description' => 'Metal filing or storage cabinet.'],
                ['name' => 'Whiteboard',    'code' => 'FURN-WHT', 'tracking_type' => 'quantity', 'description' => 'Whiteboard or chalkboard.'],
            ],
        ];
    }

    private function electricalCategory(): array
    {
        return [
            'name'        => 'Electrical',
            'code'        => 'ELEC',
            'description' => 'Electrical accessories and consumable hardware.',
            'status'      => 'active',
            'types'       => [
                ['name' => 'Extension Cord', 'code' => 'ELEC-EXT', 'tracking_type' => 'quantity', 'description' => 'Power extension cord.'],
                ['name' => 'Power Strip',    'code' => 'ELEC-STR', 'tracking_type' => 'quantity', 'description' => 'Surge-protected power strip.'],
                ['name' => 'Cable Bundle',   'code' => 'ELEC-CBL', 'tracking_type' => 'quantity', 'description' => 'Bundled cables (USB, HDMI, network).'],
            ],
        ];
    }

    private function accessoryCategory(): array
    {
        return [
            'name'        => 'Accessory',
            'code'        => 'ACCS',
            'description' => 'Peripheral and accessory items that accompany primary equipment.',
            'status'      => 'active',
            'types'       => [
                ['name' => 'Keyboard', 'code' => 'ACCS-KBD', 'tracking_type' => 'quantity', 'description' => 'USB or wireless keyboard.'],
                ['name' => 'Mouse',    'code' => 'ACCS-MSE', 'tracking_type' => 'quantity', 'description' => 'USB or wireless mouse.'],
                ['name' => 'Headset',  'code' => 'ACCS-HDP', 'tracking_type' => 'quantity', 'description' => 'Audio headset or earphones.'],
                ['name' => 'USB Hub',  'code' => 'ACCS-USB', 'tracking_type' => 'quantity', 'description' => 'USB port expansion hub.'],
            ],
        ];
    }

    private function networkCategory(): array
    {
        return [
            'name'        => 'Network',
            'code'        => 'NETW',
            'description' => 'Networking hardware including switches, routers, and access points.',
            'status'      => 'active',
            'types'       => [
                ['name' => 'Network Switch',     'code' => 'NETW-SWT', 'tracking_type' => 'individual', 'description' => 'Managed or unmanaged ethernet switch.'],
                ['name' => 'WiFi Access Point', 'code' => 'NETW-AP',  'tracking_type' => 'individual', 'description' => 'Wireless access point.'],
                ['name' => 'Network Router',    'code' => 'NETW-RTR', 'tracking_type' => 'individual', 'description' => 'Network router or gateway.'],
                ['name' => 'Network Cable Box', 'code' => 'NETW-CBL', 'tracking_type' => 'quantity',   'description' => 'Spool or box of ethernet cable (Cat5e, Cat6).'],
            ],
        ];
    }

    private function otherCategory(): array
    {
        return [
            'name'        => 'Other',
            'code'        => 'OTHR',
            'description' => 'Miscellaneous items that do not fit other categories.',
            'status'      => 'active',
            'types'       => [
                ['name' => 'Projector',   'code' => 'OTHR-PRJ', 'tracking_type' => 'individual', 'description' => 'Data projector or overhead projector.'],
                ['name' => 'TV Display',   'code' => 'OTHR-TV',  'tracking_type' => 'individual', 'description' => 'Television or large-format display.'],
                ['name' => 'Camera',       'code' => 'OTHR-CAM', 'tracking_type' => 'individual', 'description' => 'Document camera or webcam.'],
                ['name' => 'Miscellaneous','code' => 'OTHR-MSC', 'tracking_type' => 'quantity',   'description' => 'Other uncategorised items.'],
            ],
        ];
    }
}
