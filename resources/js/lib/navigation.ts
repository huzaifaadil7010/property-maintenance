import { Building2, ClipboardList, DoorOpen, LayoutGrid, Users, Wrench } from 'lucide-react';
import type { NavItem } from '@/types';
import { UserRole } from '@/wayfinder/App/Enums/UserRole';
import MaintenanceRequestsController from '@/wayfinder/App/Http/Controllers/Organization/MaintenanceRequestsController';
import PropertiesController from '@/wayfinder/App/Http/Controllers/Organization/PropertiesController';
import ResidentsController from '@/wayfinder/App/Http/Controllers/Organization/ResidentsController';
import TechniciansController from '@/wayfinder/App/Http/Controllers/Organization/TechniciansController';
import UnitsController from '@/wayfinder/App/Http/Controllers/Organization/UnitsController';
import { dashboard as appDashboard } from '@/wayfinder/routes';
import { dashboard as organizationDashboard } from '@/wayfinder/routes/organization';
import {
    dashboard as residentDashboard,
    maintenanceRequests as residentMaintenanceRequests,
} from '@/wayfinder/routes/resident';

type CurrentOrganization = {
    uuid: string;
} | null | undefined;

type UserRoleValue = string | null | undefined;

export function getDashboardHref(
    role: UserRoleValue,
    currentOrganization: CurrentOrganization,
): string {
    if (role === UserRole.RESIDENT) {
        return residentDashboard().url;
    }

    if (currentOrganization) {
        return organizationDashboard(currentOrganization.uuid).url;
    }

    return appDashboard().url;
}

export function getSidebarNavigation(
    role: UserRoleValue,
    currentOrganization: CurrentOrganization,
): {
    label: string;
    items: NavItem[];
    dashboardHref: string;
} {
    const dashboardHref = getDashboardHref(role, currentOrganization);

    if (role === UserRole.RESIDENT) {
        return {
            label: 'Resident',
            dashboardHref,
            items: [
                {
                    title: 'Dashboard',
                    href: dashboardHref,
                    icon: LayoutGrid,
                },
                {
                    title: 'My Requests',
                    href: residentMaintenanceRequests().url,
                    icon: ClipboardList,
                },
            ],
        };
    }

    if (!currentOrganization) {
        return {
            label: 'Platform',
            dashboardHref,
            items: [
                {
                    title: 'Dashboard',
                    href: dashboardHref,
                    icon: LayoutGrid,
                },
            ],
        };
    }

    return {
        label: 'Organization',
        dashboardHref,
        items: [
            {
                title: 'Dashboard',
                href: dashboardHref,
                icon: LayoutGrid,
            },
            {
                title: 'Properties',
                href: PropertiesController.index(currentOrganization.uuid).url,
                icon: Building2,
            },
            {
                title: 'Units',
                href: UnitsController.index(currentOrganization.uuid).url,
                icon: DoorOpen,
            },
            {
                title: 'Residents',
                href: ResidentsController.index(currentOrganization.uuid).url,
                icon: Users,
            },
            {
                title: 'Technicians',
                href: TechniciansController.index(currentOrganization.uuid).url,
                icon: Wrench,
            },
            {
                title: 'Maintenance Requests',
                href: MaintenanceRequestsController.index(currentOrganization.uuid).url,
                icon: ClipboardList,
            },
        ],
    };
}
