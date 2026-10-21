export type DriverArea = {
  id?: number;
  region: string;
  region_code: string | null;
  province: string;
  province_code: string | null;
  city: string;
  city_code: string | null;
  barangay: string | null;
  barangay_code: string | null;
};

export type Driver = {
  id: number;
  full_name: string;
  contact_number: string;
  photo: string | null;
  photo_url: string | null;
  is_active: boolean;
  last_login_at: string | null;
  created_at: string;
  areas: DriverArea[];
  active_deliveries_count: number;
};

export type PaginatedDrivers = {
  data: Driver[];
  current_page: number;
  last_page: number;
  total: number;
  prev_page_url: string | null;
  next_page_url: string | null;
};
