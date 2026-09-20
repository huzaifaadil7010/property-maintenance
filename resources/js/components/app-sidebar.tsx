import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { getSidebarNavigation } from '@/lib/navigation';
import type { Auth } from '@/types';
import { home } from '@/wayfinder/routes';

export function AppSidebar() {
    const { auth, currentOrganization, subscriptionAccess } = usePage<{
        auth: Auth;
        currentOrganization: { uuid: string; name: string } | null;
        subscriptionAccess: { hasPanelAccess: boolean };
    }>().props;

    const { items: availableItems, label } = getSidebarNavigation(
        auth.role,
        currentOrganization,
    );
    const items = subscriptionAccess.hasPanelAccess ? availableItems : [];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader className="p-3 pt-4">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="h-14 rounded-2xl px-2"
                        >
                            <Link href={home()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={items} label={label} />
            </SidebarContent>

            <SidebarFooter className="border-t border-sidebar-border/70 p-3">
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
