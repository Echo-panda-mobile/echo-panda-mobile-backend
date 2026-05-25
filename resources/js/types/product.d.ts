export interface Product {
    id: number;
    name: string;
    description: string | null;
    price: number;
    quantity: number;
    sku: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

