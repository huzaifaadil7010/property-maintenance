<?php

namespace Database\Seeders\Support;

use App\Enums\ActivityEventEnum;
use App\Enums\MaintenanceCategory;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceRequestStatus;
use App\Enums\PropertyType;
use App\Enums\TechnicianSpecialty;
use App\Enums\UnitStatus;

class DemoDataset
{
    public const string ORGANIZATION_SLUG = 'northstar-property-management';

    public const string OWNER_EMAIL = 'owner@northstar.test';

    public const string FEATURED_RESIDENT_EMAIL = 'ethan.parker@northstar.test';

    public const string FEATURED_TECHNICIAN_EMAIL = 'marcus.reed@northstar.test';

    public const string PASSWORD = 'password';

    public static function organization(): array
    {
        return [
            'uuid' => '4ab04f98-6325-49af-9231-737a12ef47ea',
            'name' => 'Northstar Property Management',
            'slug' => self::ORGANIZATION_SLUG,
            'email' => 'hello@northstar.test',
            'phone' => '+1 512-555-0100',
        ];
    }

    public static function owner(): array
    {
        return [
            'name' => 'Daniel Foster',
            'email' => self::OWNER_EMAIL,
            'phone' => '+1 512-555-0101',
        ];
    }

    public static function properties(): array
    {
        return [
            [
                'name' => 'Cedar Grove Apartments',
                'type' => PropertyType::APARTMENT,
                'address' => '1840 Westbridge Avenue',
                'city' => 'Austin',
                'units' => [
                    ['name' => '101', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '102', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '103', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '201', 'floor' => '2', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '202', 'floor' => '2', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '203', 'floor' => '2', 'status' => UnitStatus::VACANT],
                ],
            ],
            [
                'name' => 'Riverside Commons',
                'type' => PropertyType::APARTMENT,
                'address' => '720 Riverside Commons Drive',
                'city' => 'Austin',
                'units' => [
                    ['name' => 'A-101', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => 'A-102', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => 'A-201', 'floor' => '2', 'status' => UnitStatus::OCCUPIED],
                    ['name' => 'A-202', 'floor' => '2', 'status' => UnitStatus::OCCUPIED],
                    ['name' => 'B-101', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => 'B-102', 'floor' => '1', 'status' => UnitStatus::UNDER_MAINTENANCE],
                ],
            ],
            [
                'name' => 'Westfield Residences',
                'type' => PropertyType::APARTMENT,
                'address' => '315 Westfield Lane',
                'city' => 'Austin',
                'units' => [
                    ['name' => '1A', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '1B', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '2A', 'floor' => '2', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '2B', 'floor' => '2', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '3A', 'floor' => '3', 'status' => UnitStatus::VACANT],
                    ['name' => '3B', 'floor' => '3', 'status' => UnitStatus::UNDER_MAINTENANCE],
                ],
            ],
            [
                'name' => 'Oakline Heights',
                'type' => PropertyType::APARTMENT,
                'address' => '906 Oakline Terrace',
                'city' => 'Austin',
                'units' => [
                    ['name' => '101', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '102', 'floor' => '1', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '201', 'floor' => '2', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '202', 'floor' => '2', 'status' => UnitStatus::OCCUPIED],
                    ['name' => '301', 'floor' => '3', 'status' => UnitStatus::VACANT],
                    ['name' => '302', 'floor' => '3', 'status' => UnitStatus::VACANT],
                ],
            ],
        ];
    }

