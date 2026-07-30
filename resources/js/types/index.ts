export interface User {
    id: number;
    office_id: number | null;
    name: string;
    email: string;
    office?: {
        id: number;
        name: string;
        slug: string;
    } | null;
}

export interface Customer {
    id: number;
    office_id: number | null;
    name: string;
    phone: string;
    email: string | null;
    pix_key: string;
    monthly_value: number;
    monthly_value_formatted: string;
    due_day: number;
    status: 'active' | 'inactive';
    status_label: string;
}

export interface DashboardStats {
    active_customers: number;
    inactive_customers: number;
    charges_sent_today: number;
    charges_pending: number;
    charges_sent_this_month: number;
    charges_failed: number;
    charges_paid_this_month: number;
}

export interface NotificationItem {
    id: number;
    charge_id: number;
    customer_name: string | null;
    amount: number | null;
    channel: string;
    status: string;
    status_label: string;
    message: string;
    provider: string;
    error_message: string | null;
    duration_ms: number;
    attempt: number;
    sent_at: string | null;
    whatsapp_response: string | null;
}

export interface Settings {
    id: number;
    office_id: number | null;
    company_name: string;
    default_message: string;
    whatsapp_provider: string;
    whatsapp_provider_label: string;
    timezone: string;
}

export interface CustomerForm {
    name: string;
    phone: string;
    email: string;
    pix_key: string;
    monthly_value: number | string;
    due_day: number | string;
    status: 'active' | 'inactive';
}
