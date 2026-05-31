export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type AppVariant = 'header' | 'sidebar';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};

export type AppNotification = {
    id: number;
    message: string;
    sent_at: string | null;
    sent_at_human: string;
};

export type AppNotifications = {
    items: AppNotification[];
    count: number;
};