    public static function residents(): array
    {
        return [
            ['name' => 'Ethan Parker', 'email' => self::FEATURED_RESIDENT_EMAIL, 'phone' => '+1 512-555-0110', 'property' => 'Cedar Grove Apartments', 'unit' => '101', 'move_in_days_ago' => 55],
            ['name' => 'Liam Bennett', 'email' => 'liam.bennett@northstar.test', 'phone' => '+1 512-555-0111', 'property' => 'Cedar Grove Apartments', 'unit' => '102', 'move_in_days_ago' => 54],
            ['name' => 'Noah Mitchell', 'email' => 'noah.mitchell@northstar.test', 'phone' => '+1 512-555-0112', 'property' => 'Cedar Grove Apartments', 'unit' => '103', 'move_in_days_ago' => 53],
            ['name' => 'Mason Turner', 'email' => 'mason.turner@northstar.test', 'phone' => '+1 512-555-0113', 'property' => 'Cedar Grove Apartments', 'unit' => '201', 'move_in_days_ago' => 52],
            ['name' => 'Oliver Hayes', 'email' => 'oliver.hayes@northstar.test', 'phone' => '+1 512-555-0114', 'property' => 'Cedar Grove Apartments', 'unit' => '202', 'move_in_days_ago' => 51],
            ['name' => 'James Coleman', 'email' => 'james.coleman@northstar.test', 'phone' => '+1 512-555-0115', 'property' => 'Riverside Commons', 'unit' => 'A-101', 'move_in_days_ago' => 50],
            ['name' => 'Lucas Ward', 'email' => 'lucas.ward@northstar.test', 'phone' => '+1 512-555-0116', 'property' => 'Riverside Commons', 'unit' => 'A-102', 'move_in_days_ago' => 49],
            ['name' => 'Benjamin Scott', 'email' => 'benjamin.scott@northstar.test', 'phone' => '+1 512-555-0117', 'property' => 'Riverside Commons', 'unit' => 'A-201', 'move_in_days_ago' => 48],
            ['name' => 'Henry Adams', 'email' => 'henry.adams@northstar.test', 'phone' => '+1 512-555-0118', 'property' => 'Riverside Commons', 'unit' => 'A-202', 'move_in_days_ago' => 47],
            ['name' => 'Alexander Price', 'email' => 'alexander.price@northstar.test', 'phone' => '+1 512-555-0119', 'property' => 'Riverside Commons', 'unit' => 'B-101', 'move_in_days_ago' => 46],
            ['name' => 'William Cooper', 'email' => 'william.cooper@northstar.test', 'phone' => '+1 512-555-0120', 'property' => 'Westfield Residences', 'unit' => '1A', 'move_in_days_ago' => 45],
            ['name' => 'Dylan Richardson', 'email' => 'dylan.richardson@northstar.test', 'phone' => '+1 512-555-0121', 'property' => 'Westfield Residences', 'unit' => '1B', 'move_in_days_ago' => 44],
            ['name' => 'Jack Sullivan', 'email' => 'jack.sullivan@northstar.test', 'phone' => '+1 512-555-0122', 'property' => 'Westfield Residences', 'unit' => '2A', 'move_in_days_ago' => 43],
            ['name' => 'Michael Torres', 'email' => 'michael.torres@northstar.test', 'phone' => '+1 512-555-0123', 'property' => 'Westfield Residences', 'unit' => '2B', 'move_in_days_ago' => 42],
            ['name' => 'Samuel Wright', 'email' => 'samuel.wright@northstar.test', 'phone' => '+1 512-555-0124', 'property' => 'Oakline Heights', 'unit' => '101', 'move_in_days_ago' => 41],
            ['name' => 'Joseph Miller', 'email' => 'joseph.miller@northstar.test', 'phone' => '+1 512-555-0125', 'property' => 'Oakline Heights', 'unit' => '102', 'move_in_days_ago' => 40],
            ['name' => 'David Brooks', 'email' => 'david.brooks@northstar.test', 'phone' => '+1 512-555-0126', 'property' => 'Oakline Heights', 'unit' => '201', 'move_in_days_ago' => 39],
            ['name' => 'Matthew Collins', 'email' => 'matthew.collins@northstar.test', 'phone' => '+1 512-555-0127', 'property' => 'Oakline Heights', 'unit' => '202', 'move_in_days_ago' => 38],
        ];
    }

    public static function technicians(): array
    {
        return [
            ['name' => 'Marcus Reed', 'email' => self::FEATURED_TECHNICIAN_EMAIL, 'phone' => '+1 512-555-0130', 'specialty' => TechnicianSpecialty::PLUMBING, 'is_available' => true],
            ['name' => 'Evan Carter', 'email' => 'evan.carter@northstar.test', 'phone' => '+1 512-555-0131', 'specialty' => TechnicianSpecialty::ELECTRICAL, 'is_available' => true],
            ['name' => 'Caleb Morgan', 'email' => 'caleb.morgan@northstar.test', 'phone' => '+1 512-555-0132', 'specialty' => TechnicianSpecialty::AIR_CONDITIONING, 'is_available' => true],
            ['name' => 'Nathan Brooks', 'email' => 'nathan.brooks@northstar.test', 'phone' => '+1 512-555-0133', 'specialty' => TechnicianSpecialty::CARPENTRY, 'is_available' => false],
            ['name' => 'Owen Hayes', 'email' => 'owen.hayes@northstar.test', 'phone' => '+1 512-555-0134', 'specialty' => TechnicianSpecialty::GENERAL_MAINTENANCE, 'is_available' => true],
        ];
    }

