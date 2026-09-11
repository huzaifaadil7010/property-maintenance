import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-9 items-center justify-center rounded-xl bg-sidebar-primary text-sidebar-primary-foreground shadow-[0_5px_14px_rgba(20,86,61,0.2)]">
                <AppLogoIcon className="size-5 text-white" />
            </div>
            <div className="ml-1.5 grid flex-1 text-left text-sm">
                <span className="truncate leading-tight font-semibold tracking-[-0.01em]">
                    Property Maintenance
                </span>
                <span className="truncate text-[10px] font-medium tracking-[0.08em] text-sidebar-foreground/55 uppercase">
                    Operations
                </span>
            </div>
        </>
    );
}