    public static function maintenanceRequests(): array
    {
        return array_map(
            fn (array $request): array => [
                ...$request,
                'transitions' => self::transitionsFor($request),
            ],
            self::maintenanceRequestDefinitions(),
        );
    }

    private static function maintenanceRequestDefinitions(): array
    {
        return [
            self::request('Active leak beneath kitchen sink', 'Water is dripping steadily from the P-trap beneath the kitchen sink and has started soaking the cabinet base.', MaintenanceCategory::PLUMBING, MaintenancePriority::URGENT, MaintenanceRequestStatus::OPEN, self::FEATURED_RESIDENT_EMAIL, null, 2),
            self::request('Hallway ceiling light flickers', 'The hallway ceiling fixture flickers every few minutes even after the bulb was replaced.', MaintenanceCategory::ELECTRICAL, MaintenancePriority::LOW, MaintenanceRequestStatus::OPEN, 'liam.bennett@northstar.test', null, 5),
            self::request('Wardrobe sliding door is off track', 'The bedroom wardrobe door has come off its lower track and is difficult to move safely.', MaintenanceCategory::CARPENTRY, MaintenancePriority::NORMAL, MaintenanceRequestStatus::OPEN, 'noah.mitchell@northstar.test', null, 7),
            self::request('Damp patch spreading near ceiling', 'A damp patch has appeared near the living-room ceiling and has grown after recent rain.', MaintenanceCategory::GENERAL, MaintenancePriority::HIGH, MaintenanceRequestStatus::OPEN, 'mason.turner@northstar.test', null, 3),
            self::request('Low water pressure in shower', 'The shower pressure has dropped significantly while the other fixtures still have normal flow.', MaintenanceCategory::PLUMBING, MaintenancePriority::NORMAL, MaintenanceRequestStatus::ASSIGNED, self::FEATURED_RESIDENT_EMAIL, self::FEATURED_TECHNICIAN_EMAIL, 8),
            self::request('Bedroom outlets have no power', 'All outlets in the primary bedroom stopped working while the lights and adjacent rooms remain operational.', MaintenanceCategory::ELECTRICAL, MaintenancePriority::HIGH, MaintenanceRequestStatus::ASSIGNED, 'oliver.hayes@northstar.test', 'evan.carter@northstar.test', 10),
            self::request('Split AC indoor unit is dripping', 'Condensation is dripping from the living-room air conditioner and leaving a wet streak on the wall.', MaintenanceCategory::AIR_CONDITIONING, MaintenancePriority::NORMAL, MaintenanceRequestStatus::ASSIGNED, 'james.coleman@northstar.test', 'caleb.morgan@northstar.test', 6),
            self::request('Bedroom window latch is broken', 'The window latch no longer catches, so the bedroom window cannot be secured.', MaintenanceCategory::CARPENTRY, MaintenancePriority::HIGH, MaintenanceRequestStatus::ASSIGNED, 'lucas.ward@northstar.test', 'owen.hayes@northstar.test', 12),
            self::request('Water heater relief valve is dripping', 'The relief valve is releasing a slow but continuous drip into the utility closet drain pan.', MaintenanceCategory::PLUMBING, MaintenancePriority::HIGH, MaintenanceRequestStatus::IN_PROGRESS, 'benjamin.scott@northstar.test', self::FEATURED_TECHNICIAN_EMAIL, 14),
            self::request('Kitchen breaker keeps tripping', 'The kitchen breaker trips repeatedly when the refrigerator and countertop appliances are operating normally.', MaintenanceCategory::ELECTRICAL, MaintenancePriority::URGENT, MaintenanceRequestStatus::IN_PROGRESS, 'henry.adams@northstar.test', 'evan.carter@northstar.test', 9, administrativeStart: true),
            self::request('Living room AC is blowing warm air', 'The air conditioner runs continuously but no longer cools the living room.', MaintenanceCategory::AIR_CONDITIONING, MaintenancePriority::HIGH, MaintenanceRequestStatus::IN_PROGRESS, self::FEATURED_RESIDENT_EMAIL, 'caleb.morgan@northstar.test', 11),
            self::request('Stairwell handrail is loose', 'The second-floor handrail moves away from the wall when pressure is applied.', MaintenanceCategory::GENERAL, MaintenancePriority::URGENT, MaintenanceRequestStatus::IN_PROGRESS, 'alexander.price@northstar.test', 'owen.hayes@northstar.test', 15),
            self::request('Kitchen sink drain repaired', 'The kitchen sink drained slowly and leaked at the lower connection.', MaintenanceCategory::PLUMBING, MaintenancePriority::NORMAL, MaintenanceRequestStatus::COMPLETED, 'william.cooper@northstar.test', self::FEATURED_TECHNICIAN_EMAIL, 18, 'Replaced the worn P-trap seals, tightened both connections, and tested the drain under full flow.', '145.00'),
            self::request('Bedroom outlet replaced', 'One bedroom outlet was loose in the wall and showed heat discoloration around the upper receptacle.', MaintenanceCategory::ELECTRICAL, MaintenancePriority::HIGH, MaintenanceRequestStatus::COMPLETED, 'dylan.richardson@northstar.test', 'evan.carter@northstar.test', 20, 'Replaced the damaged receptacle, secured the electrical box, and verified voltage and grounding.', '185.00'),
            self::request('AC thermostat replaced', 'The thermostat displayed an incorrect temperature and cycled the air conditioner unpredictably.', MaintenanceCategory::AIR_CONDITIONING, MaintenancePriority::NORMAL, MaintenanceRequestStatus::COMPLETED, 'jack.sullivan@northstar.test', 'caleb.morgan@northstar.test', 16, 'Installed and calibrated a replacement thermostat, then confirmed stable cooling through a complete cycle.', '220.00'),
            self::request('Loose kitchen cabinet door repaired', 'An upper cabinet door was hanging from one hinge and could not close evenly.', MaintenanceCategory::CARPENTRY, MaintenancePriority::LOW, MaintenanceRequestStatus::COMPLETED, self::FEATURED_RESIDENT_EMAIL, 'nathan.brooks@northstar.test', 22, 'Replaced both hinges, aligned the door, and adjusted the soft-close mechanism.', '95.00'),
            self::request('Shower drain blockage cleared', 'The shower drained slowly and overflowed onto the bathroom floor during normal use.', MaintenanceCategory::PLUMBING, MaintenancePriority::HIGH, MaintenanceRequestStatus::CLOSED, 'michael.torres@northstar.test', self::FEATURED_TECHNICIAN_EMAIL, 35, 'Removed the obstruction, flushed the branch line, and confirmed normal drainage.', '165.00'),
            self::request('Bathroom exhaust fan repaired', 'The exhaust fan rattled loudly and provided very little ventilation.', MaintenanceCategory::GENERAL, MaintenancePriority::NORMAL, MaintenanceRequestStatus::CLOSED, self::FEATURED_RESIDENT_EMAIL, 'owen.hayes@northstar.test', 30, 'Cleaned the housing, replaced the worn motor assembly, and confirmed quiet airflow.', '210.00'),
            self::request('Bedroom door alignment corrected', 'The bedroom door rubbed against the frame and would not latch without force.', MaintenanceCategory::CARPENTRY, MaintenancePriority::LOW, MaintenanceRequestStatus::CLOSED, 'joseph.miller@northstar.test', 'nathan.brooks@northstar.test', 26, 'Reset the upper hinge, planed the binding edge, and aligned the latch plate.', '130.00'),
            self::request('Range hood power restored', 'The kitchen range hood stopped responding and neither the fan nor light would switch on.', MaintenanceCategory::ELECTRICAL, MaintenancePriority::NORMAL, MaintenanceRequestStatus::CLOSED, 'david.brooks@northstar.test', 'evan.carter@northstar.test', 24, 'Replaced a failed internal connection and verified all fan speeds and lighting controls.', '175.00'),
            self::request('Balcony drain cleared', 'Standing water remained on the balcony after rain because the floor drain was blocked.', MaintenanceCategory::GENERAL, MaintenancePriority::NORMAL, MaintenanceRequestStatus::CLOSED, 'matthew.collins@northstar.test', 'owen.hayes@northstar.test', 40, 'Cleared debris from the drain and downpipe, then tested flow with several gallons of water.', '120.00'),
            self::request('Recurring leak around toilet base', 'Water returned around the toilet base one day after the original seal replacement.', MaintenanceCategory::PLUMBING, MaintenancePriority::URGENT, MaintenanceRequestStatus::REOPENED, self::FEATURED_RESIDENT_EMAIL, self::FEATURED_TECHNICIAN_EMAIL, 28, 'Replaced the wax ring, reset the toilet, and verified a dry seal before leaving.', '240.00'),
            self::request('AC condensation returned after service', 'The wall beneath the indoor unit became damp again after the initial drain-line service.', MaintenanceCategory::AIR_CONDITIONING, MaintenancePriority::HIGH, MaintenanceRequestStatus::REOPENED, 'james.coleman@northstar.test', 'caleb.morgan@northstar.test', 19, 'Flushed the condensate line and cleaned the indoor drain pan.', '195.00'),
            self::request('Front door lock is sticking again', 'The entry lock has started sticking again and the key requires excessive force to turn.', MaintenanceCategory::CARPENTRY, MaintenancePriority::HIGH, MaintenanceRequestStatus::REOPENED, 'lucas.ward@northstar.test', 'nathan.brooks@northstar.test', 32, 'Realigned the strike plate, lubricated the cylinder, and tested the lock repeatedly.', '150.00'),
        ];
    }

    private static function request(
        string $title,
        string $description,
        MaintenanceCategory $category,
        MaintenancePriority $priority,
        MaintenanceRequestStatus $status,
        string $residentEmail,
        ?string $technicianEmail,
        int $createdDaysAgo,
        ?string $completionNotes = null,
        ?string $actualCost = null,
        bool $administrativeStart = false,
    ): array {
        return compact(
            'title',
            'description',
            'category',
            'priority',
            'status',
            'residentEmail',
            'technicianEmail',
            'createdDaysAgo',
            'completionNotes',
            'actualCost',
            'administrativeStart',
        );
    }

    private static function transitionsFor(array $request): array
    {
        $transitions = [[
            'status' => MaintenanceRequestStatus::OPEN,
            'actor' => 'resident',
            'hours_after' => 0,
            'notes' => 'Resident submitted the issue with supporting photo evidence.',
            'activity_event' => ActivityEventEnum::MAINTENANCE_REQUEST_CREATED,
        ]];

        if ($request['status'] === MaintenanceRequestStatus::OPEN) {
            return $transitions;
        }

        $transitions[] = [
            'status' => MaintenanceRequestStatus::ASSIGNED,
            'actor' => 'owner',
            'hours_after' => 4,
            'notes' => 'Owner reviewed the request and assigned the appropriate technician.',
            'activity_event' => ActivityEventEnum::MAINTENANCE_REQUEST_TECHNICIAN_ASSIGNED,
        ];

        if ($request['status'] === MaintenanceRequestStatus::ASSIGNED) {
            return $transitions;
        }

        $transitions[] = [
            'status' => MaintenanceRequestStatus::IN_PROGRESS,
            'actor' => $request['administrativeStart'] ? 'owner' : 'technician',
            'hours_after' => 12,
            'notes' => $request['administrativeStart']
                ? 'Owner updated the request after confirming that diagnostic work had started.'
                : 'Technician arrived on site and started diagnostic and repair work.',
            'activity_event' => $request['administrativeStart']
                ? ActivityEventEnum::MAINTENANCE_REQUEST_STATUS_UPDATED
                : ActivityEventEnum::MAINTENANCE_REQUEST_WORK_STARTED,
        ];

        if ($request['status'] === MaintenanceRequestStatus::IN_PROGRESS) {
            return $transitions;
        }

        $transitions[] = [
            'status' => MaintenanceRequestStatus::COMPLETED,
            'actor' => 'technician',
            'hours_after' => 26,
            'notes' => $request['completionNotes'],
            'activity_event' => ActivityEventEnum::MAINTENANCE_REQUEST_WORK_COMPLETED,
        ];

        if ($request['status'] === MaintenanceRequestStatus::COMPLETED) {
            return $transitions;
        }

        $transitions[] = [
            'status' => $request['status'],
            'actor' => 'resident',
            'hours_after' => 36,
            'notes' => $request['status'] === MaintenanceRequestStatus::CLOSED
                ? 'Resident inspected the completed work and confirmed the issue was resolved.'
                : 'Resident reported that the issue returned after the initial repair.',
            'activity_event' => $request['status'] === MaintenanceRequestStatus::CLOSED
                ? ActivityEventEnum::MAINTENANCE_REQUEST_RESOLUTION_CONFIRMED
                : ActivityEventEnum::MAINTENANCE_REQUEST_REOPENED,
        ];

        return $transitions;
    }
}
